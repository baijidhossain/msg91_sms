<?php
/* WHMCS SMS Addon with GNU/GPL Licence
 *
 * MSG91 Host - http://www.mimsms.com
 *
 * Updated by Mohammad Masum
 *
 * Support PHP7.2 with WHMCS 7.5
 *
 * Licence: GPLv3 (http://www.gnu.org/licenses/gpl-3.0.txt)
 *
 * */
if (!defined("WHMCS"))
	die("This file cannot be accessed directly");

require_once("smsclass.php");
$class = new MSG91Sms();
$hooks = $class->getHooks();

foreach($hooks as $hook){
    add_hook($hook['hook'], 1, $hook['function'], "");
}