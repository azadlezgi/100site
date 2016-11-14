<?php
 
	require_once "../includes/maincore.php";

	if (!checkrights("CAT") || !defined("iAUTH") || !isset($_GET['aid']) || $_GET['aid'] != iAUTH) { redirect("../index.php"); }

	include LOCALE . LOCALESET ."admin/catalog.php";


	if ($_GET['action']!="order") {
		require_once THEMES ."templates/admin_header.php";
		require_once INCLUDES."photo_functions_include.php";
		if ($settings['tinymce_enabled']) {
			$_SESSION['tinymce_sess'] = 1;
			// echo "<script language='javascript' type='text/javascript'>advanced();</script>\n";
		} else {
			require_once INCLUDES."html_buttons_include.php";
		}

		opentable($locale['001']);
	} // Yesli action ne order


	if ($_GET['action']=="order") {

		if (isset($_GET['listItem']) && is_array($_GET['listItem'])) {
			foreach ($_GET['listItem'] as $position => $item) {
				if (isnum($position) && isnum($item)) {
					dbquery("UPDATE ". DB_CATALOG ." SET catalog_order='". ($position+1) ."' WHERE catalog_id='". $item ."'");
				}
			}

			header("Content-Type: text/html; charset=". $locale['charset'] ."\n");
			echo "<div id='close-message'>\n";
			echo "	<div class='success'>". $locale['success_007'] ."</div>\n";
			echo "</div>\n";

		}


	} else if ($_GET['action']=="status") {

		$catalog_id = (INT)$_GET['id'];
		$catalog_status = (INT)$_GET['status'];
		$catalog_status = ($catalog_status ? 0 : 1);

		$result = dbquery("UPDATE ". DB_CATALOG ." SET
														catalog_status='". $catalog_status ."'
		WHERE catalog_id='". $catalog_id ."'");

		redirect(FUSION_SELF . $aidlink."&status=". ($catalog_status ? "active" : "deactive") ."&id=". $catalog_id, false);

	} else if ($_GET['action']=="del") {

		$result = dbquery("SELECT catalog_image FROM ". DB_CATALOG ." WHERE catalog_id='". (INT)$_GET['id'] ."'");
		if (dbrows($result)) {
				$data = dbarray($result);
			if (!empty($data['catalog_image']) && file_exists(IMAGES_C . $data['catalog_image'])) { unlink(IMAGES_C . $data['catalog_image']); }
			if (!empty($data['catalog_image']) && file_exists(IMAGES_C_T . $data['catalog_image'])) { unlink(IMAGES_C_T . $data['catalog_image']); }
		} // Tesli Yest DB query

		$result = dbquery("DELETE FROM ". DB_CATALOG ." WHERE catalog_id='". (INT)$_GET['id'] ."'");
		$result = dbquery("DELETE FROM ". DB_COMMENTS ." WHERE comment_item_id='". (INT)$_GET['id'] ."' and comment_type='V'");
		$result = dbquery("DELETE FROM ". DB_RATINGS ." WHERE rating_item_id='". (INT)$_GET['id'] ."' and rating_type='V'");

		$viewcompanent = viewcompanent("catalog", "name");
		$seourl_component = $viewcompanent['components_id'];
		$seourl_filedid = (INT)$_GET['id'];

		$result = dbquery("DELETE FROM ". DB_SEOURL ." WHERE seourl_component='". $seourl_component ."' AND seourl_filedid='". $seourl_filedid ."'");


		///////////////// POSITIONS /////////////////
		$position=1;
		$result_position = dbquery("SELECT catalog_id FROM ". DB_CATALOG ." ORDER BY `catalog_order`");
		if (dbrows($result_position)) {
			while ($data_position = dbarray($result_position)) {
				$position++;
				dbquery("UPDATE ". DB_CATALOG ." SET catalog_order='". $position ."' WHERE catalog_id='". $data_position['catalog_id'] ."'");
			} // db whille
		} // db query
		///////////////// POSITIONS /////////////////


		redirect(FUSION_SELF . $aidlink ."&status=del&id=". (INT)$_GET['id']);

	} else if ($_GET['action']=="add" || $_GET['action']=="edit") {

		if (isset($_POST['save'])) {

			$catalog_title = stripinput($_POST['catalog_title']);
			$catalog_description = stripinput($_POST['catalog_description']);
			$catalog_keywords = stripinput($_POST['catalog_keywords']);
			$catalog_name = stripinput($_POST['catalog_name']);
			$catalog_h1 = stripinput($_POST['catalog_h1']);
			$catalog_content = stripinput($_POST['catalog_content']);

			$catalog_image = $_FILES['catalog_image']['name'];
			$catalog_imagetmp  = $_FILES['catalog_image']['tmp_name'];
			$catalog_imagesize = $_FILES['catalog_image']['size'];
			$catalog_imagetype = $_FILES['catalog_image']['type'];

			$catalog_image_yest = stripinput($_POST['catalog_image_yest']);
			$catalog_image_del = (INT)$_POST['catalog_image_del'];

			$catalog_cat = stripinput($_POST['catalog_cat']);
			$catalog_access = (INT)$_POST['catalog_access'];
			$catalog_status = (INT)$_POST['catalog_status'];

			// if ($_GET['action']=="edit") {
			// 	$catalog_order = (INT)$_POST['catalog_order'];
			// } else {
			// 	$result_order = dbquery(
			// 		"SELECT 
			// 									catalog_id,
			// 									catalog_order
			// 		FROM ". DB_CATALOG ."
			// 		ORDER BY catalog_order DESC
			// 		LIMIT 1"
			// 	);
			// 	if (dbrows($result_order)) {
			// 		$data_order = dbarray($result_order);
			// 		$catalog_order = $data_order['catalog_order']+1;
			// 	} else {
			// 		$catalog_order = 1;
			// 	}
			// }

			$catalog_date = FUSION_TODAY;
			$catalog_comments = (INT)$_POST['catalog_comments'];
			$catalog_ratings = (INT)$_POST['catalog_ratings'];

			$catalog_alias = stripinput($_POST['catalog_alias']);

		} else if ($_GET['action']=="edit") {

			$viewcompanent = viewcompanent("catalog", "name");
			$seourl_component = $viewcompanent['components_id'];

			$result = dbquery(
				"SELECT 
											catalog_id,
											catalog_title,
											catalog_description,
											catalog_keywords,
											catalog_name,
											catalog_h1,
											catalog_content,
											catalog_image,
											catalog_cat,
											catalog_access,
											catalog_status,
											catalog_comments,
											catalog_ratings,
											seourl_url
				FROM ". DB_CATALOG ."
				LEFT JOIN ". DB_SEOURL ." ON seourl_filedid=catalog_id AND seourl_component=". $seourl_component ."
				WHERE catalog_id='". (INT)$_GET['id'] ."' LIMIT 1"
			);
			if (dbrows($result)) {
				$data = dbarray($result);

				$catalog_title = unserialize($data['catalog_title']);
				$catalog_description = unserialize($data['catalog_description']);
				$catalog_keywords = unserialize($data['catalog_keywords']);
				$catalog_name = unserialize($data['catalog_name']);
				$catalog_h1 = unserialize($data['catalog_h1']);
				$catalog_content = unserialize($data['catalog_content']);
				$catalog_image = $data['catalog_image'];
				$catalog_cat =  $data['catalog_cat'];
				$catalog_access = $data['catalog_access'];
				$catalog_status = $data['catalog_status'];
				$catalog_comments = $data['catalog_comments'];
				$catalog_ratings = $data['catalog_ratings'];

				$catalog_alias = $data['seourl_url'];

			} else {
				redirect(FUSION_SELF . $aidlink);
			}

		} else {

				$catalog_title = "";
				$catalog_description = "";
				$catalog_keywords = "";
				$catalog_name = "";
				$catalog_h1 = "";
				$catalog_content = "";
				$catalog_image = "";
				$catalog_cat = 0;
				$catalog_access = 0;
				$catalog_status = 1;
				$catalog_comments = "";
				$catalog_ratings = "";
				$catalog_alias = "";

		} // Yesli POST


		########## SEO URL OPARATIONS ##########
		if ($settings['seourl_prefix']) {
			$seourl_prefix_strlen =  strlen($settings['seourl_prefix']);
			$seourl_prefix_alias = substr($catalog_alias, -$seourl_prefix_strlen);
			if ($seourl_prefix_alias==$settings['seourl_prefix']) {
				$catalog_alias = substr($catalog_alias, 0, -$seourl_prefix_strlen);
			}
		} // yesli yest seourl_prefix

		if ($catalog_cat!=0) {
			$viewcompanent = viewcompanent("catalog_cats", "name");
			$seourl_component = $viewcompanent['components_id'];
			
			$viewseourl = viewseourl($catalog_cat, "filedid", $seourl_component);
			// echo "<pre>";
			// print_r($viewseourl);
			// echo "</pre>";
			// echo "<hr>";

			if ($viewseourl) {
				$cat_url = $viewseourl['seourl_url'];
			}
			if ($settings['seourl_prefix']) {
				$seourl_prefix_strlen =  strlen($settings['seourl_prefix']);
				$seourl_prefix_alias = substr($cat_url, -$seourl_prefix_strlen);
				if ($seourl_prefix_alias==$settings['seourl_prefix']) {
					$cat_url = substr($cat_url, 0, -$seourl_prefix_strlen);
				}
			} // yesli yest seourl_prefix

			$catalog_alias = str_replace($cat_url ."/", "", $catalog_alias);
		} else {
			$catalog_alias = str_replace($settings['companent_root_url'], "", $catalog_alias);
		}
		########## //SEO URL OPARATIONS ##########



		if (isset($_POST['save'])) {


			foreach ($languages as $key => $value) {
				if (empty($catalog_name[$value['languages_short']])) { $error .= "<div class='error'>". $locale['error_001'] ." - ". $value['languages_name'] ."</div>\n"; }
			}
			if (!$catalog_cat) { $error .= "<div class='error'>". $locale['error_002'] ."</div>\n"; }

			if ($catalog_image) {
				// if (strlen($catalog_image) > 255) { $error .= "<div class='error'>". $locale['error_050'] ."</div>\n"; $catalog_image = ""; }
				// проверяем расширение файла
				$catalog_image_ext = strtolower(substr($catalog_image, 1 + strrpos($catalog_image, ".")));
				if (!in_array($catalog_image_ext, $photo_valid_types)) { $error .= "<div class='error'>". $locale['error_051'] ."</div>\n"; $catalog_image = ""; }
				// 1. считаем кол-во точек в выражении - если большей одной - СВОБОДЕН!
				$catalog_image_findtochka = substr_count($catalog_image, ".");
				if ($catalog_image_findtochka>1) { $error .= "<div class='error'>". $locale['error_052'] ."</div>\n"; $catalog_image = ""; }
				// 2. если в имени есть .php, .html, .htm - свободен! 
				if (preg_match("/\.php/i",$catalog_image))  { $error .= "<div class='error'>". $locale['error_053'] ."</div>\n"; $catalog_image = ""; }
				if (preg_match("/\.html/i",$catalog_image)) { $error .= "<div class='error'>". $locale['error_054'] ."</div>\n"; $catalog_image = ""; }
				if (preg_match("/\.htm/i",$catalog_image))  { $error .= "<div class='error'>". $locale['error_055'] ."</div>\n"; $catalog_image = ""; }
				// 5. Размер фото
				$catalog_image_fotosize = round($catalog_imagesize/10.24)/100; // размер ЗАГРУЖАЕМОГО ФОТО в Кб.
				$catalog_image_fotomax = round($settings['catalog_photo_max_b']/10.24)/100; // максимальный размер фото в Кб.
				if ($catalog_image_fotosize>$catalog_image_fotomax) { $error .= "<div class='error'>". $locale['error_056'] ."<br />". $locale['error_057'] ." ". $catalog_image_fotosize ." Kb<br />". $locale['error_058'] ." ". $catalog_image_fotomax ." Kb</div>\n"; $catalog_image = ""; }
				// // 6. "Габариты" фото > $maxwidth х $maxheight - ДО свиданья! :-)
				$catalog_image_getsize = getimagesize($catalog_imagetmp);
				if ($catalog_image_getsize[0]>$settings['catalog_photo_max_w'] or $catalog_image_getsize[1]>$settings['catalog_photo_max_h']) { $error .= "<div class='error'>". $locale['error_059'] ."<br />". $locale['error_060'] ." ". $catalog_image_getsize[0] ."x". $catalog_image_getsize[1] ."<br />". $locale['error_061'] ." ". $settings['catalog_photo_max_w'] ."x". $settings['catalog_photo_max_h'] ."</div>\n"; $catalog_image = ""; }
				// // if ($catalog_image_getsize[0]<$catalog_image_getsize[1]) { $error .= "<div class='error'>". $locale['error_062'] ."</div>\n"; $catalog_image = ""; }
				// // Foto 0 Kb
				// if ($catalog_imagesize<0 and $catalog_imagesize>$settings['catalog_size']) { $error .= "<div class='error'>". $locale['error_063'] ."</div>\n"; $catalog_image = ""; }
			}


			if (isset($error)) {

				echo "	<div class='admin-message'>\n";
				echo "		<div id='close-message'>". $error ."</div>\n";
				echo "	</div>\n";

			} else {


				if ($catalog_image) {

					$catalog_image_ext = strrchr($catalog_image, ".");
					$catalog_image = FUSION_TODAY;
					$img_rand_key = mt_rand(100, 999);

					if ($catalog_image_ext == ".gif") {
						$catalog_image_filetype = 1;
					} elseif ($catalog_image_ext == ".jpg") {
						$catalog_image_filetype = 2;
					} elseif ($catalog_image_ext == ".png") {
						$catalog_image_filetype = 3;
					} else {
						$catalog_image_filetype = false; 
					}

					$catalog_image = image_exists(IMAGES_C, $catalog_image . $img_rand_key . $catalog_image_ext);

					move_uploaded_file($catalog_imagetmp, IMAGES_C . $catalog_image);
					// if (function_exists("chmod")) { chmod(IMAGES_C . $catalog_image, 0644); }

					$catalog_image_size = getimagesize(IMAGES_C . $catalog_image);
					$catalog_image_width = $catalog_image_size[0];
					$catalog_image_height = $catalog_image_size[1];

					if ($settings['catalog_thumb_ratio']==0) {
						createthumbnail($catalog_image_filetype, IMAGES_C . $catalog_image, IMAGES_C_T . $catalog_image, ($catalog_image_width<$settings['catalog_thumb_w'] ? $catalog_image_width : $settings['catalog_thumb_w']), ($catalog_image_height<$settings['catalog_thumb_h'] ? $catalog_image_height : $settings['catalog_thumb_h']));
					} else {
						createsquarethumbnail($catalog_image_filetype, IMAGES_C . $catalog_image, IMAGES_C_T . $catalog_image, ($catalog_image_width<$settings['catalog_thumb_w'] ? $catalog_image_width : $settings['catalog_thumb_w']));
					}
					createthumbnail($catalog_image_filetype, IMAGES_C . $catalog_image, IMAGES_C . $catalog_image, ($catalog_image_width<$settings['catalog_photo_w'] ? $catalog_image_width : $settings['catalog_photo_w']));

				} else {
					$catalog_image = $catalog_image_yest;
				}



				if ($_GET['action']=="edit") {

					if ($catalog_image_del) {
						if ($catalog_image_yest && file_exists(IMAGES_C . $catalog_image_yest)) { unlink(IMAGES_C . $catalog_image_yest); }
						if ($catalog_image_yest && file_exists(IMAGES_C_T . $catalog_image_yest)) { unlink(IMAGES_C_T . $catalog_image_yest); }
						$catalog_image = "";
					}

					$result = dbquery(
						"UPDATE ". DB_CATALOG ." SET
															catalog_title='". serialize($catalog_title) ."',
															catalog_description='". serialize($catalog_description) ."',
															catalog_keywords='". serialize($catalog_keywords) ."',
															catalog_name='". serialize($catalog_name) ."',
															catalog_h1='". serialize($catalog_h1) ."',
															catalog_content='". serialize($catalog_content) ."',
															catalog_image='". $catalog_image ."',
															catalog_cat='". $catalog_cat ."',
															catalog_access='". $catalog_access ."',
															catalog_status='". $catalog_status ."',
															catalog_comments='". $catalog_comments ."',
															catalog_ratings='". $catalog_ratings ."'
						WHERE catalog_id='". (INT)$_GET['id'] ."'"
					);
					$catalog_id = (INT)$_GET['id'];

				} else {

					$result = dbquery(
						"INSERT INTO ". DB_CATALOG ." (
															catalog_title,
															catalog_description,
															catalog_keywords,
															catalog_name,
															catalog_h1,
															catalog_content,
															catalog_image,
															catalog_cat,
															catalog_access,
															catalog_status,
															catalog_comments,
															catalog_ratings
						) VALUES (
															'". serialize($catalog_title) ."',
															'". serialize($catalog_description) ."',
															'". serialize($catalog_keywords) ."',
															'". serialize($catalog_name) ."',
															'". serialize($catalog_h1) ."',
															'". serialize($catalog_content) ."',
															'". $catalog_image ."',
															'". $catalog_cat ."',
															'". $catalog_access ."',
															'". $catalog_status ."',
															'". $catalog_comments ."',
															'". $catalog_ratings ."'
						)"
					);
					// $catalog_id = mysql_insert_id();
					$catalog_id = _DB::$linkes->insert_id;

				} // UPDATE ILI INSERT


				$viewcompanent = viewcompanent("catalog", "name");
				$seourl_component = $viewcompanent['components_id'];

				// $catalog_alias = str_replace($settings['companent_root_url'], "", $catalog_alias);
				if (empty($catalog_alias)) {
					$catalog_alias = autocrateseourls($catalog_name[LOCALESHORT]);
				} else {
					$catalog_alias = autocrateseourls($catalog_alias);
				}

				$seourl_url = (empty($catalog_alias) ? "catalog_". $catalog_id . $settings['seourl_prefix'] : $catalog_alias . $settings['seourl_prefix']);
				$seourl_filedid = $catalog_id;

				$viewseourl = viewseourl($seourl_url, "url");

				if ($viewseourl['seourl_url']==$seourl_url) {
					if (($viewseourl['seourl_filedid']==$seourl_filedid) && ($viewseourl['seourl_component']==$seourl_component)) {
						$seourl_url = $seourl_url;
					} else {
						$seourl_url = "catalog_". $catalog_id . $settings['seourl_prefix'];
					}
				}  // Yesli URL YEst


				if ($catalog_cat!=0) {
					$seourl_url = $cat_url ."/". $seourl_url;
				} else {
					$seourl_url = $settings['companent_root_url'] . $seourl_url;
				}
				$catalog_alias = $seourl_url;


				if ($_GET['action']=="edit") {
					$result = dbquery(
						"UPDATE ". DB_SEOURL ." SET
															seourl_url='". $seourl_url ."',
															seourl_lastmod='". date("Y-m-d") ."'
						WHERE seourl_filedid='". $seourl_filedid ."' AND seourl_component='". $seourl_component ."'"
					);
				} else {
					$result = dbquery(
									"INSERT INTO ". DB_SEOURL ." (
																	seourl_url,
																	seourl_component,
																	seourl_filedid,
																	seourl_lastmod
										) VALUES (
																	'". $seourl_url ."',
																	'". $seourl_component ."',
																	'". $seourl_filedid ."',
																	'". date("Y-m-d") ."'
										)"
									);
				} // Yesli action edit 



				///////////////// POSITIONS /////////////////
				if ( $_GET['action']=="add" ) {
					$position=1;
					dbquery("UPDATE ". DB_CATALOG ." SET catalog_order='". $position ."' WHERE catalog_id='". $catalog_id ."'");
					$result_position = dbquery("SELECT catalog_id FROM ". DB_CATALOG ." WHERE catalog_id!='". $catalog_id ."' ORDER BY `catalog_order`");
					if (dbrows($result_position)) {
						while ($data_position = dbarray($result_position)) {
							$position++;
							dbquery("UPDATE ". DB_CATALOG ." SET catalog_order='". $position ."' WHERE catalog_id='". $data_position['catalog_id'] ."'");
						} // db whille
					} // db query
				} // Yesli action add
				///////////////// POSITIONS /////////////////



				////////// redirect
				if ($_GET['action']=="edit") {
					redirect(FUSION_SELF . $aidlink ."&status=edit&id=". $catalog_id ."&url=". $catalog_alias, false);
				} else {
					redirect(FUSION_SELF . $aidlink ."&status=add&id=". $catalog_id ."&url=". $catalog_alias, false);
				} ////////// redirect

			} // Yesli Error

		} // Yesli POST save


		$result_cats = dbquery(
							"SELECT
												catalog_cat_id,
												catalog_cat_name
							FROM ". DB_CATALOG_CATS ."
							WHERE catalog_cat_parent=0
							ORDER BY catalog_cat_name DESC");
		$catlist = "<option value='0'". ($catalog_cat==0 ? " selected='selected'" : "") .">". $locale['510_a'] ."</option>\n";

		if (dbrows($result_cats)) {

			$result_subcats = dbquery(
								"SELECT
													catalog_cat_id,
													catalog_cat_name,
													catalog_cat_parent
								FROM ". DB_CATALOG_CATS ."
								WHERE catalog_cat_parent!=0
								ORDER BY catalog_cat_name DESC");
			$subcatlist_arr = array();
			if (dbrows($result_subcats)) {
				while ($data_subcats = dbarray($result_subcats)) {
					$subcatlist_catalog_name = unserialize($data_subcats['catalog_cat_name']);
					$subcatlist_arr[$data_subcats['catalog_cat_id']]['catalog_cat_name'] = $subcatlist_catalog_name[LOCALESHORT];
					$subcatlist_arr[$data_subcats['catalog_cat_id']]['catalog_cat_parent'] = $data_subcats['catalog_cat_parent'];
				}
			}
			// echo "<pre>";
			// print_r($subcatlist_arr);
			// echo "</pre>";
			// echo "<hr>";

			while ($data_cats = dbarray($result_cats)) {
				$catlist_catalog_cat_name = unserialize($data_cats['catalog_cat_name']);

				$avaycatlist_arr = array();
				foreach ($subcatlist_arr as $subcatlist_key => $subcatlist_value) {
					if ($data_cats['catalog_cat_id']==$subcatlist_value['catalog_cat_parent']) {
						$avaycatlist_arr[$subcatlist_key] = $subcatlist_value['catalog_cat_name'];
					}
				}

				if ($avaycatlist_arr) {
					$catlist .= "<optgroup label='". $catlist_catalog_cat_name[LOCALESHORT] ."'>\n";
						foreach ($avaycatlist_arr as $avaycatlist_key => $avaycatlist_value) {
							$catlist .= "	<option value='". $avaycatlist_key ."'". ($catalog_cat==$avaycatlist_key ? " selected='selected'" : "") .">". $avaycatlist_value ."</option>\n";
						}
					$catlist .= "</optgroup>\n";
				} else {
					$catlist .= "<option value='". $data_cats['catalog_cat_id'] ."'". ($catalog_cat==$data_cats['catalog_cat_id'] ? " selected='selected'" : "") .">". $catlist_catalog_cat_name[LOCALESHORT] ."</option>\n";
				}

			} // db whille
		} // db query





		$user_groups = getusergroups();
		$access_opts = "";
		$sel = "";
		while (list($key, $user_group) = each($user_groups)) {
			$sel = ($cat_access == $user_group['0'] ? " selected='selected'" : "");
			$access_opts .= "<option value='". $user_group['0'] ."'$sel>". $user_group['1'] ."</option>\n";
		} // user_groups while

?>

	<form name='inputform' method='POST' action='<?php echo FUSION_SELF . $aidlink; ?>&action=<?php echo $_GET['action'];?><?php echo (isset($_GET['id']) && isnum($_GET['id']) ? "&id=". (INT)$_GET['id'] : ""); ?>' enctype='multipart/form-data'>
		<input type="hidden" name="catalog_status" id="catalog_status" value="<?php echo $catalog_status; ?>" />
		<input type="hidden" name="catalog_order" id="catalog_order" value="<?php echo $catalog_order; ?>" />
		<table class='form_table'>
			<tr>
				<td colspan="2"><a href="#" id="seo_tr_button">SEO</a></td>
			</tr>
			<tr class="seo_tr">
				<td colspan="2" class="seo_tr">
					<label for="catalog_title_<?php echo LOCALESHORT; ?>"><?php echo $locale['501']; ?></label>
					<?php foreach ($languages as $key => $value) { ?>
					<?php if ($languages_count>1) { ?><span class="local_name lang_<?php echo $value['languages_short']; ?>"><?php echo $value['languages_name']; ?></span><?php } ?>
					<input type="text" name="catalog_title[<?php echo $value['languages_short']; ?>]" id="catalog_title_<?php echo $value['languages_short']; ?>" value="<?php echo $catalog_title[$value['languages_short']]; ?>" class="textbox" style="width:100%;" /><br />
					<?php } // foreach languages ?>
				</td>
			</tr>
			<tr class="seo_tr">
				<td colspan="2" class="seo_tr">
					<label for="catalog_description_<?php echo LOCALESHORT; ?>"><?php echo $locale['502']; ?></label>
					<?php foreach ($languages as $key => $value) { ?>
					<?php if ($languages_count>1) { ?><span class="local_name lang_<?php echo $value['languages_short']; ?>"><?php echo $value['languages_name']; ?></span><?php } ?>
					<input type="text" name="catalog_description[<?php echo $value['languages_short']; ?>]" id="catalog_description_<?php echo $value['languages_short']; ?>" value="<?php echo $catalog_description[$value['languages_short']]; ?>" class="textbox" style="width:100%;" /><br />
					<?php } // foreach languages ?>
				</td>
			</tr>
			<tr class="seo_tr">
				<td colspan="2" class="seo_tr">
					<label for="catalog_keywords_<?php echo LOCALESHORT; ?>"><?php echo $locale['503']; ?></label>
					<?php foreach ($languages as $key => $value) { ?>
					<?php if ($languages_count>1) { ?><span class="local_name lang_<?php echo $value['languages_short']; ?>"><?php echo $value['languages_name']; ?></span><?php } ?>
					<input type="text" name="catalog_keywords[<?php echo $value['languages_short']; ?>]" id="catalog_keywords_<?php echo $value['languages_short']; ?>" value="<?php echo $catalog_keywords[$value['languages_short']]; ?>" class="textbox" style="width:100%;" /><br />
					<?php } // foreach languages ?>
				</td>
			</tr>
			<tr class="seo_tr">
				<td colspan="2" class="seo_tr">
					<label for="catalog_h1_<?php echo LOCALESHORT; ?>"><?php echo $locale['505']; ?></label>
					<?php foreach ($languages as $key => $value) { ?>
					<?php if ($languages_count>1) { ?><span class="local_name lang_<?php echo $value['languages_short']; ?>"><?php echo $value['languages_name']; ?></span><?php } ?>
					<input type="text" name="catalog_h1[<?php echo $value['languages_short']; ?>]" id="catalog_h1_<?php echo $value['languages_short']; ?>" value="<?php echo $catalog_h1[$value['languages_short']]; ?>" class="textbox" style="width:100%;" /><br />
					<?php } // foreach languages ?>
				</td>
			</tr>
			<tr class="seo_tr">
				<td colspan="2">
					<label for="catalog_alias"><?php echo $locale['506']; ?></label>
					<input readonly type="text" name="catalog_siteurl" id="catalog_siteurl" value="<?php echo $settings['siteurl'] . ($cat_url ? $cat_url ."/" : $settings['companent_root_url']); ?>" class="textbox" style="width:25%;" />
					<input type="text" name="catalog_alias" id="catalog_alias" value="<?php echo $catalog_alias; ?>" class="textbox" style="width:65%;" />
					<?php if ($settings['seourl_prefix']) { ?><input readonly type="text" name="seourl_prefix" id="seourl_prefix" value="<?php echo $settings['seourl_prefix']; ?>" class="textbox" style="width:5%;" /><?php } ?>
				</td>
			</tr>
			<tr class="seo_tr">
				<td colspan="2"></td>
			</tr>


			<tr>
				<td colspan="2">
					<label for="catalog_name_<?php echo LOCALESHORT; ?>"><?php echo $locale['504']; ?> <span>*</span></label>
					<?php foreach ($languages as $key => $value) { ?>
					<?php if ($languages_count>1) { ?><span class="local_name lang_<?php echo $value['languages_short']; ?>"><?php echo $value['languages_name']; ?></span><?php } ?>
					<input type="text" name="catalog_name[<?php echo $value['languages_short']; ?>]" id="catalog_name_<?php echo $value['languages_short']; ?>" value="<?php echo $catalog_name[$value['languages_short']]; ?>" class="textbox" style="width:100%;" /><br />
					<?php } // foreach languages ?>
				</td>
			</tr>

			<tr>
				<td colspan="2">
					<label for="catalog_image"><?php echo $locale['507']; ?></label>
					<?php if ($catalog_image && file_exists(IMAGES_C_T . $catalog_image)) { ?>
					<label>
						<img src="<?php echo IMAGES_C_T . $catalog_image; ?>" alt="" style="height:100px;" /><br />
						<input type="checkbox" name="catalog_image_del" value="1" /> <?php echo $locale['507_b']; ?>
						<input type="hidden" name="catalog_image_yest" value="<?php echo $catalog_image; ?>" />
					</label>
					<?php } else { ?>
					<input type="file" name="catalog_image" id="catalog_image" class="filebox" style="width:100%;" accept="image/*" />
					<div id="catalog_image_preview"></div>
					<?php echo sprintf($locale['507_a'], parsebytesize($settings['catalog_photo_max_b'], 3)); ?>
					<?php }	?>
				</td>
			</tr>

			<tr>
				<td colspan="2">
					<label for="catalog_content_<?php echo LOCALESHORT; ?>"><?php echo $locale['509']; ?></label>
					<?php foreach ($languages as $key => $value) { ?>
					<?php if ($languages_count>1) { ?><span class="local_name lang_<?php echo $value['languages_short']; ?>"><?php echo $value['languages_name']; ?></span><?php } ?>
					<textarea id="editor<?php echo $value['languages_id']; ?>" name="catalog_content[<?php echo $value['languages_short']; ?>]" id="catalog_content<?php echo $value['languages_short']; ?>" class="textareabox" cols="95" rows="15" style="width:100%"><?php echo $catalog_content[$value['languages_short']]; ?></textarea><br />
					<?php } // foreach languages ?>
				</td>
			</tr>
			<?php if (!$settings['tinymce_enabled']) { ?>
			<tr>
				<td colspan="2">
					<?php echo display_html("inputform", "catalog_content", true, true, true, IMAGES_N); ?>
				</td>
			</tr>
			<?php } ?>
			<tr>
				<td>
					<label for="catalog_access"><?php echo $locale['511']; ?></label>
					<select name="catalog_access" id="catalog_access" class="selectbox" style="width:200px;">
						<?php echo $access_opts; ?>
					</select>
				</td>
				<td>
					<label for="catalog_cat"><?php echo $locale['510']; ?> <span>*</span></label>
					<select name="catalog_cat" id="catalog_cat" class="selectbox" style="width:200px;">
						<?php echo $catlist; ?>
					</select>
				</td>

			<?php if ($settings['comments_enabled'] || $settings['ratings_enabled']) { ?>
			<tr>
				<td colspan="2">
					<?php if ($settings['comments_enabled']) { ?>
					<label><input type='checkbox' name='catalog_comments' value='1'<?php echo ($catalog_comments ? " checked='checked" : ""); ?> /> <?php echo $locale['510']; ?></label><br />
					<?php } ?>
					<?php if ($settings['ratings_enabled']) { ?>
					<label><input type='checkbox' name='catalog_ratings' value='1'<?php echo ($catalog_ratings ? " checked='checked" : ""); ?> /> <?php echo $locale['511']; ?></label><br />
					<?php } ?>
				</td>
			</tr>
			<?php } ?>

			<tr>
				<td colspan="2" class="form_buttons">
					<input type="submit" name="save" value="<?php echo $locale['520']; ?>" class="button" />
					<input type="button" name="cancel" value="<?php echo $locale['521']; ?>" class="button" onclick="location.href='<?php echo FUSION_SELF . $aidlink; ?>'" />
				</td>
			</tr>
		</table>
	</form>


		<script type='text/javascript'>
		<?php
		if ($settings['tinymce_enabled']) { 
			foreach ($languages as $key => $value) {
		?>
			var ckeditor<?php echo $value['languages_id']; ?> = CKEDITOR.replace('editor<?php echo $value['languages_id']; ?>');
			CKFinder.setupCKEditor( ckeditor<?php echo $value['languages_id']; ?>, '<?php echo INCLUDES; ?>jscripts/ckeditor/ckfinder/' );
		<?php
			} // foreach $languages
		} // Yesli Text Editor CKEDITOR
		?>
		</script>

		<?php

	} else {

	if ($_GET['status']) {
		if ($_GET['status']=="add") {

			$message = "<div class='success'>". $locale['success_002'] ." ID: ". intval($_GET['id']) ."</div>\n";
			$message .= "<div class='success'>". $locale['success_001'] ."<a href='". $settings['siteurl'] . $_GET['url'] ."' target='_blank'>". $_GET['url'] ."</a></div>\n";

		} elseif ($_GET['status']=="edit") {

			$message = "<div class='success'>". $locale['success_003'] ." ID: ". intval($_GET['id']) ."</div>\n";
			$message .= "<div class='success'>". $locale['success_001'] ."<a href='". $settings['siteurl'] . $_GET['url'] ."' target='_blank'>". $settings['siteurl'] . $_GET['url'] ."</a></div>\n";

		} elseif ($_GET['status']=="del") {

			$message = "<div class='success'>". $locale['success_004'] ." ID: ". intval($_GET['id']) ."</div>\n";

		} elseif ($_GET['status']=="active") {

			$message = "<div class='success'>". $locale['success_005'] ." ID: ". intval($_GET['id']) ."</div>\n";

		} elseif ($_GET['status']=="deactive") {

			$message = "<div class='success'>". $locale['success_006'] ." ID: ". intval($_GET['id']) ."</div>\n";

		}

	} // status

	echo "	<div class='admin-message'>\n";
	if ($message) {
	echo "		<div id='close-message'>". $message ."</div>\n";
	} // message
	echo "	</div>\n";


add_to_head("<script type='text/javascript' src='". INCLUDES ."jquery/jquery-ui.js'></script>");
add_to_head("<script type='text/javascript'>
	<!--
	$(document).ready(function() {
		$('.spisok_stranic tbody').sortable({
			handle : '.handle',
			placeholder: 'state-highlight',
			connectWith: '.connected',
			scroll: true,
			axis: 'y',
			update: function () {
				var ul = $(this),
					order = ul.sortable('serialize'),
					i = 0;
				$('.admin-message').empty();
				$('.admin-message').load('". FUSION_SELF . $aidlink ."&action=order&'+ order);
				ul.find('.num').each(function(i) {
					$(this).text(i+1);
				});
				// ul.find('tr').removeClass('tbl2').removeClass('tbl1');
				// ul.find('tr:odd').addClass('tbl2');
				// ul.find('tr:even').addClass('tbl1');
				window.setTimeout('closeDiv();',2500);
			}
		});
	});
	//-->
</script>");
?>


<?php

	$viewcompanent = viewcompanent("catalog", "name");
	$seourl_component = $viewcompanent['components_id'];


	// $result_alter = dbquery("ALTER TABLE `". DB_CATALOG ."` ORDER BY `catalog_order` ASC");

	$result = dbquery("SELECT 
								catalog_id,
								catalog_name,
								catalog_order,
								catalog_status,
								seourl_url
		FROM ". DB_CATALOG ."
		LEFT JOIN ". DB_SEOURL ." ON seourl_filedid=catalog_id AND seourl_component=". $seourl_component ."
		ORDER BY catalog_order
		LIMIT ". (INT)$_GET['rowstart'] .", ". $settings['catalog_per_page']);


	echo "<a href='". FUSION_SELF . $aidlink ."&action=add' class='add_page'>". $locale['010'] ."</a><br />\n";
?>

	<table class="spisok_stranic">
		<thead>
			<tr>
				<td class="list"></td>
				<td class="name"><?php echo $locale['401']; ?></td>
				<td class="status"><?php echo $locale['402']; ?></td>
				<td class="num"><?php echo $locale['403']; ?></td>
				<td class="links"><?php echo $locale['404']; ?></td>
			</tr>
		</thead>
		<tbody class="connected ui-sortable">
	<?php
		if (dbrows($result)) {
			$catalog_arr = array();
			while ($data = dbarray($result)) {
				$catalog_arr[$data['catalog_order']]['catalog_id'] = $data['catalog_id'];
				$catalog_arr[$data['catalog_order']]['catalog_name'] = unserialize($data['catalog_name']);
				$catalog_arr[$data['catalog_order']]['catalog_order'] = $data['catalog_order'];
				$catalog_arr[$data['catalog_order']]['catalog_status'] = $data['catalog_status'];
				$catalog_arr[$data['catalog_order']]['seourl_url'] = $data['seourl_url'];
			} // whille
		} // db query

		ksort($catalog_arr);

		// echo "<pre>";
		// print_r($catalog_arr);
		// echo "</pre>";
		// echo "<hr>";

		if ($catalog_arr) {
			foreach ($catalog_arr as $data) {
	?>
			<tr id="listItem_<?php echo $data['catalog_id']; ?>">
				<td class="list"><img src="<?php echo IMAGES; ?>arrow.png" alt="<?php echo $locale['410']; ?>" class="handle" /></td>
				<td class="name"><a href="<?php echo FUSION_SELF . $aidlink; ?>&action=edit&id=<?php echo $data['catalog_id']; ?>" title="<?php echo $data['catalog_name'][LOCALESHORT]; ?>"><?php echo $data['catalog_name'][LOCALESHORT]; ?></a></td>
				<td class="status">
					<a href="<?php echo FUSION_SELF . $aidlink; ?>&action=status&id=<?php echo $data['catalog_id']; ?>&status=<?php echo $data['catalog_status']; ?>" title="<?php echo ($data['catalog_status'] ? $locale['411'] : $locale['412']); ?>"><img src="<?php echo IMAGES; ?>status/status_<?php echo $data['catalog_status']; ?>.png" alt="<?php echo ($data['catalog_id'] ? $locale['411'] : $locale['412']); ?>"></a>
				</td>
				<td class="num"><?php echo $data['catalog_order']; ?></td>
				<td class="links">
					<a href="<?php echo BASEDIR . $data['seourl_url']; ?>" target="_blank" title="<?php echo $locale['413']; ?>"><img src="<?php echo IMAGES; ?>view.png" alt="<?php echo $locale['413']; ?>"></a>
					<a href="<?php echo FUSION_SELF . $aidlink; ?>&action=edit&id=<?php echo $data['catalog_id']; ?>" title="<?php echo $locale['414']; ?>"><img src="<?php echo IMAGES; ?>edit.png" alt="<?php echo $locale['414']; ?>"></a>
					<a href="<?php echo FUSION_SELF . $aidlink; ?>&action=del&id=<?php echo $data['catalog_id']; ?>" title="<?php echo $locale['415']; ?>" onclick="return DeleteOk();"><img src="<?php echo IMAGES; ?>delete.png" alt="<?php echo $locale['415']; ?>"></a>
				</td>
			</tr>
	<?php
			} // foreach catalog_arr
		} else {
	?>
			<tr>
				<td colspan="5"><?php echo $locale['012']; ?></td>
			</tr>
	<?php
		} // $catalog_arr
	?>
		</tbody>
		<tfoot>
			<tr>
				<td colspan="5">
<?php 
	$rows = dbcount("(catalog_id)", DB_CATALOG);
	if ($rows > $settings['catalog_per_page']) { echo makepagenav((INT)$_GET['rowstart'], $settings['catalog_per_page'], $rows, 3, ADMIN . FUSION_SELF . $aidlink ."&amp;") ."\n"; }
?>
				</td>
			</tr>
		</tfoot>
	</table>

	<script type='text/javascript'>
		function DeleteOk() {
			return confirm('<?php echo $locale['450']; ?>');
		}
	</script>

<?php

		// echo navigation((INT)$_GET['page'], $settings['catalog_cat_per_page'], "catalog_id", DB_CATALOG, "");


	} // action


	if ($_GET['action']!="order") {
		closetable();

		require_once THEMES."templates/footer.php";
	} // Yesli action ne order
?>