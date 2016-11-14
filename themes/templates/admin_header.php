<?php

if (!defined("IN_FUSION")) { die("Access Denied"); }

define("ADMIN_PANEL", true);

require_once INCLUDES."output_handling_include.php";
require_once INCLUDES."header_includes.php";
require_once ADMINTHEME."theme.php";

if ($settings['maintenance'] == "1" && !iADMIN) { redirect(BASEDIR."maintenance.php"); }
if (iMEMBER) { $result = dbquery("UPDATE ".DB_USERS." SET user_lastvisit='".time()."', user_ip='".USER_IP."', user_ip_type='".USER_IP_TYPE."' WHERE user_id='".$userdata['user_id']."'"); }

echo "<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'>\n";
echo "<html xmlns='http://www.w3.org/1999/xhtml' xml:lang='".$locale['xml_lang']."' lang='".$locale['xml_lang']."'>\n";
echo "<head>\n<title>".$settings['sitename']."</title>\n";
echo "<meta http-equiv='Content-Type' content='text/html; charset=".$locale['charset']."' />\n";
echo "<link rel='stylesheet' href='".ADMINTHEME."styles.css' type='text/css' media='screen' />\n";
if (file_exists(IMAGES."favicon.ico")) { echo "<link rel='shortcut icon' href='".IMAGES."favicon.ico' type='image/x-icon' />\n"; }
if (function_exists("get_head_tags")) { echo get_head_tags(); }
echo "<script type='text/javascript' src='".INCLUDES."jquery/jquery.js'></script>\n";
echo "<script type='text/javascript' src='".INCLUDES."jscript.js'></script>\n";
echo "<script type='text/javascript' src='".INCLUDES."jquery/admin-msg.js'></script>\n";
?>

<?php
	if ($settings['tinymce_enabled']==2) {
?>
<script type="text/javascript" src="<?php echo INCLUDES; ?>jscripts/ckeditor/ckeditor.js"></script>
<script type="text/javascript" src="<?php echo INCLUDES; ?>jscripts/ckeditor/ckfinder/ckfinder.js"></script>
<script type="text/javascript" src="<?php echo INCLUDES; ?>jscripts/ckeditor/adapters/jquery.js"></script>
<?php
} elseif ($settings['tinymce_enabled']==1) {
?>
<style type='text/css'>.mceIframeContainer iframe{width:100%!important;}</style>
<script type="text/javascript" src="<?php echo INCLUDES; ?>jscripts/tiny_mce/tinymce.min.js"></script>
<script type='text/javascript'>
		tinymce.PluginManager.load('moxiemanager', 'http://www.tinymce.com/js/moxiemanager/plugin.min.js');
        tinymce.init({
        	selector:'textarea',
			plugins: [
						'advlist autolink lists link image charmap print preview anchor',
						'searchreplace visualblocks code fullscreen',
						'insertdatetime media table contextmenu paste moxiemanager'
			],
			toolbar: 'insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image',
			autosave_ask_before_unload: false,
			max_height: 200,
			min_height: 160,
			height : 180,
			language:'<?php echo $locale['tinymce']; ?>',
			external_image_list_url:'<?php echo IMAGES; ?>imagelist.js',
	});
</script>
<?php
} // Editor

echo "</head>\n<body>\n";

require_once THEMES."templates/panels.php";

ob_start();
?>