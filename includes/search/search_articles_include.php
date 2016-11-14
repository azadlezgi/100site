<?php

if (!defined("IN_FUSION")) { die("Access Denied"); }

include LOCALE.LOCALESET."search/articles.php";

if ($_GET['sort'] == "datestamp") {
		$sortby = "article_date";
} else if ($_GET['sort'] == "subject") {
	$sortby = "article_name";
} else if ($_GET['sort'] == "author") {
	$sortby = "article_name";
}

$ssubject = search_querylike("article_name");
$smessage = search_querylike("article_content");
$ssnippet = search_querylike("article_content");

if ($_GET['fields'] == 0) { $fieldsvar = search_fieldsvar($ssubject); }
else if ($_GET['fields'] == 1) { $fieldsvar = search_fieldsvar($smessage, $ssnippet); }
else if ($_GET['fields'] == 2) { $fieldsvar = search_fieldsvar($ssubject, $ssnippet, $smessage); }
else { $fieldsvar = ""; }
	

if ($fieldsvar) {

	if (isset($_GET['page'])) { $pagesay = $_GET['page']; }
	else { $pagesay = 1; }
	$rowstart = $settings['articles_per_page']*($pagesay-1);

	$viewcompanent = viewcompanent("articles", "name");
	$seourl_component = $viewcompanent['components_id'];

	$result = dbquery("SELECT 
											article_id,
											article_name,
											article_image,
											article_content,
											article_access,
											seourl_url
			FROM ". DB_ARTICLES ."
			LEFT JOIN ". DB_SEOURL ." ON seourl_filedid=article_id AND seourl_component=". $seourl_component ."
			WHERE article_status='1'
			AND article_date<'". FUSION_TODAY ."'
			AND ". groupaccess('article_access') ."
			AND ". $fieldsvar ."
			LIMIT ". $rowstart .", ". $settings['articles_per_page'] ."");

	if (dbrows($result)) { $say = 0;
		while ($data = dbarray($result)) { $say++;

			$article_id = $data['article_id'];
			$article_name = unserialize($data['article_name']);
			$article_image = $data['article_image'];
			$article_content = unserialize($data['article_content']);
			$article_access = $data['article_access'];
			$seourl_url = $data['seourl_url'];

			$search_result = "";
			$search_result .= "	<div class='articles_li". ($say==4 ? " last" : "") ."'>\n";
			$search_result .= "		<div class='articles artile". $article_id ."'>\n";
			$search_result .= "			<a href='/". $seourl_url ."' class='article_title'>". $article_name[LOCALESHORT] ."</a>\n";
			$search_result .= "			<a href='/". $seourl_url ."' class='article_img'><img src='". ($article_image ? IMAGES_A_T . $article_image : IMAGES .'imagenotfound.jpg') ."' alt=''></a>\n";
			$search_result .= "			<a href='/". $seourl_url ."' class='art_content'><p>". mb_substr(strip_tags(str_replace("&nbsp;", " ", str_replace("><", "> <", htmlspecialchars_decode($article_content[LOCALESHORT])))), 0, 300) ."</p>\n";
			$search_result .= "			</a>\n";
			$search_result .= "		</div>\n";
			$search_result .= "	</div>\n";

			if ($say==4) {
				$search_result .= "<div class='clear'></div>\n";
				$say = 0;
			} 
			search_globalarray($search_result);

		} // articles db whille

		$navigation_result = search_navigation($rows);

	} else {
		$items_count .= THEME_BULLET."&nbsp;0 ".$locale['a402']." ".$locale['522']."<br />\n";
	} // articles db query
} // if ($fieldsvar
?>