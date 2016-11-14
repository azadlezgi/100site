<?php

	if (!defined("IN_FUSION")) { die("Access Denied"); }

	require_once INCLUDES."comments_include.php";
	require_once INCLUDES."ratings_include.php";
	include LOCALE . LOCALESET ."custom_pages.php";

	//if (!isset($_GET['page_id']) || !isnum($_GET['page_id'])) { redirect("index.php"); }
	//if (!isset($_GET['rowstart']) || !isnum($_GET['rowstart'])) { $_GET['rowstart'] = 0; }

	$viewcompanent = viewcompanent("custom_pages", "name");
	$seourl_component = $viewcompanent['components_id'];

	$cp_result = dbquery("SELECT 
									page_title,
									page_description,
									page_keywords,
									page_name,
									page_h1,
									page_access,
									page_content,
									page_comments,
									page_ratings,
									seourl_url
	FROM ". DB_CUSTOM_PAGES ."
	LEFT JOIN ". DB_SEOURL ." ON seourl_filedid=page_id AND seourl_component=". $seourl_component ."
	WHERE page_id='". $filedid ."'
	AND page_status='1'");
	if (dbrows($cp_result)) {
		$cp_data = dbarray($cp_result);

		$page_title = unserialize($cp_data['page_title']);
		$page_description = unserialize($cp_data['page_description']);
		$page_keywords = unserialize($cp_data['page_keywords']);
		$page_name = unserialize($cp_data['page_name']);
		$page_h1 = unserialize($cp_data['page_h1']);
		$page_access = $cp_data['page_access'];
		$page_content = unserialize($cp_data['page_content']);
		$page_comments = $cp_data['page_comments'];
		$page_ratings = $cp_data['page_ratings'];
		$page_seourl_url = $cp_data['seourl_url'];

		set_title( ($page_title[LOCALESHORT] ? $page_title[LOCALESHORT] : $page_name[LOCALESHORT]) );
		set_meta("description",  ($page_description[LOCALESHORT] ? $page_description[LOCALESHORT] : "") );
		set_meta("keywords",  ($page_keywords[LOCALESHORT] ? $page_keywords[LOCALESHORT] : "") );
		add_to_head ("<link rel='canonical' href='http://". FUSION_HOST ."/". ($settings['opening_page']!=$page_seourl_url ? $page_seourl_url : "") ."' />");
		add_to_head ("<meta name='robots' content='index, follow' />");
		add_to_head ("<meta name='author' content='IssoHost' />");

		
		if (FUSION_URI!="/") {
		echo "<div class='breadcrumb'>\n";
		echo "	<ul>\n";
		echo "		<li><a href='". BASEDIR ."'>". $locale['640'] ."</a></li>\n";
		echo "		<li><span>". $page_name[LOCALESHORT] ."</span></li>\n";
		echo "	</ul>\n";
		echo "</div>\n";
		}


		if ($page_h1[LOCALESHORT]) {
			opentable($page_h1[LOCALESHORT]);
		} else {
			opentable($page_name[LOCALESHORT]);
		}


if (iADMIN && (iUSER_RIGHTS != "" || iUSER_RIGHTS != "C")) {
	echo "<div class='admin_buttons'>\n";
	echo "	<a href='/". ADMIN ."custom_pages.php".  $aidlink ."&action=edit&id=". $article_id ."' target='_blank' class='button_edit'><i class='fa fa-pencil'></i>". $locale['500'] ."</a>\n";
	echo "	<a href='/". ADMIN ."custom_pages.php".  $aidlink ."&action=delete&id=". $article_id ."' target='_blank' class='button_delete'  onclick='return DeleteOk();'><i class='fa fa-times'></i>". $locale['501'] ."</a>\n";
	echo "</div>\n";

	add_to_footer ("<script type='text/javascript'>
		function DeleteOk() {
			return confirm('". $locale['502'] ."');
		}
	</script>");
}


		if (checkgroup($page_access)) {

			echo "<div class='custom_pages'>\n";

			ob_start();
			eval("?>".htmlspecialchars_decode($page_content[LOCALESHORT])."<?php ");
			$custompage = ob_get_contents();
			ob_end_flush();
			$custompage = preg_split("/<!?--\s*pagebreak\s*-->/i", $custompage);
			$pagecount = count($custompage);
			echo $custompage[$_GET['rowstart']];

			echo "</div>\n";

		} else {
			echo "<div class='admin-message' style='text-align:center'><br /><img style='border:0px; vertical-align:middle;' src ='".BASEDIR."images/warn.png' alt=''/><br /> ".$locale['400']."<br /><a href='index.php' onclick='javascript:history.back();return false;'>".$locale['403']."</a>\n<br /><br /></div>\n";
		}

		closetable();

		if (isset($pagecount) && $pagecount > 1) {
			echo "<div align='center' style='margin-top:5px;'>\n".makepagenav($_GET['rowstart'], 1, $pagecount, 3, FUSION_SELF."?page_id=".$_GET['page_id']."&amp;")."\n</div>\n";
		}
		echo "<!--custompages-after-content-->\n";
		if (dbrows($cp_result) && checkgroup($cp_data['page_access'])) {
			if ($cp_data['page_comments']) { showcomments("C", DB_CUSTOM_PAGES, "page_id", $_GET['page_id'],FUSION_SELF."?page_id=".$_GET['page_id']); }
			if ($cp_data['page_ratings']) { showratings("C", $_GET['page_id'], FUSION_SELF."?page_id=".$_GET['page_id']); }
		}

	} else {

		include COMPONENTS ."404.php";

	} // db query

?>