<?php
$hook = array(
    'hook' => 'DailyCronJob',
    'function' => 'DomainRenewalNotice',
    'description' => array(
        'english' => 'Domain Renewal Notice before {x} days.'
    ),
    'type' => 'client',
    'extra' => '15',
    'defaultmessage' => 'Hi {firstname} {lastname}, your domain- {domain} will expire in {x} days i.e. on {expirydate} . Kindly visit site  to renew it. Thank You!',
    'variables' => '{firstname}, {lastname}, {domain},{expirydate},{x}'
);
if(!function_exists('DomainRenewalNotice')){
    function DomainRenewalNotice($args){

        $class = new MSG91Sms();
        $template = $class->getTemplateDetails(__FUNCTION__);
        if($template['active'] == 0){
            return null;
        }
        $settings = $class->getSettings();
        if(!$settings['api'] || !$settings['apiparams'] ){
            return null;
        }

        $extra = $template['extra'];
        $sqlDomain = "SELECT  `userid` ,  `domain` ,  `expirydate`
           FROM  `tbldomains`
           WHERE  `status` =  'Active'";
        $resultDomain = mysql_query($sqlDomain);
        while ($data = mysql_fetch_array($resultDomain)) {
            $tarih = explode("-",$data['expirydate']);
            $yesterday = mktime (0, 0, 0, $tarih[1], $tarih[2] - $extra, $tarih[0]);
            $today = date("Y-m-d");
            if (date('Y-m-d', $yesterday) == $today){
//                $userSql = "SELECT `a`.`id` as userid,`a`.`firstname`, `a`.`lastname`, `b`.`value` as `gsmnumber`
//            FROM `tblclients` as `a`
//            JOIN `tblcustomfieldsvalues` as `b` ON `b`.`relid` = `a`.`id`
//            JOIN `tblcustomfieldsvalues` as `c` ON `c`.`relid` = `a`.`id`
//            WHERE `a`.`id` = '".$data['userid']."'
//            AND `b`.`fieldid` = '".$settings['gsmnumberfield']."'
//            AND `c`.`fieldid` = '".$settings['wantsmsfield']."'
//            AND `c`.`value` = 'on'
//            LIMIT 1";
//
//                $result = mysql_query($userSql);
                $result = $class->getClientDetailsBy($data['userid']);
                $num_rows = mysql_num_rows($result);
                if($num_rows == 1){
                    $UserInformation = mysql_fetch_assoc($result);
                    $template['variables'] = str_replace(" ","",$template['variables']);
                    $replacefrom = explode(",",$template['variables']);
                    $replaceto = array($UserInformation['firstname'],$UserInformation['lastname'],$data['domain'],$data['expirydate'],$extra);
                    $message = str_replace($replacefrom,$replaceto,$template['template']);

                    $class->setCountryCode($UserInformation['country']);
                    $class->setGsmnumber($UserInformation['gsmnumber']);
                    $class->setMessage($message);
                    $class->setUserid($UserInformation['userid']);
                    $class->send();
                }
            }
        }
    }
}
return $hook;