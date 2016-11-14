<?php

if (!defined("IN_FUSION")) { die("Access Denied"); }

include LOCALE.LOCALESET."article_cats.php";

$viewcompanent = viewcompanent("article_cats", "name");
$seourl_component = $viewcompanent['components_id'];

$c_result = dbquery("SELECT 
								article_cat_id,
								article_cat_title,
								article_cat_description,
								article_cat_keywords,
								article_cat_name,
								article_cat_h1,
								article_cat_access,
								article_cat_content,
								seourl_url
FROM ". DB_ARTICLE_CATS ."
LEFT JOIN ". DB_SEOURL ." ON seourl_filedid=article_cat_id AND seourl_component=". $seourl_component ."
WHERE article_cat_id='". $filedid ."'
AND article_cat_status='1'");
if (dbrows($c_result)) {
	$c_data = dbarray($c_result);

								$article_cat_id = $c_data['article_cat_id'];
								$article_cat_title = unserialize($c_data['article_cat_title']);
								$article_cat_description = unserialize($c_data['article_cat_description']);
								$article_cat_keywords = unserialize($c_data['article_cat_keywords']);
								$article_cat_name = unserialize($c_data['article_cat_name']);
								$article_cat_h1 = unserialize($c_data['article_cat_h1']);
								$article_cat_access = $c_data['article_cat_access'];
								$article_cat_content = unserialize($c_data['article_cat_content']);
								$article_cat_seourl_url = $c_data['seourl_url'];

		if (!empty($article_cat_title[LOCALESHORT])) set_title($article_cat_title[LOCALESHORT] . ($_GET['page']>0 ? $locale['500'] . (INT)$_GET['page'] : "") );
		if (!empty($article_cat_description[LOCALESHORT])) set_meta("description", $article_cat_description[LOCALESHORT]);
		if (!empty($article_cat_keywords[LOCALESHORT])) set_meta("keywords", $article_cat_keywords[LOCALESHORT]);
		// add_to_head ("<link rel='canonical' href='http://". FUSION_HOST ."/". ($settings['opening_page']!=$article_cat_seourl_url ? $article_cat_seourl_url : "") ."' />");
		// add_to_head ("<meta name='robots' content='index, follow' />");
		// add_to_head ("<meta name='author' content='IssoHost' />");

		if (FUSION_URI!="/") {
		echo "<div class='breadcrumb'>\n";
		echo "	<ul>\n";
		echo "		<li><a href='". BASEDIR ."'>". $locale['640'] ."</a></li>\n";
		echo "		<li><span>". $article_cat_name[LOCALESHORT] ."</span></li>\n";
		echo "	</ul>\n";
		echo "</div>\n";
		}


		echo "<div class='catalog_content'>\n";

		if ($article_cat_h1[LOCALESHORT]) {
			opentable($article_cat_h1[LOCALESHORT]);
		} else {
			opentable($article_cat_name[LOCALESHORT]);
		}


		if ( file_exists(THEME ."components/article_cats.php") ) {
			include THEME ."components/article_cats.php";
		} else {



			if (checkgroup($article_cat_access)) {



				$result_artcat = dbquery("SELECT 
												article_cat_id,
												article_cat_name,
												article_cat_image,
												article_cat_access,
												seourl_url
				FROM ". DB_ARTICLE_CATS ."
				RIGHT JOIN ". DB_SEOURL ." ON seourl_filedid=article_cat_id AND seourl_component=". $seourl_component ."
				WHERE article_cat_status='1'
				AND article_cat_parent='". $article_cat_id ."'");
				if (dbrows($result_artcat)) {
					echo "<div class='artcats_list'>\n";
					$artcat_say = 0;
					while ($data_artcat = dbarray($result_artcat)) {

						$artcat_id = $data_artcat['article_cat_id'];
						$artcat_name = unserialize($data_artcat['article_cat_name']);
						$artcat_image = $data_artcat['article_cat_image'];
						$artcat_access = $data_artcat['article_cat_access'];
						$artcat_url = $data_artcat['seourl_url'];

						if (checkgroup($artcat_access)) { $artcat_say++;

							echo "	<div class='artcats artcat". $artcat_id . ($artcat_say==4 ? " last" : "") ."'>\n" ;
							echo "		<a href='". BASEDIR . $artcat_url ."' class='artcat_name'>". $artcat_name[LOCALESHORT] ."</a>\n";
							echo "		<a href='". BASEDIR . $artcat_url ."' class='artcat_img'><img src='". ($artcat_image ? IMAGES_AC_T . $artcat_image : IMAGES ."imagenotfound.jpg") ."' alt='". $artcat_name[LOCALESHORT] ."'></a>\n";
							echo "	</div>\n";

							if ($artcat_say==4) {
								echo "<div class='clear'></div>\n";
								$artcat_say=0;
							}

						} // article_cat_access
					} // db while
					echo "	<div class='clear'></div>\n";
					echo "</div>\n";
				} // db query





				if (isset($_GET['page'])) {
					$pagesay = $_GET['page'];
				} else {
					$pagesay = 1;
				}
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
					AND article_cat='". $filedid ."'
					LIMIT ". $rowstart .", ". $settings['articles_per_page'] ."");


				if (dbrows($result)) { $say = 0;
					while ($data = dbarray($result)) { $say++;

						$article_id = $data['article_id'];
						$article_name = unserialize($data['article_name']);
						$article_image = $data['article_image'];
						$article_content = unserialize($data['article_content']);
						$article_access = $data['article_access'];
						$seourl_url = $data['seourl_url'];

						if (checkgroup($article_access)) {

?>

	<div class="articles_li col-sm-6<?php echo ($say==2 ? " clearfix" : ""); ?>">
		<div class="articles artile<?php echo $article_id; ?>">
			<a href="<?php echo BASEDIR . $seourl_url; ?>" class="article_title"><h4><?php echo $article_name[LOCALESHORT]; ?></h4></a>
			<div class="art_content">
				<a href="<?php echo BASEDIR . $seourl_url; ?>" class="article_img"><img src="<?php echo ($article_image ? IMAGES_A_T . $article_image : IMAGES ."imagenotfound.jpg"); ?>" alt=""></a>
				<p><?php echo mb_substr(strip_tags(str_replace("><", "> <", htmlspecialchars_decode($article_content[LOCALESHORT]))), 0, 700) ; ?></p>
				<div class="clear"></div>
			</div>
		</div>
		<div class="clear"></div>
	</div>

<?php

	if ($say==2) {
		$say = 0;
	} 
						} // article_access
					} // db whille

					echo "<div class='clear'></div>\n";

					echo navigation($_GET['page'], $settings['articles_per_page'], "article_id", DB_ARTICLES, "article_status='1' AND article_date<'". FUSION_TODAY ."' AND article_cat='". $filedid ."'");

				} // db query


				ob_start();
				eval("?>".htmlspecialchars_decode($article_cat_content[LOCALESHORT])."<?php ");
				$custompage = ob_get_contents();
				ob_end_flush();
				$custompage = preg_split("/<!?--\s*pagebreak\s*-->/i", $custompage);
				$pagecount = count($custompage);
				echo $custompage[$_GET['rowstart']];


if (!$_GET['page']) {
	/* YouTube Video */
	include INCLUDES ."Google/autoload.php";
	include INCLUDES ."Google/Client.php";
	include INCLUDES ."Google/Service/YouTube.php";

	$YouTubeVideo = "";
	if ($article_cat_h1[LOCALESHORT]) { $YouTubeQuery = trim($article_cat_h1[LOCALESHORT]); }
	else { $YouTubeQuery = trim($article_cat_name[LOCALESHORT]); }
	$YouTubeVideo = YouTubeVideo( $YouTubeQuery );

	echo $YouTubeVideo;
	/* // YouTube Video */
} //  Yesli ne page



			} else {
				echo "<div class='admin-message' style='text-align:center'><br /><img style='border:0px; vertical-align:middle;' src ='".BASEDIR."images/warn.png' alt=''/><br /> ".$locale['400']."<br /><a href='index.php' onclick='javascript:history.back();return false;'>".$locale['403']."</a>\n<br /><br /></div>\n";
			}



		} // file_exit components article_cats.php

		closetable();

		echo "</div>\n";
}

?>