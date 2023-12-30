<?php
$hook = array(
    'hook' => 'TicketClose',
    'function' => 'TicketClose',
    'description' => array(
        'english' => 'Ticket Closure'
    ),
    'type' => 'client',
    'extra' => '',
	'defaultmessage' => 'Hello {firstname} {lastname}, The ticket with the ticket number ({ticketno}) has been successfully closed. In case of any issue, kindly contact us.',
    'variables' => '{firstname}, {lastname}, {ticketno}',
);

if(!function_exists('TicketClose')){
    function TicketClose($args){
        $class = new MSG91Sms();
        $template = $class->getTemplateDetails(__FUNCTION__);

        if($template['active'] == 0){
            return null;
        }
        $settings = $class->getSettings();
        if(!$settings['api'] || !$settings['apiparams'] ){
            return null;
        }

        $userSql = "
        SELECT a.tid,b.id as userid,b.firstname,b.lastname,`b`.`country`,`b`.`phonenumber` as `gsmnumber` FROM `tbltickets` as `a`
        JOIN tblclients as b ON b.id = a.userid WHERE a.id = '".$args['ticketid']."'
        LIMIT 1
    ";
		
        $result = mysql_query($userSql);
        $num_rows = mysql_num_rows($result);
        if($num_rows == 1){
            $UserInformation = mysql_fetch_assoc($result);
            $template['variables'] = str_replace(" ","",$template['variables']);
            $replacefrom = explode(",",$template['variables']);
            $replaceto = array($UserInformation['firstname'],$UserInformation['lastname'],$UserInformation['tid']);
            $message = str_replace($replacefrom,$replaceto,$template['template']);

            $class->setCountryCode($UserInformation['country']);
            $class->setGsmnumber($UserInformation['gsmnumber']);
            $class->setMessage($message);
            $class->setUserid($UserInformation['userid']);
            $class->send();
        }
    }
}

return $hook;
