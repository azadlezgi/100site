<?php

if (!defined("IN_FUSION")) { die("Access Denied"); }

include LOCALE.LOCALESET."catalog_index.php";


set_title( ($settings['opening_page']!=$catalog_cat_seourl_url && $settings['title'] ? $settings['title'] : $locale['title']) );
set_meta("description", ($settings['opening_page']!=$catalog_cat_seourl_url && $settings['description'] ? $settings['description'] : $locale['description']) );
set_meta("keywords", ($settings['opening_page']!=$catalog_cat_seourl_url && $settings['keywords'] ? $settings['keywords'] : $locale['keywords']) );
// add_to_head ("<link rel='canonical' href='http://". FUSION_HOST ."/". ($settings['opening_page']!=$catalog_cat_seourl_url ? $catalog_cat_seourl_url : "") ."' />");
// add_to_head ("<meta name='robots' content='index, follow' />");
// add_to_head ("<meta name='author' content='IssoHost' />");

if (FUSION_URI!="/") {
	echo "<div class='breadcrumb'>\n";
	echo "	<ul>\n";
	echo "		<li><a href='/'>". $locale['640'] ."</a></li>\n";
	echo "		<li><span>". $catalog_cat_name[LOCALESHORT] ."</span></li>\n";
	echo "	</ul>\n";
	echo "</div>\n";
}




opentable( ($settings['opening_page']!=$catalog_cat_seourl_url && $settings['sitename'] ? $settings['sitename'] : $locale['h1']) );



if ( file_exists(THEME ."components/catalog_index.php") ) {
	include THEME ."components/catalog_index.php";
} else {

$viewcompanent = viewcompanent("catalog_cats", "name");
$seourl_component = $viewcompanent['components_id'];


$result_catalog_cat = dbquery("SELECT 
												catalog_cat_id,
												catalog_cat_name,
												seourl_url
				FROM ". DB_CATALOG_CATS ."
				RIGHT JOIN ". DB_SEOURL ." ON seourl_filedid=catalog_cat_id AND seourl_component=". $seourl_component ."
				WHERE catalog_cat_status='1'
				AND catalog_cat_parent='". $catalog_cat_id ."'
				AND ". groupaccess('catalog_cat_access') ."
				AND catalog_cat_id IN (2, 7, 16, 18)");
if (dbrows($result_catalog_cat)) { $catalog_cat_say = 0;
	while ($data_catalog_cat = dbarray($result_catalog_cat)) { $catalog_cat_say++;
		$catalog_cat_id = $data_catalog_cat['catalog_cat_id'];
		$catalog_cat_name = unserialize($data_catalog_cat['catalog_cat_name']);
		$catalog_cat_url = $data_catalog_cat['seourl_url'];
?>
<fieldset class="catalog_index">
	<legend>Котлы <?php echo $catalog_cat_name[LOCALESHORT]; ?></legend>
	<a href="/<?php echo $catalog_cat_url; ?>" class="catalog_index_more">Все котлы <?php echo $catalog_cat_name[LOCALESHORT]; ?> <i class='fa fa-caret-right'></i></a>
<?php
		$viewcompanent = viewcompanent("catalog", "name");
		$seourl_component = $viewcompanent['components_id'];

		$result = dbquery("SELECT 
											catalog_id,
											catalog_name,
											catalog_image,
											catalog_price,
											catalog_content,
											seourl_url
					FROM ". DB_CATALOG ."
					LEFT JOIN ". DB_SEOURL ." ON seourl_filedid=catalog_id AND seourl_component=". $seourl_component ."
					WHERE catalog_status='1'
					AND catalog_date<'". FUSION_TODAY ."'
					AND catalog_cat='". $catalog_cat_id ."'
					AND ". groupaccess('catalog_access') ."
					LIMIT 0, 4");

		if (dbrows($result)) { $catalog_say = 0;
?>
<div class="catalog_items_list row">
<?php
			while ($data = dbarray($result)) { $catalog_say++;

				$catalog_id = $data['catalog_id'];
				$catalog_name = unserialize($data['catalog_name']);
				$catalog_image = $data['catalog_image'];
				$catalog_price = $data['catalog_price'];
				$catalog_content = unserialize($data['catalog_content']);
				$seourl_url = $data['seourl_url'];



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
				if ($say==4) {
					echo "<div class='clear'></div>\n";
					$say = 0;
				} 
			} // db while
?>
<div class="clear"></div>
</div>
<?php
		} // db query
?>
</fieldset>
<?php
	} // db while
} // db query

} // file_exit components

closetable();

?>