<?php

if (!defined("IN_FUSION")) { die("Access Denied"); }

require_once INCLUDES."comments_include.php";
require_once INCLUDES."ratings_include.php";
include LOCALE.LOCALESET."articles.php";


$viewcompanent = viewcompanent("articles", "name");
$seourl_component = $viewcompanent['components_id'];

$result = dbquery("SELECT 
								article_id,
								article_title,
								article_description,
								article_keywords,
								article_name,
								article_image,
								article_cat,
								article_h1,
								article_access,
								article_content,
								article_comments,
								article_ratings,
								seourl_url
FROM ". DB_ARTICLES ."
LEFT JOIN ". DB_SEOURL ." ON seourl_filedid=article_id AND seourl_component=". $seourl_component ."
WHERE article_id='". $filedid ."'
AND article_status='1'");

// AND article_date<'". FUSION_TODAY ."'");

if (dbrows($result)) {
	$data = dbarray($result);

								$article_id = $data['article_id'];
								$article_title = unserialize($data['article_title']);
								$article_description = unserialize($data['article_description']);
								$article_keywords = unserialize($data['article_keywords']);
								$article_name = unserialize($data['article_name']);
								$article_image = $data['article_image'];
								$article_cat = $data['article_cat'];
								$article_h1 = unserialize($data['article_h1']);
								$article_access = $data['article_access'];
								$article_content = unserialize($data['article_content']);
								$article_allow_comments = $data['article_comments'];
								$article_allow_ratings = $data['article_ratings'];
								$article_seourl_url = $data['seourl_url'];

		set_title( ($article_title[LOCALESHORT] ? $article_title[LOCALESHORT] : $article_name[LOCALESHORT]) );
		set_meta("description",  ($article_description[LOCALESHORT] ? $article_description[LOCALESHORT] : "") );
		set_meta("keywords",  ($article_keywords[LOCALESHORT] ? $article_keywords[LOCALESHORT] : "") );
		// add_to_head ("<link rel='canonical' href='http://". FUSION_HOST ."/". ($settings['opening_page']!=$article_seourl_url ? $article_seourl_url : "") ."' />");
		// add_to_head ("<meta name='robots' content='index, follow' />");
		// add_to_head ("<meta name='author' content='IssoHost' />");

$viewcompanent = viewcompanent("article_cats", "name");
$seourl_component = $viewcompanent['components_id'];

$c_result = dbquery("SELECT 
								article_cat_h1,
								article_cat_name,
								seourl_url
FROM ". DB_ARTICLE_CATS ."
LEFT JOIN ". DB_SEOURL ." ON seourl_filedid=article_cat_id AND seourl_component=". $seourl_component ."
WHERE article_cat_id='". $article_cat ."'");
if (dbrows($c_result)) {
	$c_data = dbarray($c_result);
	$article_cat_h1 = unserialize($c_data['article_cat_h1']);
	$article_cat_name = unserialize($c_data['article_cat_name']);
} // db query 
		
		if (FUSION_URI!="/") {
		echo "<div class='breadcrumb'>\n";
		echo "	<ul>\n";
		echo "		<li><a href='/'>". $locale['640'] ."</a></li>\n";
		echo "		<li><a href='/". $c_data['seourl_url'] ."'>". $article_cat_name[LOCALESHORT] ."</a></li>\n";
		echo "		<li><span>". $article_name[LOCALESHORT] ."</span></li>\n";
		echo "	</ul>\n";
		echo "</div>\n";
		}


		echo "<div class='catalog_content'>\n";

		if ($article_h1[LOCALESHORT]) {
			opentable($article_h1[LOCALESHORT]);
		} else {
			opentable($article_name[LOCALESHORT]);
		}


if (iADMIN && (iUSER_RIGHTS != "" || iUSER_RIGHTS != "C")) {
	echo "<div class='admin_buttons'>\n";
	echo "	<a href='/". ADMIN ."articles.php".  $aidlink ."&action=edit&id=". $article_id ."' target='_blank' class='button_edit'><i class='fa fa-pencil'></i>". $locale['500'] ."</a>\n";
	echo "	<a href='/". ADMIN ."articles.php".  $aidlink ."&action=delete&id=". $article_id ."' target='_blank' class='button_delete'  onclick='return DeleteOk();'><i class='fa fa-times'></i>". $locale['501'] ."</a>\n";
	echo "</div>\n";

	add_to_footer ("<script type='text/javascript'>
		function DeleteOk() {
			return confirm('". $locale['502'] ."');
		}
	</script>");
}



if ( file_exists(THEME ."components/article_item.php") ) {
	include THEME ."components/article_item.php";
} else {

			if (checkgroup($article_access)) {

				echo "<div class='article_single'>\n";

				if ($article_image) {
					echo "<a class='article_img meduim_img' href='". IMAGES_A . $article_image ."' rel='lightbox[article_img]' title='". ($article_h1[LOCALESHORT] ? $article_h1[LOCALESHORT] : $article_name[LOCALESHORT]) ."'><img src='". IMAGES_A_T . $article_image ."' alt='". ($article_h1[LOCALESHORT] ? $article_h1[LOCALESHORT] : $article_name[LOCALESHORT]) ."'></a>\n";
				}
				ob_start();
				eval("?>".htmlspecialchars_decode($article_content[LOCALESHORT])."<?php ");
				$custompage = ob_get_contents();
				ob_end_flush();
				$custompage = preg_split("/<!?--\s*pagebreak\s*-->/i", $custompage);
				$pagecount = count($custompage);
				echo $custompage[$_GET['rowstart']];


echo "<div class='clear'></div>";


/* YouTube Video */
include INCLUDES ."Google/autoload.php";
include INCLUDES ."Google/Client.php";
include INCLUDES ."Google/Service/YouTube.php";

$YouTubeVideo = "";
if ($article_h1[LOCALESHORT]) { $YouTubeQuery = trim($article_h1[LOCALESHORT]); }
else { $YouTubeQuery = trim($article_name[LOCALESHORT]); }
$YouTubeVideo = YouTubeVideo( $YouTubeQuery );

if (!$YouTubeVideo) {
	if ($article_cat_h1[LOCALESHORT]) { $YouTubeQuery = trim($article_cat_h1[LOCALESHORT]); }
	else { $YouTubeQuery = trim($article_cat_name[LOCALESHORT]); }
	$YouTubeVideo = YouTubeVideo( $YouTubeQuery );
}
echo $YouTubeVideo;
/* // YouTube Video */



				echo "</div>\n";

			} else {
				echo "<div class='admin-message' style='text-align:center'><br /><img style='border:0px; vertical-align:middle;' src ='".BASEDIR."images/warn.png' alt=''/><br /> ".$locale['400']."<br /><a href='index.php' onclick='javascript:history.back();return false;'>".$locale['403']."</a>\n<br /><br /></div>\n";
			}

} // file_exit components article_item.php

		closetable();

		echo "</div>\n";
}



if (isset($pagecount) && $pagecount > 1) {
    echo "<div align='center' style='margin-top:5px;'>\n".makepagenav($_GET['rowstart'], 1, $pagecount, 3, FUSION_SELF."?article_id=". $filedid ."&amp;")."\n</div>\n";
}
echo "<!--custompages-after-content-->\n";
if (dbrows($result) && checkgroup($data['article_access'])) {
	if ($cp_data['article_allow_comments']) { showcomments("C", DB_ARTICLES, "article_id", $filedid,FUSION_SELF."?article_id=". $filedid); }
	if ($cp_data['article_allow_ratings']) { showratings("C", $filedid, FUSION_SELF."?article_id=". $filedid); }
}

?>