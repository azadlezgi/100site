<?php

if (!defined("IN_FUSION")) { die("Access Denied"); }

include LOCALE.LOCALESET."404.php";

	header("HTTP/1.0 404 Not Found");
	if (!empty($locale['title'])) set_title($locale['title']);
	if (!empty($locale['description'])) set_meta("description", $locale['description']);
	if (!empty($locale['keywords'])) set_meta("keywords", $locale['keywords']);

	opentable($locale['h1']);
		echo "<div class='error404'>\n<img src='". IMAGES ."404.png' alt=''>\n</div>\n";
	closetable();
	
?>