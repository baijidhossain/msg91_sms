<?php
$hook = array(
  'hook' => 'AfterModuleChangePackage',
  'function' => 'AfterModuleChangePackage',
  'description' => array(
    'english' => 'Following Module Package Change'
  ),
  'type' => 'client',
  'extra' => '',
  'defaultmessage' => 'Hello {firstname} {lastname}, The product/service package for your domain {domain} is changed. Kindly contact us for more details',
  'variables' => '{firstname},{lastname},{domain}'
);
if (!function_exists('AfterModuleChangePackage')) {
  function AfterModuleChangePackage($args)
  {
    $type = $args['params']['producttype'];

    if ($type == "hostingaccount") {
      $class = new MSG91Sms();
      $template = $class->getTemplateDetails(__FUNCTION__);
      if ($template['active'] == 0) {
        return null;
      }
      $settings = $class->getSettings();
      //            if(!$settings['api'] || !$settings['apiparams'] || !$settings['gsmnumberfield'] || !$settings['wantsmsfield']){
      if (!$settings['api'] || !$settings['apiparams']) {
        return null;
      }
    } else {
      return null;
    }

    //        $result = mysql_query($userSql);
    $result = $class->getClientDetailsBy($args['params']['clientsdetails']['userid']);
    $num_rows = mysql_num_rows($result);
    if ($num_rows == 1) {
      $UserInformation = mysql_fetch_assoc($result);

      $template['variables'] = str_replace(" ", "", $template['variables']);
      $replacefrom = explode(",", $template['variables']);
      $replaceto = array($UserInformation['firstname'], $UserInformation['lastname'], $args['params']['domain']);
      $message = str_replace($replacefrom, $replaceto, $template['template']);

      $class->setCountryCode($UserInformation['country']);
      $class->setGsmnumber($UserInformation['gsmnumber']);
      $class->setUserid($args['params']['clientsdetails']['userid']);
      $class->setMessage($message);
      $class->send();
    }
  }
}
return $hook;
