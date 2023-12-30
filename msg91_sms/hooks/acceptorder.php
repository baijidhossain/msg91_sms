<?php
$hook = array(
  'hook' => 'AcceptOrder',
  'function' => 'AcceptOrder_SMS',
  'description' => array(
    'english' => 'Post Order Acceptance'
  ),
  'type' => 'client',
  'extra' => '',
  'defaultmessage' => 'Dear {firstname} {lastname}, Your order associated with the ID {orderid} has been approved.',
  'variables' => '{firstname},{lastname},{orderid}'
);
if (!function_exists('AcceptOrder_SMS')) {
  function AcceptOrder_SMS($args)
  {

    $class = new MSG91Sms();
    $template = $class->getTemplateDetails(__FUNCTION__);
    if ($template['active'] == 0) {
      return null;
    }
    $settings = $class->getSettings();
    //        if(!$settings['api'] || !$settings['apiparams'] || !$settings['gsmnumberfield'] || !$settings['wantsmsfield']){
    if (!$settings['api'] || !$settings['apiparams']) {
      return null;
    }


    $userSql = "SELECT `a`.`id`,`a`.`firstname`, `a`.`lastname`, `a`.`phonenumber` as `gsmnumber`, `a`.`country`
        FROM `tblclients` as `a`
        WHERE `a`.`id` IN (SELECT userid FROM tblorders WHERE id = '" . $args['orderid'] . "')
        LIMIT 1";

    $result = mysql_query($userSql);
    $num_rows = mysql_num_rows($result);
    if ($num_rows == 1) {
      $UserInformation = mysql_fetch_assoc($result);

      $template['variables'] = str_replace(" ", "", $template['variables']);
      $replacefrom = explode(",", $template['variables']);
      $replaceto = array($UserInformation['firstname'], $UserInformation['lastname'], $args['orderid']);
      $message = str_replace($replacefrom, $replaceto, $template['template']);


      $class->setCountryCode($UserInformation['country']);
      $class->setGsmnumber($UserInformation['gsmnumber']);
      $class->setUserid($UserInformation['id']);
      $class->setMessage($message);
      $class->send();
    }
  }
}

return $hook;
