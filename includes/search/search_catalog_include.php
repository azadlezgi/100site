<?php

if (!defined("IN_FUSION")) { die("Access Denied"); }

include LOCALE.LOCALESET."search/catalog.php";

if ($_GET['sort'] == "datestamp") {
		$sortby = "catalog_date";
} else if ($_GET['sort'] == "subject") {
	$sortby = "catalog_name";
} else if ($_GET['sort'] == "author") {
	$sortby = "catalog_name";
}

$ssubject = search_querylike("catalog_name");
$smessage = search_querylike("catalog_content");
$ssnippet = search_querylike("catalog_content");

if ($_GET['fields'] == 0) { $fieldsvar = search_fieldsvar($ssubject); }
else if ($_GET['fields'] == 1) { $fieldsvar = search_fieldsvar($smessage, $ssnippet); }
else if ($_GET['fields'] == 2) { $fieldsvar = search_fieldsvar($ssubject, $ssnippet, $smessage); }
else { $fieldsvar = ""; }
	

if ($fieldsvar) {

	if (isset($_GET['page'])) { $pagesay = $_GET['page']; }
	else { $pagesay = 1; }
	$rowstart = $settings['catalog_per_page']*($pagesay-1);

	$viewcompanent = viewcompanent("catalog", "name");
	$seourl_component = $viewcompanent['components_id'];

	$result = dbquery("SELECT 
											catalog_id,
											catalog_name,
											catalog_image,
											catalog_content,
											catalog_access,
											seourl_url
			FROM ". DB_CATALOG ."
			LEFT JOIN ". DB_SEOURL ." ON seourl_filedid=catalog_id AND seourl_component=". $seourl_component ."
			WHERE catalog_status='1'
			AND catalog_date<'". FUSION_TODAY ."'
			AND ". groupaccess('catalog_access') ."
			AND ". $fieldsvar ."
			LIMIT ". $rowstart .", ". $settings['catalog_per_page'] ."");

	if (dbrows($result)) { $say = 0;
		while ($data = dbarray($result)) { $say++;

			$catalog_id = $data['catalog_id'];
			$catalog_name = unserialize($data['catalog_name']);
			$catalog_image = $data['catalog_image'];
			$catalog_content = unserialize($data['catalog_content']);
			$catalog_access = $data['catalog_access'];
			$seourl_url = $data['seourl_url'];

			$search_result = "";
			$search_result .= "	<div class='catalog_li". ($say==4 ? " last" : "") ."'>\n";
			$search_result .= "		<div class='catalog artile". $catalog_id ."'>\n";
			$search_result .= "			<a href='/". $seourl_url ."' class='catalog_title'>". $catalog_name[LOCALESHORT] ."</a>\n";
			$search_result .= "			<a href='/". $seourl_url ."' class='catalog_img'><img src='". ($catalog_image ? IMAGES_C_T . $catalog_image : IMAGES .'imagenotfound.jpg') ."' alt=''></a>\n";
			$search_result .= "			<a href='/". $seourl_url ."' class='art_content'><p>". mb_substr(strip_tags(str_replace("&nbsp;", " ", str_replace("><", "> <", htmlspecialchars_decode($catalog_content[LOCALESHORT])))), 0, 300) ."</p>\n";
			$search_result .= "			</a>\n";
			$search_result .= "		</div>\n";
			$search_result .= "	</div>\n";

			if ($say==4) {
				$search_result .= "<div class='clear'></div>\n";
				$say = 0;
			} 
			search_globalarray($search_result);

		} // catalog db whille

		$navigation_result = search_navigation($rows);

	} else {
		$items_count .= THEME_BULLET."&nbsp;0 ".$locale['a402']." ".$locale['522']."<br />\n";
	} // catalog db query
} // if ($fieldsvar
?>