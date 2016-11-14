<?php

if (!defined("IN_FUSION")) { die("Access Denied"); }

require_once INCLUDES."comments_include.php";
require_once INCLUDES."ratings_include.php";
include LOCALE.LOCALESET."catalog.php";


$viewcompanent = viewcompanent("catalog", "name");
$seourl_component = $viewcompanent['components_id'];

$result = dbquery("SELECT 
								catalog_id,
								catalog_title,
								catalog_description,
								catalog_keywords,
								catalog_name,
								catalog_image,
								catalog_cat,
								catalog_price,
								catalog_h1,
								catalog_access,
								catalog_content,
								catalog_comments,
								catalog_ratings,
								seourl_url
FROM ". DB_CATALOG ."
LEFT JOIN ". DB_SEOURL ." ON seourl_filedid=catalog_id AND seourl_component=". $seourl_component ."
WHERE catalog_id='". $filedid ."'
AND catalog_status='1'");

// AND catalog_date<'". FUSION_TODAY ."'");

if (dbrows($result)) {
	$data = dbarray($result);

	// echo "<pre>";
	// print_r($data);
	// echo "</pre>";
	// echo "<hr>";

								$catalog_id = $data['catalog_id'];
								$catalog_title = unserialize($data['catalog_title']);
								$catalog_description = unserialize($data['catalog_description']);
								$catalog_keywords = unserialize($data['catalog_keywords']);
								$catalog_name = unserialize($data['catalog_name']);
								$catalog_image = $data['catalog_image'];
								$catalog_cat = $data['catalog_cat'];
								$catalog_price = $data['catalog_price'];
								$catalog_h1 = unserialize($data['catalog_h1']);
								$catalog_access = $data['catalog_access'];
								$catalog_content = unserialize($data['catalog_content']);
								$catalog_allow_comments = $data['catalog_comments'];
								$catalog_allow_ratings = $data['catalog_ratings'];
								$catalog_seourl_url = $data['seourl_url'];

		set_title( ($catalog_title[LOCALESHORT] ? $catalog_title[LOCALESHORT] : $catalog_name[LOCALESHORT]) );
		set_meta("description",  ($catalog_description[LOCALESHORT] ? $catalog_description[LOCALESHORT] : "") );
		set_meta("keywords",  ($catalog_keywords[LOCALESHORT] ? $catalog_keywords[LOCALESHORT] : "") );
		// add_to_head ("<link rel='canonical' href='http://". FUSION_HOST ."/". ($settings['opening_page']!=$catalog_seourl_url ? $catalog_seourl_url : "") ."' />");
		// add_to_head ("<meta name='robots' content='index, follow' />");
		// add_to_head ("<meta name='author' content='IssoHost' />");

$viewcompanent = viewcompanent("catalog_cats", "name");
$seourl_component = $viewcompanent['components_id'];

$c_result = dbquery("SELECT 
								catalog_cat_h1,
								catalog_cat_name,
								catalog_cat_parent,
								seourl_url
FROM ". DB_CATALOG_CATS ."
LEFT JOIN ". DB_SEOURL ." ON seourl_filedid=catalog_cat_id AND seourl_component=". $seourl_component ."
WHERE catalog_cat_id='". $catalog_cat ."'");
if (dbrows($c_result)) {
	$c_data = dbarray($c_result);
	$catalog_cat_h1 = unserialize($c_data['catalog_cat_h1']);
	$catalog_cat_name = unserialize($c_data['catalog_cat_name']);
	$catalog_cat_parent = $c_data['catalog_cat_parent'];
} // db query 


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
		if ($catalog_cat_parent>0) {
		echo "		<li><a href='/". $cp_cat_seourl_url ."'>". $cp_cat_name[LOCALESHORT] ."</a></li>\n";
		}
		echo "		<li><a href='/". $c_data['seourl_url'] ."'>". $catalog_cat_name[LOCALESHORT] ."</a></li>\n";
		echo "		<li><span>". $catalog_name[LOCALESHORT] ."</span></li>\n";
		echo "	</ul>\n";
		echo "</div>\n";
		}


		if ($catalog_h1[LOCALESHORT]) {
			opentable($catalog_h1[LOCALESHORT]);
		} else {
			opentable($catalog_name[LOCALESHORT]);
		}


if (iADMIN && (iUSER_RIGHTS != "" || iUSER_RIGHTS != "C")) {
	echo "<div class='admin_buttons'>\n";
	echo "	<a href='/". ADMIN ."catalog.php".  $aidlink ."&action=edit&id=". $catalog_id ."' target='_blank' class='button_edit'><i class='fa fa-pencil'></i>". $locale['500'] ."</a>\n";
	echo "	<a href='/". ADMIN ."catalog.php".  $aidlink ."&action=delete&id=". $catalog_id ."' target='_blank' class='button_delete'  onclick='return DeleteOk();'><i class='fa fa-times'></i>". $locale['501'] ."</a>\n";
	echo "</div>\n";

	add_to_footer ("<script type='text/javascript'>
		function DeleteOk() {
			return confirm('". $locale['502'] ."');
		}
	</script>");
}



if ( file_exists(THEME ."components/catalog_item.php") ) {
	include THEME ."components/catalog_item.php";
} else {

			if (checkgroup($catalog_access)) {
?>
<div class='catalog_single row'>
	<div class="col-sm-5">
<?php
				if ($catalog_image) {
					echo "<a class='catalog_img meduim_img' href='". IMAGES_C . $catalog_image ."' rel='lightbox[catalog_img]' title='". ($catalog_h1[LOCALESHORT] ? $catalog_h1[LOCALESHORT] : $catalog_name[LOCALESHORT]) ."'><img src='". IMAGES_C_T . $catalog_image ."' alt='". ($catalog_h1[LOCALESHORT] ? $catalog_h1[LOCALESHORT] : $catalog_name[LOCALESHORT]) ."'></a>\n";
				}

$catalog_ustanovkoy = 0;
if ($catalog_price>0 && $catalog_price<30000) { $catalog_ustanovkoy = (INT)$catalog_price+10000; }
elseif ($catalog_price>=30000 && $catalog_price<50000) { $catalog_ustanovkoy = (INT)$catalog_price+15000; }
elseif ($catalog_price>=50000 && $catalog_price<80000) { $catalog_ustanovkoy = (INT)$catalog_price+20000; }
elseif ($catalog_price>=80000 && $catalog_price<150000) { $catalog_ustanovkoy = (INT)$catalog_price+30000; }
elseif ($catalog_price<=150000) { $catalog_ustanovkoy = (INT)$catalog_price+50000; }

$catalog_ustanovkoy = (INT)$catalog_ustanovkoy;
?>

		<div class="catalog_price">
			Цена: <?php echo ( ($catalog_price ? viewcena($catalog_price) : $locale['010'] ) ); ?>
			<?php if ($catalog_ustanovkoy>0) { echo "<div class='catalog_ustanovkoy'>Цена с установкой: ". viewcena($catalog_ustanovkoy) ."</div>\n"; } ?>
		</div>


		<div class="catalog_order">
			<button id="order_form_button" class="btn">Заказать</button>
		</div>


	</div>
	<div class="col-sm-7">
<?php



		$catalog_cpv_array = array();
		$catalog_cpv_id = array();
		$result_cpv = dbquery("SELECT 
											catalogpvalue_id,
											catalogpvalue_param_id,
											catalogpvalue_text
		FROM ". DB_CATALOG_PVALUE ."
		WHERE catalogpvalue_catalog_id = '". $catalog_id ."'");
		if (dbrows($result_cpv)) {
			while ($data_cpv = dbarray($result_cpv)) {
				if ( !in_array($data_cpv['catalogpvalue_param_id'], $catalog_cpv_id) ) {
					$catalog_cpv_id[] = $data_cpv['catalogpvalue_param_id'];
				}
				$catalogpvalue_text = unserialize($data_cpv['catalogpvalue_text']);
				$catalog_cpv_array[$data_cpv['catalogpvalue_id']]['catalogpvalue_param_id'] = $data_cpv['catalogpvalue_param_id'];
				$catalog_cpv_array[$data_cpv['catalogpvalue_id']]['catalogpvalue_text'] = $catalogpvalue_text[LOCALESHORT];
			} // host params whille
			// echo "<pre>";
			// print_r($catalog_cpv_array);
			// echo "</pre>";
			// echo "<hr>";
		} // Yesli host params net


		$catalog_cpv_id = implode(",", $catalog_cpv_id);
		$catalog_cpp_array = array();
		$catalog_cp_id = array();
		$result_cpp = dbquery("SELECT 
											catalogpparam_id,
											catalogpparam_name,
											catalogpparam_pgruop_id,
											catalogpparam_cat_id
		FROM ". DB_CATALOG_PPARAM ."
		WHERE catalogpparam_status = '1'
		AND catalogpparam_id IN (". $catalog_cpv_id .")
		AND ". groupaccess('catalogpparam_access') ."
		ORDER BY catalogpparam_order");
		if (dbrows($result_cpp)) {
			while ($data_cpp = dbarray($result_cpp)) {
				if ( !in_array($data_cpp['catalogpparam_pgruop_id'], $catalog_cpp_id) ) {
					$catalog_cpp_id[] = $data_cpp['catalogpparam_pgruop_id'];
				}
				$catalogpparam_name = unserialize($data_cpp['catalogpparam_name']);
				$catalog_cpp_array[$data_cpp['catalogpparam_id']]['catalogpparam_name'] = $catalogpparam_name[LOCALESHORT];
				$catalog_cpp_array[$data_cpp['catalogpparam_id']]['catalogpparam_pgruop_id'] = $data_cpp['catalogpparam_pgruop_id'];
				$catalog_cpp_array[$data_cpp['catalogpparam_id']]['catalogpparam_cat_id'] = $data_cpp['catalogpparam_cat_id'];
			} // host params whille
			// echo "<pre>";
			// print_r($catalog_cpp_array);
			// echo "</pre>";
			// echo "<hr>";
		} // Yesli host params net
	

		$catalog_cpp_id = implode(",", $catalog_cpp_id);
		$catalog_cpg_array = array();
		$result_cpg = dbquery("SELECT 
											catalogpgroup_id,
											catalogpgroup_name
		FROM ". DB_CATALOG_PGROUP ."
		WHERE catalogpgroup_id IN (". $catalog_cpp_id .")
		");
		if (dbrows($result_cpg)) {
			while ($data_cpg = dbarray($result_cpg)) {
				$catalogpgroup_name = unserialize($data_cpg['catalogpgroup_name']);
				$catalog_cpg_array[$data_cpg['catalogpgroup_id']] = $catalogpgroup_name[LOCALESHORT];
			} // host params whille
			// echo "<pre>";
			// print_r($catalog_cpg_array);
			// echo "</pre>";
			// echo "<hr>";
		} // Yesli host params net


if ($catalog_cpg_array) {
	echo "<div class='catalog_params'>\n";
	foreach ($catalog_cpg_array as $cpg_key => $cpg_value) {
		echo "<fieldset>\n";
		echo "<legend>". $cpg_value ."</legend>\n";
		if ($catalog_cpp_array) {
			echo "<ul>\n";
			foreach ($catalog_cpp_array as $cpp_key => $cpp_value) {
				if ($cpg_key==$cpp_value['catalogpparam_pgruop_id']) {
					echo "<li>\n";
					echo "<label>". $cpp_value['catalogpparam_name'] ."</label>\n";
					if ($catalog_cpv_array) {
						foreach ($catalog_cpv_array as $cpv_key => $cpv_value) {
							if ($cpp_key==$cpv_value['catalogpvalue_param_id']) {
								echo "<span>". $cpv_value['catalogpvalue_text'] ."</span>\n";
							} // 
						} // foreach catalog_cpv_array
					} // if catalog_cpv_array
					echo "</li>\n";
				} // if catalogpparam_pgruop_id
			} // foreach
		} // if catalog_cpp_array
		echo "</fieldset>\n";
		} // foreach catalog_cpg_array
	echo "</div>\n";
} // if 
?>
	</div>
	<div class='clear'></div>
	<div class="col-sm-12">
<?php
				if ($catalog_content) {
					echo "<div class='catalog_content'>\n";
					echo htmlspecialchars_decode($catalog_content[LOCALESHORT]);
					echo "</div>\n";
				}
?>
	</div>
	<div class="col-sm-12">
<?php
/* YouTube Video */
include INCLUDES ."Google/autoload.php";
include INCLUDES ."Google/Client.php";
include INCLUDES ."Google/Service/YouTube.php";

$YouTubeVideo = "";
if ($catalog_h1[LOCALESHORT]) { $YouTubeQuery = trim($catalog_h1[LOCALESHORT]); }
else { $YouTubeQuery = trim($catalog_name[LOCALESHORT]); }
$YouTubeVideo = YouTubeVideo( $YouTubeQuery );

if (!$YouTubeVideo) {
	if ($catalog_cat_h1[LOCALESHORT]) { $YouTubeQuery = trim($catalog_cat_h1[LOCALESHORT]); }
	else { $YouTubeQuery = trim($catalog_cat_name[LOCALESHORT]); }
	$YouTubeVideo = YouTubeVideo( $YouTubeQuery );
}
echo $YouTubeVideo;
/* // YouTube Video */

?>
	</div>
</div>
<?php
			} else {
				echo "<div class='admin-message' style='text-align:center'><br /><img style='border:0px; vertical-align:middle;' src ='".BASEDIR."images/warn.png' alt=''/><br /> ".$locale['400']."<br /><a href='index.php' onclick='javascript:history.back();return false;'>".$locale['403']."</a>\n<br /><br /></div>\n";
			}

} // file_exit components catalog_item.php

		closetable();
}



if (isset($pagecount) && $pagecount > 1) {
    echo "<div align='center' style='margin-top:5px;'>\n".makepagenav($_GET['rowstart'], 1, $pagecount, 3, FUSION_SELF."?catalog_id=". $filedid ."&amp;")."\n</div>\n";
}
echo "<!--custompages-after-content-->\n";
if (dbrows($result) && checkgroup($data['catalog_access'])) {
	if ($cp_data['catalog_allow_comments']) { showcomments("C", DB_CATALOG, "catalog_id", $filedid,FUSION_SELF."?catalog_id=". $filedid); }
	if ($cp_data['catalog_allow_ratings']) { showratings("C", $filedid, FUSION_SELF."?catalog_id=". $filedid); }
}

?>