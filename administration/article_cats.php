<?php

	require_once "../includes/maincore.php";

	if (!checkrights("AC") || !defined("iAUTH") || !isset($_GET['aid']) || $_GET['aid'] != iAUTH) { redirect("../index.php"); }

	include LOCALE . LOCALESET ."admin/article_cats.php";


	if (!$settings['companent_root_url']) {
		$settings['companent_root_url'] = "articles/";
	}
	if (!$settings['seourl_prefix']) {
		$settings['seourl_prefix'] = "";
	}


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
					dbquery("UPDATE ". DB_ARTICLE_CATS ." SET article_cat_order='". ($position+1) ."' WHERE article_cat_id='". $item ."'");
				}
			}

			header("Content-Type: text/html; charset=". $locale['charset'] ."\n");
			echo "<div id='close-message'>\n";
			echo "	<div class='success'>". $locale['success_007'] ."</div>\n";
			echo "</div>\n";

		}


	} else if ($_GET['action']=="status") {

		$article_cat_id = (INT)$_GET['id'];
		$article_cat_status = (INT)$_GET['status'];
		$article_cat_status = ($article_cat_status ? 0 : 1);

		$result = dbquery("UPDATE ". DB_ARTICLE_CATS ." SET
														article_cat_status='". $article_cat_status ."'
		WHERE article_cat_id='". $article_cat_id ."'");

		redirect(FUSION_SELF . $aidlink."&status=". ($article_cat_status ? "active" : "deactive") ."&id=". $article_cat_id, false);

	} else if ($_GET['action']=="del") {

		$article_count = dbcount("(article_id)", DB_ARTICLES, "article_cat='". (INT)$_GET['id'] ."'");
		$article_cats_count = dbcount("(article_cat_id)", DB_ARTICLE_CATS, "article_cat_parent='". (INT)$_GET['id'] ."'");
		if ($article_count>0 || $article_cats_count>0) {

			redirect(FUSION_SELF . $aidlink ."&status=nodel&id=". (INT)$_GET['id']);

		} else {

			$result = dbquery("SELECT article_cat_image FROM ". DB_ARTICLE_CATS ." WHERE article_cat_id='". (INT)$_GET['id'] ."'");
			if (dbrows($result)) {
				$data = dbarray($result);
				if (!empty($data['article_cat_image']) && file_exists(IMAGES_AC . $data['article_cat_image'])) { unlink(IMAGES_AC . $data['article_cat_image']); }
				if (!empty($data['article_cat_image']) && file_exists(IMAGES_AC_T . $data['article_cat_image'])) { unlink(IMAGES_AC_T . $data['article_cat_image']); }
			} // Tesli Yest DB query

			$result = dbquery("DELETE FROM ". DB_ARTICLE_CATS ." WHERE article_cat_id='". (INT)$_GET['id'] ."'");

			$viewcompanent = viewcompanent("article_cats", "name");
			$seourl_component = $viewcompanent['components_id'];
			$seourl_filedid = (INT)$_GET['id'];

			$result = dbquery("DELETE FROM ". DB_SEOURL ." WHERE seourl_component='". $seourl_component ."' AND seourl_filedid='". $seourl_filedid ."'");


			///////////////// POSITIONS /////////////////
			$position=1;
			$result_position = dbquery("SELECT article_cat_id FROM ". DB_ARTICLE_CATS ." ORDER BY `article_cat_order`");
			if (dbrows($result_position)) {
				while ($data_position = dbarray($result_position)) {
					$position++;
					dbquery("UPDATE ". DB_ARTICLE_CATS ." SET article_cat_order='". $position ."' WHERE article_cat_id='". $data_position['article_cat_id'] ."'");
				} // db whille
			} // db query
			///////////////// POSITIONS /////////////////


			redirect(FUSION_SELF . $aidlink ."&status=del&id=". (INT)$_GET['id']);

		} // Yesli yest article

	} else if ($_GET['action']=="add" || $_GET['action']=="edit") {

		if (isset($_POST['save'])) {

			$article_cat_title = stripinput($_POST['article_cat_title']);
			$article_cat_description = stripinput($_POST['article_cat_description']);
			$article_cat_keywords = stripinput($_POST['article_cat_keywords']);
			$article_cat_name = stripinput($_POST['article_cat_name']);
			$article_cat_h1 = stripinput($_POST['article_cat_h1']);
			$article_cat_content = stripinput($_POST['article_cat_content']);

			$article_cat_image = $_FILES['article_cat_image']['name'];
			$article_cat_imagetmp  = $_FILES['article_cat_image']['tmp_name'];
			$article_cat_imagesize = $_FILES['article_cat_image']['size'];
			$article_cat_imagetype = $_FILES['article_cat_image']['type'];

			$article_cat_image_yest = stripinput($_POST['article_cat_image_yest']);
			$article_cat_image_del = (INT)$_POST['article_cat_image_del'];

			$article_cat_parent = stripinput($_POST['article_cat_parent']);
			$article_cat_access = (INT)$_POST['article_cat_access'];
			$article_cat_status = (INT)$_POST['article_cat_status'];

			// if ($_GET['action']=="edit") {
			// 	$article_cat_order = (INT)$_POST['article_cat_order'];
			// } else {
			// 	$result_cat_order = dbquery(
			// 		"SELECT 
			// 									article_cat_id,
			// 									article_cat_order
			// 		FROM ". DB_ARTICLE_CATS ."
			// 		ORDER BY article_cat_order DESC
			// 		LIMIT 1"
			// 	);
			// 	if (dbrows($result_cat_order)) {
			// 		$data_cat_order = dbarray($result_cat_order);
			// 		$article_cat_order = $data_cat_order['article_cat_order']+1;
			// 	} else {
			// 		$article_cat_order = 1;
			// 	}
			// }

			$article_cat_date = FUSION_TODAY;

			$article_cat_alias = stripinput($_POST['article_cat_alias']);

		} else if ($_GET['action']=="edit") {

			$viewcompanent = viewcompanent("article_cats", "name");
			$seourl_component = $viewcompanent['components_id'];

			$result = dbquery(
				"SELECT 
											article_cat_id,
											article_cat_title,
											article_cat_description,
											article_cat_keywords,
											article_cat_name,
											article_cat_h1,
											article_cat_content,
											article_cat_image,
											article_cat_parent,
											article_cat_access,
											article_cat_status,
											seourl_url
				FROM ". DB_ARTICLE_CATS ."
				LEFT JOIN ". DB_SEOURL ." ON seourl_filedid=article_cat_id AND seourl_component=". $seourl_component ."
				WHERE article_cat_id='". (INT)$_GET['id'] ."' LIMIT 1"
			);
			if (dbrows($result)) {
				$data = dbarray($result);

				$article_cat_title = unserialize($data['article_cat_title']);
				$article_cat_description = unserialize($data['article_cat_description']);
				$article_cat_keywords = unserialize($data['article_cat_keywords']);
				$article_cat_name = unserialize($data['article_cat_name']);
				$article_cat_h1 = unserialize($data['article_cat_h1']);
				$article_cat_content = unserialize($data['article_cat_content']);
				$article_cat_image = $data['article_cat_image'];
				$article_cat_parent =  $data['article_cat_parent'];
				$article_cat_access = $data['article_cat_access'];
				$article_cat_status = $data['article_cat_status'];

				$article_cat_alias = $data['seourl_url'];

			} else {
				redirect(FUSION_SELF . $aidlink);
			}

		} else {

				$article_cat_title = "";
				$article_cat_description = "";
				$article_cat_keywords = "";
				$article_cat_name = "";
				$article_cat_h1 = "";
				$article_cat_content = "";
				$article_cat_image = "";
				$article_cat_parent = 0;
				$article_cat_access = 0;
				$article_cat_status = 1;
				$article_cat_alias = "";

		} // Yesli POST


		########## SEO URL OPARATIONS ##########
		if ($settings['seourl_prefix']) {
			$seourl_prefix_strlen =  strlen($settings['seourl_prefix']);
			$seourl_prefix_alias = substr($article_cat_alias, -$seourl_prefix_strlen);
			if ($seourl_prefix_alias==$settings['seourl_prefix']) {
				$article_cat_alias = substr($article_cat_alias, 0, -$seourl_prefix_strlen);
			}
		} // yesli yest seourl_prefix

		if ($article_cat_parent!=0) {
			$viewcompanent = viewcompanent("article_cats", "name");
			$seourl_component = $viewcompanent['components_id'];

			$viewseourl = viewseourl($article_cat_parent, "filedid", $seourl_component);
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

			$article_cat_alias = str_replace($parent_url ."/", "", $article_cat_alias);
		} else {
			$article_cat_alias = str_replace($settings['companent_root_url'], "", $article_cat_alias);
		}
		########## //SEO URL OPARATIONS ##########



		if (isset($_POST['save'])) {


			foreach ($languages as $key => $value) {
				if (empty($article_cat_name[$value['languages_short']])) { $error .= "<div class='error'>". $locale['error_001'] ." - ". $value['languages_name'] ."</div>\n"; }
			}
			// foreach ($languages as $key => $value) {
			// 	if (empty($article_cat_content[$value['languages_short']])) { $error .= "<div class='error'>". $locale['error_002'] ." - ". $value['languages_name'] ."</div>\n"; }
			// }

			if ($article_cat_image) {
				// if (strlen($article_cat_image) > 255) { $error .= "<div class='error'>". $locale['error_050'] ."</div>\n"; $article_cat_image = ""; }
				// проверяем расширение файла
				$article_cat_image_ext = strtolower(substr($article_cat_image, 1 + strrpos($article_cat_image, ".")));
				if (!in_array($article_cat_image_ext, $photo_valid_types)) { $error .= "<div class='error'>". $locale['error_051'] ."</div>\n"; $article_cat_image = ""; }
				// 1. считаем кол-во точек в выражении - если большей одной - СВОБОДЕН!
				$article_cat_image_findtochka = substr_count($article_cat_image, ".");
				if ($article_cat_image_findtochka>1) { $error .= "<div class='error'>". $locale['error_052'] ."</div>\n"; $article_cat_image = ""; }
				// 2. если в имени есть .php, .html, .htm - свободен! 
				if (preg_match("/\.php/i",$article_cat_image))  { $error .= "<div class='error'>". $locale['error_053'] ."</div>\n"; $article_cat_image = ""; }
				if (preg_match("/\.html/i",$article_cat_image)) { $error .= "<div class='error'>". $locale['error_054'] ."</div>\n"; $article_cat_image = ""; }
				if (preg_match("/\.htm/i",$article_cat_image))  { $error .= "<div class='error'>". $locale['error_055'] ."</div>\n"; $article_cat_image = ""; }
				// 5. Размер фото
				$article_cat_image_fotosize = round($article_cat_imagesize/10.24)/100; // размер ЗАГРУЖАЕМОГО ФОТО в Кб.
				$article_cat_image_fotomax = round($settings['article_cat_photo_max_b']/10.24)/100; // максимальный размер фото в Кб.
				if ($article_cat_image_fotosize>$article_cat_image_fotomax) { $error .= "<div class='error'>". $locale['error_056'] ."<br />". $locale['error_057'] ." ". $article_cat_image_fotosize ." Kb<br />". $locale['error_058'] ." ". $article_cat_image_fotomax ." Kb</div>\n"; $article_cat_image = ""; }
				// // 6. "Габариты" фото > $maxwidth х $maxheight - ДО свиданья! :-)
				$article_cat_image_getsize = getimagesize($article_cat_imagetmp);
				if ($article_cat_image_getsize[0]>$settings['article_cat_photo_max_w'] or $article_cat_image_getsize[1]>$settings['article_cat_photo_max_h']) { $error .= "<div class='error'>". $locale['error_059'] ."<br />". $locale['error_060'] ." ". $article_cat_image_getsize[0] ."x". $article_cat_image_getsize[1] ."<br />". $locale['error_061'] ." ". $settings['article_cat_photo_max_w'] ."x". $settings['article_cat_photo_max_h'] ."</div>\n"; $article_cat_image = ""; }
				// // if ($article_cat_image_getsize[0]<$article_cat_image_getsize[1]) { $error .= "<div class='error'>". $locale['error_062'] ."</div>\n"; $article_cat_image = ""; }
				// // Foto 0 Kb
				// if ($article_cat_imagesize<0 and $article_cat_imagesize>$settings['article_cat_size']) { $error .= "<div class='error'>". $locale['error_063'] ."</div>\n"; $article_cat_image = ""; }
			}


			if (isset($error)) {

				echo "	<div class='admin-message'>\n";
				echo "		<div id='close-message'>". $error ."</div>\n";
				echo "	</div>\n";

				$article_image = "";

			} else {


				if ($article_cat_image) {

					$article_cat_image_ext = strrchr($article_cat_image, ".");
					$article_cat_image = FUSION_TODAY;
					$img_rand_key = mt_rand(100, 999);

					if ($article_cat_image_ext == ".gif") {
						$article_cat_image_filetype = 1;
					} elseif ($article_cat_image_ext == ".jpg") {
						$article_cat_image_filetype = 2;
					} elseif ($article_cat_image_ext == ".png") {
						$article_cat_image_filetype = 3;
					} else {
						$article_cat_image_filetype = false; 
					}

					$article_cat_image = image_exists(IMAGES_AC, $article_cat_image . $img_rand_key . $article_cat_image_ext);

					move_uploaded_file($article_cat_imagetmp, IMAGES_AC . $article_cat_image);
					// if (function_exists("chmod")) { chmod(IMAGES_AC . $article_cat_image, 0644); }

					$article_cat_image_size = getimagesize(IMAGES_AC . $article_cat_image);
					$article_cat_image_width = $article_cat_image_size[0];
					$article_cat_image_height = $article_cat_image_size[1];

					if ($settings['article_cat_thumb_ratio']==0) {
						createthumbnail($article_cat_image_filetype, IMAGES_AC . $article_cat_image, IMAGES_AC_T . $article_cat_image, ($article_cat_image_width<$settings['article_cat_thumb_w'] ? $article_cat_image_width : $settings['article_cat_thumb_w']), ($article_cat_image_height<$settings['article_cat_thumb_h'] ? $article_cat_image_height : $settings['article_cat_thumb_h']));
					} else {
						createsquarethumbnail($article_cat_image_filetype, IMAGES_AC . $article_cat_image, IMAGES_AC_T . $article_cat_image, ($article_cat_image_width<$settings['article_cat_thumb_w'] ? $article_cat_image_width : $settings['article_cat_thumb_w']));
					}
					createthumbnail($article_cat_image_filetype, IMAGES_AC . $article_cat_image, IMAGES_AC . $article_cat_image, ($article_cat_image_width<$settings['article_cat_photo_w'] ? $article_cat_image_width : $settings['article_cat_photo_w']));

				} else {
					$article_cat_image = $article_cat_image_yest;
				}



				if ($_GET['action']=="edit") {

					if ($article_cat_image_del) {
						if ($article_cat_image_yest && file_exists(IMAGES_AC . $article_cat_image_yest)) { unlink(IMAGES_AC . $article_cat_image_yest); }
						if ($article_cat_image_yest && file_exists(IMAGES_AC_T . $article_cat_image_yest)) { unlink(IMAGES_AC_T . $article_cat_image_yest); }
						$article_cat_image = "";
					}

					$result = dbquery(
						"UPDATE ". DB_ARTICLE_CATS ." SET
															article_cat_title='". serialize($article_cat_title) ."',
															article_cat_description='". serialize($article_cat_description) ."',
															article_cat_keywords='". serialize($article_cat_keywords) ."',
															article_cat_name='". serialize($article_cat_name) ."',
															article_cat_h1='". serialize($article_cat_h1) ."',
															article_cat_content='". serialize($article_cat_content) ."',
															article_cat_image='". $article_cat_image ."',
															article_cat_parent='". $article_cat_parent ."',
															article_cat_access='". $article_cat_access ."',
															article_cat_status='". $article_cat_status ."'
						WHERE article_cat_id='". (INT)$_GET['id'] ."'"
					);
					$article_cat_id = (INT)$_GET['id'];

				} else {

					$result = dbquery(
						"INSERT INTO ". DB_ARTICLE_CATS ." (
															article_cat_title,
															article_cat_description,
															article_cat_keywords,
															article_cat_name,
															article_cat_h1,
															article_cat_content,
															article_cat_image,
															article_cat_parent,
															article_cat_access,
															article_cat_status
						) VALUES (
															'". serialize($article_cat_title) ."',
															'". serialize($article_cat_description) ."',
															'". serialize($article_cat_keywords) ."',
															'". serialize($article_cat_name) ."',
															'". serialize($article_cat_h1) ."',
															'". serialize($article_cat_content) ."',
															'". $article_cat_image ."',
															'". $article_cat_parent ."',
															'". $article_cat_access ."',
															'". $article_cat_status ."'
						)"
					);
					// $article_cat_id = mysql_insert_id();
					$article_cat_id = _DB::$linkes->insert_id;;

				} // UPDATE ILI INSERT


				$viewcompanent = viewcompanent("article_cats", "name");
				$seourl_component = $viewcompanent['components_id'];

				// $article_cat_alias = str_replace($settings['companent_root_url'], "", $article_cat_alias);
				if (empty($article_cat_alias)) {
					$article_cat_alias = autocrateseourls($article_cat_name[LOCALESHORT]);
				} else {
					$article_cat_alias = autocrateseourls($article_cat_alias);
				}

				$seourl_url = (empty($article_cat_alias) ? "article_cat_". $article_cat_id . $settings['seourl_prefix'] : $article_cat_alias . $settings['seourl_prefix']);
				$seourl_filedid = $article_cat_id;

				$viewseourl = viewseourl($seourl_url, "url");

				if ($viewseourl['seourl_url']==$seourl_url) {
					if (($viewseourl['seourl_filedid']==$seourl_filedid) && ($viewseourl['seourl_component']==$seourl_component)) {
						$seourl_url = $seourl_url;
					} else {
						$seourl_url = "article_cat_". $article_cat_id . $settings['seourl_prefix'];
					}
				}  // Yesli URL YEst


				if ($article_cat_parent!=0) {
					$seourl_url = $parent_url ."/". $seourl_url;
				} else {
					$seourl_url = $settings['companent_root_url'] . $seourl_url;
				}
				$article_cat_alias = $seourl_url;


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
					dbquery("UPDATE ". DB_ARTICLE_CATS ." SET article_cat_order='". $position ."' WHERE article_cat_id='". $article_cat_id ."'");
					$result_position = dbquery("SELECT article_cat_id FROM ". DB_ARTICLE_CATS ." WHERE article_cat_id!='". $article_cat_id ."' ORDER BY `article_cat_order`");
					if (dbrows($result_position)) {
						while ($data_position = dbarray($result_position)) {
							$position++;
							dbquery("UPDATE ". DB_ARTICLE_CATS ." SET article_cat_order='". $position ."' WHERE article_cat_id='". $data_position['article_cat_id'] ."'");
						} // db whille
					} // db query
				} // Yesli action add
				///////////////// POSITIONS /////////////////


				////////// redirect
				if ($_GET['action']=="edit") {
					redirect(FUSION_SELF . $aidlink ."&status=edit&id=". $article_cat_id ."&url=". $article_cat_alias, false);
				} else {
					redirect(FUSION_SELF . $aidlink ."&status=add&id=". $article_cat_id ."&url=". $article_cat_alias, false);
				} ////////// redirect

			} // Yesli Error

		} // Yesli POST save


	$result_parent = dbquery(
							"SELECT
												article_cat_id,
												article_cat_name
							FROM ". DB_ARTICLE_CATS ."
							WHERE article_cat_parent=0
							ORDER BY article_cat_name DESC");
	$parent_opts = "<option value='0'". ($article_cat_parent==0 ? " selected='selected'" : "") .">". $locale['513'] ."</option>\n";
	while ($data_parent = dbarray($result_parent)) {
		$parent_opts_article_cat_name = unserialize($data_parent['article_cat_name']);
		$parent_opts .= "<option value='". $data_parent['article_cat_id'] ."'". ($article_cat_parent==$data_parent['article_cat_id'] ? " selected='selected'" : "") .">". $parent_opts_article_cat_name[LOCALESHORT] ."</option>\n";
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
		<input type="hidden" name="article_cat_status" id="article_cat_status" value="<?php echo $article_cat_status; ?>" />
		<input type="hidden" name="article_cat_order" id="article_cat_order" value="<?php echo $article_cat_order; ?>" />
		<table class='form_table'>
			<tr>
				<td colspan="2"><a href="#" id="seo_tr_button">SEO</a></td>
			</tr>
			<tr class="seo_tr">
				<td colspan="2" class="seo_tr">
					<label for="article_cat_title_<?php echo LOCALESHORT; ?>"><?php echo $locale['501']; ?></label>
					<?php foreach ($languages as $key => $value) { ?>
					<?php if ($languages_count>1) { ?><span class="local_name lang_<?php echo $value['languages_short']; ?>" style="background-image: url(<?php echo IMAGES ."flags/". $value['languages_short'] .".png"; ?>)"><?php echo $value['languages_name']; ?></span><?php } ?>
					<input type="text" name="article_cat_title[<?php echo $value['languages_short']; ?>]" id="article_cat_title_<?php echo $value['languages_short']; ?>" value="<?php echo $article_cat_title[$value['languages_short']]; ?>" class="textbox" style="width:100%;" /><br />
					<?php } // foreach languages ?>
				</td>
			</tr>
			<tr class="seo_tr">
				<td colspan="2" class="seo_tr">
					<label for="article_cat_description_<?php echo LOCALESHORT; ?>"><?php echo $locale['502']; ?></label>
					<?php foreach ($languages as $key => $value) { ?>
					<?php if ($languages_count>1) { ?><span class="local_name lang_<?php echo $value['languages_short']; ?>" style="background-image: url(<?php echo IMAGES ."flags/". $value['languages_short'] .".png"; ?>)"><?php echo $value['languages_name']; ?></span><?php } ?>
					<input type="text" name="article_cat_description[<?php echo $value['languages_short']; ?>]" id="article_cat_description_<?php echo $value['languages_short']; ?>" value="<?php echo $article_cat_description[$value['languages_short']]; ?>" class="textbox" style="width:100%;" /><br />
					<?php } // foreach languages ?>
				</td>
			</tr>
			<tr class="seo_tr">
				<td colspan="2" class="seo_tr">
					<label for="article_cat_keywords_<?php echo LOCALESHORT; ?>"><?php echo $locale['503']; ?></label>
					<?php foreach ($languages as $key => $value) { ?>
					<?php if ($languages_count>1) { ?><span class="local_name lang_<?php echo $value['languages_short']; ?>" style="background-image: url(<?php echo IMAGES ."flags/". $value['languages_short'] .".png"; ?>)"><?php echo $value['languages_name']; ?></span><?php } ?>
					<input type="text" name="article_cat_keywords[<?php echo $value['languages_short']; ?>]" id="article_cat_keywords_<?php echo $value['languages_short']; ?>" value="<?php echo $article_cat_keywords[$value['languages_short']]; ?>" class="textbox" style="width:100%;" /><br />
					<?php } // foreach languages ?>
				</td>
			</tr>
			<tr class="seo_tr">
				<td colspan="2" class="seo_tr">
					<label for="article_cat_h1_<?php echo LOCALESHORT; ?>"><?php echo $locale['505']; ?></label>
					<?php foreach ($languages as $key => $value) { ?>
					<?php if ($languages_count>1) { ?><span class="local_name lang_<?php echo $value['languages_short']; ?>" style="background-image: url(<?php echo IMAGES ."flags/". $value['languages_short'] .".png"; ?>)"><?php echo $value['languages_name']; ?></span><?php } ?>
					<input type="text" name="article_cat_h1[<?php echo $value['languages_short']; ?>]" id="article_cat_h1_<?php echo $value['languages_short']; ?>" value="<?php echo $article_cat_h1[$value['languages_short']]; ?>" class="textbox" style="width:100%;" /><br />
					<?php } // foreach languages ?>
				</td>
			</tr>
			<tr class="seo_tr">
				<td colspan="2">
					<label for="article_cat_alias"><?php echo $locale['506']; ?></label>
					<input readonly type="text" name="article_cat_siteurl" id="article_cat_siteurl" value="<?php echo $settings['siteurl'] . ($parent_url ? $parent_url ."/" : $settings['companent_root_url']); ?>" class="textbox" style="width:25%;" />
					<input type="text" name="article_cat_alias" id="article_cat_alias" value="<?php echo $article_cat_alias; ?>" class="textbox" style="width:65%;" />
					<?php if ($settings['seourl_prefix']) { ?><input readonly type="text" name="seourl_prefix" id="seourl_prefix" value="<?php echo $settings['seourl_prefix']; ?>" class="textbox" style="width:5%;" /><?php } ?>
				</td>
			</tr>
			<tr class="seo_tr">
				<td colspan="2"></td>
			</tr>


			<tr>
				<td colspan="2">
					<label for="article_cat_name_<?php echo LOCALESHORT; ?>"><?php echo $locale['504']; ?> <span>*</span></label>
					<?php foreach ($languages as $key => $value) { ?>
					<?php if ($languages_count>1) { ?><span class="local_name lang_<?php echo $value['languages_short']; ?>" style="background-image: url(<?php echo IMAGES ."flags/". $value['languages_short'] .".png"; ?>)"><?php echo $value['languages_name']; ?></span><?php } ?>
					<input type="text" name="article_cat_name[<?php echo $value['languages_short']; ?>]" id="article_cat_name_<?php echo $value['languages_short']; ?>" value="<?php echo $article_cat_name[$value['languages_short']]; ?>" class="textbox" style="width:100%;" /><br />
					<?php } // foreach languages ?>
				</td>
			</tr>

			<tr>
				<td colspan="2">
					<label for="article_cat_image"><?php echo $locale['507']; ?></label>
					<?php if ($article_cat_image && file_exists(IMAGES_AC_T . $article_cat_image)) { ?>
					<label>
						<img src="<?php echo IMAGES_AC_T . $article_cat_image; ?>" alt="" style="height:100px;" /><br />
						<input type="checkbox" name="article_cat_image_del" value="1" /> <?php echo $locale['507_b']; ?>
						<input type="hidden" name="article_cat_image_yest" value="<?php echo $article_cat_image; ?>" />
					</label>
					<?php } else { ?>
					<input type="file" name="article_cat_image" id="article_cat_image" class="filebox" style="width:100%;" accept="image/*" />
					<div id="article_image_preview"></div>
					<?php echo sprintf($locale['507_a'], parsebytesize($settings['article_cat_photo_max_b'], 3)); ?>
					<?php }	?>
				</td>
			</tr>

			<tr>
				<td colspan="2">
					<label for="article_cat_content_<?php echo LOCALESHORT; ?>"><?php echo $locale['509']; ?></label>
					<?php foreach ($languages as $key => $value) { ?>
					<?php if ($languages_count>1) { ?><span class="local_name lang_<?php echo $value['languages_short']; ?>" style="background-image: url(<?php echo IMAGES ."flags/". $value['languages_short'] .".png"; ?>)"><?php echo $value['languages_name']; ?></span><?php } ?>
					<textarea id="editor<?php echo $value['languages_id']; ?>" name="article_cat_content[<?php echo $value['languages_short']; ?>]" id="article_cat_content<?php echo $value['languages_short']; ?>" class="textareabox" cols="95" rows="15" style="width:100%"><?php echo $article_cat_content[$value['languages_short']]; ?></textarea><br />
					<?php } // foreach languages ?>
				</td>
			</tr>
			<?php if (!$settings['tinymce_enabled']) { ?>
			<tr>
				<td colspan="2">
					<?php echo display_html("inputform", "article_cat_content", true, true, true, IMAGES_N); ?>
				</td>
			</tr>
			<?php } ?>
			<tr>
				<td>
					<label for="article_cat_access"><?php echo $locale['511']; ?></label>
					<select name="article_cat_access" id="article_cat_access" class="selectbox" style="width:200px;">
						<?php echo $access_opts; ?>
					</select>
				</td>
				<td>
					<label for="article_cat_parent"><?php echo $locale['512']; ?></label>
					<select name="article_cat_parent" id="article_cat_access" class="selectbox" style="width:200px;">
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
			$("#article_image").change(function () {
				if (typeof (FileReader) != "undefined") {
					var dvPreview = $("#article_image_preview");
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


add_to_footer("<script type='text/javascript' src='". INCLUDES ."jquery/jquery-ui.js'></script>");
add_to_footer("<script type='text/javascript'>
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



	$result_all = dbquery("SELECT 
											article_cat_parent
						FROM ". DB_ARTICLE_CATS ."
						WHERE article_cat_parent!=0");
	if (dbrows($result_all)) { $j_all = 0; $result_all_arr = array();
		while ($data_all = dbarray($result_all)) { $j_all++;
			if (!in_array($data_all['article_cat_parent'], $result_all_arr)) {
				$result_all_arr[$j_all] = $data_all['article_cat_parent'];
			}
		} // db while
	} // db query
	// echo "<pre>";
	// print_r($result_all_arr);
	// echo "</pre>";
	// echo "<hr>";

	$viewcompanent = viewcompanent("article_cats", "name");
	$seourl_component = $viewcompanent['components_id'];


	$result_alter = dbquery("ALTER TABLE `". DB_ARTICLE_CATS ."` ORDER BY `article_cat_order` ASC");

	$result = dbquery("SELECT 
								article_cat_id,
								article_cat_name,
								article_cat_order,
								article_cat_status,
								seourl_url
		FROM ". DB_ARTICLE_CATS ."
		LEFT JOIN ". DB_SEOURL ." ON seourl_filedid=article_cat_id AND seourl_component=". $seourl_component ."
		WHERE article_cat_parent=0
		LIMIT ". (INT)$_GET['rowstart'] .", ". $settings['article_cat_per_page']);

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
			while ($data = dbarray($result)) {
				$article_cat_name = unserialize($data['article_cat_name']);
	?>
			<tr id="listItem_<?php echo $data['article_cat_id']; ?>">
				<td class="list"><img src="<?php echo IMAGES; ?>arrow.png" alt="<?php echo $locale['410']; ?>" class="handle" /></td>
				<td class="name">
					<?php if (in_array($data['article_cat_id'], $result_all_arr)) { echo "<a href='#' id='views_parents". $data['article_cat_id'] ."' class='views_parents' title='Покозать под категории'>+</a>\n"; } ?>
					<a href="<?php echo FUSION_SELF . $aidlink; ?>&action=edit&id=<?php echo $data['article_cat_id']; ?>" title="<?php echo $article_cat_name[LOCALESHORT]; ?>"><?php echo $article_cat_name[LOCALESHORT]; ?></a>
				</td>
				<td class="status">
					<a href="<?php echo FUSION_SELF . $aidlink; ?>&action=status&id=<?php echo $data['article_cat_id']; ?>&status=<?php echo $data['article_cat_status']; ?>" title="<?php echo ($data['article_cat_status'] ? $locale['411'] : $locale['412']); ?>"><img src="<?php echo IMAGES; ?>status/status_<?php echo $data['article_cat_status']; ?>.png" alt="<?php echo ($data['article_cat_status'] ? $locale['411'] : $locale['412']); ?>"></a>
				</td>
				<td class="num"><?php echo $data['article_cat_order']; ?></td>
				<td class="links">
					<a href="<?php echo BASEDIR . $data['seourl_url']; ?>" target="_blank" title="<?php echo $locale['413']; ?>"><img src="<?php echo IMAGES; ?>view.png" alt="<?php echo $locale['413']; ?>"></a>
					<a href="<?php echo FUSION_SELF . $aidlink; ?>&action=edit&id=<?php echo $data['article_cat_id']; ?>" title="<?php echo $locale['414']; ?>"><img src="<?php echo IMAGES; ?>edit.png" alt="<?php echo $locale['414']; ?>"></a>
					<a href="<?php echo FUSION_SELF . $aidlink; ?>&action=del&id=<?php echo $data['article_cat_id']; ?>" title="<?php echo $locale['415']; ?>" onclick="return DeleteOk();"><img src="<?php echo IMAGES; ?>delete.png" alt="<?php echo $locale['415']; ?>"></a>
				</td>
			</tr>
	<?php
				if (in_array($data['article_cat_id'], $result_all_arr)) {
	?>
		</tbody>
		<tbody id="parents_list_bottom<?php echo $data['article_cat_id']; ?>" class="parents_list_bottom" style="display:none;">
			<tr>
				<td colspan="5"></td>
			</tr>
		</tbody>
		<tbody>
	<?php
				} // in_array

			} // db whille
	?>
	<script type="text/javascript">
		<!--
		$(document).ready(function() {
			$( '.spisok_stranic .views_parents' ).click(function() {
				var views_parents_id = $( this ).attr( 'id' );
				views_parents_id = views_parents_id.replace('views_parents', '');

				if ( $( this ).hasClass( "active_parents" ) ) {
					$( this ).removeClass( 'active_parents' );
					$( this ).text( '+' );
					$( '.spisok_stranic #parents_list_bottom'+ views_parents_id ).css( 'display', 'none' );

					html = '';
					html += '<tr>';
					html += '	<td colspan=\'5\'></td>';
					html += '</tr>';
					$( '.spisok_stranic #parents_list_bottom'+ views_parents_id +'' ).html( html );
				} else {
					$( this ).addClass( 'active_parents' );
					$( this ).text( '-' );
					$( '.spisok_stranic #parents_list_bottom'+ views_parents_id ).removeAttr( 'style' );

					$( '.spisok_stranic #parents_list_bottom'+ views_parents_id +' td' ).html('<img src=\'/<?php echo IMAGES; ?>ajax-loader.gif\' alt=\'\' class=\'ajax-loader\' />');
					$.ajax({
						type: 'POST',
						url: '<?php echo INCLUDES; ?>Json/article_cats-parents.php',
						dataType: 'json',
						data: {parent: views_parents_id},
						success: function(data){
							var html = '';
							$.each(data,function(inx, item) {

								html += '<tr id=\'listItem_'+ item.article_cat_id +'\'>';
								html += '	<td class=\'list\'><img src=\'<?php echo IMAGES; ?>arrow.png\' alt=\'<?php echo $locale['410']; ?>\' class=\'handle\' /></td>';
								html += '	<td class=\'name\'>';
								html += '		<a href=\'<?php echo FUSION_SELF . $aidlink; ?>&action=edit&id='+ item.article_cat_id +'\' title=\''+ item.article_cat_name +'\'>└ <i>'+ item.article_cat_name +'</i></a>';
								html += '	</td>';
								html += '	<td class=\'status\'>';
								if (item.article_cat_status>0) {
									html += '		<a href=\'<?php echo FUSION_SELF . $aidlink; ?>&action=status&id='+ item.article_cat_id +'&status='+ item.article_cat_status +'\' title=\'<?php echo $locale['411']; ?>\'><img src=\'<?php echo IMAGES; ?>status/status_'+ item.article_cat_status +'.png\' alt=\'<?php echo $locale['411']; ?>\'></a>';
								} else {
									html += '		<a href=\'<?php echo FUSION_SELF . $aidlink; ?>&action=status&id='+ item.article_cat_id +'&status='+ item.article_cat_status +'\' title=\'<?php echo $locale['412']; ?>\'><img src=\'<?php echo IMAGES; ?>status/status_'+ item.article_cat_status +'.png\' alt=\'<?php echo $locale['412']; ?>\'></a>';
								}
								html += '	</td>';
								html += '	<td class=\'num\'>'+ item.article_cat_order +'</td>';
								html += '	<td class=\'links\'>';
								html += '		<a href=\'<?php echo BASEDIR; ?>'+ item.seourl_url +'\' target=\'_blank\' title=\'<?php echo $locale['413']; ?>\'><img src=\'<?php echo IMAGES; ?>view.png\' alt=\'<?php echo $locale['413']; ?>\'></a>';
								html += '		<a href=\'<?php echo FUSION_SELF . $aidlink; ?>&action=edit&id='+ item.article_cat_id +'\' title=\'<?php echo $locale['414']; ?>\'><img src=\'<?php echo IMAGES; ?>edit.png\' alt=\'<?php echo $locale['414']; ?>\'></a>';
								html += '		<a href=\'<?php echo FUSION_SELF . $aidlink; ?>&action=del&id='+ item.article_cat_id +'\' title=\'<?php echo $locale['415']; ?>\' onclick=\'return DeleteOk();\'><img src=\'<?php echo IMAGES; ?>delete.png\' alt=\'<?php echo $locale['415']; ?>\'></a>';
								html += '	</td>';
								html += '</tr>';

							});
							html += '<tr>';
							html += '	<td colspan=\'5\'></td>';
							html += '</tr>';

							$( '.spisok_stranic #parents_list_bottom'+ views_parents_id +'' ).html( html );
						}
					});

				}
				// console.log( views_parents_id );
			});
		});
		//-->
	</script>
	<?php
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
<?php 
	$rows = dbcount("(article_cat_id)", DB_ARTICLE_CATS);
	if ($rows > $settings['article_cat_per_page']) { echo makepagenav((INT)$_GET['rowstart'], $settings['article_cat_per_page'], $rows, 3, ADMIN . FUSION_SELF . $aidlink ."&amp;") ."\n"; }
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

		// echo navigation((INT)$_GET['page'], $settings['article_cat_per_page'], "article_cat_id", DB_ARTICLE_CATS, "article_cat_parent=0");

	} // action


	if ($_GET['action']!="order") {
		closetable();

		require_once THEMES."templates/footer.php";
	} // Yesli action ne order
?>