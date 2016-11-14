<?php

	require_once "../includes/maincore.php";

	if (!checkrights("N") || !defined("iAUTH") || !isset($_GET['aid']) || $_GET['aid'] != iAUTH) { redirect("../index.php"); }

	include LOCALE.LOCALESET."admin/news.php";

	if ($_GET['action']!="order") {
		require_once INCLUDES."photo_functions_include.php";
		require_once THEMES."templates/admin_header.php";

		if ($settings['tinymce_enabled']) {
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
					dbquery("UPDATE ". DB_NEWS ." SET news_order='". ($position+1) ."' WHERE news_id='". $item ."'");
				}
			}

			header("Content-Type: text/html; charset=". $locale['charset'] ."\n");
			echo "<div id='close-message'>\n";
			echo "	<div class='success'>". $locale['success_007'] ."</div>\n";
			echo "</div>\n";

		}


	} else if ($_GET['action']=="status") {

		$news_id = (INT)$_GET['id'];
		$news_status = (INT)$_GET['status'];
		$news_status = ($news_status ? 0 : 1);

		$result = dbquery("UPDATE ". DB_NEWS ." SET
														news_status='". $news_status ."'
		WHERE news_id='". $news_id ."'");

		redirect(FUSION_SELF.$aidlink."&status=". ($news_status ? "active" : "deactive") ."&id=". $news_id, false);

	} else if ($_GET['action']=="del") {

		$result = dbquery("SELECT news_image FROM ". DB_NEWS ." WHERE news_id='". (INT)$_GET['id'] ."' LIMIT 1");
		if (dbrows($result)) {
			$data = dbarray($result);
			if (!empty($data['news_image']) && file_exists(IMAGES_N . $data['news_image'])) { unlink(IMAGES_N . $data['news_image']); }
			if (!empty($data['news_image']) && file_exists(IMAGES_N_T ."t1_". $data['news_image'])) { unlink(IMAGES_N_T ."t1_". $data['news_image']); }
			if (!empty($data['news_image']) && file_exists(IMAGES_N_T ."t2_".  $data['news_image'])) { unlink(IMAGES_N_T ."t2_". $data['news_image']); }
		} // Tesli Yest DB query

		$result = dbquery("DELETE FROM ". DB_NEWS ." WHERE news_id='". (INT)$_GET['id'] ."'");
		$result = dbquery("DELETE FROM ". DB_COMMENTS ." WHERE comment_item_id='". (INT)$_GET['id'] ."' and comment_type='N'");
		$result = dbquery("DELETE FROM ". DB_COMMENTS ." WHERE rating_item_id='". (INT)$_GET['id'] ."' and rating_type='N'");

		$viewcompanent = viewcompanent("news", "name");
		$seourl_component = $viewcompanent['components_id'];
		$seourl_filedid = (INT)$_GET['id'];

		$result = dbquery("DELETE FROM ". DB_SEOURL ." WHERE seourl_component='". $seourl_component ."' AND seourl_filedid='". $seourl_filedid ."'");

		redirect(FUSION_SELF . $aidlink ."&status=del&id=". (INT)$_GET['id']);

	} else if ($_GET['action']=="add" || $_GET['action']=="edit") {

		if ($_POST['save']) {

			$news_title = stripinput($_POST['news_title']);
			$news_description = stripinput($_POST['news_description']);
			$news_keywords = stripinput($_POST['news_keywords']);
			$news_name = stripinput($_POST['news_name']);
			$news_h1 = stripinput($_POST['news_h1']);
			$news_short = stripinput($_POST['news_short']);
			$news_extended = stripinput($_POST['news_extended']);

			$news_image_yest = stripinput($_POST['news_image_yest']);

			$news_image =  $_FILES['news_image']['name'];
			$news_imagetmp  = $_FILES['news_image']['tmp_name'];
			$news_imagesize = $_FILES['news_image']['size'];
			$news_imagetype = $_FILES['news_image']['type'];

			// $news_cat = isnum($_POST['news_cat']) ? (INT)$_POST['news_cat'] : 0;
			$news_access = isnum($_POST['news_access']) ? (INT)$_POST['news_access'] : 0;
			$news_status = isnum($_POST['news_status']) ? (INT)$_POST['news_status'] : 1;
			// $news_order = "";
			$news_date = FUSION_TODAY;
			$news_comments = isnum($_POST['news_comments']) ? (INT)$_POST['news_comments'] : 0;
			$news_ratings = isnum($_POST['news_ratings']) ? (INT)$_POST['news_ratings'] : 0;
			$news_alias = stripinput($_POST['news_alias']);

		} else if ($_GET['action']=="edit") {

			$viewcompanent = viewcompanent("news", "name");
			$seourl_component = $viewcompanent['components_id'];

			$result = dbquery("SELECT
										news_title,
										news_description,
										news_keywords,
										news_name,
										news_h1,
										news_short,
										news_extended,
										news_image,
										news_access,
										news_status,
										news_order,
										news_date,
										news_comments,
										news_ratings,
										seourl_url
			FROM ". DB_NEWS ."
			LEFT JOIN ". DB_SEOURL ." ON seourl_filedid=news_id AND seourl_component=". $seourl_component ."
			WHERE news_id='" .(INT)$_GET['id'] ."'
			LIMIT 1");

			// news_cat,

			if (dbrows($result)) {
				$data = dbarray($result);

				$news_title = unserialize($data['news_title']);
				$news_description = unserialize($data['news_description']);
				$news_keywords = unserialize($data['news_keywords']);
				$news_name = unserialize($data['news_name']);
				$news_h1 = unserialize($data['news_h1']);
				$news_short = unserialize($data['news_short']);
				$news_extended = unserialize($data['news_extended']);
				$news_image = $data['news_image'];
				// $news_cat = (INT)$data['news_cat'];
				$news_access = (INT)$data['news_access'];
				$news_status = (INT)$data['news_status'];
				$news_order = (INT)$data['news_order'];
				$news_date = (INT)$data['news_date'];
				$news_comments = (INT)$data['news_comments'];
				$news_ratings = (INT)$data['news_ratings'];
				$news_alias = $data['seourl_url'];

			} else {
				redirect(FUSION_SELF . $aidlink);
			} // yesli yest result

		} else {

			$news_title = "";
			$news_description = "";
			$news_keywords = "";
			$news_name = "";
			$news_h1 = "";
			$news_short = "";
			$news_extended = "";
			$news_image = "";
			// $news_cat = 0;
			$news_access = 0;
			$news_status = 1;
			$news_order = "";
			$news_date = "";
			$news_comments = 1;
			$news_ratings = 1;

		} // POST save


		if ($_POST['save']) {

			foreach ($languages as $key => $value) {
				if (empty($news_name[$value['languages_short']])) { $error .= "<div class='error'>". $locale['error_001'] ." - ". $value['languages_name'] ."</div>\n"; }
			}
			foreach ($languages as $key => $value) {
				if (empty($news_short[$value['languages_short']])) { $error .= "<div class='error'>". $locale['error_002'] ." - ". $value['languages_name'] ."</div>\n"; }
			}
			foreach ($languages as $key => $value) {
				if (empty($news_extended[$value['languages_short']])) { $error .= "<div class='error'>". $locale['error_003'] ." - ". $value['languages_name'] ."</div>\n"; }
			}

			if (!empty($news_image)) {
				if (strlen($news_image) > 100) { $error .= "<div class='error'>". $locale['error_050'] ."</div>\n"; }
				// проверяем расширение файла
				$news_image_ext = strtolower(substr($news_image, 1 + strrpos($news_image, ".")));
				if (!in_array($news_image_ext, $photo_valid_types)) { $error .= "<div class='error'>". $locale['error_051'] ."</div>\n"; }
				// 1. считаем кол-во точек в выражении - если большей одной - СВОБОДЕН!
				$news_image_findtochka = substr_count($news_image, ".");
				if ($news_image_findtochka>1) { $error .= "<div class='error'>". $locale['error_052'] ."</div>\n"; }
				// 2. если в имени есть .php, .html, .htm - свободен! 
				if (preg_match("/\.php/i",$news_image))  { $error .= "<div class='error'>". $locale['error_053'] ."</div>\n"; }
				if (preg_match("/\.html/i",$news_image)) { $error .= "<div class='error'>". $locale['error_054'] ."</div>\n"; }
				if (preg_match("/\.htm/i",$news_image))  { $error .= "<div class='error'>". $locale['error_055'] ."</div>\n"; }
				// 5. Размер фото
				// $news_image_fotosize = round($news_imagesize/10.24)/100; // размер ЗАГРУЖАЕМОГО ФОТО в Кб.
				// $news_image_fotomax = round($settings['news_photo_max_b']/10.24)/100; // максимальный размер фото в Кб.
				// if ($news_image_fotosize>$news_image_fotomax) { $error .= "<div class='error'>". $locale['error_056'] ."<br />". $locale['error_057'] ." ". $news_image_fotosize ." Kb<br />". $locale['error_058'] ." ". $news_image_fotomax ." Kb</div>\n"; }
				// 6. "Габариты" фото > $maxwidth х $maxheight - ДО свиданья! :-)
				// $news_image_getsize = getimagesize($news_imagetmp);
				// if ($news_image_getsize[0]>$settings['news_photo_max_w'] or $news_image_getsize[1]>$settings['news_photo_max_h']) { $error .= "<div class='error'>". $locale['error_059'] ."<br />". $locale['error_060'] ." ". $news_image_getsize[0] ."x". $news_image_getsize[1] ."<br />". $locale['error_061'] ." ". $settings['news_photo_max_w'] ."x". $settings['news_photo_max_h'] ."</div>\n"; }
				// if ($news_image_getsize[0]<$news_image_getsize[1]) { $error .= "<div class='error'>". $locale['error_062'] ."</div>\n"; }
				// Foto 0 Kb
				// if ($news_imagesize<0 and $news_imagesize>$settings['foto_size']) { $error .= "<div class='error'>". $locale['error_063'] ."</div>\n"; }
			}


			if (isset($error)) {

				echo "	<div class='admin-message'>\n";
				echo "		<div id='close-message'>". $error ."</div>\n";
				echo "	</div>\n";

				$news_image = "";

			} else {


				if ($news_image) {

					$img_rand_key = mt_rand(100,999);

					// $$news_image = stripfilename(str_replace(" ", "_", strtolower($news_image)));
					$news_image_ext = strrchr($news_image, ".");
					$news_image = stripfilename(str_replace(" ", "_", strtolower(substr($news_image, 0, strrpos($news_image, ".")))));


					if ($news_image_ext == ".gif") {
						$news_image_filetype = 1;
					} elseif ($news_image_ext == ".jpg") {
						$news_image_filetype = 2;
					} elseif ($news_image_ext == ".png") {
						$news_image_filetype = 3;
					} else {
						$news_image_filetype = false; 
					}

					$news_image_t1 = image_exists(IMAGES_N_T, "t1_". $news_image . $img_rand_key . $news_image_ext);
					$news_image_t2 = image_exists(IMAGES_N_T, "t2_". $news_image . $img_rand_key . $news_image_ext);
					$news_image = image_exists(IMAGES_N, $news_image . $img_rand_key . $news_image_ext);

					move_uploaded_file($news_imagetmp, IMAGES_N . $news_image);
					if (function_exists("chmod")) { chmod(IMAGES_N . $news_image, 0644); }

					createthumbnail($news_image_filetype, IMAGES_N . $news_image, IMAGES_N_T . $news_image_t1, $settings['news_photo_w'], $settings['news_photo_h']);
					if ($settings['news_thumb_ratio'] == 0) {
						createthumbnail($news_image_filetype, IMAGES_N . $news_image, IMAGES_N_T . $news_image_t2, $settings['news_thumb_w'], $settings['news_thumb_h']);
					} else {
						createsquarethumbnail($news_image_filetype, IMAGES_N . $news_image, IMAGES_N_T . $news_image_t2, $settings['news_thumb_w']);
					}
					createthumbnail($news_image_filetype, IMAGES_N . $news_image, IMAGES_N . $news_image, $settings['news_photo_max_w'], $settings['news_photo_max_h']);
				} else {
					$news_image = $news_image_yest;
				} // Photo Upload





				if ($_GET['action']=="edit") {

					if (isset($_POST['news_del_image'])) {
						if (!empty($news_image_yest) && file_exists(IMAGES_N . $news_image_yest)) { unlink(IMAGES_N . $news_image_yest); }
						if (!empty($news_image_yest) && file_exists(IMAGES_N_T . "t1_". $news_image_yest)) { unlink(IMAGES_N_T . "t1_". $news_image_yest); }
						if (!empty($news_image_yest) && file_exists(IMAGES_N_T . "t2_". $news_image_yest)) { unlink(IMAGES_N_T . "t2_". $news_image_yest); }
						$news_image = "";
					} // del_image

					$result = dbquery(
						"UPDATE ". DB_NEWS ." SET
															news_title='". serialize($news_title) ."',
															news_description='". serialize($news_description) ."',
															news_keywords='". serialize($news_keywords) ."',
															news_name='". serialize($news_name) ."',
															news_h1='". serialize($news_h1) ."',
															news_short='". serialize($news_short) ."',
															news_extended='". serialize($news_extended) ."',
															news_image='". $news_image ."',
															news_access='". $news_access ."',
															news_comments='". $news_comments ."',
															news_ratings='". $news_ratings ."'
						WHERE news_id='". (INT)$_GET['id'] ."'"
					);
															// news_cat='". $news_cat ."',
					$news_id = (INT)$_GET['id'];

				} else {

					$result = dbquery(
						"INSERT INTO ". DB_NEWS ." (
															news_title,
															news_description,
															news_keywords,
															news_name,
															news_h1,
															news_short,
															news_extended,
															news_image,
															news_access,
															news_status,
															news_order,
															news_date,
															news_comments,
															news_ratings
						) VALUES (
															'". serialize($news_title) ."',
															'". serialize($news_description) ."',
															'". serialize($news_keywords) ."',
															'". serialize($news_name) ."',
															'". serialize($news_h1) ."',
															'". serialize($news_short) ."',
															'". serialize($news_extended) ."',
															'". $news_image ."',
															'". $news_access ."',
															'". $news_status ."',
															'". $news_order ."',
															'". $news_date ."',
															'". $news_comments ."',
															'". $news_ratings ."'
						)"
					);
															// news_cat,
															// '". $news_cat ."',
					$news_id = mysql_insert_id();

				} // UPDATE ILI INSERT



				$viewcompanent = viewcompanent("news", "name");
				$seourl_component = $viewcompanent['components_id'];

				$news_alias = str_replace("news/", "", $news_alias);
				if (empty($news_alias)) {
					$news_alias = autocrateseourls($news_name[LOCALESHORT]);
				} else {
					$news_alias = autocrateseourls($news_alias);
				}

				$seourl_url = (empty($news_alias) ? "news/news". $news_id .".php" : "news/". $news_alias);
				$seourl_filedid = $news_id;

				$viewseourl = viewseourl($seourl_url, "url");

				if ($viewseourl['seourl_url']==$seourl_url) {
					if (($viewseourl['seourl_filedid']==$seourl_filedid) && ($viewseourl['seourl_component']==$seourl_component)) {
						$seourl_url = $seourl_url;
					} else {
						$seourl_url = "news/news". $news_id .".php";
					}
				}  // Yesli URL YEst

				$news_alias = $seourl_url;


			if ($_GET['action']=="edit") {
				$result = dbquery(
					"UPDATE ".DB_SEOURL." SET
														seourl_url='". $seourl_url ."',
														seourl_lastmod='". date("Y-m-d") ."'
					WHERE seourl_filedid='". $seourl_filedid ."' AND seourl_component='". $seourl_component ."'"
				);
			} else {
				$result = dbquery(
								"INSERT INTO ".DB_SEOURL." (
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


				////////// redirect
				if ($_GET['action']=="edit") {
					redirect(FUSION_SELF . $aidlink ."&status=edit&id=". $news_id ."&url=". $news_alias, false);
				} else {
					redirect(FUSION_SELF . $aidlink ."&status=add&id=". $news_id ."&url=". $news_alias, false);
				} ////////// redirect


			} // Yesli Error


		} // POST save





		// // news_cats
		// $result = dbquery("SELECT news_cat_id, news_cat_name FROM ". DB_NEWS_CATS ." ORDER BY news_cat_name");
		// $news_cat_opts = ""; $sel = "";
		// if (dbrows($result)) {
		// 	while ($data = dbarray($result)) {
		// 		if (isset($news_cat)) $sel = ($news_cat == $data['news_cat_id'] ? " selected='selected'" : "");
		// 		$news_cat_opts .= "<option value='". $data['news_cat_id'] ."'". $sel .">". $data['news_cat_name'] ."</option>\n";
		// 	}
		// } // news_cats


		// user_group
		$access_opts = ""; $sel = "";
		$user_groups = getusergroups();
		while(list($key, $user_group) = each($user_groups)){
			$sel = ($news_access == $user_group['0'] ? " selected='selected'" : "");
			$access_opts .= "<option value='". $user_group['0'] ."'". $sel .">". $user_group['1'] ."</option>\n";
		} // user_group



		echo "<a href='". FUSION_SELF . $aidlink ."' class='go_back'>". $locale['011'] ."</a><br />\n";
?>



	<form name="inputform" method="POST" action="<?php echo FUSION_SELF . $aidlink . ($_GET['action'] ? "&action=". $_GET['action'] : "") . ($_GET['id'] ? "&id=". (INT)$_GET['id'] : ""); ?>" enctype="multipart/form-data">
		<table class="form_table">
			<tr>
				<td colspan="2"><a href="#" id="seo_tr_button">SEO</a></td>
			</tr>
			<tr class="seo_tr">
				<td colspan="2">
					<label for="news_title_<?php echo LOCALESHORT; ?>"><?php echo $locale['501']; ?></label>
					<?php foreach ($languages as $key => $value) { ?>
					<span class="local_name lang_<?php echo $value['languages_short']; ?>"><?php echo $value['languages_name']; ?></span>
					<input type="text" name="news_title[<?php echo $value['languages_short']; ?>]" id="news_title_<?php echo $value['languages_short']; ?>" value="<?php echo $news_title[$value['languages_short']]; ?>" class="textbox" style="width:98%;" /><br />
					<?php } // foreach languages ?>
				</td>
			</tr>
			<tr class="seo_tr">
				<td colspan="2">
					<label for="news_description_<?php echo LOCALESHORT; ?>"><?php echo $locale['502']; ?></label>
					<?php foreach ($languages as $key => $value) { ?>
					<span class="local_name lang_<?php echo $value['languages_short']; ?>"><?php echo $value['languages_name']; ?></span>
					<input type="text" name="news_description[<?php echo $value['languages_short']; ?>]" id="news_description_<?php echo $value['languages_short']; ?>" value="<?php echo $news_description[$value['languages_short']]; ?>" class="textbox" style="width:98%;" /><br />
					<?php } // foreach languages ?>
				</td>
			</tr class="seo_tr">
			<tr class="seo_tr">
				<td colspan="2">
					<label for="news_keywords_<?php echo LOCALESHORT; ?>"><?php echo $locale['503']; ?></label>
					<?php foreach ($languages as $key => $value) { ?>
					<span class="local_name lang_<?php echo $value['languages_short']; ?>"><?php echo $value['languages_name']; ?></span>
					<input type="text" name="news_keywords[<?php echo $value['languages_short']; ?>]" id="news_keywords_<?php echo $value['languages_short']; ?>" value="<?php echo $news_keywords[$value['languages_short']]; ?>" class="textbox" style="width:98%;" /><br />
					<?php } // foreach languages ?>
				</td>
			</tr>
			<tr class="seo_tr">
				<td colspan="2">
					<label for="news_h1_<?php echo LOCALESHORT; ?>"><?php echo $locale['505']; ?></label>
					<?php foreach ($languages as $key => $value) { ?>
					<span class="local_name lang_<?php echo $value['languages_short']; ?>"><?php echo $value['languages_name']; ?></span>
					<input type="text" name="news_h1[<?php echo $value['languages_short']; ?>]" id="news_h1_<?php echo $value['languages_short']; ?>" value="<?php echo $news_h1[$value['languages_short']]; ?>" class="textbox" style="width:98%;" /><br />
					<?php } // foreach languages ?>
				</td>
			</tr>
			<tr class="seo_tr">
				<td colspan="2">
					<label for="news_alias"><?php echo $locale['506']; ?></label>
					<input readonly type="text" name="news_siteurl" id="news_siteurl" value="<?php echo $settings['siteurl']; ?>" class="textbox" style="width:150px;" />
					<input type="text" name="news_alias" id="news_alias" value="<?php echo $news_alias; ?>" class="textbox" style="width:430px;" />
				</td>
			</tr>
			<tr class="seo_tr">
				<td colspan="2"></td>
			</tr>
			
			<tr>
				<td colspan="2">
					<label for="news_name_<?php echo LOCALESHORT; ?>"><?php echo $locale['504']; ?> <span>*</span></label>
					<?php foreach ($languages as $key => $value) { ?>
					<span class="local_name lang_<?php echo $value['languages_short']; ?>"><?php echo $value['languages_name']; ?></span>
					<input type="text" name="news_name[<?php echo $value['languages_short']; ?>]" id="news_name_<?php echo $value['languages_short']; ?>" value="<?php echo $news_name[$value['languages_short']]; ?>" class="textbox" style="width:98%;" /><br />
					<?php } // foreach languages ?>
				</td>
			</tr>
			<tr>
				<td colspan="2">
					<label for="news_image"><?php echo $locale['507']; ?></label>
					<?php if ($news_image) { ?>
					<label>
						<img src="<?php echo IMAGES_N_T . "t2_". $news_image; ?>" alt="" /><br />
						<input type="checkbox" name="news_del_image" value="1" /> <?php echo $locale['516']; ?>
						<input type="hidden" name="news_image_yest" value="<?php echo $news_image; ?>" />
					</label>
					<?php } else { ?>
					<input type="file" name="news_image" id="news_image" class="filebox" style="width:250px;" /><br />
					<?php echo sprintf($locale['514'], parsebytesize($settings['news_photo_max_b'])); ?>
					<?php }	?>
				</td>
			</tr>

			<tr>
				<td colspan="2">
					<label for="news_short_<?php echo LOCALESHORT; ?>"><?php echo $locale['508']; ?> <span>*</span></label>
					<?php foreach ($languages as $key => $value) { ?>
					<span class="local_name lang_<?php echo $value['languages_short']; ?>"><?php echo $value['languages_name']; ?></span>
					<textarea id="editor<?php echo $value['languages_id']; ?>" name="news_short[<?php echo $value['languages_short']; ?>]" id="news_short<?php echo $value['languages_short']; ?>" class="textareabox" cols="95" rows="15" style="width:98%"><?php echo $news_short[$value['languages_short']]; ?></textarea><br />
					<?php } // foreach languages ?>
				</td>
			</tr>
			<?php if (!$settings['tinymce_enabled']) { ?>
			<tr>
				<td colspan="2">
					<?php echo display_html("inputform", "news_short", true, true, true, IMAGES_N); ?>
				</td>
			</tr>
			<?php } ?>
			<tr>
				<td colspan="2">
					<label for="news_extended_<?php echo LOCALESHORT; ?>"><?php echo $locale['509']; ?> <span>*</span></label>
					<?php foreach ($languages as $key => $value) { ?>
					<span class="local_name lang_<?php echo $value['languages_short']; ?>"><?php echo $value['languages_name']; ?></span>
					<textarea id="editor2<?php echo $value['languages_id']; ?>" name="news_extended[<?php echo $value['languages_short']; ?>]" class="textareabox" cols="95" rows="15" style="width:98%"><?php echo $news_extended[$value['languages_short']]; ?></textarea><br />
					<?php } ?>
				</td>
			</tr>
			<?php if (!$settings['tinymce_enabled']) { ?>
			<tr>
				<td colspan="2">
					<?php echo display_html("inputform", "news_extended", true, true, true, IMAGES_N); ?>
				</td>
			</tr>
			<?php } ?>
			<tr>
				<td>
					<?php /*
					<label for="news_cat"><?php echo $locale['510']; ?></label>
					<select name="news_cat" id="news_cat" class="selectbox">
						<option value="0"><?php echo $locale['515']; ?></option>
						<?php echo $news_cat_opts; ?>
					</select>
					*/ ?>
				</td>
				<td>
					<label for="news_access"><?php echo $locale['511']; ?></label>
					<select name="news_access" id="news_access" class="selectbox" style="width:200px;">
						<?php echo $access_opts; ?>
					</select>
				</td>
			</tr>
			<?php if ($settings['comments_enabled']) { ?>
			<tr>
				<td colspan="2">
					<label><input type="checkbox" name="news_comments" value="1" onclick="SetRatings();"<?php echo ($news_comments ? " checked" : ""); ?> /> <?php echo $locale['512']; ?></label>
				</td>
			</tr>
			<?php } // comments_enabled ?>
			<?php if ($settings['ratings_enabled']) { ?>
			<tr>
				<td colspan="2">
					<label><input type="checkbox" name="news_ratings" value="1"<?php echo ($news_ratings ? " checked" : ""); ?> /> <?php echo $locale['513']; ?></label>
				</td>
			</tr>
			<?php } // ratings_enabled ?>
			<tr>
				<td colspan="2" class="form_buttons">
					<input type="submit" name="save" value="<?php echo $locale['520']; ?>" class="button" />
					<input type="button" name="cancel" value="<?php echo $locale['521']; ?>" class="button" onclick="location.href='<?php echo FUSION_SELF . $aidlink; ?>'" />
				</td>
			</tr>
		</table>
	</form>



	<?php if ($settings['comments_enabled'] && $settings['ratings_enabled']) { ?>
	<script type='text/javascript'>
		<!--
		function SetRatings() {
			if (inputform.news_comments.checked == false) {
				inputform.news_ratings.checked = false;
				inputform.news_ratings.disabled = true;
			} else {
				inputform.news_ratings.disabled = false;
				inputform.news_ratings.checked = true;
			}
		}
		//-->
	</script>
	<?php } // comments_enabled && ratings_enabled ?>

	<script type='text/javascript'>
		<!--
		<?php
		if ($settings['tinymce_enabled']==2) {
			foreach ($languages as $key => $value) {
		?>
		var ckeditor<?php echo $value['languages_id']; ?> = CKEDITOR.replace('editor<?php echo $value['languages_id']; ?>');
		CKFinder.setupCKEditor( ckeditor<?php echo $value['languages_id']; ?>, '<?php echo INCLUDES; ?>jscripts/ckeditor/ckfinder/' );
		var ckeditor2<?php echo $value['languages_id']; ?> = CKEDITOR.replace('editor2<?php echo $value['languages_id']; ?>');
		CKFinder.setupCKEditor( ckeditor2<?php echo $value['languages_id']; ?>, '<?php echo INCLUDES; ?>jscripts/ckeditor/ckfinder/' );
		<?php
			} // foreach $languages
		} // Yesli Text Editor CKEDITOR
		?>
		//-->
	</script>

<?php
	} else {

	if ($_GET['status']) {
		if ($_GET['status']=="add") {

			$message = "<div class='success'>". $locale['success_002'] ." ID: ". intval($_GET['id']) ."</div>\n";
			$message .= "<div class='success'>". $locale['success_001'] ."<a href='". BASEDIR . $_GET['url'] ."' target='_blank'>". $_GET['url'] ."</a></div>\n";

		} elseif ($_GET['status']=="edit") {

			$message = "<div class='success'>". $locale['success_003'] ." ID: ". intval($_GET['id']) ."</div>\n";
			$message .= "<div class='success'>". $locale['success_001'] ."<a href='". BASEDIR . $_GET['url'] ."' target='_blank'>". $_GET['url'] ."</a></div>\n";

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
	$viewcompanent = viewcompanent("news", "name");
	$seourl_component = $viewcompanent['components_id'];


	$result = dbquery("SELECT 
								news_id,
								news_name,
								news_order,
								news_status,
								seourl_url
		FROM ". DB_NEWS ."
		LEFT JOIN ". DB_SEOURL ." ON seourl_filedid=news_id AND seourl_component=". $seourl_component ."
		ORDER BY news_order");

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
			while ($data = dbarray($result)) {
				$news_name = unserialize($data['news_name']);
	?>
			<tr id="listItem_<?php echo $data['news_id']; ?>">
				<td class="list"><img src="<?php echo IMAGES; ?>arrow.png" alt="<?php echo $locale['410']; ?>" class="handle" /></td>
				<td class="name"><a href="<?php echo FUSION_SELF . $aidlink; ?>&action=edit&id=<?php echo $data['news_id']; ?>" title="<?php echo $news_name[LOCALESHORT]; ?>"><?php echo $news_name[LOCALESHORT]; ?></a></td>
				<td class="status">
					<a href="<?php echo FUSION_SELF . $aidlink; ?>&action=status&id=<?php echo $data['news_id']; ?>&status=<?php echo $data['news_status']; ?>" title="<?php echo ($data['news_id'] ? $locale['411'] : $locale['412']); ?>"><img src="<?php echo IMAGES; ?>status/status_<?php echo $data['news_status']; ?>.png" alt="<?php echo ($data['news_id'] ? $locale['411'] : $locale['412']); ?>"></a>
				</td>
				<td class="num"><?php echo $data['news_order']; ?></td>
				<td class="links">
					<a href="<?php echo BASEDIR . $data['seourl_url']; ?>" target="_blank" title="<?php echo $locale['413']; ?>"><img src="<?php echo IMAGES; ?>view.png" alt="<?php echo $locale['413']; ?>"></a>
					<a href="<?php echo FUSION_SELF . $aidlink; ?>&action=edit&id=<?php echo $data['news_id']; ?>" title="<?php echo $locale['414']; ?>"><img src="<?php echo IMAGES; ?>edit.png" alt="<?php echo $locale['414']; ?>"></a>
					<a href="<?php echo FUSION_SELF . $aidlink; ?>&action=del&id=<?php echo $data['news_id']; ?>" title="<?php echo $locale['415']; ?>" onclick="return DeleteOk();"><img src="<?php echo IMAGES; ?>delete.png" alt="<?php echo $locale['415']; ?>"></a>
				</td>
			</tr>

	<?php
			} // db whille
	?>
		</tbody>
		<tfoot>
			<tr>
				<td colspan="5"></td>
			</tr>
		</tfoot>
	</table>

	<script type='text/javascript'>
		function DeleteOk() {
			return confirm('<?php echo $locale['450']; ?>');
		}
	</script>


<?php
	} // action


	if ($_GET['action']!="order") {
		closetable();

		require_once THEMES."templates/footer.php";
	} // Yesli action ne order
?>