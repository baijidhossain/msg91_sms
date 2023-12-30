<?php
$hook = array(
    'hook' => 'AfterModuleCreate',
    'function' => 'AfterModuleCreate_Hosting',
    'description' => array(
        'english' => 'Post Service Activation'
    ),
    'type' => 'client',
    'extra' => '',
    'defaultmessage' => 'Hello! The services for the domain ({domain}) have now been made active . The login details for the accounts are- Username:{username} Password: {password}',
    'variables' => '{firstname}, {lastname}, {domain}, {username}, {password}'
);
if(!function_exists('AfterModuleCreate_Hosting')){
    function AfterModuleCreate_Hosting($args){

        $type = $args['params']['producttype'];

        if($type == "hostingaccount"){
            $class = new MSG91Sms();
            $template = $class->getTemplateDetails(__FUNCTION__);
            if($template['active'] == 0){
                return null;
            }
            $settings = $class->getSettings();
            if(!$settings['api'] || !$settings['apiparams'] ){
                return null;
            }
        }else{
            return null;
        }

//        $userSql = "SELECT `a`.`id`,`a`.`firstname`, `a`.`lastname`, `b`.`value` as `gsmnumber`
//    FROM `tblclients` as `a`
//    WHERE `a`.`id`  = '".$args['params']['clientsdetails']['userid']."'
//    LIMIT 1";

//        $result = mysql_query($userSql);
        $result = $class->getClientDetailsBy($args['params']['clientsdetails']['userid']);
        $num_rows = mysql_num_rows($result);
        if($num_rows == 1){
            $UserInformation = mysql_fetch_assoc($result);

            $template['variables'] = str_replace(" ","",$template['variables']);
            $replacefrom = explode(",",$template['variables']);
            $replaceto = array($UserInformation['firstname'],$UserInformation['lastname'],$args['params']['domain'],$args['params']['username'],$args['params']['password']);
            $message = str_replace($replacefrom,$replaceto,$template['template']);

            $class->setCountryCode($UserInformation['country']);
            $class->setGsmnumber($UserInformation['gsmnumber']);
            $class->setUserid($args['params']['clientsdetails']['userid']);
            $class->setMessage($message);
            $class->send();
        }
    }
}
return $hook;
