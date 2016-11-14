<?php
	require_once "../includes/maincore.php";

	if (!defined("iAUTH") || !isset($_GET['aid']) || $_GET['aid'] != iAUTH) { redirect("../index.php"); }

	require_once THEMES ."templates/admin_header.php";
	include LOCALE . LOCALESET ."admin/404.php";

	opentable($locale['400']);

	echo "<div class='admin_404'>\n";
	echo " <p>". $locale['410'] ."</p>\n";
	echo "	<img src='". ADMIN ."images/access-denied.jpg' alt='' />\n";
	echo "</div>\n";

	closetable();
	require_once THEMES."templates/footer.php";
?>