<?php

if (!defined("IN_FUSION")) { die("Access Denied"); }

include LOCALE.LOCALESET."catalog_cats.php"; 

$viewcompanent = viewcompanent("catalog_cats", "name");
$seourl_component = $viewcompanent['components_id'];

$c_result = dbquery("SELECT 
								catalog_cat_id,
								catalog_cat_title,
								catalog_cat_description,
								catalog_cat_keywords,
								catalog_cat_name,
								catalog_cat_h1,
								catalog_cat_access,
								catalog_cat_content,
								catalog_cat_parent,
								seourl_url
FROM ". DB_CATALOG_CATS ."
LEFT JOIN ". DB_SEOURL ." ON seourl_filedid=catalog_cat_id AND seourl_component=". $seourl_component ."
WHERE catalog_cat_id='". $filedid ."'");
if (dbrows($c_result)) {
	$c_data = dbarray($c_result);

								$catalog_cat_id = $c_data['catalog_cat_id'];
								$catalog_cat_title = unserialize($c_data['catalog_cat_title']);
								$catalog_cat_description = unserialize($c_data['catalog_cat_description']);
								$catalog_cat_keywords = unserialize($c_data['catalog_cat_keywords']);
								$catalog_cat_name = unserialize($c_data['catalog_cat_name']);
								$catalog_cat_h1 = unserialize($c_data['catalog_cat_h1']);
								$catalog_cat_access = $c_data['catalog_cat_access'];
								$catalog_cat_content = unserialize($c_data['catalog_cat_content']);
								$catalog_cat_parent = $c_data['catalog_cat_parent'];
								$catalog_cat_seourl_url = $c_data['seourl_url'];

		if (!empty($catalog_cat_title[LOCALESHORT])) set_title($catalog_cat_title[LOCALESHORT] . ($_GET['page']>0 ? $locale['500'] . (INT)$_GET['page'] : "") );
		if (!empty($catalog_cat_description[LOCALESHORT])) set_meta("description", $catalog_cat_description[LOCALESHORT]);
		if (!empty($catalog_cat_keywords[LOCALESHORT])) set_meta("keywords", $catalog_cat_keywords[LOCALESHORT]);
		// add_to_head ("<link rel='canonical' href='http://". FUSION_HOST ."/". ($settings['opening_page']!=$catalog_cat_seourl_url ? $catalog_cat_seourl_url : "") ."' />");
		// add_to_head ("<meta name='robots' content='index, follow' />");
		// add_to_head ("<meta name='author' content='IssoHost' />");

if ($catalog_cat_parent>0) {
	$cp_result = dbquery("SELECT 
									catalog_cat_id,
									catalog_cat_name,
									seourl_url
	FROM ". DB_CATALOG_CATS ."
	LEFT JOIN ". DB_SEOURL ." ON seourl_filedid=catalog_cat_id AND seourl_component=". $seourl_component ."
	WHERE catalog_cat_id='". $catalog_cat_parent ."'");
	if (dbrows($cp_result)) {
		$cp_data = dbarray($cp_result);
		$cp_cat_id = $cp_data['catalog_cat_id'];
		$cp_cat_name = unserialize($cp_data['catalog_cat_name']);
		$cp_cat_seourl_url = $cp_data['seourl_url'];
	} // db query
} // catalog_cat_parent > 0



		if (FUSION_URI!="/") {
		echo "<div class='breadcrumb'>\n";
		echo "	<ul>\n";
		echo "		<li><a href='/'>". $locale['640'] ."</a></li>\n";
		echo "		<li><a href='/catalog'>". $locale['641'] ."</a></li>\n";
		if ($catalog_cat_parent>0) {
		echo "		<li><a href='". $cp_cat_seourl_url ."'>". $cp_cat_name[LOCALESHORT] ."</a></li>\n";
		}
		echo "		<li><span>". $catalog_cat_name[LOCALESHORT] ."</span></li>\n";
		echo "	</ul>\n";
		echo "</div>\n";
		}


		echo "<div class='catalog_content'>\n";

		if ($catalog_cat_h1[LOCALESHORT]) {
			opentable($catalog_cat_h1[LOCALESHORT]);
		} else {
			opentable($catalog_cat_name[LOCALESHORT]);
		}


		if ( file_exists(THEME ."components/catalog_cats.php") ) {
			include THEME ."components/catalog_cats.php";
		} else {



			if (checkgroup($catalog_cat_access)) {



				$result_catalog_cat = dbquery("SELECT 
												catalog_cat_id,
												catalog_cat_name,
												catalog_cat_image,
												catalog_cat_access,
												seourl_url
				FROM ". DB_CATALOG_CATS ."
				RIGHT JOIN ". DB_SEOURL ." ON seourl_filedid=catalog_cat_id AND seourl_component=". $seourl_component ."
				WHERE catalog_cat_status='1'
				AND catalog_cat_parent='". $catalog_cat_id ."'");
				if (dbrows($result_catalog_cat)) {
					echo "<div class='catalog_cats_list row'>\n";
					$catalog_cat_say = 0;
					while ($data_catalog_cat = dbarray($result_catalog_cat)) {

						$catalog_cat_id = $data_catalog_cat['catalog_cat_id'];
						$catalog_cat_name = unserialize($data_catalog_cat['catalog_cat_name']);
						$catalog_cat_image = $data_catalog_cat['catalog_cat_image'];
						$catalog_cat_access = $data_catalog_cat['catalog_cat_access'];
						$catalog_cat_url = $data_catalog_cat['seourl_url'];

						if (checkgroup($catalog_cat_access)) { $catalog_cat_say++;

							echo "	<div class='catalog_cats col-sm-3 catalog_cat". $catalog_cat_id . ($catalog_cat_say==4 ? " last" : "") ."'>\n" ;
							echo "		<a href='". BASEDIR . $catalog_cat_url ."' class='catalog_cat_name'>". $catalog_cat_name[LOCALESHORT] ."</a>\n";
							echo "		<a href='". BASEDIR . $catalog_cat_url ."' class='catalog_cat_img'><img src='". ($catalog_cat_image ? IMAGES_CC_T . $catalog_cat_image : IMAGES ."imagenotfound.jpg") ."' alt='". $catalog_cat_name[LOCALESHORT] ."'></a>\n";
							echo "	</div>\n";

							if ($catalog_cat_say==4) {
								echo "<div class='clear'></div>\n";
								$catalog_cat_say=0;
							}

						} // catalog_cat_access
					} // db while
					echo "	<div class='clear'></div>\n";
					echo "</div>\n";
				} // db query





				if (isset($_GET['page'])) {
					$pagesay = $_GET['page'];
				} else {
					$pagesay = 1;
				}
				$rowstart = $settings['catalog_per_page']*($pagesay-1);

				$viewcompanent = viewcompanent("catalog", "name");
				$seourl_component = $viewcompanent['components_id'];

				$result = dbquery("SELECT 
											catalog_id,
											catalog_name,
											catalog_image,
											catalog_price,
											catalog_access,
											seourl_url
					FROM ". DB_CATALOG ."
					LEFT JOIN ". DB_SEOURL ." ON seourl_filedid=catalog_id AND seourl_component=". $seourl_component ."
					WHERE catalog_status='1'
					AND catalog_date<'". FUSION_TODAY ."'
					AND catalog_cat='". $filedid ."'
					LIMIT ". $rowstart .", ". $settings['catalog_per_page'] ."");


				if (dbrows($result)) { $catalog_item_say = 0;
?>
<div class="catalog_items_list row">
<?php
					while ($data = dbarray($result)) { $catalog_item_say++;

						$catalog_id = $data['catalog_id'];
						$catalog_name = unserialize($data['catalog_name']);
						$catalog_image = $data['catalog_image'];
						$catalog_price = $data['catalog_price'];
						$catalog_access = $data['catalog_access'];
						$seourl_url = $data['seourl_url'];

						if (checkgroup($catalog_access)) {

							$catalog_moshnost1 = "";
							$catalog_moshnost2 = "";
							$result_param = dbquery("SELECT
																		catalogpvalue_text
													FROM ". DB_CATALOG_PPARAM ."
													LEFT JOIN ". DB_CATALOG_PVALUE ." ON catalogpvalue_param_id=catalogpparam_id
													WHERE catalogpparam_pgruop_id='1'
													AND catalogpparam_id='3'
													AND catalogpvalue_catalog_id='". $catalog_id ."'");
							if (dbrows($result_param)) {
								$data_param = dbarray($result_param);
								$catalog_moshnost1 = $data_param['catalogpvalue_text'];
								$catalog_moshnost1 = unserialize($catalog_moshnost1);
							} // db query

							if (!$catalog_moshnost1) {
								$result_param = dbquery("SELECT
																			catalogpvalue_text
														FROM ". DB_CATALOG_PPARAM ."
														LEFT JOIN ". DB_CATALOG_PVALUE ." ON catalogpvalue_param_id=catalogpparam_id
														WHERE catalogpparam_pgruop_id='1'
														AND catalogpparam_id='22'
														AND catalogpvalue_catalog_id='". $catalog_id ."'");
								if (dbrows($result_param)) {
									$data_param = dbarray($result_param);
									$catalog_moshnost2 = $data_param['catalogpvalue_text'];
									$catalog_moshnost2 = unserialize($catalog_moshnost2);
								} // db query	
							} // if !$catalog_moshnost1

		// echo "<pre>";
		// print_r($catalog_moshnost1);
		// echo "</pre>";
		// echo "<hr>\n\n";

?>
	<div class="catalog_items catalog_item<?php echo $catalog_id; ?> col-sm-3<?php echo ($catalog_item_say==4 ? " clearfix" : ""); ?>">
		<div>
			<a href="<?php echo BASEDIR . $seourl_url; ?>" class="catalog_title"><?php echo $catalog_name[LOCALESHORT]; ?></a>
			<a href="<?php echo BASEDIR . $seourl_url; ?>" class="catalog_img"><img src="<?php echo ($catalog_image ? IMAGES_C_T . $catalog_image : IMAGES ."imagenotfound.jpg"); ?>" alt=""></a>
			<div class="catalog_content">
				<?php if ($catalog_moshnost1 || $catalog_moshnost2) { ?>
				<div class="catalog_power"><label>Мощность:</label> <span><?php echo ($catalog_moshnost1[LOCALESHORT] ? $catalog_moshnost1[LOCALESHORT] : $catalog_moshnost2[LOCALESHORT]); ?></span></div>
				<?php } ?>
				<div class="catalog_price"><?php print_r( ($catalog_price ? viewcena($catalog_price) : $locale['010'] ) ); ?></div>
			</div>
		</div>
	</div>
<?php
	if ($catalog_item_say==4) {
		$catalog_item_say = 0;
	} 
						} // catalog_access
					} // db whille
?>
	<div class="clear"></div>
<?php echo navigation($_GET['page'], $settings['catalog_per_page'], "catalog_id", DB_CATALOG, "catalog_status='1' AND catalog_date<'". FUSION_TODAY ."' AND catalog_cat='". $filedid ."'"); ?>
</div>
<?php
				} // db query


				ob_start();
				eval("?>".htmlspecialchars_decode($catalog_cat_content[LOCALESHORT])."<?php ");
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
	if ($catalog_cat_h1[LOCALESHORT]) { $YouTubeQuery = trim($catalog_cat_h1[LOCALESHORT]); }
	else { $YouTubeQuery = trim($catalog_cat_name[LOCALESHORT]); }
	$YouTubeVideo = YouTubeVideo( $YouTubeQuery );

	echo $YouTubeVideo;
	/* // YouTube Video */
} //  Yesli ne page



			} else {
				echo "<div class='admin-message' style='text-align:center'><br /><img style='border:0px; vertical-align:middle;' src ='".BASEDIR."images/warn.png' alt=''/><br /> ".$locale['400']."<br /><a href='index.php' onclick='javascript:history.back();return false;'>".$locale['403']."</a>\n<br /><br /></div>\n";
			}



		} // file_exit components catalog_cats.php

		closetable();

		echo "</div>\n";
}

?>