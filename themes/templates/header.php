<?php

if (!defined("IN_FUSION")) { die("Access Denied"); }

require_once INCLUDES."output_handling_include.php";
require_once INCLUDES."header_includes.php";
require_once THEME."theme.php";
require_once THEMES."templates/render_functions.php";

if ($settings['maintenance'] == "1" && ((iMEMBER && $settings['maintenance_level'] == "1" 
	&& $userdata['user_id'] != "1") || ($settings['maintenance_level'] > $userdata['user_level'])
)) { 
	redirect(BASEDIR."maintenance.php");
 }
if (iMEMBER) { 
	$result = dbquery(
		"UPDATE ".DB_USERS." SET user_lastvisit='".time()."', user_ip='".USER_IP."', user_ip_type='".USER_IP_TYPE."'
		WHERE user_id='".$userdata['user_id']."'"
	); 
}

echo "<!DOCTYPE html>\n";
echo "<html lang='". $locale['xml_lang'] ."'>\n";
echo "<head>\n";
// echo "<base href='". $settings['siteurl'] ."' />\n";
echo "<base href='http://". FUSION_HOST ."/' />\n";
// echo "<meta http-equiv='Content-Type' content='text/html; charset=". $locale['charset'] ."' />\n";
echo "<meta charset='". $locale['charset'] ."' />\n";
echo "<title>". $settings['sitename'] ."</title>\n";
echo "<meta name='description' content='". $settings['description'] ."' />\n";
// echo "<meta name='keywords' content='". $settings['keywords'] ."' />\n";
// add_to_footer ("<link rel='stylesheet' href='". THEME ."styles.css' type='text/css' media='screen' />");
// echo "<link rel='stylesheet' href='". THEME ."styles.css' type='text/css' media='screen' />\n";
// echo "<meta http-equiv='Cache-Control' content='max-age=604800, must-revalidate' />\n";
if (file_exists(THEME ."images/favicon.ico")) {
echo "<link rel='shortcut icon' href='". THEME ."images/favicon.ico' type='image/x-icon' />\n";
echo "<link rel='icon' href='". THEME ."images/favicon.ico' type='image/x-icon' />\n";
}
if (function_exists("get_head_tags")) { echo get_head_tags(); }

if ($_POST['domain_name']) {
	add_to_head ("<script type='text/javascript' src='".INCLUDES."jquery/jquery.js'></script>");
} else {
	add_to_footer ("<script type='text/javascript' src='".INCLUDES."jquery/jquery.js'></script>");
} // Yesli Domain check

// echo "<script type='text/javascript' src='".INCLUDES."jquery/jquery.js'></script>\n";
// echo "<script async type='text/javascript' src='". INCLUDES ."jscript.js'></script>\n";
echo "</head>\n";
echo "<body". (TRUE_PHP_SELF=="/" ? " class='home'" : "") .">\n";

require_once THEMES."templates/panels.php";
ob_start();
?>