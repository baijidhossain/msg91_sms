<?php
$hook = array(
    'hook' => 'ClientAdd',
    'function' => 'ClientAdd',
    'description' => array(
        'english' => 'After Client Registration'
    ),
    'type' => 'client',
    'extra' => '',
    'defaultmessage' => 'Hi {firstname} {lastname}, Thank you for registering with us. The details of your account are- Email: {email} Password: {password}',
    'variables' => '{firstname},{lastname},{email},{password}'
);
if(!function_exists('ClientAdd')){
    function ClientAdd($args){
        $class = new MSG91Sms();
        $template = $class->getTemplateDetails(__FUNCTION__);
        if($template['active'] == 0){
            return null;
        }
        $settings = $class->getSettings();
        if(!$settings['api'] || !$settings['apiparams'] ){
            return null;
        }

//        $userSql = "SELECT `a`.`id`,`a`.`firstname`, `a`.`lastname`, `b`.`value` as `gsmnumber`
//        FROM `tblclients` as `a`
//        JOIN `tblcustomfieldsvalues` as `b` ON `b`.`relid` = `a`.`id`
//        JOIN `tblcustomfieldsvalues` as `c` ON `c`.`relid` = `a`.`id`
//        WHERE `a`.`id` = '".$args['userid']."'
//        AND `b`.`fieldid` = '".$settings['gsmnumberfield']."'
//        AND `c`.`fieldid` = '".$settings['wantsmsfield']."'
//        AND `c`.`value` = 'on'
//        LIMIT 1";
//        $result = mysql_query($userSql);
        $result = $class->getClientDetailsBy($args['userid']);
        $num_rows = mysql_num_rows($result);

        if($num_rows == 1){
            $UserInformation = mysql_fetch_assoc($result);

            $template['variables'] = str_replace(" ","",$template['variables']);
            $replacefrom = explode(",",$template['variables']);
            $replaceto = array($UserInformation['firstname'],$UserInformation['lastname'],$args['email'],$args['password']);
            $message = str_replace($replacefrom,$replaceto,$template['template']);

            $class->setCountryCode($UserInformation['country']);
            $class->setGsmnumber($UserInformation['gsmnumber']);
            $class->setMessage($message);
            $class->setUserid($args['userid']);
            $class->send();
        }
    }
}

return $hook;