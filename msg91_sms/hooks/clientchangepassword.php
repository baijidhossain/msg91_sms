<?php
$hook = array(
  'hook' => 'ClientChangePassword',
  'function' => 'ClientChangePassword',
  'description' => array(
    'english' => 'After client change password'
  ),
  'type' => 'client',
  'extra' => '',
  'variables' => '{firstname},{lastname}',
  'defaultmessage' => 'Hi {firstname} {lastname}, password has been changed successfully.',
);

if (!function_exists('ClientChangePassword')) {
  function ClientChangePassword($args)
  {
    $class = new MSG91Sms();
    $template = $class->getTemplateDetails(__FUNCTION__);
    if ($template['active'] == 0) {
      return null;
    }
    $settings = $class->getSettings();
    if (!$settings['api'] || !$settings['apiparams']) {
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
    if ($num_rows == 1) {
      $UserInformation = mysql_fetch_assoc($result);
      $template['variables'] = str_replace(" ", "", $template['variables']);
      $replacefrom = explode(",", $template['variables']);
      $replaceto = array($UserInformation['firstname'], $UserInformation['lastname']);
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
