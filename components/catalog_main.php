<?php

if (!defined("IN_FUSION")) { die("Access Denied"); }

include LOCALE.LOCALESET."catalog_main.php"; 

$viewcompanent = viewcompanent("catalog_cats", "name");
$seourl_component = $viewcompanent['components_id'];


set_title($locale['title']);
set_meta("description", $locale['description']);
set_meta("keywords", $locale['keywords']);
// add_to_head ("<link rel='canonical' href='http://". FUSION_HOST ."/". ($settings['opening_page']!=$catalog_cat_seourl_url ? $catalog_cat_seourl_url : "") ."' />");
// add_to_head ("<meta name='robots' content='index, follow' />");
// add_to_head ("<meta name='author' content='IssoHost' />");

if (FUSION_URI!="/") {
	echo "<div class='breadcrumb'>\n";
	echo "	<ul>\n";
	echo "		<li><a href='/'>". $locale['640'] ."</a></li>\n";
	echo "		<li><span>". $locale['641'] ."</span></li>\n";
	echo "	</ul>\n";
	echo "</div>\n";
}


echo "<div class='catalog_content catalog_main'>\n";

opentable($locale['h1']);


if ( file_exists(THEME ."components/catalog_cats.php") ) {
	include THEME ."components/catalog_cats.php";
} else {

	$result_catalog_cat = dbquery("SELECT 
												catalog_cat_id,
												catalog_cat_name,
												catalog_cat_image,
												catalog_cat_access,
												seourl_url
				FROM ". DB_CATALOG_CATS ."
				RIGHT JOIN ". DB_SEOURL ." ON seourl_filedid=catalog_cat_id AND seourl_component=". $seourl_component ."
				WHERE catalog_cat_status='1'
				AND catalog_cat_parent=''");
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
	<div class="catalog_items catalog_item<?php echo $catalog_id; ?> col-sm-3<?php echo ($catalog_item_say==4 ? " catalog_last" : ""); ?>">
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
		echo "<div class='clear'></div>\n";
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

} // file_exit components catalog_cats.php

closetable();

echo "</div>\n";

?>