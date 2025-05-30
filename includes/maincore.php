<?php

error_reporting('E_ALL');

session_start();

if (preg_match("/maincore.php/i", $_SERVER['PHP_SELF'])) { die(); }


$photo_valid_types=array("gif","jpg","png","jpeg");  // допустимые расширения


// кеширования всеми серверами на 1 час
header("Cache-Control: public");
header("Expires: " . date("r", time() + 604800));



// Calculate script start/end time
function get_microtime() {
	list($usec, $sec) = explode(" ", microtime());
	return ((float)$usec + (float)$sec);
}

// Define script start time
define("START_TIME", get_microtime());
define("IN_FUSION", TRUE);

// Prevent any possible XSS attacks via $_GET.
if (stripget($_GET)) {
	die("Prevented a XSS attack through a GET variable!");
}

// Locate config.php and set the basedir path
$folder_level = ""; $i = 0;
while (!file_exists($folder_level."config.php")) {
	$folder_level .= "../"; $i++;
	if ($i == 7) { die("config.php file not found"); }
}
define("BASEDIR", $folder_level);

require_once BASEDIR."config.php";

// If config.php is empty, activate setup.php script
if (!isset($db_name)) { redirect("setup.php"); }

require_once BASEDIR."includes/multisite_include.php";




// if (!isset($_SESSION['zuSxGeo'])) {

// 	include BASEDIR ."includes/SxGeo/SxGeo.php";
// 	$SxGeo = new SxGeo(BASEDIR ."includes/SxGeo/SxGeo.dat", SXGEO_BATCH | SXGEO_MEMORY);
// 	$SxGeoCountry = $SxGeo->getCountry($_SERVER['REMOTE_ADDR']);
// 	$_SESSION['zuSxGeo'] = $SxGeoCountry;

// 	// echo "<!--";
// 	// echo "<pre>";
// 	// print_r($SxGeo);
// 	// echo "</pre>";
// 	// echo "-->";
// }



// Checking file types of the uploaded file with known mime types list to prevent uploading unwanted files
if(isset($_FILES) && count($_FILES)) {
	require_once BASEDIR.'includes/mimetypes_include.php';
	$mime_types = mimeTypes();
	foreach($_FILES as $each) {
		if(isset($each['name']) && strlen($each['tmp_name'])) {
			$file_info = pathinfo($each['name']);
			$extension = $file_info['extension'];
			if(array_key_exists($extension, $mime_types)) {
				//An extension may have more than one mime type
				if(is_array($mime_types[$extension])) {
					//We should check each extension one by one
					$valid_mimetype = false;
					foreach($mime_types[$extension] as $each_mimetype) {
						//If we have a match, we set the value to true and break the loop
						if($each_mimetype==$each['type']) {
							$valid_mimetype = true;
							break;
						}
					}

					if(!$valid_mimetype) {
						die('Prevented an unwanted file upload attempt!');
					}
					unset($valid_mimetype);
				} else {
					if($mime_types[$extension]!=$each['type']) {
						die('Prevented an unwanted file upload attempt!');
					}
				}
			} /*else { //Let's disable this for now
				//almost impossible with provided array, but we throw an error anyways
				die('Unknown file type');
			}*/
			unset($file_info,$extension);
		}
	}
	unset($mime_types);
}


/*
// Establish mySQL database connection
$link = dbconnect($db_host, $db_user, $db_pass, $db_name);
unset($db_host, $db_user, $db_pass);
*/

// New establish mySQL database connection
_DB::connectDB($db_host, $db_user, $db_pass, $db_name); // подключились
unset($db_host, $db_user, $db_pass);



// Fetch the settings from the database
$settings = array();
$result = dbquery("SELECT * FROM ".DB_SETTINGS);
if (dbrows($result)) {
	while ($data = dbarray($result)) {
		$settings[$data['settings_name']] = $data['settings_value'];
	}
} else {
	die("Settings do not exist, please check your config.php file or run setup.php again.");
}


// Fetch the languages from the database
$languages = array();
$result = dbquery("SELECT * FROM ". DB_LANGUAGES ." ORDER BY languages_id ASC");
if (dbrows($result)) {
	$lang_say=0;
	while ($data = dbarray($result)) { $lang_say++;
		$languages[] = $data;
		$languages_count = $lang_say;
	}
} else {
	die("Languages do not exist, please check one languages.");
}


// Fetch the currency from the database
$currency = array();
$result = dbquery("SELECT * FROM ". DB_CURRENCY ." ORDER BY currency_id ASC");
if (dbrows($result)) {
	while ($data = dbarray($result)) {
		$currency[] = $data;
	}
} else {
	die("Currency do not exist, please check one currency.");
}


// Fetch the components from the database
$components = array();
$result = dbquery("SELECT * FROM ".DB_COMPONENTS." ");
if (dbrows($result)) {
	while ($data = dbarray($result)) {
		$components[] = $data;
	}
} else {
	die("Components do not exist, please check one components.");
}





// Settings dependent functions
date_default_timezone_set($settings['default_timezone']);
//ob_start("ob_gzhandler"); //Uncomment this line and comment the one below to enable output compression.
ob_start();

// Sanitise $_SERVER globals
$_SERVER['PHP_SELF'] = cleanurl($_SERVER['PHP_SELF']);
$_SERVER['QUERY_STRING'] = isset($_SERVER['QUERY_STRING']) ? cleanurl($_SERVER['QUERY_STRING']) : "";
$_SERVER['REQUEST_URI'] = isset($_SERVER['REQUEST_URI']) ? cleanurl($_SERVER['REQUEST_URI']) : "";
$PHP_SELF = cleanurl($_SERVER['PHP_SELF']);

// echo $_SERVER['SERVER_PORT'];

// Common definitions
define("FUSION_REQUEST", isset($_SERVER['REQUEST_URI']) && $_SERVER['REQUEST_URI'] != "" ? $_SERVER['REQUEST_URI'] : $_SERVER['SCRIPT_NAME']);
define("FUSION_QUERY", isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : "");
define("FUSION_SELF", basename($_SERVER['PHP_SELF']));
define("FUSION_IP", $_SERVER['REMOTE_ADDR']);
define("QUOTES_GPC", (ini_get('magic_quotes_gpc') ? TRUE : FALSE));
define("FUSION_URI", $_SERVER['REQUEST_URI']);
define("FUSION_REFERER", $_SERVER['HTTP_REFERER']);
define("FUSION_HOST", $_SERVER['HTTP_HOST']);
define("FUSION_TODAY", mktime());

// Path definitions
define("ADMIN", BASEDIR."administration/");
define("INCLUDES", BASEDIR."includes/");
define("CLASSES", INCLUDES ."classes/");
define("CACHE", INCLUDES ."cache/");
define("DOWNLOADS", BASEDIR."downloads/");
define("IMAGES", BASEDIR . "sites/". SITE ."/uploads/");
define("IMAGES_A", IMAGES."articles/");
define("IMAGES_A_T", IMAGES."articles/thumbs/");
define("IMAGES_AC", IMAGES."articles_cat/");
define("IMAGES_AC_T", IMAGES."articles_cat/thumbs/");
define("IMAGES_C", IMAGES."catalog/");
define("IMAGES_C_T", IMAGES."catalog/thumbs/");
define("IMAGES_CC", IMAGES."catalog_cats/");
define("IMAGES_CC_T", IMAGES."catalog_cats/thumbs/");
define("IMAGES_N", IMAGES."news/");
define("IMAGES_N_T", IMAGES."news/thumbs/");
define("RANKS", IMAGES."ranks/");
define("LOCALE", BASEDIR."locale/");
// if (isset($_GET['locale'])) {
// 	if (($_GET['locale']=="Nederlands") || ($_GET['locale']=="Lithuanian")) {
// 		$_SESSION['locale'] = "English";
// 	} else {
// 		$_SESSION['locale'] = $_GET['locale'];
// 	}
// 	redirect(FUSION_REFERER);
// }

//unset($_SESSION['locale']);

// if ((FUSION_HOST=="issohost.com") && (!$_SESSION['locale'])) {
// 	$_SESSION['locale'] = "English";
// }
// if ((FUSION_HOST=="issohost.ru") && (!$_SESSION['locale'])) {
// 	$_SESSION['locale'] = "Russian";
// }
// if ((FUSION_HOST=="issohost.fr") && (!$_SESSION['locale'])) {
// 	$_SESSION['locale'] = "French";
// }
// if ((FUSION_HOST=="issohost.nl") && (!$_SESSION['locale'])) {
// 	$_SESSION['locale'] = "English";
// }
// if ((FUSION_HOST=="issohost.lt") && (!$_SESSION['locale'])) {
// 	$_SESSION['locale'] = "English";
// }

if ($_SESSION['locale']) {
	define("LOCALESET", $_SESSION['locale']."/");
} else {
	define("LOCALESET", $settings['locale']."/");
}

foreach ($languages as $key => $value) {
	if ($value['languages_folder']."/"==LOCALESET) {
define("LOCALESHORT", $value['languages_short']);
	}
}

/* currency */
// if ($_SESSION['zuSxGeo']=="RU") {
// 	$settings['currency'] = 3;
// } else {
// 	$settings['currency'] = 1;
// }

if (isset($_GET['currency'])) {
	$_SESSION['currency'] = $_GET['currency'];
	redirect(FUSION_REFERER);
}
if ($_SESSION['currency']) {
	define("CURRENCYSET", $_SESSION['currency']);
} else {
	define("CURRENCYSET", $settings['currency']);
}
/* //currency */


define("FORUM", BASEDIR."forum/");
define("INFUSIONS", BASEDIR."infusions/");
define("PHOTOS", IMAGES."photoalbum/");
define("THEMES", BASEDIR."themes/");
define("COMPONENTS", BASEDIR."components/");
define("ADMINTHEME", THEMES."admin/");

// Variables initializing
$mysql_queries_count = 0;
$mysql_queries_time = array();
$smiley_cache = "";
$bbcode_cache = "";
$groups_cache = "";
$forum_rank_cache = "";
$forum_mod_rank_cache = "";
$locale = array();

// Calculate current true url
$script_url = explode("/", $_SERVER['REQUEST_URI']);
$url_count = count($script_url);
$base_url_count = substr_count(BASEDIR, "/") + 1;
$current_page = "";
while ($base_url_count != 0) {
	$current = $url_count - $base_url_count;
	$current_page .= "/".$script_url[$current];
	$base_url_count--;
}
$current_page = explode("?", $current_page);
$current_page = $current_page[0];


// print_r($current_page);

define("TRUE_PHP_SELF", $current_page);
define("START_PAGE", substr(preg_replace("#(&amp;|\?)(s_action=edit&amp;shout_id=)([0-9]+)#s", "", TRUE_PHP_SELF /*.(FUSION_QUERY ? "?".FUSION_QUERY : "")*/ ), 1));


// IP address functions
include BASEDIR."includes/ip_handling_include.php";

// Error Handling
require_once BASEDIR."includes/error_handling_include.php";

// Redirects to the index if the URL is invalid (eg. file.php/folder/)
if ($_SERVER['SCRIPT_NAME'] != $_SERVER['PHP_SELF']) { redirect($settings['siteurl']); }

// Load the Global language file
include LOCALE.LOCALESET."global.php";

// Autenticate user
require_once CLASSES."Authenticate.class.php";

// Log in user
if (isset($_POST['login']) && isset($_POST['user_name']) && isset($_POST['user_pass'])) {
	$auth = new Authenticate($_POST['user_name'], $_POST['user_pass'], (isset($_POST['remember_me']) ? true : false));
	$userdata = $auth->getUserData();
	unset($auth, $_POST['user_name'], $_POST['user_pass']);
} elseif (isset($_GET['logout']) && $_GET['logout'] == "yes") {
	$userdata = Authenticate::logOut();
	redirect(BASEDIR."index.php");
} else {
	$userdata = Authenticate::validateAuthUser();
}

// User level, Admin Rights & User Group definitions
define("iGUEST", $userdata['user_level'] == 0 ? 1 : 0);
define("iMEMBER", $userdata['user_level'] >= 101 ? 1 : 0);
define("iADMIN", $userdata['user_level'] >= 102 ? 1 : 0);
define("iSUPERADMIN", $userdata['user_level'] == 103 ? 1 : 0);
define("iUSER", $userdata['user_level']);
define("iUSER_RIGHTS", $userdata['user_rights']);
define("iUSER_GROUPS", substr($userdata['user_groups'], 1));

if (iADMIN) {
	define("iAUTH", substr(md5($userdata['user_password'].USER_IP), 16, 16));
	$aidlink = "?aid=".iAUTH;
}

// PHP-Fusion user cookie functions
if (!isset($_COOKIE[COOKIE_PREFIX.'visited'])) {
	$result = dbquery("UPDATE ".DB_SETTINGS." SET settings_value=settings_value+1 WHERE settings_name='counter'");
	setcookie(COOKIE_PREFIX."visited", "yes", time() + 31536000, "/", "", "0");
}
$lastvisited = Authenticate::setLastVisitCookie();




/*
// MySQL database functions
function dbquery($query) {
	global $mysql_queries_count, $mysql_queries_time; $mysql_queries_count++;

	$query_time = get_microtime();
	$result = @mysql_query($query);
	$query_time = substr((get_microtime() - $query_time),0,7);

	$mysql_queries_time[$mysql_queries_count] = array($query_time, $query);

	if (!$result) {
		echo mysql_error();
		return false;
	} else {
		return $result;
	}
}

function dbcount($field, $table, $conditions = "") {
	global $mysql_queries_count, $mysql_queries_time; $mysql_queries_count++;

	$cond = ($conditions ? " WHERE ".$conditions : "");
	$query_time = get_microtime();
	$result = @mysql_query("SELECT Count".$field." FROM ".$table.$cond);
	$query_time = substr((get_microtime() - $query_time),0,7);

	$mysql_queries_time[$mysql_queries_count] = array($query_time, "SELECT COUNT".$field." FROM ".$table.$cond);

	if (!$result) {
		echo mysql_error();
		return false;
	} else {
		$rows = mysql_result($result, 0);
		return $rows;
	}
}

function dbresult($query, $row) {
	global $mysql_queries_count, $mysql_queries_time;

	$query_time = get_microtime();
	$result = @mysql_result($query, $row);
	$query_time = substr((get_microtime() - $query_time),0,7);

	$mysql_queries_time[$mysql_queries_count] = array($query_time, $query);

	if (!$result) {
		echo mysql_error();
		return false;
	} else {
		return $result;
	}
}

function dbrows($query) {
	$result = @mysql_num_rows($query);
	return $result;
}

function dbarray($query) {
	$result = @mysql_fetch_assoc($query);
	if (!$result) {
		echo mysql_error();
		return false;
	} else {
		return $result;
	}
}

function dbarraynum($query) {
	$result = @mysql_fetch_row($query);
	if (!$result) {
		echo mysql_error();
		return false;
	} else {
		return $result;
	}
}

function dbconnect($db_host, $db_user, $db_pass, $db_name) {
	global $db_connect;

	$db_connect = @mysql_connect($db_host, $db_user, $db_pass);
	$db_select = @mysql_select_db($db_name);
	mysql_query("SET NAMES 'utf8'") or die("Error: " . mysql_error()); 
	if (!$db_connect) {
		die("<strong>Unable to establish connection to MySQL</strong><br />".mysql_errno()." : ".mysql_error());
	} elseif (!$db_select) {
		die("<strong>Unable to select MySQL database</strong><br />".mysql_errno()." : ".mysql_error());
	}
}
*/


// New MySQL database functions
function dbm_result($res, $row, $field=0) {
	$res->data_seek($row);
	$datarow = $res->fetch_array();
	return $datarow[$field];
}

function dbquery($query) {
	global $mysql_queries_count, $mysql_queries_time; $mysql_queries_count++;
	$query_time = START_TIME;
	if (!$result = _DB::$linkes->query($query)) {
		echo _DB::$linkes->error;
		return FALSE;
	} else {
		$query_time = substr((START_TIME - $query_time),0,7);
		$mysql_queries_time[$mysql_queries_count] = array($query_time, $query);
		return $result;
	}
}
 
function dbcount($field, $table, $conditions = "") {
	global $mysql_queries_count, $mysql_queries_time; $mysql_queries_count++;
	$cond = ($conditions ? " WHERE ".$conditions : "");
	$query_time = START_TIME;
	if (!$result = _DB::$linkes->query("SELECT COUNT".$field." FROM ".$table.$cond)) {
		echo _DB::$linkes->error;
		return FALSE;
	} else {
		$query_time = substr((START_TIME - $query_time),0,7);
		$mysql_queries_time[$mysql_queries_count] = array($query_time, "SELECT COUNT".$field." FROM ".$table.$cond);
		$rows = dbm_result($result, 0);
		return $rows;
	}
}
 
function dbresult($query, $row) {
	global $mysql_queries_count, $mysql_queries_time;
	$query_time = START_TIME;
	if (!$result = dbm_result($query, $row)) {
		echo _DB::$linkes->error;
		return FALSE;
	} else {
		$query_time = substr((START_TIME - $query_time),0,7);
		$mysql_queries_time[$mysql_queries_count] = array($query_time, $query);
		return $result;
	}
}
 
function dbrows($query) {
	$result = $query->num_rows;
	return $result;
}
 
function dbarray($query) {
	if (!$result = $query->fetch_assoc()) {
		echo _DB::$linkes->error;
		return FALSE;
	} else {
		return $result;
	}
}
 
function dbarraynum($query) {
	if (!$result = $query->fetch_row()) {
		echo _DB::$linkes->error;
		return FALSE;
	} else {
		return $result;
	}
}
   

class _DB {
	static $linkes;
	public static function connectDB($host, $user, $pass, $name) {
		if (empty(self::$linkes)) {
			self::$linkes = new mysqli($host, $user, $pass, $name);
			if (self::$linkes->connect_errno) {
				die("<strong>Unable to establish connection to MySQL:</strong><br />".self::$linkes->connect_error);
			}
			self::$linkes->set_charset("utf8");
			// ^ раскомментировать при необходимости
			// если в utf-8, меняем win1251 на utf8
		}
	}
}




// Set theme
set_theme($userdata['user_theme']);

// Check if a given theme exists and is valid
function theme_exists($theme) {
	global $settings;

	if ($theme == "Default") { $theme = $settings['theme']; }
	if (!file_exists(BASEDIR . "sites/". SITE ."/") || !is_dir(BASEDIR . "sites/". SITE ."/") || !is_string($theme) || !preg_match("/^([a-z0-9_-]){2,50}$/i", $theme) || !file_exists(BASEDIR . "sites/". SITE ."/".$theme)) {
		return false;
	} elseif (file_exists(BASEDIR . "sites/". SITE ."/".$theme."/theme.php") && file_exists(BASEDIR . "sites/". SITE ."/".$theme."/styles.css")) {
		return true;
	} else {
		return false;
	}
}

// Set a valid theme
function set_theme($theme) {
	global $settings, $locale;

	if (!defined("THEME")) {
		// If the theme is valid set it
		if (theme_exists($theme)) {
			define("THEME", BASEDIR . "sites/". SITE ."/".($theme == "Default" ? $settings['theme'] : $theme)."/");
		// The theme is invalid, search for a valid one inside themes folder and set it
		} else {
			$dh = opendir(BASEDIR . "sites/". SITE ."/");
			while (false !== ($entry = readdir($dh))) {
				if ($entry != "." && $entry != ".." && is_dir(BASEDIR . "sites/". SITE ."/".$entry)) {
					if (theme_exists($entry)) {
						define("THEME", BASEDIR . "sites/". SITE ."/".$entry."/");
						break;
					}
				}
			}
			closedir($dh);
		}
		// If can't find and set any valid theme show a warning
		if (!defined("THEME")) {
			echo "<strong>".$theme." - ".$locale['global_300'].".</strong><br /><br />\n";
			echo $locale['global_301'];
			die();
		}
	}
}

// Set the admin password when needed
function set_admin_pass($password) {

	Authenticate::setAdminCookie($password);

}

// Check if admin password matches userdata
function check_admin_pass($password) {

	return Authenticate::validateAuthAdmin($password);

}

// Redirect browser using header or script function
function redirect($location, $script = false) {
	if (!$script) {
		header("Location: ".str_replace("&amp;", "&", $location));
		exit;
	} else {
		echo "<script type='text/javascript'>document.location.href='".str_replace("&amp;", "&", $location)."'</script>\n";
		exit;
	}
}

// Clean URL Function, prevents entities in server globals
function cleanurl($url) {
	$bad_entities = array("&", "\"", "'", '\"', "\'", "<", ">", "(", ")", "*");
	$safe_entities = array("&amp;", "", "", "", "", "", "", "", "", "");
	$url = str_replace($bad_entities, $safe_entities, $url);
	return $url;
}

// Strip Input Function, prevents HTML in unwanted places
function stripinput($text) {
	if (!is_array($text)) {
		$text = stripslash(trim($text));
		$text = preg_replace("/(&amp;)+(?=\#([0-9]{2,3});)/i", "&", $text);
		$search = array("&", "\"", "'", "\\", '\"', "\'", "<", ">", "&nbsp;");
		$replace = array("&amp;", "&quot;", "&#39;", "&#92;", "&quot;", "&#39;", "&lt;", "&gt;", " ");
		$text = str_replace($search, $replace, $text);
	} else {
		foreach ($text as $key => $value) {
			$text[$key] = stripinput($value);
		}
	}
	return $text;
}

// Prevent any possible XSS attacks via $_GET.
function stripget($check_url) {
	$return = false;
	if (is_array($check_url)) {
		foreach ($check_url as $value) {
			if (stripget($value) == true) {
				return true;
			}
		}
	} else {
		$check_url = str_replace(array("\"", "\'"), array("", ""), urldecode($check_url));
		if (preg_match("/<[^<>]+>/i", $check_url)) {
			return true;
		}
	}
	return $return;
}

// Strip file name
function stripfilename($filename) {
	$filename = strtolower(str_replace(" ", "_", $filename));
	$filename = preg_replace("/[^a-zA-Z0-9_-]/", "", $filename);
	$filename = preg_replace("/^\W/", "", $filename);
	$filename = preg_replace('/([_-])\1+/', '$1', $filename);
	if ($filename == "") { $filename = time(); }

	return $filename;
}

// Strip Slash Function, only stripslashes if magic_quotes_gpc is on
function stripslash($text) {
	if (QUOTES_GPC) { $text = stripslashes($text); }
	return $text;
}

// Add Slash Function, add correct number of slashes depending on quotes_gpc
function addslash($text) {
	if (!QUOTES_GPC) {
		$text = addslashes(addslashes($text));
	} else {
		$text = addslashes($text);
	}
	return $text;
}

// htmlentities is too agressive so we use this function
function phpentities($text) {
	$search = array("&", "\"", "'", "\\", "<", ">");
	$replace = array("&amp;", "&quot;", "&#39;", "&#92;", "&lt;", "&gt;");
	$text = str_replace($search, $replace, $text);
	return $text;
}

// Trim a line of text to a preferred length
function trimlink($text, $length) {
	$dec = array("&", "\"", "'", "\\", '\"', "\'", "<", ">");
	$enc = array("&amp;", "&quot;", "&#39;", "&#92;", "&quot;", "&#39;", "&lt;", "&gt;");
	$text = str_replace($enc, $dec, $text);
	if (strlen($text) > $length) $text = substr($text, 0, ($length-3))."...";
	$text = str_replace($dec, $enc, $text);
	return $text;
}

// Validate numeric input
function isnum($value) {
	if (!is_array($value)) {
		return (preg_match("/^[0-9]+$/", $value));
	} else {
		return false;
	}
}

// Custom preg-match function
function preg_check($expression, $value) {
	if (!is_array($value)) {
		return preg_match($expression, $value);
	} else {
		return false;
	}
}

// Cache smileys mysql
function cache_smileys() {
	global $smiley_cache;
	$result = dbquery("SELECT smiley_code, smiley_image, smiley_text FROM ".DB_SMILEYS);
	if (dbrows($result)) {
		$smiley_cache = array();
		while ($data = dbarray($result)) {
			$smiley_cache[] = array(
				"smiley_code" => $data['smiley_code'],
				"smiley_image" => $data['smiley_image'],
				"smiley_text" => $data['smiley_text']
			);
		}
	} else {
		$smiley_cache = array();
	}
}

// Parse smiley bbcode
function parsesmileys($message) {
	global $smiley_cache;
	if (!preg_match("#(\[code\](.*?)\[/code\]|\[geshi=(.*?)\](.*?)\[/geshi\]|\[php\](.*?)\[/php\])#si", $message)) {
		if (!$smiley_cache) { cache_smileys(); }
		if (is_array($smiley_cache) && count($smiley_cache)) {
			foreach ($smiley_cache as $smiley) {
				$smiley_code = preg_quote($smiley['smiley_code'], '#');
				$smiley_image = "<img src='".get_image("smiley_".$smiley['smiley_text'])."' alt='".$smiley['smiley_text']."' style='vertical-align:middle;' />";
				$message = preg_replace("#{$smiley_code}#si", $smiley_image, $message);
			}
		}
	}
	return $message;
}

// Show smiley icons in comments, forum and other post pages
function displaysmileys($textarea, $form = "inputform") {
	global $smiley_cache;
	$smileys = ""; $i = 0;
	if (!$smiley_cache) { cache_smileys(); }
	if (is_array($smiley_cache) && count($smiley_cache)) {
		foreach ($smiley_cache as $smiley) {
			if ($i != 0 && ($i % 10 == 0)) { $smileys .= "<br />\n"; $i++; }
			$smileys .= "<img src='".get_image("smiley_".$smiley['smiley_text'])."' alt='".$smiley['smiley_text']."' onclick=\"insertText('".$textarea."', '".$smiley['smiley_code']."', '".$form."');\" />\n";
		}
	}
	return $smileys;
}

// Cache bbcode mysql
function cache_bbcode() {
	global $bbcode_cache;
	$result = dbquery("SELECT bbcode_name FROM ".DB_BBCODES." ORDER BY bbcode_order ASC");
	if (dbrows($result)) {
		$bbcode_cache = array();
		while ($data = dbarray($result)) {
			$bbcode_cache[] = $data['bbcode_name'];
		}
	} else {
		$bbcode_cache = array();
	}
}

// Parse bbcode
function parseubb($text, $selected = false) {
	global $bbcode_cache;
	if (!$bbcode_cache) { cache_bbcode(); }
	if (is_array($bbcode_cache) && count($bbcode_cache)) {
		if ($selected) { $sel_bbcodes = explode("|", $selected); }
		foreach ($bbcode_cache as $bbcode) {
			if ($selected && in_array($bbcode, $sel_bbcodes)) {
				if (file_exists(INCLUDES."bbcodes/".$bbcode."_bbcode_include.php")) {
					if (file_exists(LOCALE.LOCALESET."bbcodes/".$bbcode.".php")) {
						include (LOCALE.LOCALESET."bbcodes/".$bbcode.".php");
					} elseif (file_exists(LOCALE."English/bbcodes/".$bbcode.".php")) {
						include (LOCALE."English/bbcodes/".$bbcode.".php");
					}
					include (INCLUDES."bbcodes/".$bbcode."_bbcode_include.php");
				}
			} elseif (!$selected) {
				if (file_exists(INCLUDES."bbcodes/".$bbcode."_bbcode_include.php")) {
					if (file_exists(LOCALE.LOCALESET."bbcodes/".$bbcode.".php")) {
						include (LOCALE.LOCALESET."bbcodes/".$bbcode.".php");
					} elseif (file_exists(LOCALE."English/bbcodes/".$bbcode.".php")) {
						include (LOCALE."English/bbcodes/".$bbcode.".php");
					}
					include (INCLUDES."bbcodes/".$bbcode."_bbcode_include.php");
				}
			}
		}
	}
	$text = descript($text, false);
	return $text;
}

// Javascript email encoder by Tyler Akins
// http://rumkin.com/tools/mailto_encoder/
function hide_email($email, $title = "", $subject = "") {
	if (strpos($email, "@")) {
		$parts = explode("@", $email);
		$MailLink = "<a href='mailto:".$parts[0]."@".$parts[1];
		if ($subject != "") { $MailLink .= "?subject=".urlencode($subject); }
		$MailLink .= "'>".($title?$title:$parts[0]."@".$parts[1])."</a>";
		$MailLetters = "";
		for ($i = 0; $i < strlen($MailLink); $i++) {
			$l = substr($MailLink, $i, 1);
			if (strpos($MailLetters, $l) === false) {
				$p = rand(0, strlen($MailLetters));
				$MailLetters = substr($MailLetters, 0, $p).$l.substr($MailLetters, $p, strlen($MailLetters));
			}
		}
		$MailLettersEnc = str_replace("\\", "\\\\", $MailLetters);
		$MailLettersEnc = str_replace("\"", "\\\"", $MailLettersEnc);
		$MailIndexes = "";
		for ($i = 0; $i < strlen($MailLink); $i ++) {
			$index = strpos($MailLetters, substr($MailLink, $i, 1));
			$index += 48;
			$MailIndexes .= chr($index);
		}
		$MailIndexes = str_replace("\\", "\\\\", $MailIndexes);
		$MailIndexes = str_replace("\"", "\\\"", $MailIndexes);

		$res = "<script type='text/javascript'>";
		$res .= "/*<![CDATA[*/";
		$res .= "ML=\"".str_replace("<", "xxxx", $MailLettersEnc)."\";";
		$res .= "MI=\"".str_replace("<", "xxxx", $MailIndexes)."\";";
		$res .= "ML=ML.replace(/xxxx/g, '<');";
		$res .= "MI=MI.replace(/xxxx/g, '<');";	$res .= "OT=\"\";";
		$res .= "for(j=0;j < MI.length;j++){";
		$res .= "OT+=ML.charAt(MI.charCodeAt(j)-48);";
		$res .= "}document.write(OT);";
		$res .= "/*]]>*/";
		$res .= "</script>";

		return $res;
	} else {
		return $email;
	}
}

// Format spaces and tabs in code bb tags
function formatcode($text) {
	$text = str_replace("  ", "&nbsp; ", $text);
	$text = str_replace("  ", " &nbsp;", $text);
	$text = str_replace("\t", "&nbsp; &nbsp;", $text);
	$text = preg_replace("/^ {1}/m", "&nbsp;", $text);
	return $text;
}

// Highlights given words in subject
// Don't forget to remove later
function highlight_words($word, $subject) {
	for($i = 0, $l = count($word); $i < $l; $i++) {
		$word[$i] = str_replace(array("\\", "+", "*", "?", "[", "^", "]", "$", "(", ")", "{", "}", "=", "!", "<", ">", "|", ":", "#", "-", "_"), "", $word[$i]);
		if (!empty($word[$i])) {
			$subject = preg_replace("#($word[$i])(?![^<]*>)#i", "<span style='background-color:yellow;color:#333;font-weight:bold;padding-left:2px;padding-right:2px'>\${1}</span>", $subject);
		}
	}
	return $subject;
}


// This function sanitises news & article submissions
function descript($text, $striptags = true) {
	// Convert problematic ascii characters to their true values
	$search = array("40","41","58","65","66","67","68","69","70",
		"71","72","73","74","75","76","77","78","79","80","81",
		"82","83","84","85","86","87","88","89","90","97","98",
		"99","100","101","102","103","104","105","106","107",
		"108","109","110","111","112","113","114","115","116",
		"117","118","119","120","121","122"
		);
	$replace = array("(",")",":","a","b","c","d","e","f","g","h",
		"i","j","k","l","m","n","o","p","q","r","s","t","u",
		"v","w","x","y","z","a","b","c","d","e","f","g","h",
		"i","j","k","l","m","n","o","p","q","r","s","t","u",
		"v","w","x","y","z"
		);
	$entities = count($search);
	for ($i=0; $i < $entities; $i++) {
		$text = preg_replace("#(&\#)(0*".$search[$i]."+);*#si", $replace[$i], $text);
	}
	$text = preg_replace('#(&\#x)([0-9A-F]+);*#si', "", $text);
	$text = preg_replace('#(<[^>]+[/\"\'\s])(onmouseover|onmousedown|onmouseup|onmouseout|onmousemove|onclick|ondblclick|onfocus|onload|xmlns)[^>]*>#iU', ">", $text);
	$text = preg_replace('#([a-z]*)=([\`\'\"]*)script:#iU', '$1=$2nojscript...', $text);
	$text = preg_replace('#([a-z]*)=([\`\'\"]*)javascript:#iU', '$1=$2nojavascript...', $text);
	$text = preg_replace('#([a-z]*)=([\'\"]*)vbscript:#iU', '$1=$2novbscript...', $text);
	$text = preg_replace('#(<[^>]+)style=([\`\'\"]*).*expression\([^>]*>#iU', "$1>", $text);
	$text = preg_replace('#(<[^>]+)style=([\`\'\"]*).*behaviour\([^>]*>#iU', "$1>", $text);
	if ($striptags) {
		do {
			$thistext = $text;
			$text = preg_replace('#</*(applet|meta|xml|blink|link|style|script|embed|object|iframe|frame|frameset|ilayer|layer|bgsound|title|base)[^>]*>#i', "", $text);
		} while ($thistext != $text);
	}
	return $text;
}

// Scan image files for malicious code
function verify_image($file) {
	$txt = file_get_contents($file);
	$image_safe = true;
	if (preg_match('#<?php#i', $txt)) { $image_safe = false; } //edit
	elseif (preg_match('#&(quot|lt|gt|nbsp|<?php);#i', $txt)) { $image_safe = false; }
	elseif (preg_match("#&\#x([0-9a-f]+);#i", $txt)) { $image_safe = false; }
	elseif (preg_match('#&\#([0-9]+);#i', $txt)) { $image_safe = false; }
	elseif (preg_match("#([a-z]*)=([\`\'\"]*)script:#iU", $txt)) { $image_safe = false; }
	elseif (preg_match("#([a-z]*)=([\`\'\"]*)javascript:#iU", $txt)) { $image_safe = false; }
	elseif (preg_match("#([a-z]*)=([\'\"]*)vbscript:#iU", $txt)) { $image_safe = false; }
	elseif (preg_match("#(<[^>]+)style=([\`\'\"]*).*expression\([^>]*>#iU", $txt)) { $image_safe = false; }
	elseif (preg_match("#(<[^>]+)style=([\`\'\"]*).*behaviour\([^>]*>#iU", $txt)) { $image_safe = false; }
	elseif (preg_match("#</*(applet|link|style|script|iframe|frame|frameset)[^>]*>#i", $txt)) { $image_safe = false; }
	return $image_safe;
}

// Replace offensive words with the defined replacement word
function censorwords($text) {
	global $settings;
	if ($settings['bad_words_enabled'] == "1" && $settings['bad_words'] != "" ) {
		$word_list = explode("\r\n", $settings['bad_words']);
		for ($i=0; $i < count($word_list); $i++) {
			if ($word_list[$i] != "") $text = preg_replace("/".$word_list[$i]."/si", $settings['bad_word_replace'], $text);
		}
	}
	return $text;
}

// Display the user's level
function getuserlevel($userlevel) {
	global $locale;
	if ($userlevel == 101) { return $locale['user1'];
	} elseif ($userlevel == 102) { return $locale['user2'];
	} elseif ($userlevel == 103) { return $locale['user3']; }
}

// Display the user's status
function getuserstatus($userstatus) {
	global $locale;
	if ($userstatus == 0) { return $locale['status0'];
	} elseif ($userstatus == 1) { return $locale['status1'];
	} elseif ($userstatus == 2) { return $locale['status2'];
	} elseif ($userstatus == 3) { return $locale['status3'];
	} elseif ($userstatus == 4) { return $locale['status4'];
	} elseif ($userstatus == 5) { return $locale['status5'];
	} elseif ($userstatus == 6) { return $locale['status6'];
	} elseif ($userstatus == 7) { return $locale['status7'];
	} elseif ($userstatus == 8) { return $locale['status8']; }
}

// Check if Administrator has correct rights assigned
function checkrights($right) {
	if (iADMIN && in_array($right, explode(".", iUSER_RIGHTS))) {
		return true;
	} else {
		return false;
	}
}

function checkAdminPageAccess($right) {
	if (!checkrights($right) || !defined("iAUTH") || !isset($_GET['aid']) || $_GET['aid'] != iAUTH) {
		return false;
	} else {
		return true;
	}
}

// Check if user is assigned to the specified user group
function checkgroup($group) {
	if (iSUPERADMIN) { return true; }
	elseif (iADMIN && ($group == "0" || $group == "101" || $group == "102")) { return true;
	} elseif (iMEMBER && ($group == "0" || $group == "101")) { return true;
	} elseif (iGUEST && $group == "0") { return true;
	} elseif (iMEMBER && $group && in_array($group, explode(".", iUSER_GROUPS))) {
		return true;
	} else {
		return false;
	}
}

// Cache groups mysql
function cache_groups() {
	global $groups_cache;
	$result = dbquery("SELECT * FROM ".DB_USER_GROUPS." ORDER BY group_id ASC");
	if (dbrows($result)) {
		$groups_cache = array();
		while ($data = dbarray($result)) {
			$groups_cache[] = $data;
		}
	} else {
		$groups_cache = array();
	}
}

// Compile access levels & user group array
function getusergroups() {
	global $locale, $groups_cache;
	$groups_array = array(
		array("0", $locale['user0']),
		array("101", $locale['user1']),
		array("102", $locale['user2']),
		array("103", $locale['user3'])
	);
	if (!$groups_cache) { cache_groups(); }
	if (is_array($groups_cache) && count($groups_cache)) {
		foreach ($groups_cache as $group) {
			array_push($groups_array, array($group['group_id'], $group['group_name']));
		}
	}
	return $groups_array;
}

// Get the name of the access level or user group
function getgroupname($group_id, $return_desc = false) {
	global $locale, $groups_cache;
	if ($group_id == "0") { return $locale['user0'];
	} elseif ($group_id == "101") { return $locale['user1']; exit;
	} elseif ($group_id == "102") { return $locale['user2']; exit;
	} elseif ($group_id == "103") { return $locale['user3']; exit;
	} else {
		if (!$groups_cache) { cache_groups(); }
		if (is_array($groups_cache) && count($groups_cache)) {
			foreach ($groups_cache as $group) {
				if ($group_id == $group['group_id']) { return ($return_desc ? ($group['group_description'] ? $group['group_description'] : '-') : $group['group_name']); exit; }
			}
		}
	}
	return $locale['user_na'];
}

// Getting the access levels used when asking the database for data
function groupaccess($field) {
	if (iGUEST) { return "$field = '0'";
	} elseif (iSUPERADMIN) { return "1 = 1";
	} elseif (iADMIN) { $res = "($field='0' OR $field='101' OR $field='102'";
	} elseif (iMEMBER) { $res = "($field='0' OR $field='101'";
	}
	if (iUSER_GROUPS != "" && !iSUPERADMIN) { $res .= " OR $field='".str_replace(".", "' OR $field='", iUSER_GROUPS)."'"; }
	$res .= ")";
	return $res;
}

// Create a list of files or folders and store them in an array
// You may filter out extensions by adding them to $extfilter as:
// $ext_filter = "gif|jpg"
function makefilelist($folder, $filter, $sort = true, $type = "files", $ext_filter = "") {
	$res = array();
	$filter = explode("|", $filter);
	if ($type == "files" && !empty($ext_filter)) {
		$ext_filter = explode("|", strtolower($ext_filter));
	}
	$temp = opendir($folder);
	while ($file = readdir($temp)) {
		if ($type == "files" && !in_array($file, $filter)) {
			if (!empty($ext_filter)) {
				if (!in_array(substr(strtolower(stristr($file, '.')), +1), $ext_filter) && !is_dir($folder.$file)) { $res[] = $file; }
			} else {
				if (!is_dir($folder.$file)) { $res[] = $file; }
			}
		} elseif ($type == "folders" && !in_array($file, $filter)) {
			if (is_dir($folder.$file)) { $res[] = $file; }
		}
	}
	closedir($temp);
	if ($sort) { sort($res); }
	return $res;
}

// Create a selection list from an array created by makefilelist()
function makefileopts($files, $selected = "") {
	$res = "";
	for ($i = 0; $i < count($files); $i++) {
		$sel = ($selected == $files[$i] ? " selected='selected'" : "");
		$res .= "<option value='".$files[$i]."'$sel>".$files[$i]."</option>\n";
	}
	return $res;
}

// Making Page Navigation
function makepagenav($start, $count, $total, $range = 0, $link = "", $getname = "rowstart") {
	global $locale;

	if ($link == "") { $link = FUSION_SELF."?"; }
	if (!preg_match("#[0-9]+#", $count) || $count == 0) return false;
	
	$pg_cnt = ceil($total / $count);
	if ($pg_cnt <= 1) { return ""; }

	$idx_back = $start - $count;
	$idx_next = $start + $count;
	$cur_page = ceil(($start + 1) / $count);

	$res = "<li class='qaf'>". $locale['global_092']." ".$cur_page.$locale['global_093'].$pg_cnt.": </li>\n";
	if ($idx_back >= 0) {
		if ($cur_page > ($range + 1)) {
			$res .= "<li class='say'><a href='".$link.$getname."=0'>1</a></li>";
			if ($cur_page != ($range + 2)) {
				$res .= "<li class='say'><span>...</span></li>";
			}
		}
	}
	$idx_fst = max($cur_page - $range, 1);
	$idx_lst = min($cur_page + $range, $pg_cnt);
	if ($range == 0) {
		$idx_fst = 1;
		$idx_lst = $pg_cnt;
	}
	for ($i = $idx_fst; $i <= $idx_lst; $i++) {
		$offset_page = ($i - 1) * $count;
		if ($i == $cur_page) {
			$res .= "<li class='say active'><span>".$i."</span></li>";
		} else {
			$res .= "<li class='say'><a href='".$link.$getname."=".$offset_page."'>".$i."</a></li>";
		}
	}
	if ($idx_next < $total) {
		if ($cur_page < ($pg_cnt - $range)) {
			if ($cur_page != ($pg_cnt - $range - 1)) {
				$res .= "<li class='say'><span>...</span></li>";
			}
			$res .= "<li class='say'><a href='".$link.$getname."=".($pg_cnt - 1) * $count."'>".$pg_cnt."</a></li>\n";
		}
	}

	return "<div class='pagenav'>\n<ul>\n".$res."</ul>\n</div>\n";
}





function navigation($page="", $views, $row, $db, $filter="") {

	$page = (INT)$page;

	if (!empty($page)) {
		$say = $page;
	} else {
		$say = 1;
	}

	$rowstart = $views*($say-1);

	$sayplus = ($say+1);
	$saycix = ($say-1);

	// $zuurl = (empty($page) ? FUSION_URI . (strrpos(FUSION_URI, "?") ? "&" : "?") : str_replace("page=". $page, "", FUSION_URI));
	// $zuurl = (strrpos($zuurl, "?aid=") ? $zuurl ."&" : $zuurl);

	$zuurl = FUSION_URI;
	$zuurl = explode("?", $zuurl);
	$zuurl = $zuurl[0];
	if ( strrpos($zuurl, "/page_") ) {
		$zuurl = explode("/page_", $zuurl);
		$zuurl = $zuurl[0];
	}

	if ( strrpos(FUSION_URI, "?page=") || strrpos(FUSION_URI, "&amp;page=") ) {
		header('HTTP/1.1 301 Moved Permanently');
		$zuurl = $zuurl ."/page_". (INT)$_GET['page'];
		redirect( $zuurl );
		exit();
	}


	// if (strrpos(FUSION_URI, "?page=0") || strrpos(FUSION_URI, "&amp;page=0") || ($_GET['page']==1) ) {
	// 	header('HTTP/1.1 301 Moved Permanently');
	// 	$zuurl = str_replace("?page=0", "", str_replace("&amp;page=0", "", str_replace("?page=1", "", str_replace("&amp;page=1", "", FUSION_URI))));
	// 	 redirect( $zuurl );
	// 	exit();
	// }


	if (strrpos(FUSION_URI, "/page_0") || $_GET['page']==1 ) {
		header('HTTP/1.1 301 Moved Permanently');
		$zuurl = str_replace("/page_0", "", str_replace("/page_1", "", $zuurl));
		redirect( $zuurl );
		exit();
	}


	$numtot = dbcount("(". $row .")", $db, str_replace("WHERE", "", $filter));

	$numpageblock  = null;
	if ($numtot>=$views) {
		$numpageblock .= "<div class='navigation'>\n";
		$numpageblock .= "	<ul>\n";

		if ($rowstart == 0) {
			$numpageblock .= "		<li><span class='arrows left'>&lt;&lt;&lt;</span></li>\n";
		} else {
			// $numpageblock .= "		<li><a class='arrows left azaz' href='". ($saycix>1 ? $zuurl . "page=". $saycix : str_replace("?page=". $saycix, "", str_replace("&page=". $saycix, "", $zuurl . "page=". $saycix)) ) ."'>&lt;&lt;&lt;</a></li>\n";
			$numpageblock .= "		<li><a class='arrows left azaz' href='". ($saycix>1 ? $zuurl . "/page_". $saycix : $zuurl ) ."'>&lt;&lt;&lt;</a></li>\n";
		}

		if (!isset($_GET['page'])) {
			$page=1;
		} else {
			$page=$_GET['page'];

			if (!ctype_digit($page)) {
				$page=1;
			}

			if ($page<1) {
				$page=1;
			}
		}
		
		$maxpage = ceil($numtot/$views);

		if ($page>$maxpage) {
			$page=$maxpage;
		}

		// echo "numtot: ". $numtot ."<br />";
		// echo "views: ". $views ."<br />";
		// echo "maxpage: ". $maxpage ."<br />";
		// echo "page: ". $page ."<br />";

		// БЛОК для вывода на экран СПИСКА СТРАНИЦ
		if ($page>=4 and $maxpage>5)  {
			// $numpageblock .= "		<li><a class='link first' href=". str_replace("?page=1", "", str_replace("&page=1", "", $zuurl . "page=1")) .">1</a></li>\n";
			$numpageblock .= "		<li><a class='link first' href=". str_replace("/page_1", "", $zuurl . "/page_1") .">1</a></li>\n";
			$numpageblock .= "		<li><span class='link'>...</span></li>\n";
		}

		$f1=$page+2;
		$f2=$page-2;
		
		if ($page==1) {
			$f1=$page+4;
			$f2=$page;
		}
		
		if ($page==2) {
			$f1=$page+3;
			$f2=$page-1;
		}
		
		if ($page==$maxpage) {
			$f1=$page;
			$f2=$page-4;
		}
		
		if ($page==$maxpage-1) {
			$f1=$page+1;
			$f2=$page-3;
		}

		if ($maxpage<4) {
			$f1=$maxpage;
			$f2=1;
		}

		for($i=$f2; $i<=$f1; $i++) {
			if ($page==$i) {
				$numpageblock .= "		<li><span class='link active'>". $i ."</span></li>\n";
			} else {
				// if ($i!=0 && $i<=$maxpage) { $numpageblock .= "		<li><a class='link aaaa' href=". ($i>1 ? $zuurl . "page=". $i : str_replace("?page=". $i, "", str_replace("&page=". $i, "", $zuurl . "page=". $i)) ) .">". $i ."</a></li>\n"; }
				if ($i!=0 && $i<=$maxpage) { $numpageblock .= "		<li><a class='link aaaa' href=". ($i>1 ? $zuurl . "/page_". $i : $zuurl) .">". $i ."</a></li>\n"; }
			}
		}

		if ($page<=$maxpage-3 && $maxpage>5) {
			$numpageblock .= "		<li><span class='link'>...</span></li>\n";
			// $numpageblock .= "		<li><a class='link' href=". $zuurl ."page=". $maxpage .">". $maxpage ."</a></li>\n";
			$numpageblock .= "		<li><a class='link' href=". $zuurl ."/page_". $maxpage .">". $maxpage ."</a></li>\n";
		} else {
			$numpageblock .= "";
		}

		if ($rowstart > $numtot-$views) {
			$numpageblock .= "		<li><span class='arrows right'>&gt;&gt;&gt;</span></li>";
		} else {
			// $numpageblock .= "		<li><a class='arrows right' href='". $zuurl ."page=". $sayplus ."'>&gt;&gt;&gt;</a></li>\n";
			$numpageblock .= "		<li><a class='arrows right' href='". $zuurl ."/page_". $sayplus ."'>&gt;&gt;&gt;</a></li>\n";
		}
		$numpageblock .= "	</ul>\n";
		$numpageblock .= "</div>\n";
	}

	return $numpageblock;
}



// Format the date & time accordingly
function showdate($format, $val) {
	global $settings, $userdata;

	if (isset($userdata['user_offset'])) {
		$offset = $userdata['user_offset']+$settings['serveroffset'];
	} else {
		$offset = $settings['timeoffset']+$settings['serveroffset'];
	}
	if ($format == "shortdate" || $format == "longdate" || $format == "forumdate" || $format == "newsdate") {
		return strftime($settings[$format], $val + ($offset * 3600));
	} else {
		return strftime($format, $val + ($offset * 3600));
	}
}

// Translate bytes into kB, MB, GB or TB by CrappoMan, lelebart fix
function parsebytesize($size, $digits = 2, $dir = false) {
	global $locale;
	$kb = 1024; $mb = 1024 * $kb; $gb= 1024 * $mb; $tb = 1024 * $gb;
	if (($size == 0) && ($dir)) { return $locale['global_460']; }
	elseif ($size < $kb) { return $size.$locale['global_461']; }
	elseif ($size < $mb) { return round($size / $kb,$digits).$locale['global_462']; }
	elseif ($size < $gb) { return round($size / $mb,$digits).$locale['global_463']; }
	elseif ($size < $tb) { return round($size / $gb,$digits).$locale['global_464']; }
	else { return round($size / $tb, $digits).$locale['global_465']; }
}

// User profile link
function profile_link($user_id, $user_name, $user_status, $class = "profile-link") {
	global $locale, $settings;

	$class = ($class ? " class='$class'" : "");

	if ((in_array($user_status, array(0, 3, 7)) || checkrights("M")) && (iMEMBER || $settings['hide_userprofiles'] == "0")) {
		$link = "<a href='".BASEDIR."profile.php?lookup=".$user_id."'".$class.">".$user_name."</a>";
	} elseif ($user_status == "5" || $user_status == "6") {
		$link = $locale['user_anonymous'];
	} else {
		$link = $user_name;
	}

	return $link;
}

function viewvalyuta($view, $table) {
	global $currency;
	$return = array();
	foreach ($currency as $key => $value) {
		if ($value['currency_'. $table .'']==$view) {
			$return = $value;
		}
	} // foreach $component
	return $return;
} // Companent info

function viewcena($price="") {
	
	$viewvalyuta = viewvalyuta(CURRENCYSET, "id");

	$price = number_format($price, $viewvalyuta['currency_decimal_place'], ".", " ");
	$return = ($viewvalyuta['currency_symbol_left'] ? "<span class='cost_simvol icon_". strtolower($viewvalyuta['currency_code']) ."'>". $viewvalyuta['currency_symbol_left'] ."</span>" : "") . $price . ($viewvalyuta['currency_symbol_right'] ? "<span class='cost_simvol icon_". strtolower($viewvalyuta['currency_code']) ."'>". $viewvalyuta['currency_symbol_right'] ."</span>" : "");

	return $return;

} // Yesli CurrSes rovno Valyutu



function viewseourl($view, $table, $component="") {

	// Fetch the seourl from the database
	$seourl = array();
	$result = dbquery("SELECT * FROM ". DB_SEOURL . ($component ? " WHERE seourl_component=". $component : ""));
	if (dbrows($result)) {
		while ($data = dbarray($result)) {
			$seourl[] = $data;
		}
	}
	
	$return = array();
	foreach ($seourl as $key => $value) {
		if ($value['seourl_'.$table.'']==$view) {
			$return = $value;
		}
	} // foreach $seourl
	return $return;
} // Seourl info


function viewcompanent($view, $table) {
	global $components;
				$return = array();
		foreach ($components as $key => $value) {
			if ($value['components_'.$table.'']==$view) {
				$return = $value;
			}
		} // foreach $component
	return $return;
} // Companent info




//generate Seo Random String
function seorandomstring($length = 10) {
    $characters = 'abcdefghijklmnopqrstuvwxyz';
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $randomString;
}

// Transliterate characters
// function autocrateseourls($st) {

//   //$st = iconv('utf-8', 'windows-1251', $st);

//   $st = strtr($st, 
//     "àáâãäåæçèéêëìíîïðñòóôûýÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÛÝ",
//     "abvgdegziyklmnoprstufieABVGDEGZIYKLMNOPRSTUFIE"
//   );
//   $st = strtr($st, array(
//     '¸'=>"yo",    'õ'=>"h",  'ö'=>"ts",  '÷'=>"ch", 'ø'=>"sh",  
//     'ù'=>"shch",  'ú'=>'',   'ü'=>'',    'þ'=>"yu", 'ÿ'=>"ya",
//     '¨'=>"Yo",    'Õ'=>"H",  'Ö'=>"Ts",  '×'=>"Ch", 'Ø'=>"Sh",
//     'Ù'=>"Shch",  'Ú'=>'',   'Ü'=>'',    'Þ'=>"Yu", 'ß'=>"Ya",
//   ));

// 	$st = strtolower($st);
// 	$st = str_replace(" ", "_", $st);
// 	preg_match_all("/[a-zA-Z0-9._-]/", $st, $matches);

// 	foreach ($matches as $key => $value) {
// 		foreach ($value as $key => $value) {
// 			$str .= $value;
// 		}
// 	}

//   return $str;
// }



function autocrateseourls($string)
{
    $converter = array(
        'а' => 'a',   'б' => 'b',   'в' => 'v',
        'г' => 'g',   'д' => 'd',   'е' => 'e',
        'ё' => 'e',   'ж' => 'zh',  'з' => 'z',
        'и' => 'i',   'й' => 'y',   'к' => 'k',
        'л' => 'l',   'м' => 'm',   'н' => 'n',
        'о' => 'o',   'п' => 'p',   'р' => 'r',
        'с' => 's',   'т' => 't',   'у' => 'u',
        'ф' => 'f',   'х' => 'h',   'ц' => 'c',
        'ч' => 'ch',  'ш' => 'sh',  'щ' => 'sch',
        'ь' => "",  'ы' => 'y',   'ъ' => "",
        'э' => 'e',   'ю' => 'yu',  'я' => 'ya',

        'А' => 'A',   'Б' => 'B',   'В' => 'V',
        'Г' => 'G',   'Д' => 'D',   'Е' => 'E',
        'Ё' => 'E',   'Ж' => 'Zh',  'З' => 'Z',
        'И' => 'I',   'Й' => 'Y',   'К' => 'K',
        'Л' => 'L',   'М' => 'M',   'Н' => 'N',
        'О' => 'O',   'П' => 'P',   'Р' => 'R',
        'С' => 'S',   'Т' => 'T',   'У' => 'U',
        'Ф' => 'F',   'Х' => 'H',   'Ц' => 'C',
        'Ч' => 'Ch',  'Ш' => 'Sh',  'Щ' => 'Sch',
        'Ь' => "",  'Ы' => 'Y',   'Ъ' => "",
        'Э' => 'E',   'Ю' => 'Yu',  'Я' => 'Ya',
    );

	$string = strtr($string, $converter);

	$string = strtolower($string);

	if ( preg_match("/.php/", $string, $matches) ) {
		$string = str_replace(".php", "", $string);
		$string_format = ".php";
	} else if ( preg_match("/.html/", $string, $matches) ) {
		$string = str_replace(".html", "", $string);
		$string_format = ".html";
	} else if ( preg_match("/.htm/", $string, $matches) ) {
		$string = str_replace(".htm", "", $string);
		$string_format = ".htm";
	} else {
		$string_format = "";
	}

    //Strip any unwanted characters
    $string = preg_replace("/[^a-z0-9_\s-]/", "", $string);
    //Clean multiple dashes or whitespaces
    $string = preg_replace("/[\s-]+/", " ", $string);
    //Convert whitespaces and underscore to dash
    $string = preg_replace("/[\s_]/", "_", $string);

    if ($string_format) {
    	$string = $string . $string_format;
    }

		// echo "<pre>";
		// print_r($string);
		// echo "</pre>";

    return $string;
}




######## Партнерская программа ########
if ($_GET['partner']) {
	$_SESSION['partner'] = (INT)$_GET['partner'];
	header("HTTP/1.1 301 Moved Permanently");
	header("Location: /");
	exit();
}
######## Партнерская программа ########


######## UTM метки ########
if ($_GET['utm_source']) {
	require_once INCLUDES."sendmail_include.php";

	$utm_mailname = "IssoHost";
	$utm_email = "no-reply@issohost.com";
	$utm_subject = "UTM Metki ". $_GET['utm_source'];

	$utm_message .= "date: ". date("d.m.Y | H:i:s") ."\n";

	if ($_GET['utm_type']) { $utm_message .= "utm_type: ". stripinput($_GET['utm_type']) ."\n"; }
	if ($_GET['utm_source']) { $utm_message .= "utm_source: ". stripinput($_GET['utm_source']) ."\n"; }
	if ($_GET['utm_added']) { $utm_message .= "utm_added: ". stripinput($_GET['utm_added']) ."\n"; }
	if ($_GET['utm_block']) { $utm_message .= "utm_block: ". stripinput($_GET['utm_block']) ."\n"; }
	if ($_GET['utm_pos']) { $utm_message .= "utm_pos: ". stripinput($_GET['utm_pos']) ."\n"; }
	if ($_GET['utm_key']) { $utm_message .= "utm_key: ". stripinput($_GET['utm_key']) ."\n"; }
	if ($_GET['utm_campaign']) { $utm_message .= "utm_campaign: ". stripinput($_GET['utm_campaign']) ."\n"; }
	if ($_GET['utm_ad']) { $utm_message .= "utm_ad: ". stripinput($_GET['utm_ad']) ."\n"; }
	if ($_GET['utm_phrase']) { $utm_message .= "utm_phrase: ". stripinput($_GET['utm_phrase']) ."\n"; }

	if ($_GET['utm_medium']) { $utm_message .= "utm_medium: ". stripinput($_GET['utm_medium']) ."\n"; }
	if ($_GET['utm_content']) { $utm_message .= "utm_content: ". stripinput($_GET['utm_content']) ."\n"; }
	if ($_GET['utm_term']) { $utm_message .= "utm_term: ". stripinput($_GET['utm_term']) ."\n"; }
	sendemail($settings['siteusername'], $settings['siteemail'], $utm_mailname, $utm_email, $utm_subject, $utm_message);
}
######## UTM метки ########






function YouTubeVideo( $y_search_q, $maxResults=1, $part="snippet" ) {

	$DeveloperKey = 'AIzaSyCbwDKMv_2zqSriJOyPkTt5gTOKGvDuiJc';

	$client = new Google_Client();
	$client->setDeveloperKey($DeveloperKey);
	$youtube = new Google_Service_YouTube($client);

	try {
		$searchResponse = $youtube->search->listSearch('id', array(
		  'part' => $part,
		  'q' => $y_search_q,
		  'maxResults' => $maxResults,
		));

		$videos = "";
		$channels = "";
		$playlists = "";


		foreach ($searchResponse['items'] as $searchResult) {

			// echo "<pre>";
			// print_r($searchResult);
			// echo "</pre>";
			// echo "<hr>";

		  switch ($searchResult['id']['kind']) {
				case 'youtube#video':
					$videos .= sprintf("<h2>%s</h2><iframe class='catalog_video' width='605' height='350' src='https://www.youtube.com/embed/%s' frameborder='0' allowfullscreen></iframe>\n", $searchResult['snippet']['title'], $searchResult['id']['videoId']);
				break;
				// case 'youtube#channel':
				// 	$channels .= sprintf("<h2>%s</h2><iframe class='catalog_video' width='605' height='350' src='https://www.youtube.com/embed/videoseries?list=%s' frameborder='0' allowfullscreen></iframe>\n", $searchResult['snippet']['title'], $searchResult['id']['channelId']);
				// break;
				case 'youtube#playlist':
					$playlists .= sprintf("<h2>%s</h2><iframe class='catalog_video' width='605' height='350' src='https://www.youtube.com/embed/videoseries?list=%s' frameborder='0' allowfullscreen></iframe>\n", $searchResult['snippet']['title'], $searchResult['id']['playlistId']);
				break;
			}
		} // foreach searchResponse items

		$htmlBody .= $videos;
		$htmlBody .= $channels;
		$htmlBody .= $playlists;

// $htmlBody .= <<<END
// $videos
// END;


	} catch (Google_Service_Exception $e) {
		$htmlBody .= sprintf('<p>A service error occurred: <code>%s</code></p>',
		htmlspecialchars($e->getMessage()));
	} catch (Google_Exception $e) {
		$htmlBody .= sprintf('<p>An client error occurred: <code>%s</code></p>',
		htmlspecialchars($e->getMessage()));
	}

	return $htmlBody;

} // function YouTubeVideo





include INCLUDES."system_images.php";
?>
