<?php
$hook = array(
    'hook' => 'AfterRegistrarRenewal',
    'function' => 'AfterRegistrarRenewal',
    'description' => array(
        'english' => 'After domain renewal'
    ),
    'type' => 'client',
    'extra' => '',
    'defaultmessage' => 'Dear {firstname} {lastname}, Your domain {domain} is successfully renewed.',
    'variables' => '{firstname},{lastname},{domain}'
);
if(!function_exists('AfterRegistrarRenewal')){
    function AfterRegistrarRenewal($args){
    $class = new MSG91Sms();
    $template = $class->getTemplateDetails(__FUNCTION__);
    if($template['active'] == 0){
        return null;
    }
    $settings = $class->getSettings();
    if(!$settings['api'] || !$settings['apiparams'] ){
        return null;
    }

//    $userSql = "SELECT `a`.`id`,`a`.`firstname`, `a`.`lastname`, `b`.`value` as `gsmnumber`
//	FROM `tblclients` as `a`
//	JOIN `tblcustomfieldsvalues` as `b` ON `b`.`relid` = `a`.`id`
//	JOIN `tblcustomfieldsvalues` as `c` ON `c`.`relid` = `a`.`id`
//	WHERE `a`.`id` = '".$args['params']['userid']."'
//	AND `b`.`fieldid` = '".$settings['gsmnumberfield']."'
//	AND `c`.`fieldid` = '".$settings['wantsmsfield']."'
//	AND `c`.`value` = 'on'
//	LIMIT 1";
//    $result = mysql_query($userSql);
    $result = $class->getClientDetailsBy($args['params']['userid']);
    $num_rows = mysql_num_rows($result);
    if($num_rows == 1){
        $UserInformation = mysql_fetch_assoc($result);

        $template['variables'] = str_replace(" ","",$template['variables']);
        $replacefrom = explode(",",$template['variables']);
        $replaceto = array($UserInformation['firstname'],$UserInformation['lastname'],$args['params']['sld'].".".$args['params']['tld']);
        $message = str_replace($replacefrom,$replaceto,$template['template']);

        $class->setCountryCode($UserInformation['country']);
        $class->setGsmnumber($UserInformation['gsmnumber']);
        $class->setUserid($args['params']['userid']);
        $class->setMessage($message);
        $class->send();
    }

}
}

return $hook;