<?php
$hook = array(
    'hook' => 'InvoiceCreated',
    'function' => 'InvoiceCreated',
    'description' => array(
        
        'english' => 'After Invoice Creation'
    ),
    'type' => 'client',
    'extra' => '',
    'defaultmessage' => 'Hello {firstname} {lastname}, Your invoice with id {invoiceid} has been generated. Total amount is  {total}. The last day of payment is {duedate}. Kindly pay your bill before due date to use services without interruption',
    'variables' => '{firstname}, {lastname}, {duedate}, {total}, {invoiceid}'
);
if(!function_exists('InvoiceCreated')){
    function InvoiceCreated($args){

        $class = new MSG91Sms();
        $template = $class->getTemplateDetails(__FUNCTION__);
        if($template['active'] == 0){
            return null;
        }
        $settings = $class->getSettings();
        if(!$settings['api'] || !$settings['apiparams']){
            return null;
        }

//        $userSql = "
//        SELECT a.total,a.duedate,b.id as userid,b.firstname,b.lastname,`c`.`value` as `gsmnumber` FROM `tblinvoices` as `a`
//        JOIN tblclients as b ON b.id = a.userid
//        JOIN `tblcustomfieldsvalues` as `c` ON `c`.`relid` = `a`.`userid`
//        JOIN `tblcustomfieldsvalues` as `d` ON `d`.`relid` = `a`.`userid`
//        WHERE a.id = '".$args['invoiceid']."'
//        AND `c`.`fieldid` = '".$settings['gsmnumberfield']."'
//        AND `d`.`fieldid` = '".$settings['wantsmsfield']."'
//        AND `d`.`value` = 'on'
//        LIMIT 1
//    ";
        mail("sameer@walkover.in","invlice created");
        $userSql = "
        SELECT a.total,a.duedate,b.id as userid,b.firstname,b.lastname,`b`.`country`,`b`.`phonenumber` as `gsmnumber` FROM `tblinvoices` as `a`
        JOIN tblclients as b ON b.id = a.userid
        WHERE a.id = '".$args['invoiceid']."'
        LIMIT 1
    ";

        $result = mysql_query($userSql);
//        $result = $class->getClientAndInvoiceDetailsBy($args['invoiceid']);
        $num_rows = mysql_num_rows($result);
        if($num_rows == 1){
            $UserInformation = mysql_fetch_assoc($result);
            $template['variables'] = str_replace(" ","",$template['variables']);
            $replacefrom = explode(",",$template['variables']);
            $replaceto = array($UserInformation['firstname'],$UserInformation['lastname'],$class->changeDateFormat($UserInformation['duedate']),$UserInformation['total'],$args['invoiceid']);
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
