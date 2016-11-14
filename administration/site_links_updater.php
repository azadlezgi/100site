<?php

require_once "../includes/maincore.php";

if (!checkrights("SL") || !defined("iAUTH") || !isset($_GET['aid']) || $_GET['aid'] != iAUTH) { redirect("../index.php"); }

include LOCALE.LOCALESET."admin/sitelinks.php";

if (isset($_GET['listItem']) && is_array($_GET['listItem'])) {
	foreach ($_GET['listItem'] as $position => $item) {
		if (isnum($position) && isnum($item)) {
			dbquery("UPDATE ".DB_SITE_LINKS." SET link_order='".($position+1)."' WHERE link_id='".$item."'");
		}
	}
	header("Content-Type: text/html; charset=".$locale['charset']."\n");
	echo "<div id='close-message'><div class='admin-message'>".$locale['455']."</div></div>";
}
?>