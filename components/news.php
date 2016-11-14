<?php

if (!defined("IN_FUSION")) { die("Access Denied"); }

if ($_GET['readmore']) {
	header("HTTP/1.1 301 Moved Permanently");
	header("Location: ". BASEDIR ."news.php");
	exit();
}

require_once INCLUDES ."comments_include.php";
require_once INCLUDES ."ratings_include.php";
include LOCALE.LOCALESET ."news.php";


$viewcompanent = viewcompanent("news", "name");
$seourl_component = $viewcompanent['components_id'];

$cp_result = dbquery("SELECT 
								news_title,
								news_description,
								news_keywords,
								news_h1,
								news_name,
								news_extended,
								news_image,
								news_date,
								news_comments,
								news_ratings,
								seourl_url
FROM ". DB_NEWS ."
LEFT JOIN ". DB_SEOURL ." ON seourl_filedid=news_id AND seourl_component=". $seourl_component ."
WHERE news_id='". $filedid ."'");
if (dbrows($cp_result)) {
	$cp_data = dbarray($cp_result);

								$news_title = unserialize($cp_data['news_title']);
								$news_description = unserialize($cp_data['news_description']);
								$news_keywords = unserialize($cp_data['news_keywords']);
								$news_h1 = unserialize($cp_data['news_h1']);
								$news_name = unserialize($cp_data['news_name']);
								$news_extended = unserialize($cp_data['news_extended']);
								$news_image = $cp_data['news_image'];
								$news_date = $cp_data['news_date'];
								$news_comments = $cp_data['news_comments'];
								$news_ratings = $cp_data['news_ratings'];
								$news_seourl_url = $cp_data['seourl_url'];

		set_title( ($news_title[LOCALESHORT] ? $news_title[LOCALESHORT] : $news_name[LOCALESHORT]) );
		set_meta("description",  ($news_description[LOCALESHORT] ? $news_description[LOCALESHORT] : "") );
		set_meta("keywords",  ($news_keywords[LOCALESHORT] ? $news_keywords[LOCALESHORT] : "") );
		add_to_head ("<link rel='canonical' href='http://". FUSION_HOST ."/". ($settings['opening_page']!=$news_seourl_url ? $news_seourl_url : "") ."' />");
		add_to_head ("<meta name='robots' content='index, follow' />");
		add_to_head ("<meta name='author' content='IssoHost' />");


		echo "<div class='breadcrumb'>\n";
		echo "	<ul>\n";
		echo "		<li><a href='". BASEDIR ."'>". $locale['640'] ."</a></li>\n";
		echo "		<li><a href='". BASEDIR ."news.php'>". $locale['641'] ."</a></li>\n";
		echo "		<li><span>". $news_name[LOCALESHORT] ."</span></li>\n";
		echo "	</ul>\n";
		echo "</div>\n";


		opentable(($news_h1[LOCALESHORT] ? $news_h1[LOCALESHORT] : $news_name[LOCALESHORT]));

		echo "<div class='news_page'>\n";

		ob_start();
		eval("?>". ($news_image ? "<figure><a href='". IMAGES_N . $news_image ."' title='". $news_name[LOCALESHORT] ."' rel='lightbox[news]'><img src='". IMAGES_N_T ."t1_". $news_image ."' alt='". $news_name[LOCALESHORT] ."'></a></figure>\n" : "") . htmlspecialchars_decode($news_extended[LOCALESHORT])."\n<div class='date'>". $locale['010'] ." ". date("d.m.Y", $news_date) ."</div>\n<div class='clear'></div><?php ");
		$customnews = ob_get_contents();
		ob_end_flush();

		echo "</div>\n";

		closetable();


	if (isset($newscount) && $newscount > 1) {
	    echo "<div align='center' style='margin-top:5px;'>\n".makenewsnav($_GET['rowstart'], 1, $newscount, 3, FUSION_SELF."?news_id=".$_GET['news_id']."&amp;")."\n</div>\n";
	}
	echo "<!--customnewss-after-content-->\n";
	if (dbrows($cp_result) && checkgroup($cp_data['news_access'])) {
		if ($cp_data['news_allow_comments']) { showcomments("N", DB_NEWS, "news_id", $_GET['news_id'],FUSION_SELF."?news_id=".$_GET['news_id']); }
		if ($cp_data['news_allow_ratings']) { showratings("N", $_GET['news_id'], FUSION_SELF."?news_id=".$_GET['news_id']); }
	}

} else {

	if (!empty($locale['title'])) set_title($locale['title']);
	if (!empty($locale['description'])) set_meta("description", $locale['description']);
	if (!empty($locale['keywords'])) set_meta("keywords", $locale['keywords']);
	add_to_head ("<link rel='canonical' href='http://". FUSION_HOST ."/". ($settings['opening_page']!="news.php" ? "news.php" : "") ."' />");
	add_to_head ("<meta name='robots' content='index, follow' />");

	echo "<div class='breadcrumb'>\n";
	echo "	<ul>\n";
	echo "		<li><a href='". BASEDIR ."'>". $locale['640'] ."</a></li>\n";
	echo "		<li><span>". $locale['641'] ."</span></li>\n";
	echo "	</ul>\n";
	echo "</div>\n";

	opentable($locale['h1']);

	echo "<div class='news_list'>\n";

	$viewcompanent = viewcompanent("news", "name");
	$seourl_component = $viewcompanent['components_id'];

	$result = dbquery("SELECT 
									news_name,
									news_short,
									news_date,
									news_image,
									seourl_url
					FROM ". DB_NEWS ."
					LEFT JOIN ". DB_SEOURL ." ON seourl_filedid=news_id AND seourl_component=". $seourl_component ."
					WHERE ". groupaccess('news_access') ."
					ORDER BY news_order ASC");
	if (dbrows($result)) {
		while ($data = dbarray($result)) { $j++;

			$news_name = unserialize($data['news_name']);
			$news_short = unserialize($data['news_short']);
			$news_date = $data['news_date'];
			//$news_date = strtotime('14.04.2012');
			$news_image = $data['news_image'];
			$seourl_url = $data['seourl_url'];
			
			//echo $news_date;

	?>
		<div class="news new<?php echo $j; ?>">
			<div class="title"><a href="<?php echo BASEDIR . $seourl_url; ?>"><?php echo $news_name[LOCALESHORT]; ?></a></div>
			<?php if ($news_image) { ?>
			<figure><a href="<?php echo BASEDIR . $seourl_url; ?>"><img src="<?php echo IMAGES_N_T ."t2_". $news_image; ?>" alt="<?php echo $news_name[LOCALESHORT]; ?>"></a></figure>
			<?php } ?>
			<div class="date"><?php echo date("d.m.Y", $news_date); ?></div>
			<div class="text"><?php echo htmlspecialchars_decode($news_short[LOCALESHORT]); ?></div>
			<div class="clear"></div>
		</div>
	<?php
		} // db while
	} // db query
	echo "</div>\n";

	closetable();

} // Yesli news

?>