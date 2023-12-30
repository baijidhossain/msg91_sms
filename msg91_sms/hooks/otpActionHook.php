<?php

$class = new MSG91Sms();
$template = $class->getTemplateDetails('ClientAreaRegister_clientarea');
	

if(isset($_POST['action']) && $_POST['action'] === 'resendOtp'):
	
	//Update the OTP and Request.
	$otp_id = $_POST['otp_id'];
	$otp_req = $class->getOtp( $otp_id );
	
	//Regenerate the OTP and Request and Save
	$new_otp = $class->randomString(6 , true);
	$new_request = $class->randomString(20);

	//Client Info
	$client_query = $class->getClientDetailsBy( $otp_req['relid'] );
	$client = mysql_fetch_array( $client_query , MYSQL_ASSOC);
	
	$message = str_replace(['{firstname}' , '{lastname}' , '{otp}' , '{request}'] , [$client['firstname'], $client['lastname'] , $new_otp , $new_request] , $template['template']);
	
	update_query( "mod_msg91sms_otp", array("otp" => $new_otp, "request" => $new_request, "text" => $message,"status" => "0", "datetime" => date("Y-m-d H:i:s")), array( "id" => $otp_id ) );
	$class->addLog("OTP Updated");

	//Send The OTP:
	$class->setUserid( $client['id'] );
	$class->setGsmnumber( $client['gsmnumber'] );
	$class->setMessage( $message );
	$class->setCountryCode( $client['country'] );
	
	$class->send();
	
	update_query( "mod_msg91sms_otp", array( "status" => "1"), array( "id" => $otp_id ) );
    $class->addLog("OTP sent.");
		
	echo json_encode(['code' => 1 , 'message' => 'OTP ' . $new_otp . ' with Request no. ' . $new_request . ' was resent on phone no. ' . $client['gsmnumber'] , 'data' => []]);
	exit;
endif;

if(isset($_POST['action']) && $_POST['action'] === 'otpVerification'):
		
	//echo json_encode(['id' => $_POST['otp_id']]);
	$user_otp = $_POST['otp'];
	$otp_id = $_POST['otp_id'];
	//Lets Comprate and Validate the OTP Provided by the User,
	//Once its set then, update the OTP Status
	//$class = new MSG91Sms();
	$otp_data = $class->getOtp( $otp_id );	
	
	
	if($user_otp === $otp_data['otp']):
		
		//When OTP is Verified, update the database and set status to 2.
		update_query( "mod_msg91sms_otp", array( "status" => "2"), array( "id" => $otp_id ) );

		echo json_encode(['code' => 1 , 'message' => 'Your phone number has been verified.' , 'data' => []]);
		exit;
	endif;	
	
	echo json_encode(['code' => 0 , 'message' => 'OTP you have entered didn\'t match' , 'data' => []]);
	exit;
	
endif;


//Action Hooks.

add_hook('AdminAreaClientSummaryPage', 1, function($vars) {
	
	$class = new MSG91Sms();
	//Now if OTP is verified, show that in tha ADMIN
	$user_id = $vars['userid'];
	if($user_id):
	
		$otp = $class->getClientOTP( $user_id );
		$userDetails = mysql_fetch_array($class->getClientDetailsBy( $user_id ) , MYSQL_ASSOC);
		if(count($otp)):
			foreach($otp as $key => $value):
				if($class->util_gsmnumber($userDetails['gsmnumber']) === $class->util_gsmnumber($value['phonenumber'])):
					if($value['status'] == 2):
						return '<div class="clientsummaryactions">Phone Verified: <span id="taxstatus" class="csajaxtoggle"><strong class="textgreen">Yes</strong></span></div>';
					else:
						return '<div class="clientsummaryactions">Phone Verified: <span id="taxstatus" class="csajaxtoggle"><strong class="textred">No</strong></span></div>';						
					endif;
				endif;
			endforeach;
		endif;	
		
	endif;
});



	
add_hook('ClientAreaHeadOutput', 1, function($vars) {
	return '<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.css" />
			<script src="//cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.js"></script>
			<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/4.4.0/bootbox.min.js"></script>';
});


add_hook('ClientAreaHeaderOutput', 1, function($vars) {
	
    // Perform hook code here...
	//Look For Unverified.
	$class = new MSG91Sms();
	
	if(isset($vars['client'])):
		
		$unverified_otp_data = $class->getUnverifiedOTP( $vars['client']->id );
		//Lets Verify the Present Phone Number
		$_otp_data = [];
		if(count($unverified_otp_data)):
		
			foreach($unverified_otp_data as $key => $value):
				
				//Match the Phone Number with clients Existing.
				if($class->util_gsmnumber($value['phonenumber']) === $class->util_gsmnumber($vars['client']->phonenumber)):
					$_otp_data = $value;
				endif;
								
			endforeach;
		
		
			$return = '<!-- Trigger the modal with a button -->
						<!-- Modal -->
						<form method="post" class="using-password-strength" action="" onSubmit="return checkOtpForm(this);" role="form" name="otpVerification" id="frmOtpVerification">
							<input type="hidden" name="action" value="otpVerification">
							<input type="hidden" name="otp_id" value="' . $_otp_data['id'] . '">
							<div id="hook-modal" class="modal fade" role="dialog">
							  <div class="modal-dialog">
							
								<!-- Modal content-->
								<div class="modal-content">
								  <div class="modal-header">
									<button type="button" class="close" data-dismiss="modal">&times;</button>
									<h4 class="modal-title">OTP Verification</h4>
									<p>Please enter the OTP sent to Phone number: ' . $_otp_data['phonenumber'] . ' with request no. ' . $_otp_data['request'] . '</p>
								  </div>
								  <div class="modal-body">
									<div class="form-group">
										<label for="otp">Please enter OTP:</label>
										<input type="text" name="otp" class="form-control" id="otp" >
									</div>
								  </div>
								  <div class="modal-footer">
									<button type="submit" class="btn btn-primary">Submit</button>
									<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
									<hr />
									<p class="text-center margin-10">In Case you haven\'t recieved it, please click here to 
										<button type="button" class="btn btn-primary" id="resend-otp">Resend</button>
									</p>
								  </div>
								</div>
							
							  </div>
							</div>
						</form>';
		endif;
		
	endif;
	return $return;
});


add_hook('ClientAreaFooterOutput', 1, function($vars) {
	  $return = '<script type="text/javascript">
					  $(window).on(\'load\',function(){
						  $(\'#hook-modal	\').modal(\'show\');
					  });
					  
					  $("#resend-otp").click(function(e){

							  $.ajax({
									  url: \'clientarea.php\',
									  type: \'post\',
									  data: {"otp_id": $("input[name=otp_id]").val()  , "action":"resendOtp" },
									  //async: false,
									  beforeSend: function () {
										  //Can we add anything here.
									  },
									  cache: true,
									  dataType: \'json\',
									  crossDomain: true,
									  success: function (data) {
										  console.log(data);
										  if (data.code == 1) {
											  
											  swal({
													  title: "Success!",
													  text: data.message,
													  type: "success"
												  }, function() {
													  window.location = "clientarea.php";
												  });
											  
										  } else {
											  bootbox.alert(data.message);
										  }
									  },
									  error: function (data) {
										  console.log(\'Error:\', data);
									  }
								  });
							  

					  });
					  
					  function checkOtpForm(elements) {
						  
						  if(elements.otp.value == \'\') {
							  bootbox.alert("Please Enter OTP Number");
							  elements.otp.focus();
						  } else {
							  
							  $.ajax({
									  url: \'clientarea.php\',
									  type: \'post\',
									  data: $(elements).serialize(),
									  //async: false,
									  beforeSend: function () {
										  //Can we add anything here.
									  },
									  cache: true,
									  dataType: \'json\',
									  crossDomain: true,
									  success: function (data) {
										  console.log(data);
										  if (data.code == 1) {
											  
											  swal({
													  title: "Success!",
													  text: data.message,
													  type: "success"
												  }, function() {
													  window.location = "clientarea.php";
												  });
											  
										  } else {
											  bootbox.alert(data.message);
										  }
									  },
									  error: function (data) {
										  console.log(\'Error:\', data);
									  }
								  });
							  
							  
						  }
						  
						  return false;
					  }
					  
				  </script>';
	return $return;					
});


add_hook('ClientEdit', 1, function($vars) {
    // Perform hook code here...
});


add_hook('ClientAdd', 1, function($vars) {
    // Perform hook code here...
});