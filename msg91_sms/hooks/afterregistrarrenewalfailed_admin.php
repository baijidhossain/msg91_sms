<?php
$hook = array(
    'hook' => 'AfterRegistrarRenewalFailed',
    'function' => 'AfterRegistrarRenewalFailed_admin',
    'description' => array(
        'english' => 'When domain registration failed.'
    ),
    'type' => 'admin',
    'extra' => '',
    'defaultmessage' => 'An error occurred while updating the domain {domain}',
    'variables' => '{domain}'
);
if(!function_exists('AfterRegistrarRenewalFailed_admin')){
    function AfterRegistrarRenewalFailed_admin($args){
        $class = new MSG91Sms();
        $template = $class->getTemplateDetails(__FUNCTION__);
        if($template['active'] == 0){
            return null;
        }
        $settings = $class->getSettings();
        if(!$settings['api'] || !$settings['apiparams'] ){
            return null;
        }
        $admingsm = explode(",",$template['admingsm']);

        $template['variables'] = str_replace(" ","",$template['variables']);
        $replacefrom = explode(",",$template['variables']);
        $replaceto = array($args['params']['sld'].".".$args['params']['tld']);
        $message = str_replace($replacefrom,$replaceto,$template['template']);

        foreach($admingsm as $gsm){
            if(!empty($gsm)){
                $class->setGsmnumber( trim($gsm));
                $class->setUserid(0);
                $class->setMessage($message);
                $class->send();
            }
        }
    }
}

return $hook;