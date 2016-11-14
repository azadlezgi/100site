<?php

if (!defined("IN_FUSION")) { die("Access Denied"); }

require_once INCLUDES."sendmail_include.php";
include LOCALE.LOCALESET."lostpassword.php";

if (iMEMBER) redirect("index.php");

function __autoload($class) {
  require CLASSES.$class.".class.php";
  if (!class_exists($class)) { die("Class not found"); }
}

add_to_title($locale['global_200'].$locale['400']);
opentable($locale['400']);

$obj = new LostPassword();
if (isset($_GET['user_email']) && isset($_GET['account'])) {
	$obj->checkPasswordRequest($_GET['user_email'], $_GET['account']);
	$obj->displayOutput();
} elseif (isset($_POST['send_password'])) {
	$obj->sendPasswordRequest($_POST['email']);
	$obj->displayOutput();
} else {
	$obj->renderInputForm();
	$obj->displayOutput();
}

closetable();


?>