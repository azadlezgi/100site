<?php

	require_once "../includes/maincore.php";

	if (!checkrights("CATP") || !defined("iAUTH") || !isset($_GET['aid']) || $_GET['aid'] != iAUTH) { redirect("../index.php"); }

	include LOCALE . LOCALESET ."admin/catalog_params.php";




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
					dbquery("UPDATE ". DB_CATALOG_PPARAM ." SET catalogpparam_order='". ($position+1) ."' WHERE catalogpparam_id='". $item ."'");
				}
			}

			header("Content-Type: text/html; charset=". $locale['charset'] ."\n");
			echo "<div id='close-message'>\n";
			echo "	<div class='success'>". $locale['success_007'] ."</div>\n";
			echo "</div>\n";

		}


	} else if ($_GET['action']=="status") {

		$catalogpparam_id = (INT)$_GET['id'];
		$catalogpparam_status = (INT)$_GET['status'];
		$catalogpparam_status = ($catalogpparam_status ? 0 : 1);

		$result = dbquery("UPDATE ". DB_CATALOG_PPARAM ." SET
														catalogpparam_status='". $catalogpparam_status ."'
		WHERE catalogpparam_id='". $catalogpparam_id ."'");

		redirect(FUSION_SELF . $aidlink."&status=". ($catalogpparam_status ? "active" : "deactive") ."&id=". $catalogpparam_id, false);

	} else if ($_GET['action']=="del") {

		$catalogpvalue_count = dbcount("(catalogpvalue_id)", DB_CATALOG_PVALUE, "catalogpvalue_param_id='". (INT)$_GET['id'] ."'");
		if ($catalogpvalue_count>0) {

			redirect(FUSION_SELF . $aidlink ."&status=nodel&id=". (INT)$_GET['id']);

		} else {

			$result = dbquery("SELECT catalogpparam_image FROM ". DB_CATALOG_PPARAM ." WHERE catalogpparam_id='". (INT)$_GET['id'] ."'");
			if (dbrows($result)) {
				$data = dbarray($result);
				if (!empty($data['catalogpparam_image']) && file_exists(IMAGES_CC . $data['catalogpparam_image'])) { unlink(IMAGES_CC . $data['catalogpparam_image']); }
				if (!empty($data['catalogpparam_image']) && file_exists(IMAGES_CC_T . $data['catalogpparam_image'])) { unlink(IMAGES_CC_T . $data['catalogpparam_image']); }
			} // Tesli Yest DB query

			$result = dbquery("DELETE FROM ". DB_CATALOG_PPARAM ." WHERE catalogpparam_id='". (INT)$_GET['id'] ."'");

			$viewcompanent = viewcompanent("catalog_cats", "name");
			$seourl_component = $viewcompanent['components_id'];
			$seourl_filedid = (INT)$_GET['id'];

			$result = dbquery("DELETE FROM ". DB_SEOURL ." WHERE seourl_component='". $seourl_component ."' AND seourl_filedid='". $seourl_filedid ."'");


			///////////////// POSITIONS /////////////////
			$position=1;
			$result_position = dbquery("SELECT catalogpparam_id FROM ". DB_CATALOG_PPARAM ." ORDER BY `catalogpparam_order`");
			if (dbrows($result_position)) {
				while ($data_position = dbarray($result_position)) {
					$position++;
					dbquery("UPDATE ". DB_CATALOG_PPARAM ." SET catalogpparam_order='". $position ."' WHERE catalogpparam_id='". $data_position['catalogpparam_id'] ."'");
				} // db whille
			} // db query
			///////////////// POSITIONS /////////////////


			redirect(FUSION_SELF . $aidlink ."&status=del&id=". (INT)$_GET['id']);

		} // Yesli yest catalog

	} else if ($_GET['action']=="add" || $_GET['action']=="edit") {

		if (isset($_POST['save'])) {

			$catalogpparam_title = stripinput($_POST['catalogpparam_title']);
			$catalogpparam_description = stripinput($_POST['catalogpparam_description']);
			$catalogpparam_keywords = stripinput($_POST['catalogpparam_keywords']);
			$catalogpparam_name = stripinput($_POST['catalogpparam_name']);
			$catalogpparam_h1 = stripinput($_POST['catalogpparam_h1']);
			$catalogpparam_content = stripinput($_POST['catalogpparam_content']);

			$catalogpparam_image = $_FILES['catalogpparam_image']['name'];
			$catalogpparam_imagetmp  = $_FILES['catalogpparam_image']['tmp_name'];
			$catalogpparam_imagesize = $_FILES['catalogpparam_image']['size'];
			$catalogpparam_imagetype = $_FILES['catalogpparam_image']['type'];

			$catalogpparam_image_yest = stripinput($_POST['catalogpparam_image_yest']);
			$catalogpparam_image_del = (INT)$_POST['catalogpparam_image_del'];

			$catalogpparam_parent = stripinput($_POST['catalogpparam_parent']);
			$catalogpparam_access = (INT)$_POST['catalogpparam_access'];
			$catalogpparam_status = (INT)$_POST['catalogpparam_status'];

			// if ($_GET['action']=="edit") {
			// 	$catalogpparam_order = (INT)$_POST['catalogpparam_order'];
			// } else {
			// 	$result_cat_order = dbquery(
			// 		"SELECT 
			// 									catalogpparam_id,
			// 									catalogpparam_order
			// 		FROM ". DB_CATALOG_PPARAM ."
			// 		ORDER BY catalogpparam_order DESC
			// 		LIMIT 1"
			// 	);
			// 	if (dbrows($result_cat_order)) {
			// 		$data_cat_order = dbarray($result_cat_order);
			// 		$catalogpparam_order = $data_cat_order['catalogpparam_order']+1;
			// 	} else {
			// 		$catalogpparam_order = 1;
			// 	}
			// }

			$catalogpparam_date = FUSION_TODAY;

			$catalogpparam_alias = stripinput($_POST['catalogpparam_alias']);

		} else if ($_GET['action']=="edit") {

			$viewcompanent = viewcompanent("catalog_cats", "name");
			$seourl_component = $viewcompanent['components_id'];

			$result = dbquery(
				"SELECT 
											catalogpparam_id,
											catalogpparam_title,
											catalogpparam_description,
											catalogpparam_keywords,
											catalogpparam_name,
											catalogpparam_h1,
											catalogpparam_content,
											catalogpparam_image,
											catalogpparam_parent,
											catalogpparam_access,
											catalogpparam_status,
											seourl_url
				FROM ". DB_CATALOG_PPARAM ."
				LEFT JOIN ". DB_SEOURL ." ON seourl_filedid=catalogpparam_id AND seourl_component=". $seourl_component ."
				WHERE catalogpparam_id='". (INT)$_GET['id'] ."' LIMIT 1"
			);
			if (dbrows($result)) {
				$data = dbarray($result);

				$catalogpparam_title = unserialize($data['catalogpparam_title']);
				$catalogpparam_description = unserialize($data['catalogpparam_description']);
				$catalogpparam_keywords = unserialize($data['catalogpparam_keywords']);
				$catalogpparam_name = unserialize($data['catalogpparam_name']);
				$catalogpparam_h1 = unserialize($data['catalogpparam_h1']);
				$catalogpparam_content = unserialize($data['catalogpparam_content']);
				$catalogpparam_image = $data['catalogpparam_image'];
				$catalogpparam_parent =  $data['catalogpparam_parent'];
				$catalogpparam_access = $data['catalogpparam_access'];
				$catalogpparam_status = $data['catalogpparam_status'];

				$catalogpparam_alias = $data['seourl_url'];

			} else {
				redirect(FUSION_SELF . $aidlink);
			}

		} else {

				$catalogpparam_title = "";
				$catalogpparam_description = "";
				$catalogpparam_keywords = "";
				$catalogpparam_name = "";
				$catalogpparam_h1 = "";
				$catalogpparam_content = "";
				$catalogpparam_image = "";
				$catalogpparam_parent = 0;
				$catalogpparam_access = 0;
				$catalogpparam_status = 1;
				$catalogpparam_alias = "";

		} // Yesli POST


		########## SEO URL OPARATIONS ##########
		if ($settings['seourl_prefix']) {
			$seourl_prefix_strlen =  strlen($settings['seourl_prefix']);
			$seourl_prefix_alias = substr($catalogpparam_alias, -$seourl_prefix_strlen);
			if ($seourl_prefix_alias==$settings['seourl_prefix']) {
				$catalogpparam_alias = substr($catalogpparam_alias, 0, -$seourl_prefix_strlen);
			}
		} // yesli yest seourl_prefix

		if ($catalogpparam_parent!=0) {
			$viewcompanent = viewcompanent("catalog_cats", "name");
			$seourl_component = $viewcompanent['components_id'];

			$viewseourl = viewseourl($catalogpparam_parent, "filedid", $seourl_component);
			// echo "<pre>";
			// print_r($viewseourl);
			// echo "</pre>";
			// echo "<hr>";

			if ($viewseourl) {
				$parent_url = $viewseourl['seourl_url'];
			}
			if ($settings['seourl_prefix']) {
				$seourl_prefix_strlen =  strlen($settings['seourl_prefix']);
				$seourl_prefix_alias = substr($parent_url, -$seourl_prefix_strlen);
				if ($seourl_prefix_alias==$settings['seourl_prefix']) {
					$parent_url = substr($parent_url, 0, -$seourl_prefix_strlen);
				}
			} // yesli yest seourl_prefix

			$catalogpparam_alias = str_replace($parent_url ."/", "", $catalogpparam_alias);
		} else {
			$catalogpparam_alias = str_replace($settings['companent_root_url'], "", $catalogpparam_alias);
		}
		########## //SEO URL OPARATIONS ##########



		if (isset($_POST['save'])) {


			foreach ($languages as $key => $value) {
				if (empty($catalogpparam_name[$value['languages_short']])) { $error .= "<div class='error'>". $locale['error_001'] ." - ". $value['languages_name'] ."</div>\n"; }
			}
			// foreach ($languages as $key => $value) {
			// 	if (empty($catalogpparam_content[$value['languages_short']])) { $error .= "<div class='error'>". $locale['error_002'] ." - ". $value['languages_name'] ."</div>\n"; }
			// }

			if ($catalogpparam_image) {
				// if (strlen($catalogpparam_image) > 255) { $error .= "<div class='error'>". $locale['error_050'] ."</div>\n"; $catalogpparam_image = ""; }
				// проверяем расширение файла
				$catalogpparam_image_ext = strtolower(substr($catalogpparam_image, 1 + strrpos($catalogpparam_image, ".")));
				if (!in_array($catalogpparam_image_ext, $photo_valid_types)) { $error .= "<div class='error'>". $locale['error_051'] ."</div>\n"; $catalogpparam_image = ""; }
				// 1. считаем кол-во точек в выражении - если большей одной - СВОБОДЕН!
				$catalogpparam_image_findtochka = substr_count($catalogpparam_image, ".");
				if ($catalogpparam_image_findtochka>1) { $error .= "<div class='error'>". $locale['error_052'] ."</div>\n"; $catalogpparam_image = ""; }
				// 2. если в имени есть .php, .html, .htm - свободен! 
				if (preg_match("/\.php/i",$catalogpparam_image))  { $error .= "<div class='error'>". $locale['error_053'] ."</div>\n"; $catalogpparam_image = ""; }
				if (preg_match("/\.html/i",$catalogpparam_image)) { $error .= "<div class='error'>". $locale['error_054'] ."</div>\n"; $catalogpparam_image = ""; }
				if (preg_match("/\.htm/i",$catalogpparam_image))  { $error .= "<div class='error'>". $locale['error_055'] ."</div>\n"; $catalogpparam_image = ""; }
				// 5. Размер фото
				$catalogpparam_image_fotosize = round($catalogpparam_imagesize/10.24)/100; // размер ЗАГРУЖАЕМОГО ФОТО в Кб.
				$catalogpparam_image_fotomax = round($settings['catalogpparam_photo_max_b']/10.24)/100; // максимальный размер фото в Кб.
				if ($catalogpparam_image_fotosize>$catalogpparam_image_fotomax) { $error .= "<div class='error'>". $locale['error_056'] ."<br />". $locale['error_057'] ." ". $catalogpparam_image_fotosize ." Kb<br />". $locale['error_058'] ." ". $catalogpparam_image_fotomax ." Kb</div>\n"; $catalogpparam_image = ""; }
				// // 6. "Габариты" фото > $maxwidth х $maxheight - ДО свиданья! :-)
				$catalogpparam_image_getsize = getimagesize($catalogpparam_imagetmp);
				if ($catalogpparam_image_getsize[0]>$settings['catalogpparam_photo_max_w'] or $catalogpparam_image_getsize[1]>$settings['catalogpparam_photo_max_h']) { $error .= "<div class='error'>". $locale['error_059'] ."<br />". $locale['error_060'] ." ". $catalogpparam_image_getsize[0] ."x". $catalogpparam_image_getsize[1] ."<br />". $locale['error_061'] ." ". $settings['catalogpparam_photo_max_w'] ."x". $settings['catalogpparam_photo_max_h'] ."</div>\n"; $catalogpparam_image = ""; }
				// // if ($catalogpparam_image_getsize[0]<$catalogpparam_image_getsize[1]) { $error .= "<div class='error'>". $locale['error_062'] ."</div>\n"; $catalogpparam_image = ""; }
				// // Foto 0 Kb
				// if ($catalogpparam_imagesize<0 and $catalogpparam_imagesize>$settings['catalogpparam_size']) { $error .= "<div class='error'>". $locale['error_063'] ."</div>\n"; $catalogpparam_image = ""; }
			}


			if (isset($error)) {

				echo "	<div class='admin-message'>\n";
				echo "		<div id='close-message'>". $error ."</div>\n";
				echo "	</div>\n";

				$catalog_image = "";

			} else {


				if ($catalogpparam_image) {

					$catalogpparam_image_ext = strrchr($catalogpparam_image, ".");
					$catalogpparam_image = FUSION_TODAY;
					$img_rand_key = mt_rand(100, 999);

					if ($catalogpparam_image_ext == ".gif") {
						$catalogpparam_image_filetype = 1;
					} elseif ($catalogpparam_image_ext == ".jpg") {
						$catalogpparam_image_filetype = 2;
					} elseif ($catalogpparam_image_ext == ".png") {
						$catalogpparam_image_filetype = 3;
					} else {
						$catalogpparam_image_filetype = false; 
					}

					$catalogpparam_image = image_exists(IMAGES_CC, $catalogpparam_image . $img_rand_key . $catalogpparam_image_ext);

					move_uploaded_file($catalogpparam_imagetmp, IMAGES_CC . $catalogpparam_image);
					// if (function_exists("chmod")) { chmod(IMAGES_CC . $catalogpparam_image, 0644); }

					$catalogpparam_image_size = getimagesize(IMAGES_CC . $catalogpparam_image);
					$catalogpparam_image_width = $catalogpparam_image_size[0];
					$catalogpparam_image_height = $catalogpparam_image_size[1];

					if ($settings['catalogpparam_thumb_ratio']==0) {
						createthumbnail($catalogpparam_image_filetype, IMAGES_CC . $catalogpparam_image, IMAGES_CC_T . $catalogpparam_image, ($catalogpparam_image_width<$settings['catalogpparam_thumb_w'] ? $catalogpparam_image_width : $settings['catalogpparam_thumb_w']), ($catalogpparam_image_height<$settings['catalogpparam_thumb_h'] ? $catalogpparam_image_height : $settings['catalogpparam_thumb_h']));
					} else {
						createsquarethumbnail($catalogpparam_image_filetype, IMAGES_CC . $catalogpparam_image, IMAGES_CC_T . $catalogpparam_image, ($catalogpparam_image_width<$settings['catalogpparam_thumb_w'] ? $catalogpparam_image_width : $settings['catalogpparam_thumb_w']));
					}
					createthumbnail($catalogpparam_image_filetype, IMAGES_CC . $catalogpparam_image, IMAGES_CC . $catalogpparam_image, ($catalogpparam_image_width<$settings['catalogpparam_photo_w'] ? $catalogpparam_image_width : $settings['catalogpparam_photo_w']));

				} else {
					$catalogpparam_image = $catalogpparam_image_yest;
				}



				if ($_GET['action']=="edit") {

					if ($catalogpparam_image_del) {
						if ($catalogpparam_image_yest && file_exists(IMAGES_CC . $catalogpparam_image_yest)) { unlink(IMAGES_CC . $catalogpparam_image_yest); }
						if ($catalogpparam_image_yest && file_exists(IMAGES_CC_T . $catalogpparam_image_yest)) { unlink(IMAGES_CC_T . $catalogpparam_image_yest); }
						$catalogpparam_image = "";
					}

					$result = dbquery(
						"UPDATE ". DB_CATALOG_PPARAM ." SET
															catalogpparam_title='". serialize($catalogpparam_title) ."',
															catalogpparam_description='". serialize($catalogpparam_description) ."',
															catalogpparam_keywords='". serialize($catalogpparam_keywords) ."',
															catalogpparam_name='". serialize($catalogpparam_name) ."',
															catalogpparam_h1='". serialize($catalogpparam_h1) ."',
															catalogpparam_content='". serialize($catalogpparam_content) ."',
															catalogpparam_image='". $catalogpparam_image ."',
															catalogpparam_parent='". $catalogpparam_parent ."',
															catalogpparam_access='". $catalogpparam_access ."',
															catalogpparam_status='". $catalogpparam_status ."'
						WHERE catalogpparam_id='". (INT)$_GET['id'] ."'"
					);
					$catalogpparam_id = (INT)$_GET['id'];

				} else {

					$result = dbquery(
						"INSERT INTO ". DB_CATALOG_PPARAM ." (
															catalogpparam_title,
															catalogpparam_description,
															catalogpparam_keywords,
															catalogpparam_name,
															catalogpparam_h1,
															catalogpparam_content,
															catalogpparam_image,
															catalogpparam_parent,
															catalogpparam_access,
															catalogpparam_status
						) VALUES (
															'". serialize($catalogpparam_title) ."',
															'". serialize($catalogpparam_description) ."',
															'". serialize($catalogpparam_keywords) ."',
															'". serialize($catalogpparam_name) ."',
															'". serialize($catalogpparam_h1) ."',
															'". serialize($catalogpparam_content) ."',
															'". $catalogpparam_image ."',
															'". $catalogpparam_parent ."',
															'". $catalogpparam_access ."',
															'". $catalogpparam_status ."'
						)"
					);
					// $catalogpparam_id = mysql_insert_id();
					$catalogpparam_id = _DB::$linkes->insert_id;

				} // UPDATE ILI INSERT


				$viewcompanent = viewcompanent("catalog_cats", "name");
				$seourl_component = $viewcompanent['components_id'];

				// $catalogpparam_alias = str_replace($settings['companent_root_url'], "", $catalogpparam_alias);
				if (empty($catalogpparam_alias)) {
					$catalogpparam_alias = autocrateseourls($catalogpparam_name[LOCALESHORT]);
				} else {
					$catalogpparam_alias = autocrateseourls($catalogpparam_alias);
				}

				$seourl_url = (empty($catalogpparam_alias) ? "catalogpparam_". $catalogpparam_id . $settings['seourl_prefix'] : $catalogpparam_alias . $settings['seourl_prefix']);
				$seourl_filedid = $catalogpparam_id;

				$viewseourl = viewseourl($seourl_url, "url");

				if ($viewseourl['seourl_url']==$seourl_url) {
					if (($viewseourl['seourl_filedid']==$seourl_filedid) && ($viewseourl['seourl_component']==$seourl_component)) {
						$seourl_url = $seourl_url;
					} else {
						$seourl_url = "catalogpparam_". $catalogpparam_id . $settings['seourl_prefix'];
					}
				}  // Yesli URL YEst


				if ($catalogpparam_parent!=0) {
					$seourl_url = $parent_url ."/". $seourl_url;
				} else {
					$seourl_url = $settings['companent_root_url'] . $seourl_url;
				}
				$catalogpparam_alias = $seourl_url;


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
					dbquery("UPDATE ". DB_CATALOG_PPARAM ." SET catalogpparam_order='". $position ."' WHERE catalogpparam_id='". $catalogpparam_id ."'");
					$result_position = dbquery("SELECT catalogpparam_id FROM ". DB_CATALOG_PPARAM ." WHERE catalogpparam_id!='". $catalogpparam_id ."' ORDER BY `catalogpparam_order`");
					if (dbrows($result_position)) {
						while ($data_position = dbarray($result_position)) {
							$position++;
							dbquery("UPDATE ". DB_CATALOG_PPARAM ." SET catalogpparam_order='". $position ."' WHERE catalogpparam_id='". $data_position['catalogpparam_id'] ."'");
						} // db whille
					} // db query
				} // Yesli action add
				///////////////// POSITIONS /////////////////


				////////// redirect
				if ($_GET['action']=="edit") {
					redirect(FUSION_SELF . $aidlink ."&status=edit&id=". $catalogpparam_id ."&url=". $catalogpparam_alias, false);
				} else {
					redirect(FUSION_SELF . $aidlink ."&status=add&id=". $catalogpparam_id ."&url=". $catalogpparam_alias, false);
				} ////////// redirect

			} // Yesli Error

		} // Yesli POST save


	$result_parent = dbquery(
							"SELECT
												catalogpparam_id,
												catalogpparam_name
							FROM ". DB_CATALOG_PPARAM ."
							WHERE catalogpparam_parent=0
							ORDER BY catalogpparam_name DESC");
	$parent_opts = "<option value='0'". ($catalogpparam_parent==0 ? " selected='selected'" : "") .">". $locale['513'] ."</option>\n";
	while ($data_parent = dbarray($result_parent)) {
		$parent_opts_catalogpparam_name = unserialize($data_parent['catalogpparam_name']);
		$parent_opts .= "<option value='". $data_parent['catalogpparam_id'] ."'". ($catalogpparam_parent==$data_parent['catalogpparam_id'] ? " selected='selected'" : "") .">". $parent_opts_catalogpparam_name[LOCALESHORT] ."</option>\n";
	}


	$user_groups = getusergroups();
	$access_opts = "";
	$sel = "";
	while (list($key, $user_group) = each($user_groups)) {
		$sel = ($cat_access == $user_group['0'] ? " selected='selected'" : "");
		$access_opts .= "<option value='". $user_group['0'] ."'$sel>". $user_group['1'] ."</option>\n";
	}

	echo "<a href='". FUSION_SELF.$aidlink ."' class='go_back'>". $locale['471'] ."</a><br />\n";
?>


	<form name='inputform' method='POST' action='<?php echo FUSION_SELF . $aidlink; ?>&action=<?php echo $_GET['action'];?><?php echo (isset($_GET['id']) && isnum($_GET['id']) ? "&id=". (INT)$_GET['id'] : ""); ?>' enctype='multipart/form-data'>
		<input type="hidden" name="catalogpparam_status" id="catalogpparam_status" value="<?php echo $catalogpparam_status; ?>" />
		<input type="hidden" name="catalogpparam_order" id="catalogpparam_order" value="<?php echo $catalogpparam_order; ?>" />
		<table class='form_table'>
			<tr>
				<td colspan="2"><a href="#" id="seo_tr_button">SEO</a></td>
			</tr>
			<tr class="seo_tr">
				<td colspan="2" class="seo_tr">
					<label for="catalogpparam_title_<?php echo LOCALESHORT; ?>"><?php echo $locale['501']; ?></label>
					<?php foreach ($languages as $key => $value) { ?>
					<?php if ($languages_count>1) { ?><span class="local_name lang_<?php echo $value['languages_short']; ?>" style="background-image: url(<?php echo IMAGES ."flags/". $value['languages_short'] .".png"; ?>)"><?php echo $value['languages_name']; ?></span><?php } ?>
					<input type="text" name="catalogpparam_title[<?php echo $value['languages_short']; ?>]" id="catalogpparam_title_<?php echo $value['languages_short']; ?>" value="<?php echo $catalogpparam_title[$value['languages_short']]; ?>" class="textbox" style="width:100%;" /><br />
					<?php } // foreach languages ?>
				</td>
			</tr>
			<tr class="seo_tr">
				<td colspan="2" class="seo_tr">
					<label for="catalogpparam_description_<?php echo LOCALESHORT; ?>"><?php echo $locale['502']; ?></label>
					<?php foreach ($languages as $key => $value) { ?>
					<?php if ($languages_count>1) { ?><span class="local_name lang_<?php echo $value['languages_short']; ?>" style="background-image: url(<?php echo IMAGES ."flags/". $value['languages_short'] .".png"; ?>)"><?php echo $value['languages_name']; ?></span><?php } ?>
					<input type="text" name="catalogpparam_description[<?php echo $value['languages_short']; ?>]" id="catalogpparam_description_<?php echo $value['languages_short']; ?>" value="<?php echo $catalogpparam_description[$value['languages_short']]; ?>" class="textbox" style="width:100%;" /><br />
					<?php } // foreach languages ?>
				</td>
			</tr>
			<tr class="seo_tr">
				<td colspan="2" class="seo_tr">
					<label for="catalogpparam_keywords_<?php echo LOCALESHORT; ?>"><?php echo $locale['503']; ?></label>
					<?php foreach ($languages as $key => $value) { ?>
					<?php if ($languages_count>1) { ?><span class="local_name lang_<?php echo $value['languages_short']; ?>" style="background-image: url(<?php echo IMAGES ."flags/". $value['languages_short'] .".png"; ?>)"><?php echo $value['languages_name']; ?></span><?php } ?>
					<input type="text" name="catalogpparam_keywords[<?php echo $value['languages_short']; ?>]" id="catalogpparam_keywords_<?php echo $value['languages_short']; ?>" value="<?php echo $catalogpparam_keywords[$value['languages_short']]; ?>" class="textbox" style="width:100%;" /><br />
					<?php } // foreach languages ?>
				</td>
			</tr>
			<tr class="seo_tr">
				<td colspan="2" class="seo_tr">
					<label for="catalogpparam_h1_<?php echo LOCALESHORT; ?>"><?php echo $locale['505']; ?></label>
					<?php foreach ($languages as $key => $value) { ?>
					<?php if ($languages_count>1) { ?><span class="local_name lang_<?php echo $value['languages_short']; ?>" style="background-image: url(<?php echo IMAGES ."flags/". $value['languages_short'] .".png"; ?>)"><?php echo $value['languages_name']; ?></span><?php } ?>
					<input type="text" name="catalogpparam_h1[<?php echo $value['languages_short']; ?>]" id="catalogpparam_h1_<?php echo $value['languages_short']; ?>" value="<?php echo $catalogpparam_h1[$value['languages_short']]; ?>" class="textbox" style="width:100%;" /><br />
					<?php } // foreach languages ?>
				</td>
			</tr>
			<tr class="seo_tr">
				<td colspan="2">
					<label for="catalogpparam_alias"><?php echo $locale['506']; ?></label>
					<input readonly type="text" name="catalogpparam_siteurl" id="catalogpparam_siteurl" value="<?php echo $settings['siteurl'] . ($parent_url ? $parent_url ."/" : $settings['companent_root_url']); ?>" class="textbox" style="width:25%;" />
					<input type="text" name="catalogpparam_alias" id="catalogpparam_alias" value="<?php echo $catalogpparam_alias; ?>" class="textbox" style="width:65%;" />
					<?php if ($settings['seourl_prefix']) { ?><input readonly type="text" name="seourl_prefix" id="seourl_prefix" value="<?php echo $settings['seourl_prefix']; ?>" class="textbox" style="width:5%;" /><?php } ?>
				</td>
			</tr>
			<tr class="seo_tr">
				<td colspan="2"></td>
			</tr>


			<tr>
				<td colspan="2">
					<label for="catalogpparam_name_<?php echo LOCALESHORT; ?>"><?php echo $locale['504']; ?> <span>*</span></label>
					<?php foreach ($languages as $key => $value) { ?>
					<?php if ($languages_count>1) { ?><span class="local_name lang_<?php echo $value['languages_short']; ?>" style="background-image: url(<?php echo IMAGES ."flags/". $value['languages_short'] .".png"; ?>)"><?php echo $value['languages_name']; ?></span><?php } ?>
					<input type="text" name="catalogpparam_name[<?php echo $value['languages_short']; ?>]" id="catalogpparam_name_<?php echo $value['languages_short']; ?>" value="<?php echo $catalogpparam_name[$value['languages_short']]; ?>" class="textbox" style="width:100%;" /><br />
					<?php } // foreach languages ?>
				</td>
			</tr>

			<tr>
				<td colspan="2">
					<label for="catalogpparam_image"><?php echo $locale['507']; ?></label>
					<?php if ($catalogpparam_image && file_exists(IMAGES_CC_T . $catalogpparam_image)) { ?>
					<label>
						<img src="<?php echo IMAGES_CC_T . $catalogpparam_image; ?>" alt="" style="height:100px;" /><br />
						<input type="checkbox" name="catalogpparam_image_del" value="1" /> <?php echo $locale['507_b']; ?>
						<input type="hidden" name="catalogpparam_image_yest" value="<?php echo $catalogpparam_image; ?>" />
					</label>
					<?php } else { ?>
					<input type="file" name="catalogpparam_image" id="catalogpparam_image" class="filebox" style="width:100%;" accept="image/*" />
					<div id="catalog_image_preview"></div>
					<?php echo sprintf($locale['507_a'], parsebytesize($settings['catalogpparam_photo_max_b'], 3)); ?>
					<?php }	?>
				</td>
			</tr>

			<tr>
				<td colspan="2">
					<label for="catalogpparam_content_<?php echo LOCALESHORT; ?>"><?php echo $locale['509']; ?></label>
					<?php foreach ($languages as $key => $value) { ?>
					<?php if ($languages_count>1) { ?><span class="local_name lang_<?php echo $value['languages_short']; ?>" style="background-image: url(<?php echo IMAGES ."flags/". $value['languages_short'] .".png"; ?>)"><?php echo $value['languages_name']; ?></span><?php } ?>
					<textarea id="editor<?php echo $value['languages_id']; ?>" name="catalogpparam_content[<?php echo $value['languages_short']; ?>]" id="catalogpparam_content<?php echo $value['languages_short']; ?>" class="textareabox" cols="95" rows="15" style="width:100%"><?php echo $catalogpparam_content[$value['languages_short']]; ?></textarea><br />
					<?php } // foreach languages ?>
				</td>
			</tr>
			<?php if (!$settings['tinymce_enabled']) { ?>
			<tr>
				<td colspan="2">
					<?php echo display_html("inputform", "catalogpparam_content", true, true, true, IMAGES_CC); ?>
				</td>
			</tr>
			<?php } ?>
			<tr>
				<td>
					<label for="catalogpparam_access"><?php echo $locale['511']; ?></label>
					<select name="catalogpparam_access" id="catalogpparam_access" class="selectbox" style="width:200px;">
						<?php echo $access_opts; ?>
					</select>
				</td>
				<td>
					<label for="catalogpparam_parent"><?php echo $locale['512']; ?></label>
					<select name="catalogpparam_parent" id="catalogpparam_access" class="selectbox" style="width:200px;">
						<?php echo $parent_opts; ?>
					</select>
				</td>
			</tr>
			<tr>
				<td colspan="2" class="form_buttons">
					<input type="submit" name="save" value="<?php echo $locale['520']; ?>" class="button" />
					<input type="button" name="cancel" value="<?php echo $locale['521']; ?>" class="button" onclick="location.href='<?php echo FUSION_SELF . $aidlink; ?>'" />
				</td>
			</tr>
		</table>
	</form>


	<script language="javascript" type="text/javascript">
		<!--
		$(function () {
			$("#catalog_image").change(function () {
				if (typeof (FileReader) != "undefined") {
					var dvPreview = $("#catalog_image_preview");
					dvPreview.html("");
					// var regex = /^([a-zA-Z0-9\s_\\.\-:])+(.jpg|.jpeg|.gif|.png|.bmp)$/;
					$($(this)[0].files).each(function () {
						var file = $(this);
						// if (regex.test(file[0].name.toLowerCase())) {
							var reader = new FileReader();
							reader.onload = function (e) {
								var img = $("<img />");
								img.attr("style", "height:100px");
								img.attr("src", e.target.result);
								// console.log( img );
								dvPreview.append(img);
							}
							reader.readAsDataURL(file[0]);
						// } else {
						//	 alert(file[0].name + " is not a valid image file.");
						//	 dvPreview.html("");
						//	 return false;
						// }
					});
				} else {
					alert("This browser does not support HTML5 FileReader.");
				}
			});
		});
		//-->
	</script>

	<script type='text/javascript'>
	<?php
	if ($settings['tinymce_enabled']==2) { 
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
			$message .= "<div class='success'>". $locale['success_001'] ."<a href='". $settings['siteurl'] . $_GET['url'] ."' target='_blank'>". $settings['siteurl'] . $_GET['url'] ."</a></div>\n";

		} elseif ($_GET['status']=="edit") {

			$message = "<div class='success'>". $locale['success_003'] ." ID: ". intval($_GET['id']) ."</div>\n";
			$message .= "<div class='success'>". $locale['success_001'] ."<a href='". $settings['siteurl'] . $_GET['url'] ."' target='_blank'>". $settings['siteurl'] . $_GET['url'] ."</a></div>\n";

		} elseif ($_GET['status']=="del") {

			$message = "<div class='success'>". $locale['success_004'] ." ID: ". intval($_GET['id']) ."</div>\n";

		} elseif ($_GET['status']=="nodel") {

			$message = "<div class='error'>". $locale['success_008'] ." ID: ". intval($_GET['id']) ."</div>\n";

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



	$result_group = dbquery("SELECT 
									catalogpgroup_id,
									catalogpgroup_name
			FROM ". DB_CATALOG_PGROUP);
	if (dbrows($result_group)) {
		add_to_footer("<script type='text/javascript' src='". INCLUDES ."jquery/jquery-ui.js'></script>");
		while ($data_group = dbarray($result_group)) {
			$catalogpgroup_name = unserialize($data_group['catalogpgroup_name']);
			echo "<label>". $catalogpgroup_name[LOCALESHORT] ."</label>\n";

			add_to_footer("<script type='text/javascript'>
				<!--
				$(document).ready(function() {
					$('.spisok_stranic". $data_group['catalogpgroup_id'] ." tbody').sortable({
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


			$result = dbquery("SELECT 
											catalogpparam_id,
											catalogpparam_name,
											catalogpparam_order,
											catalogpparam_status
					FROM ". DB_CATALOG_PPARAM ."
					WHERE catalogpparam_pgruop_id='". $data_group['catalogpgroup_id'] ."'
					ORDER BY catalogpparam_order");

			echo "<a href='". FUSION_SELF . $aidlink ."&action=add' class='add_page'>". $locale['010'] ."</a><br />\n";
			?>

			<table class="spisok_stranic spisok_stranic<?php echo $data_group['catalogpgroup_id']; ?>">
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
					while ($data = dbarray($result)) {
						$catalogpparam_name = unserialize($data['catalogpparam_name']);
			?>
					<tr id="listItem_<?php echo $data['catalogpparam_id']; ?>">
						<td class="list"><img src="<?php echo IMAGES; ?>arrow.png" alt="<?php echo $locale['410']; ?>" class="handle" /></td>
						<td class="name">
							<a href="<?php echo FUSION_SELF . $aidlink; ?>&action=edit&id=<?php echo $data['catalogpparam_id']; ?>" title="<?php echo $catalogpparam_name[LOCALESHORT]; ?>"><?php echo $catalogpparam_name[LOCALESHORT]; ?></a>
						</td>
						<td class="status">
							<a href="<?php echo FUSION_SELF . $aidlink; ?>&action=status&id=<?php echo $data['catalogpparam_id']; ?>&status=<?php echo $data['catalogpparam_status']; ?>" title="<?php echo ($data['catalogpparam_status'] ? $locale['411'] : $locale['412']); ?>"><img src="<?php echo IMAGES; ?>status/status_<?php echo $data['catalogpparam_status']; ?>.png" alt="<?php echo ($data['catalogpparam_status'] ? $locale['411'] : $locale['412']); ?>"></a>
						</td>
						<td class="num"><?php echo $data['catalogpparam_order']; ?></td>
						<td class="links">
							<!-- <a href="<?php echo BASEDIR . $data['seourl_url']; ?>" target="_blank" title="<?php echo $locale['413']; ?>"><img src="<?php echo IMAGES; ?>view.png" alt="<?php echo $locale['413']; ?>"></a> -->
							<a href="<?php echo FUSION_SELF . $aidlink; ?>&action=edit&id=<?php echo $data['catalogpparam_id']; ?>" title="<?php echo $locale['414']; ?>"><img src="<?php echo IMAGES; ?>edit.png" alt="<?php echo $locale['414']; ?>"></a>
							<a href="<?php echo FUSION_SELF . $aidlink; ?>&action=del&id=<?php echo $data['catalogpparam_id']; ?>" title="<?php echo $locale['415']; ?>" onclick="return DeleteOk();"><img src="<?php echo IMAGES; ?>delete.png" alt="<?php echo $locale['415']; ?>"></a>
						</td>
					</tr>
			<?php
					} // db whille
				} else {
			?>
					<tr>
						<td colspan="5"><?php echo $locale['012']; ?></td>
					</tr>
			<?php
				} // db query
			?>
				</tbody>
				<tfoot>
					<tr>
						<td colspan="5">
							&nbsp;
						</td>
					</tr>
				</tfoot>
			</table>

	<?php
		} // db whille group
	} else {
	?>
		<div><?php echo $locale['012']; ?></div>
	<?php
	} // db query group

	add_to_footer("<script type='text/javascript'>
		<!--
		function DeleteOk() {
			return confirm('". $locale['450'] ."');
		}
		//-->
	</script>");

} // action


if ($_GET['action']!="order") {
	closetable();

	require_once THEMES."templates/footer.php";
} // Yesli action ne order
?>