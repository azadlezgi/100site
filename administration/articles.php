<?php
 
	require_once "../includes/maincore.php";

	if (!checkrights("A") || !defined("iAUTH") || !isset($_GET['aid']) || $_GET['aid'] != iAUTH) { redirect("../index.php"); }

	include LOCALE . LOCALESET ."admin/articles.php";


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
					dbquery("UPDATE ". DB_ARTICLES ." SET article_order='". ($position+1) ."' WHERE article_id='". $item ."'");
				}
			}

			header("Content-Type: text/html; charset=". $locale['charset'] ."\n");
			echo "<div id='close-message'>\n";
			echo "	<div class='success'>". $locale['success_007'] ."</div>\n";
			echo "</div>\n";

		}


	} else if ($_GET['action']=="status") {

		$article_id = (INT)$_GET['id'];
		$article_status = (INT)$_GET['status'];
		$article_status = ($article_status ? 0 : 1);

		$result = dbquery("UPDATE ". DB_ARTICLES ." SET
														article_status='". $article_status ."'
		WHERE article_id='". $article_id ."'");

		redirect(FUSION_SELF . $aidlink."&status=". ($article_status ? "active" : "deactive") ."&id=". $article_id, false);

	} else if ($_GET['action']=="del") {

		$result = dbquery("SELECT article_image FROM ". DB_ARTICLES ." WHERE article_id='". (INT)$_GET['id'] ."'");
		if (dbrows($result)) {
				$data = dbarray($result);
			if (!empty($data['article_image']) && file_exists(IMAGES_C . $data['article_image'])) { unlink(IMAGES_C . $data['article_image']); }
			if (!empty($data['article_image']) && file_exists(IMAGES_C_T . $data['article_image'])) { unlink(IMAGES_C_T . $data['article_image']); }
		} // Tesli Yest DB query

		$result = dbquery("DELETE FROM ". DB_ARTICLES ." WHERE article_id='". (INT)$_GET['id'] ."'");
		$result = dbquery("DELETE FROM ". DB_COMMENTS ." WHERE comment_item_id='". (INT)$_GET['id'] ."' and comment_type='V'");
		$result = dbquery("DELETE FROM ". DB_RATINGS ." WHERE rating_item_id='". (INT)$_GET['id'] ."' and rating_type='V'");

		$viewcompanent = viewcompanent("articles", "name");
		$seourl_component = $viewcompanent['components_id'];
		$seourl_filedid = (INT)$_GET['id'];

		$result = dbquery("DELETE FROM ". DB_SEOURL ." WHERE seourl_component='". $seourl_component ."' AND seourl_filedid='". $seourl_filedid ."'");


		///////////////// POSITIONS /////////////////
		$position=1;
		$result_position = dbquery("SELECT article_id FROM ". DB_ARTICLES ." ORDER BY `article_order`");
		if (dbrows($result_position)) {
			while ($data_position = dbarray($result_position)) {
				$position++;
				dbquery("UPDATE ". DB_ARTICLES ." SET article_order='". $position ."' WHERE article_id='". $data_position['article_id'] ."'");
			} // db whille
		} // db query
		///////////////// POSITIONS /////////////////


		redirect(FUSION_SELF . $aidlink ."&status=del&id=". (INT)$_GET['id']);

	} else if ($_GET['action']=="add" || $_GET['action']=="edit") {

		if (isset($_POST['save'])) {

			$article_title = stripinput($_POST['article_title']);
			$article_description = stripinput($_POST['article_description']);
			$article_keywords = stripinput($_POST['article_keywords']);
			$article_name = stripinput($_POST['article_name']);
			$article_h1 = stripinput($_POST['article_h1']);
			$article_content = stripinput($_POST['article_content']);

			$article_image = $_FILES['article_image']['name'];
			$article_imagetmp  = $_FILES['article_image']['tmp_name'];
			$article_imagesize = $_FILES['article_image']['size'];
			$article_imagetype = $_FILES['article_image']['type'];

			$article_image_yest = stripinput($_POST['article_image_yest']);
			$article_image_del = (INT)$_POST['article_image_del'];

			$article_cat = stripinput($_POST['article_cat']);
			$article_access = (INT)$_POST['article_access'];
			$article_status = (INT)$_POST['article_status'];

			// if ($_GET['action']=="edit") {
			// 	$article_order = (INT)$_POST['article_order'];
			// } else {
			// 	$result_order = dbquery(
			// 		"SELECT 
			// 									article_id,
			// 									article_order
			// 		FROM ". DB_ARTICLES ."
			// 		ORDER BY article_order DESC
			// 		LIMIT 1"
			// 	);
			// 	if (dbrows($result_order)) {
			// 		$data_order = dbarray($result_order);
			// 		$article_order = $data_order['article_order']+1;
			// 	} else {
			// 		$article_order = 1;
			// 	}
			// }

			$article_date = FUSION_TODAY;
			$article_comments = (INT)$_POST['article_comments'];
			$article_ratings = (INT)$_POST['article_ratings'];

			$article_alias = stripinput($_POST['article_alias']);

		} else if ($_GET['action']=="edit") {

			$viewcompanent = viewcompanent("articles", "name");
			$seourl_component = $viewcompanent['components_id'];

			$result = dbquery(
				"SELECT 
											article_id,
											article_title,
											article_description,
											article_keywords,
											article_name,
											article_h1,
											article_content,
											article_image,
											article_cat,
											article_access,
											article_status,
											article_comments,
											article_ratings,
											seourl_url
				FROM ". DB_ARTICLES ."
				LEFT JOIN ". DB_SEOURL ." ON seourl_filedid=article_id AND seourl_component=". $seourl_component ."
				WHERE article_id='". (INT)$_GET['id'] ."' LIMIT 1"
			);
			if (dbrows($result)) {
				$data = dbarray($result);

				$article_title = unserialize($data['article_title']);
				$article_description = unserialize($data['article_description']);
				$article_keywords = unserialize($data['article_keywords']);
				$article_name = unserialize($data['article_name']);
				$article_h1 = unserialize($data['article_h1']);
				$article_content = unserialize($data['article_content']);
				$article_image = $data['article_image'];
				$article_cat =  $data['article_cat'];
				$article_access = $data['article_access'];
				$article_status = $data['article_status'];
				$article_comments = $data['article_comments'];
				$article_ratings = $data['article_ratings'];

				$article_alias = $data['seourl_url'];

			} else {
				redirect(FUSION_SELF . $aidlink);
			}

		} else {

				$article_title = "";
				$article_description = "";
				$article_keywords = "";
				$article_name = "";
				$article_h1 = "";
				$article_content = "";
				$article_image = "";
				$article_cat = 0;
				$article_access = 0;
				$article_status = 1;
				$article_comments = "";
				$article_ratings = "";
				$article_alias = "";

		} // Yesli POST


		########## SEO URL OPARATIONS ##########
		if ($settings['seourl_prefix']) {
			$seourl_prefix_strlen =  strlen($settings['seourl_prefix']);
			$seourl_prefix_alias = substr($article_alias, -$seourl_prefix_strlen);
			if ($seourl_prefix_alias==$settings['seourl_prefix']) {
				$article_alias = substr($article_alias, 0, -$seourl_prefix_strlen);
			}
		} // yesli yest seourl_prefix

		if ($article_cat!=0) {
			$viewcompanent = viewcompanent("article_cats", "name");
			$seourl_component = $viewcompanent['components_id'];

			$viewseourl = viewseourl($article_cat, "filedid", $seourl_component);
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

			$article_alias = str_replace($cat_url ."/", "", $article_alias);
		} else {
			$article_alias = str_replace($settings['companent_root_url'], "", $article_alias);
		}
		########## //SEO URL OPARATIONS ##########



		if (isset($_POST['save'])) {


			foreach ($languages as $key => $value) {
				if (empty($article_name[$value['languages_short']])) { $error .= "<div class='error'>". $locale['error_001'] ." - ". $value['languages_name'] ."</div>\n"; }
			}
			if (!$article_cat) { $error .= "<div class='error'>". $locale['error_002'] ."</div>\n"; }

			if ($article_image) {
				// if (strlen($article_image) > 255) { $error .= "<div class='error'>". $locale['error_050'] ."</div>\n"; $article_image = ""; }
				// проверяем расширение файла
				$article_image_ext = strtolower(substr($article_image, 1 + strrpos($article_image, ".")));
				if (!in_array($article_image_ext, $photo_valid_types)) { $error .= "<div class='error'>". $locale['error_051'] ."</div>\n"; $article_image = ""; }
				// 1. считаем кол-во точек в выражении - если большей одной - СВОБОДЕН!
				$article_image_findtochka = substr_count($article_image, ".");
				if ($article_image_findtochka>1) { $error .= "<div class='error'>". $locale['error_052'] ."</div>\n"; $article_image = ""; }
				// 2. если в имени есть .php, .html, .htm - свободен! 
				if (preg_match("/\.php/i",$article_image))  { $error .= "<div class='error'>". $locale['error_053'] ."</div>\n"; $article_image = ""; }
				if (preg_match("/\.html/i",$article_image)) { $error .= "<div class='error'>". $locale['error_054'] ."</div>\n"; $article_image = ""; }
				if (preg_match("/\.htm/i",$article_image))  { $error .= "<div class='error'>". $locale['error_055'] ."</div>\n"; $article_image = ""; }
				// 5. Размер фото
				$article_image_fotosize = round($article_imagesize/10.24)/100; // размер ЗАГРУЖАЕМОГО ФОТО в Кб.
				$article_image_fotomax = round($settings['articles_photo_max_b']/10.24)/100; // максимальный размер фото в Кб.
				if ($article_image_fotosize>$article_image_fotomax) { $error .= "<div class='error'>". $locale['error_056'] ."<br />". $locale['error_057'] ." ". $article_image_fotosize ." Kb<br />". $locale['error_058'] ." ". $article_image_fotomax ." Kb</div>\n"; $article_image = ""; }
				// // 6. "Габариты" фото > $maxwidth х $maxheight - ДО свиданья! :-)
				$article_image_getsize = getimagesize($article_imagetmp);
				if ($article_image_getsize[0]>$settings['articles_photo_max_w'] or $article_image_getsize[1]>$settings['articles_photo_max_h']) { $error .= "<div class='error'>". $locale['error_059'] ."<br />". $locale['error_060'] ." ". $article_image_getsize[0] ."x". $article_image_getsize[1] ."<br />". $locale['error_061'] ." ". $settings['articles_photo_max_w'] ."x". $settings['articles_photo_max_h'] ."</div>\n"; $article_image = ""; }
				// // if ($article_image_getsize[0]<$article_image_getsize[1]) { $error .= "<div class='error'>". $locale['error_062'] ."</div>\n"; $article_image = ""; }
				// // Foto 0 Kb
				// if ($article_imagesize<0 and $article_imagesize>$settings['article_size']) { $error .= "<div class='error'>". $locale['error_063'] ."</div>\n"; $article_image = ""; }
			}


			if (isset($error)) {

				echo "	<div class='admin-message'>\n";
				echo "		<div id='close-message'>". $error ."</div>\n";
				echo "	</div>\n";

			} else {


				if ($article_image) {

					$article_image_ext = strrchr($article_image, ".");
					$article_image = FUSION_TODAY;
					$img_rand_key = mt_rand(100, 999);

					if ($article_image_ext == ".gif") {
						$article_image_filetype = 1;
					} elseif ($article_image_ext == ".jpg") {
						$article_image_filetype = 2;
					} elseif ($article_image_ext == ".png") {
						$article_image_filetype = 3;
					} else {
						$article_image_filetype = false; 
					}

					$article_image = image_exists(IMAGES_C, $article_image . $img_rand_key . $article_image_ext);

					move_uploaded_file($article_imagetmp, IMAGES_C . $article_image);
					// if (function_exists("chmod")) { chmod(IMAGES_C . $article_image, 0644); }

					$article_image_size = getimagesize(IMAGES_C . $article_image);
					$article_image_width = $article_image_size[0];
					$article_image_height = $article_image_size[1];

					if ($settings['articles_thumb_ratio']==0) {
						createthumbnail($article_image_filetype, IMAGES_C . $article_image, IMAGES_C_T . $article_image, ($article_image_width<$settings['articles_thumb_w'] ? $article_image_width : $settings['articles_thumb_w']), ($article_image_height<$settings['articles_thumb_h'] ? $article_image_height : $settings['articles_thumb_h']));
					} else {
						createsquarethumbnail($article_image_filetype, IMAGES_C . $article_image, IMAGES_C_T . $article_image, ($article_image_width<$settings['articles_thumb_w'] ? $article_image_width : $settings['articles_thumb_w']));
					}
					createthumbnail($article_image_filetype, IMAGES_C . $article_image, IMAGES_C . $article_image, ($article_image_width<$settings['articles_photo_w'] ? $article_image_width : $settings['articles_photo_w']));

				} else {
					$article_image = $article_image_yest;
				}



				if ($_GET['action']=="edit") {

					if ($article_image_del) {
						if ($article_image_yest && file_exists(IMAGES_C . $article_image_yest)) { unlink(IMAGES_C . $article_image_yest); }
						if ($article_image_yest && file_exists(IMAGES_C_T . $article_image_yest)) { unlink(IMAGES_C_T . $article_image_yest); }
						$article_image = "";
					}

					$result = dbquery(
						"UPDATE ". DB_ARTICLES ." SET
															article_title='". serialize($article_title) ."',
															article_description='". serialize($article_description) ."',
															article_keywords='". serialize($article_keywords) ."',
															article_name='". serialize($article_name) ."',
															article_h1='". serialize($article_h1) ."',
															article_content='". serialize($article_content) ."',
															article_image='". $article_image ."',
															article_cat='". $article_cat ."',
															article_access='". $article_access ."',
															article_status='". $article_status ."',
															article_comments='". $article_comments ."',
															article_ratings='". $article_ratings ."'
						WHERE article_id='". (INT)$_GET['id'] ."'"
					);
					$article_id = (INT)$_GET['id'];

				} else {

					$result = dbquery(
						"INSERT INTO ". DB_ARTICLES ." (
															article_title,
															article_description,
															article_keywords,
															article_name,
															article_h1,
															article_content,
															article_image,
															article_cat,
															article_access,
															article_status,
															article_comments,
															article_ratings
						) VALUES (
															'". serialize($article_title) ."',
															'". serialize($article_description) ."',
															'". serialize($article_keywords) ."',
															'". serialize($article_name) ."',
															'". serialize($article_h1) ."',
															'". serialize($article_content) ."',
															'". $article_image ."',
															'". $article_cat ."',
															'". $article_access ."',
															'". $article_status ."',
															'". $article_comments ."',
															'". $article_ratings ."'
						)"
					);
					// $article_id = mysql_insert_id();
					$article_id = _DB::$linkes->insert_id;

				} // UPDATE ILI INSERT


				$viewcompanent = viewcompanent("articles", "name");
				$seourl_component = $viewcompanent['components_id'];

				// $article_alias = str_replace($settings['companent_root_url'], "", $article_alias);
				if (empty($article_alias)) {
					$article_alias = autocrateseourls($article_name[LOCALESHORT]);
				} else {
					$article_alias = autocrateseourls($article_alias);
				}

				$seourl_url = (empty($article_alias) ? "article_". $article_id . $settings['seourl_prefix'] : $article_alias . $settings['seourl_prefix']);
				$seourl_filedid = $article_id;

				$viewseourl = viewseourl($seourl_url, "url");

				if ($viewseourl['seourl_url']==$seourl_url) {
					if (($viewseourl['seourl_filedid']==$seourl_filedid) && ($viewseourl['seourl_component']==$seourl_component)) {
						$seourl_url = $seourl_url;
					} else {
						$seourl_url = "article_". $article_id . $settings['seourl_prefix'];
					}
				}  // Yesli URL YEst


				if ($article_cat!=0) {
					$seourl_url = $cat_url ."/". $seourl_url;
				} else {
					$seourl_url = $settings['companent_root_url'] . $seourl_url;
				}
				$article_alias = $seourl_url;


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
					dbquery("UPDATE ". DB_ARTICLES ." SET article_order='". $position ."' WHERE article_id='". $article_id ."'");
					$result_position = dbquery("SELECT article_id FROM ". DB_ARTICLES ." WHERE article_id!='". $article_id ."' ORDER BY `article_order`");
					if (dbrows($result_position)) {
						while ($data_position = dbarray($result_position)) {
							$position++;
							dbquery("UPDATE ". DB_ARTICLES ." SET article_order='". $position ."' WHERE article_id='". $data_position['article_id'] ."'");
						} // db whille
					} // db query
				} // Yesli action add
				///////////////// POSITIONS /////////////////



				////////// redirect
				if ($_GET['action']=="edit") {
					redirect(FUSION_SELF . $aidlink ."&status=edit&id=". $article_id ."&url=". $article_alias, false);
				} else {
					redirect(FUSION_SELF . $aidlink ."&status=add&id=". $article_id ."&url=". $article_alias, false);
				} ////////// redirect

			} // Yesli Error

		} // Yesli POST save


		$result_cats = dbquery(
							"SELECT
												article_cat_id,
												article_cat_name
							FROM ". DB_ARTICLE_CATS ."
							WHERE article_cat_parent=0
							ORDER BY article_cat_name DESC");
		$catlist = "<option value='0'". ($article_cat==0 ? " selected='selected'" : "") .">". $locale['510_a'] ."</option>\n";

		if (dbrows($result_cats)) {

			$result_subcats = dbquery(
								"SELECT
													article_cat_id,
													article_cat_name,
													article_cat_parent
								FROM ". DB_ARTICLE_CATS ."
								WHERE article_cat_parent!=0
								ORDER BY article_cat_name DESC");
			$subcatlist_arr = array();
			if (dbrows($result_subcats)) {
				while ($data_subcats = dbarray($result_subcats)) {
					$subcatlist_article_name = unserialize($data_subcats['article_cat_name']);
					$subcatlist_arr[$data_subcats['article_cat_id']]['article_cat_name'] = $subcatlist_article_name[LOCALESHORT];
					$subcatlist_arr[$data_subcats['article_cat_id']]['article_cat_parent'] = $data_subcats['article_cat_parent'];
				}
			}
			// echo "<pre>";
			// print_r($subcatlist_arr);
			// echo "</pre>";
			// echo "<hr>";

			while ($data_cats = dbarray($result_cats)) {
				$catlist_article_cat_name = unserialize($data_cats['article_cat_name']);

				$avaycatlist_arr = array();
				foreach ($subcatlist_arr as $subcatlist_key => $subcatlist_value) {
					if ($data_cats['article_cat_id']==$subcatlist_value['article_cat_parent']) {
						$avaycatlist_arr[$subcatlist_key] = $subcatlist_value['article_cat_name'];
					}
				}

				if ($avaycatlist_arr) {
					$catlist .= "<optgroup label='". $catlist_article_cat_name[LOCALESHORT] ."'>\n";
						foreach ($avaycatlist_arr as $avaycatlist_key => $avaycatlist_value) {
							$catlist .= "	<option value='". $avaycatlist_key ."'". ($article_cat==$avaycatlist_key ? " selected='selected'" : "") .">". $avaycatlist_value ."</option>\n";
						}
					$catlist .= "</optgroup>\n";
				} else {
					$catlist .= "<option value='". $data_cats['article_cat_id'] ."'". ($article_cat==$data_cats['article_cat_id'] ? " selected='selected'" : "") .">". $catlist_article_cat_name[LOCALESHORT] ."</option>\n";
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
		<input type="hidden" name="article_status" id="article_status" value="<?php echo $article_status; ?>" />
		<input type="hidden" name="article_order" id="article_order" value="<?php echo $article_order; ?>" />
		<table class='form_table'>
			<tr>
				<td colspan="2"><a href="#" id="seo_tr_button">SEO</a></td>
			</tr>
			<tr class="seo_tr">
				<td colspan="2" class="seo_tr">
					<label for="article_title_<?php echo LOCALESHORT; ?>"><?php echo $locale['501']; ?></label>
					<?php foreach ($languages as $key => $value) { ?>
					<?php if ($languages_count>1) { ?><span class="local_name lang_<?php echo $value['languages_short']; ?>"><?php echo $value['languages_name']; ?></span><?php } ?>
					<input type="text" name="article_title[<?php echo $value['languages_short']; ?>]" id="article_title_<?php echo $value['languages_short']; ?>" value="<?php echo $article_title[$value['languages_short']]; ?>" class="textbox" style="width:100%;" /><br />
					<?php } // foreach languages ?>
				</td>
			</tr>
			<tr class="seo_tr">
				<td colspan="2" class="seo_tr">
					<label for="article_description_<?php echo LOCALESHORT; ?>"><?php echo $locale['502']; ?></label>
					<?php foreach ($languages as $key => $value) { ?>
					<?php if ($languages_count>1) { ?><span class="local_name lang_<?php echo $value['languages_short']; ?>"><?php echo $value['languages_name']; ?></span><?php } ?>
					<input type="text" name="article_description[<?php echo $value['languages_short']; ?>]" id="article_description_<?php echo $value['languages_short']; ?>" value="<?php echo $article_description[$value['languages_short']]; ?>" class="textbox" style="width:100%;" /><br />
					<?php } // foreach languages ?>
				</td>
			</tr>
			<tr class="seo_tr">
				<td colspan="2" class="seo_tr">
					<label for="article_keywords_<?php echo LOCALESHORT; ?>"><?php echo $locale['503']; ?></label>
					<?php foreach ($languages as $key => $value) { ?>
					<?php if ($languages_count>1) { ?><span class="local_name lang_<?php echo $value['languages_short']; ?>"><?php echo $value['languages_name']; ?></span><?php } ?>
					<input type="text" name="article_keywords[<?php echo $value['languages_short']; ?>]" id="article_keywords_<?php echo $value['languages_short']; ?>" value="<?php echo $article_keywords[$value['languages_short']]; ?>" class="textbox" style="width:100%;" /><br />
					<?php } // foreach languages ?>
				</td>
			</tr>
			<tr class="seo_tr">
				<td colspan="2" class="seo_tr">
					<label for="article_h1_<?php echo LOCALESHORT; ?>"><?php echo $locale['505']; ?></label>
					<?php foreach ($languages as $key => $value) { ?>
					<?php if ($languages_count>1) { ?><span class="local_name lang_<?php echo $value['languages_short']; ?>"><?php echo $value['languages_name']; ?></span><?php } ?>
					<input type="text" name="article_h1[<?php echo $value['languages_short']; ?>]" id="article_h1_<?php echo $value['languages_short']; ?>" value="<?php echo $article_h1[$value['languages_short']]; ?>" class="textbox" style="width:100%;" /><br />
					<?php } // foreach languages ?>
				</td>
			</tr>
			<tr class="seo_tr">
				<td colspan="2">
					<label for="article_alias"><?php echo $locale['506']; ?></label>
					<input readonly type="text" name="article_siteurl" id="article_siteurl" value="<?php echo $settings['siteurl'] . ($cat_url ? $cat_url ."/" : $settings['companent_root_url']); ?>" class="textbox" style="width:25%;" />
					<input type="text" name="article_alias" id="article_alias" value="<?php echo $article_alias; ?>" class="textbox" style="width:65%;" />
					<?php if ($settings['seourl_prefix']) { ?><input readonly type="text" name="seourl_prefix" id="seourl_prefix" value="<?php echo $settings['seourl_prefix']; ?>" class="textbox" style="width:5%;" /><?php } ?>
				</td>
			</tr>
			<tr class="seo_tr">
				<td colspan="2"></td>
			</tr>


			<tr>
				<td colspan="2">
					<label for="article_name_<?php echo LOCALESHORT; ?>"><?php echo $locale['504']; ?> <span>*</span></label>
					<?php foreach ($languages as $key => $value) { ?>
					<?php if ($languages_count>1) { ?><span class="local_name lang_<?php echo $value['languages_short']; ?>"><?php echo $value['languages_name']; ?></span><?php } ?>
					<input type="text" name="article_name[<?php echo $value['languages_short']; ?>]" id="article_name_<?php echo $value['languages_short']; ?>" value="<?php echo $article_name[$value['languages_short']]; ?>" class="textbox" style="width:100%;" /><br />
					<?php } // foreach languages ?>
				</td>
			</tr>

			<tr>
				<td colspan="2">
					<label for="article_image"><?php echo $locale['507']; ?></label>
					<?php if ($article_image && file_exists(IMAGES_C_T . $article_image)) { ?>
					<label>
						<img src="<?php echo IMAGES_C_T . $article_image; ?>" alt="" style="height:100px;" /><br />
						<input type="checkbox" name="article_image_del" value="1" /> <?php echo $locale['507_b']; ?>
						<input type="hidden" name="article_image_yest" value="<?php echo $article_image; ?>" />
					</label>
					<?php } else { ?>
					<input type="file" name="article_image" id="article_image" class="filebox" style="width:100%;" accept="image/*" />
					<div id="article_image_preview"></div>
					<?php echo sprintf($locale['507_a'], parsebytesize($settings['articles_photo_max_b'], 3)); ?>
					<?php }	?>
				</td>
			</tr>

			<tr>
				<td colspan="2">
					<label for="article_content_<?php echo LOCALESHORT; ?>"><?php echo $locale['509']; ?></label>
					<?php foreach ($languages as $key => $value) { ?>
					<?php if ($languages_count>1) { ?><span class="local_name lang_<?php echo $value['languages_short']; ?>"><?php echo $value['languages_name']; ?></span><?php } ?>
					<textarea id="editor<?php echo $value['languages_id']; ?>" name="article_content[<?php echo $value['languages_short']; ?>]" id="article_content<?php echo $value['languages_short']; ?>" class="textareabox" cols="95" rows="15" style="width:100%"><?php echo $article_content[$value['languages_short']]; ?></textarea><br />
					<?php } // foreach languages ?>
				</td>
			</tr>
			<?php if (!$settings['tinymce_enabled']) { ?>
			<tr>
				<td colspan="2">
					<?php echo display_html("inputform", "article_content", true, true, true, IMAGES_N); ?>
				</td>
			</tr>
			<?php } ?>
			<tr>
				<td>
					<label for="article_access"><?php echo $locale['511']; ?></label>
					<select name="article_access" id="article_access" class="selectbox" style="width:200px;">
						<?php echo $access_opts; ?>
					</select>
				</td>
				<td>
					<label for="article_cat"><?php echo $locale['510']; ?> <span>*</span></label>
					<select name="article_cat" id="article_cat" class="selectbox" style="width:200px;">
						<?php echo $catlist; ?>
					</select>
				</td>

			<?php if ($settings['comments_enabled'] || $settings['ratings_enabled']) { ?>
			<tr>
				<td colspan="2">
					<?php if ($settings['comments_enabled']) { ?>
					<label><input type='checkbox' name='article_comments' value='1'<?php echo ($article_comments ? " checked='checked" : ""); ?> /> <?php echo $locale['510']; ?></label><br />
					<?php } ?>
					<?php if ($settings['ratings_enabled']) { ?>
					<label><input type='checkbox' name='article_ratings' value='1'<?php echo ($article_ratings ? " checked='checked" : ""); ?> /> <?php echo $locale['511']; ?></label><br />
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

	$viewcompanent = viewcompanent("articles", "name");
	$seourl_component = $viewcompanent['components_id'];


	$result_alter = dbquery("ALTER TABLE `". DB_ARTICLES ."` ORDER BY `article_order` ASC");

	$result = dbquery("SELECT 
								article_id,
								article_name,
								article_order,
								article_status,
								seourl_url
		FROM ". DB_ARTICLES ."
		LEFT JOIN ". DB_SEOURL ." ON seourl_filedid=article_id AND seourl_component=". $seourl_component ."
		LIMIT ". (INT)$_GET['rowstart'] .", ". $settings['articles_per_page']);

		// ORDER BY article_order

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
				$article_name = unserialize($data['article_name']);
	?>
			<tr id="listItem_<?php echo $data['article_id']; ?>">
				<td class="list"><img src="<?php echo IMAGES; ?>arrow.png" alt="<?php echo $locale['410']; ?>" class="handle" /></td>
				<td class="name"><a href="<?php echo FUSION_SELF . $aidlink; ?>&action=edit&id=<?php echo $data['article_id']; ?>" title="<?php echo $article_name[LOCALESHORT]; ?>"><?php echo $article_name[LOCALESHORT]; ?></a></td>
				<td class="status">
					<a href="<?php echo FUSION_SELF . $aidlink; ?>&action=status&id=<?php echo $data['article_id']; ?>&status=<?php echo $data['article_status']; ?>" title="<?php echo ($data['article_status'] ? $locale['411'] : $locale['412']); ?>"><img src="<?php echo IMAGES; ?>status/status_<?php echo $data['article_status']; ?>.png" alt="<?php echo ($data['article_id'] ? $locale['411'] : $locale['412']); ?>"></a>
				</td>
				<td class="num"><?php echo $data['article_order']; ?></td>
				<td class="links">
					<a href="<?php echo BASEDIR . $data['seourl_url']; ?>" target="_blank" title="<?php echo $locale['413']; ?>"><img src="<?php echo IMAGES; ?>view.png" alt="<?php echo $locale['413']; ?>"></a>
					<a href="<?php echo FUSION_SELF . $aidlink; ?>&action=edit&id=<?php echo $data['article_id']; ?>" title="<?php echo $locale['414']; ?>"><img src="<?php echo IMAGES; ?>edit.png" alt="<?php echo $locale['414']; ?>"></a>
					<a href="<?php echo FUSION_SELF . $aidlink; ?>&action=del&id=<?php echo $data['article_id']; ?>" title="<?php echo $locale['415']; ?>" onclick="return DeleteOk();"><img src="<?php echo IMAGES; ?>delete.png" alt="<?php echo $locale['415']; ?>"></a>
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
<?php 
	$rows = dbcount("(article_id)", DB_ARTICLES);
	if ($rows > $settings['article_per_page']) { echo makepagenav((INT)$_GET['rowstart'], $settings['article_per_page'], $rows, 3, ADMIN . FUSION_SELF . $aidlink ."&amp;") ."\n"; }
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

		// echo navigation((INT)$_GET['page'], $settings['article_cat_per_page'], "article_id", DB_ARTICLES, "");


	} // action


	if ($_GET['action']!="order") {
		closetable();

		require_once THEMES."templates/footer.php";
	} // Yesli action ne order
?>