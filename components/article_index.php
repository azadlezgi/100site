<?php

if (!defined("IN_FUSION")) { die("Access Denied"); }

include LOCALE.LOCALESET."article_index.php";


set_title( ($settings['opening_page']!=$article_cat_seourl_url && $settings['title'] ? $settings['title'] : $locale['title']) );
set_meta("description", ($settings['opening_page']!=$article_cat_seourl_url && $settings['description'] ? $settings['description'] : $locale['description']) );
set_meta("keywords", ($settings['opening_page']!=$article_cat_seourl_url && $settings['keywords'] ? $settings['keywords'] : $locale['keywords']) );
// add_to_head ("<link rel='canonical' href='http://". FUSION_HOST ."/". ($settings['opening_page']!=$article_cat_seourl_url ? $article_cat_seourl_url : "") ."' />");
// add_to_head ("<meta name='robots' content='index, follow' />");
// add_to_head ("<meta name='author' content='IssoHost' />");

if (FUSION_URI!="/") {
	echo "<div class='breadcrumb'>\n";
	echo "	<ul>\n";
	echo "		<li><a href='/'>". $locale['640'] ."</a></li>\n";
	echo "		<li><span>". $article_cat_name[LOCALESHORT] ."</span></li>\n";
	echo "	</ul>\n";
	echo "</div>\n";
}




opentable( ($settings['opening_page']!=$article_cat_seourl_url && $settings['sitename'] ? $settings['sitename'] : $locale['h1']) );



if ( file_exists(THEME ."components/article_index.php") ) {
	include THEME ."components/article_index.php";
} else {

$viewcompanent = viewcompanent("article_cats", "name");
$seourl_component = $viewcompanent['components_id'];


$result_artcat = dbquery("SELECT 
												article_cat_id,
												article_cat_name,
												seourl_url
				FROM ". DB_ARTICLE_CATS ."
				RIGHT JOIN ". DB_SEOURL ." ON seourl_filedid=article_cat_id AND seourl_component=". $seourl_component ."
				WHERE article_cat_status='1'
				AND article_cat_parent='". $article_cat_id ."'
				AND ". groupaccess('article_cat_access') ."
				AND article_cat_id IN (2, 7, 16, 18)");
if (dbrows($result_artcat)) { $artcat_say = 0;
	while ($data_artcat = dbarray($result_artcat)) { $artcat_say++;
		$artcat_id = $data_artcat['article_cat_id'];
		$artcat_name = unserialize($data_artcat['article_cat_name']);
		$artcat_url = $data_artcat['seourl_url'];
?>
<fieldset class="art_cat_index">
	<legend>Котлы <?php echo $artcat_name[LOCALESHORT]; ?></legend>
	<a href="/<?php echo $artcat_url; ?>" class="all_cat_index">Все котлы <?php echo $artcat_name[LOCALESHORT]; ?> <i class='fa fa-caret-right'></i></a>
<?php
		$viewcompanent = viewcompanent("articles", "name");
		$seourl_component = $viewcompanent['components_id'];

		$result = dbquery("SELECT 
											article_id,
											article_name,
											article_image,
											article_content,
											seourl_url
					FROM ". DB_ARTICLES ."
					LEFT JOIN ". DB_SEOURL ." ON seourl_filedid=article_id AND seourl_component=". $seourl_component ."
					WHERE article_status='1'
					AND article_date<'". FUSION_TODAY ."'
					AND article_cat='". $artcat_id ."'
					AND ". groupaccess('article_access') ."
					LIMIT 0, 4");

		if (dbrows($result)) { $say = 0;
			while ($data = dbarray($result)) { $say++;

				$article_id = $data['article_id'];
				$article_name = unserialize($data['article_name']);
				$article_image = $data['article_image'];
				$article_content = unserialize($data['article_content']);
				$seourl_url = $data['seourl_url'];
?>
	<div class="articles_li<?php echo ($say==4 ? " last" : ""); ?>">
		<div class="articles artile<?php echo $article_id; ?>">
			<a href="<?php echo BASEDIR . $seourl_url; ?>" class="article_title"><?php echo $article_name[LOCALESHORT]; ?></a>
			<a href="<?php echo BASEDIR . $seourl_url; ?>" class="article_img"><img src="<?php echo ($article_image ? IMAGES_A_T . $article_image : IMAGES ."imagenotfound.jpg"); ?>" alt=""></a>
			<a href="<?php echo BASEDIR . $seourl_url; ?>" class="art_content">
				<p><?php echo mb_substr(strip_tags(str_replace("&nbsp;", " ", str_replace("><", "> <", htmlspecialchars_decode($article_content[LOCALESHORT])))), 0, 300) ; ?></p>
			</a>
		</div>
	</div>
<?php
				if ($say==4) {
					echo "<div class='clear'></div>\n";
					$say = 0;
				} 
			} // db while
			echo "<div class='clear'></div>\n";
		} // db query
?>
</fieldset>
<?php
	} // db while
} // db query

} // file_exit components

closetable();

?>