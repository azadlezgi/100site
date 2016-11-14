<?php
if (!defined("IN_FUSION")) { die("Access Denied"); }

$viewcompanent = viewcompanent("catalog_cats", "name");
$seourl_component = $viewcompanent['components_id'];

$left_cats_arr = array();
$result = dbquery("SELECT
												catalog_cat_id,
												catalog_cat_name,
												catalog_cat_parent,
												seourl_url
					FROM ". DB_CATALOG_CATS ."
					LEFT JOIN ". DB_SEOURL ." ON seourl_filedid=catalog_cat_id AND seourl_component=". $seourl_component ."
					WHERE catalog_cat_status='1'");
if (dbrows($result)) {
	$say=0;
	while ($data = dbarray($result)) { $say++;
		$catalog_cat_name = unserialize($data['catalog_cat_name']);

		$left_cats_arr[$say]['catalog_cat_id'] = $data['catalog_cat_id'];
		$left_cats_arr[$say]['catalog_cat_name'] = $catalog_cat_name[LOCALESHORT];
		$left_cats_arr[$say]['catalog_cat_parent'] = $data['catalog_cat_parent'];
		$left_cats_arr[$say]['seourl_url'] = $data['seourl_url'];
	} // db whille
} // db query
// echo "<pre>";
// print_r($left_cats_arr);
// echo "</pre>";
// echo "<hr>";

?>
<div class="left_catalog">
<?php openside("<i class='fa fa-bars'></i> Каталог"); ?>
<?php if ($left_cats_arr) { ?>
 <ul>
<?php

$views_shyas = 30;
$foreach_say = 0;
foreach ($left_cats_arr as $key_cats => $value_cats) {
	if ($value_cats['catalog_cat_parent']==0)  { $foreach_say++;


		$sub_li_list = "";
		foreach ($left_cats_arr as $key_cats_sub => $value_cats_sub) {
			if ($value_cats_sub['catalog_cat_parent']==$value_cats['catalog_cat_id'])  {
				$sub_li_list .= "    <li class='". str_replace("/", "_", $value_cats_sub['seourl_url']) . (preg_match("/\/". str_replace("/", "\/", $value_cats_sub['seourl_url']) ."/", FUSION_URI) ? " active" : "") ."'><a href='". $value_cats_sub['seourl_url'] ."'><i class='fa fa-caret-right'></i>". $value_cats_sub['catalog_cat_name'] ."</a></li>\n";
			} // catalog_cat_parent != 0
		} // foreach left_cats_arr

		echo "  <li class='". str_replace("/", "_", $value_cats['seourl_url']) . (preg_match("/\/".  str_replace("/", "\/", $value_cats['seourl_url']) ."/", FUSION_URI) ? " active" : "") . ($foreach_say==$views_shyas ? " views_shyas" : "") . ($foreach_say==1 ? " fist" : "") ."'". ($foreach_say>$views_shyas ? " style='display:none;'" : "") ."><a href='". $value_cats['seourl_url'] ."'>". $value_cats['catalog_cat_name'] . ($sub_li_list ? "<i class='fa fa-angle-right'></i>" : "") ."</a>";

		if ($sub_li_list) {
		echo "\n   <ul>\n";
		echo $sub_li_list;
		echo "   </ul>\n  ";
		} // Yesli yest sub_li_list

		echo "</li>\n";
	} // catalog_cat_parent == 0
} // foreach left_cats_arr

?>
 </ul>
<?php if ($foreach_say>$views_shyas) { ?>
 <a href="#" class="view_all">ещё<i class='fa fa-caret-down'></i></a>
	<?php
add_to_footer ("<script type='text/javascript'>
	<!--
	$(document).ready(function() {
		$( '.left_catalog .view_all' ).click(function() {
			$( '.left_catalog .side-body > ul > li' ).removeClass( 'views_shyas' );
			$( '.left_catalog .side-body > ul > li' ).show( 'slow' );
			$( '.left_catalog .view_all' ).css( 'display', 'none' );
			return false;
		});
	});
	//-->
</script>");
	} // oreach_say > views_shyas

} else {
	echo "Нет ничего!\n";
} // Yest array
?>
<?php closeside(); ?>
</div>