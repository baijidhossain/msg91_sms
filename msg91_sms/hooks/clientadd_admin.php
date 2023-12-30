<?php
$hook = array(
    'hook' => 'ClientAdd',
    'function' => 'ClientAdd_admin',
    'description' => array(
        'english' => 'When client is added.'
    ),
    'type' => 'admin',
    'extra' => '',
    'defaultmessage' => 'New customer has been added to the website.',
    'variables' => ''
);
if(!function_exists('ClientAdd_admin')){
    function ClientAdd_admin($args){
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

        foreach($admingsm as $gsm){
            if(!empty($gsm)){
                $class->setGsmnumber(trim($gsm));
                $class->setUserid(0);
                $class->setMessage($template['template']);
                $class->send();
            }
        }
    }
}
return $hook;