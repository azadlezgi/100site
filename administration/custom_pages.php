<?php

	require_once "../includes/maincore.php";

	if (!checkrights("CP") || !defined("iAUTH") || !isset($_GET['aid']) || $_GET['aid'] != iAUTH) { redirect("../index.php"); }

	include LOCALE . LOCALESET ."admin/custom_pages.php";


	if ($_GET['action']!="order") {
		require_once THEMES ."templates/admin_header.php";
		if ($settings['tinymce_enabled']) {
			//echo "<script language='javascript' type='text/javascript'>advanced();</script>\n";
		} else {
			require_once INCLUDES."html_buttons_include.php";
		}

		opentable($locale['001']);
	} // Yesli action ne order




	if ($_GET['action']=="order") {

		if (isset($_GET['listItem']) && is_array($_GET['listItem'])) {
			foreach ($_GET['listItem'] as $position => $item) {
				if (isnum($position) && isnum($item)) {
					dbquery("UPDATE ". DB_CUSTOM_PAGES ." SET page_order='". ($position+1) ."' WHERE page_id='". $item ."'");
				}
			}

			header("Content-Type: text/html; charset=". $locale['charset'] ."\n");
			echo "<div id='close-message'>\n";
			echo "	<div class='success'>". $locale['success_007'] ."</div>\n";
			echo "</div>\n";

		}


	} else if ($_GET['action']=="status") {

		$page_id = (INT)$_GET['id'];
		$page_status = (INT)$_GET['status'];
		$page_status = ($page_status ? 0 : 1);

		$result = dbquery("UPDATE ". DB_CUSTOM_PAGES ." SET
														page_status='". $page_status ."'
		WHERE page_id='". $page_id ."'");

		redirect(FUSION_SELF.$aidlink."&status=". ($page_status ? "active" : "deactive") ."&id=". $page_id, false);

	} else if ($_GET['action']=="del") {

		$result = dbquery("DELETE FROM ". DB_CUSTOM_PAGES ." WHERE page_id='". (INT)$_GET['id'] ."'");
		$result = dbquery("DELETE FROM ". DB_COMMENTS ." WHERE comment_item_id='". (INT)$_GET['id'] ."' and comment_type='C'");
		$result = dbquery("DELETE FROM ". DB_COMMENTS ." WHERE rating_item_id='". (INT)$_GET['id'] ."' and rating_type='C'");

		$viewcompanent = viewcompanent("custom_pages", "name");
		$seourl_component = $viewcompanent['components_id'];
		$seourl_filedid = (INT)$_GET['id'];

		$result = dbquery("DELETE FROM ". DB_SEOURL ." WHERE seourl_component='". $seourl_component ."' AND seourl_filedid='". $seourl_filedid ."'");

		redirect(FUSION_SELF . $aidlink ."&status=del&id=". (INT)$_GET['id']);

	} else if ($_GET['action']=="add" || $_GET['action']=="edit") {

		if (isset($_POST['save'])) {

			$page_title = stripinput($_POST['page_title']);
			$page_description = stripinput($_POST['page_description']);
			$page_keywords = stripinput($_POST['page_keywords']);
			$page_name = stripinput($_POST['page_name']);
			$page_h1 = stripinput($_POST['page_h1']);
			$page_alias = stripinput($_POST['page_alias']);
			$page_content = stripinput($_POST['page_content']);
			$page_access = $_POST['page_access'];
			$page_comments = isset($_POST['page_comments']) ? "1" : "0";
			$page_ratings = isset($_POST['page_ratings']) ? "1" : "0";
			$addlink = isset($_POST['add_link']) ? " checked='checked'" : "";


		} else if ($_GET['action']=="edit") {

			$viewcompanent = viewcompanent("custom_pages", "name");
			$seourl_component = $viewcompanent['components_id'];

			$result = dbquery(
				"SELECT 
											page_id,
											page_title,
											page_description,
											page_keywords,
											page_name,
											page_h1,
											page_access,
											page_content,
											page_comments,
											page_ratings,
											seourl_url
				FROM ". DB_CUSTOM_PAGES ."
				LEFT JOIN ". DB_SEOURL ." ON seourl_filedid=page_id AND seourl_component=". $seourl_component ."
				WHERE page_id='". (INT)$_GET['id'] ."' LIMIT 1"
			);
			if (dbrows($result)) {
				$data = dbarray($result);
											$page_title = unserialize($data['page_title']);
											$page_description = unserialize($data['page_description']);
											$page_keywords = unserialize($data['page_keywords']);
											$page_name = unserialize($data['page_name']);
											$page_h1 = unserialize($data['page_h1']);
											$page_alias = $data['seourl_url'];
											$page_access = $data['page_access'];
											$page_content = unserialize($data['page_content']);
											$page_comments = ($data['page_comments'] == "1" ? " checked='checked'" : "");
											$page_ratings = ($data['page_ratings'] == "1" ? " checked='checked'" : "");
											$addlink = "";

			} else {
				redirect(FUSION_SELF . $aidlink);
			}

		} else {

				$page_title = "";
				$page_description = "";
				$page_keywords = "";
				$page_name = "";
				$page_h1 = "";
				$page_alias = "";
				$page_access = "";
				$page_content = "";
				$page_comments = "";
				$page_ratings = "";
				$addlink = "";

		}


		if (isset($_POST['save'])) {


			foreach ($languages as $key => $value) {
				if (empty($page_name[$value['languages_short']])) { $error .= "<div class='error'>". $locale['error_001'] ." - ". $value['languages_name'] ."</div>\n"; }
			}
			foreach ($languages as $key => $value) {
				if (empty($page_content[$value['languages_short']])) { $error .= "<div class='error'>". $locale['error_002'] ." - ". $value['languages_name'] ."</div>\n"; }
			}


		if (isset($error)) {

			echo "	<div class='admin-message'>\n";
			echo "		<div id='close-message'>". $error ."</div>\n";
			echo "	</div>\n";

		} else {

			if ($_GET['action']=="edit") {

				$result = dbquery(
					"UPDATE ". DB_CUSTOM_PAGES ." SET
														page_title='". serialize($page_title) ."',
														page_description='". serialize($page_description) ."',
														page_keywords='". serialize($page_keywords) ."',
														page_name='". serialize($page_name) ."',
														page_h1='". serialize($page_h1) ."',
														page_access='". $page_access ."',
														page_content='". serialize($page_content) ."',
														page_comments='". $page_comments ."',
														page_ratings='". $page_ratings ."'
					WHERE page_id='". (INT)$_GET['id'] ."'"
				);
				$page_id = (INT)$_GET['id'];

			} else {

				$result = dbquery(
					"INSERT INTO ". DB_CUSTOM_PAGES ." (
														page_title,
														page_description,
														page_keywords,
														page_name,
														page_h1,
														page_access,
														page_content,
														page_comments,
														page_ratings
					) VALUES (
														'". serialize($page_title) ."',
														'". serialize($page_description) ."',
														'". serialize($page_keywords) ."',
														'". serialize($page_name) ."',
														'". serialize($page_h1) ."',
														'". $page_access ."',
														'". serialize($page_content) ."',
														'". $page_comments ."',
														'". $page_ratings ."'
					)"
				);
				// $page_id = mysql_insert_id();
				$page_id = _DB::$linkes->insert_id;

			} // UPDATE ILI INSERT


			$viewcompanent = viewcompanent("custom_pages", "name");
			$seourl_component = $viewcompanent['components_id'];


			if (empty($page_alias)) {
				$page_alias = autocrateseourls($page_name[LOCALESHORT]);
			} else {
				$page_alias = autocrateseourls($page_alias);
			}

			$seourl_url = (empty($page_alias) ? "page". $page_id .".php" : $page_alias);
			$seourl_filedid = $page_id;

			$viewseourl = viewseourl($seourl_url, "url");

			if ($viewseourl['seourl_url']==$seourl_url) {
				if (($viewseourl['seourl_filedid']==$seourl_filedid) && ($viewseourl['seourl_component']==$seourl_component)) {
					$seourl_url = $seourl_url;
				} else {
					$seourl_url = "page". $page_id .".php";
				}
			}  // Yesli URL YEst


			$page_alias = $seourl_url;


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


			 // add_link BEGIN
			if (isset($_POST['add_link'])) {

				$data = dbarray(dbquery(
							"SELECT link_order
							FROM ". DB_SITE_LINKS ."
							ORDER BY link_order DESC LIMIT 1"));
				$link_order = $data['link_order'] + 1;
				$result = dbquery(
					"INSERT INTO ". DB_SITE_LINKS ." (
														link_name,
														link_url,
														link_visibility,
														link_position,
														link_window,
														link_order
					) VALUES (
														'".serialize($page_name)."',
														'".$page_alias."',
														'".$page_access."',
														'1',
														'0',
														'".$link_order."'
					)"
				);
			} // add_link END

			////////// redirect
			if ($_GET['action']=="edit") {
				redirect(FUSION_SELF . $aidlink ."&status=edit&id=". $page_id ."&url=". $page_alias, false);
			} else {
				redirect(FUSION_SELF . $aidlink ."&status=add&id=". $page_id ."&url=". $page_alias, false);
			} ////////// redirect

		} // Yesli Error yest
	} // Yesli POST


	$user_groups = getusergroups();
	$access_opts = "";
	$sel = "";
	while (list($key, $user_group) = each($user_groups)) {
		$sel = ($cat_access == $user_group['0'] ? " selected='selected'" : "");
		$access_opts .= "<option value='". $user_group['0'] ."'$sel>". $user_group['1'] ."</option>\n";
	}
	

	// echo "<a href='". FUSION_SELF . $aidlink ."' class='go_back'>". $locale['011'] ."</a><br />\n";
?>

	<form name='inputform' method='POST' action='<?php echo FUSION_SELF . $aidlink; ?>&action=<?php echo $_GET['action'];?><?php echo (isset($_GET['id']) && isnum($_GET['id']) ? "&id=". $_GET['id'] : ""); ?>'>
		<table class="form_table">
			<tr>
				<td colspan="2"><a href="#" id="seo_tr_button">SEO</a></td>
			</tr>
			<tr class="seo_tr">
				<td colspan="2">
					<label for="page_title_<?php echo LOCALESHORT; ?>"><?php echo $locale['501']; ?></label>
					<?php foreach ($languages as $key => $value) { ?>
					<?php if ($lang_say>1) { ?><span class="local_name lang_<?php echo $value['languages_short']; ?>"><?php echo $value['languages_name']; ?></span><?php } ?>
					<input type="text" name="page_title[<?php echo $value['languages_short']; ?>]" id="page_title_<?php echo $value['languages_short']; ?>" value="<?php echo $page_title[$value['languages_short']]; ?>" class="textbox" style="width:98%;" /><br />
					<?php } // foreach languages ?>
				</td>
			</tr>
			<tr class="seo_tr">
				<td colspan="2">
					<label for="page_description_<?php echo LOCALESHORT; ?>"><?php echo $locale['502']; ?></label>
					<?php foreach ($languages as $key => $value) { ?>
					<?php if ($lang_say>1) { ?><span class="local_name lang_<?php echo $value['languages_short']; ?>"><?php echo $value['languages_name']; ?></span><?php } ?>
					<input type="text" name="page_description[<?php echo $value['languages_short']; ?>]" id="page_description_<?php echo $value['languages_short']; ?>" value="<?php echo $page_description[$value['languages_short']]; ?>" class="textbox" style="width:98%;" /><br />
					<?php } // foreach languages ?>
				</td>
			</tr>
			<tr class="seo_tr">
				<td colspan="2">
					<label for="page_keywords_<?php echo LOCALESHORT; ?>"><?php echo $locale['503']; ?></label>
					<?php foreach ($languages as $key => $value) { ?>
					<?php if ($lang_say>1) { ?><span class="local_name lang_<?php echo $value['languages_short']; ?>"><?php echo $value['languages_name']; ?></span><?php } ?>
					<input type="text" name="page_keywords[<?php echo $value['languages_short']; ?>]" id="page_keywords_<?php echo $value['languages_short']; ?>" value="<?php echo $page_keywords[$value['languages_short']]; ?>" class="textbox" style="width:98%;" /><br />
					<?php } // foreach languages ?>
				</td>
			</tr>
			<tr class="seo_tr">
				<td colspan="2">
					<label for="page_h1_<?php echo LOCALESHORT; ?>"><?php echo $locale['505']; ?></label>
					<?php foreach ($languages as $key => $value) { ?>
					<?php if ($lang_say>1) { ?><span class="local_name lang_<?php echo $value['languages_short']; ?>"><?php echo $value['languages_name']; ?></span><?php } ?>
					<input type="text" name="page_h1[<?php echo $value['languages_short']; ?>]" id="page_h1_<?php echo $value['languages_short']; ?>" value="<?php echo $page_h1[$value['languages_short']]; ?>" class="textbox" style="width:98%;" /><br />
					<?php } // foreach languages ?>
				</td>
			</tr>
			<tr class="seo_tr">
				<td colspan="2">
					<label for="page_alias"><?php echo $locale['506']; ?></label>
					<input readonly type="text" name="page_siteurl" id="page_siteurl" value="<?php echo $settings['siteurl']; ?><?php echo ($page_type_alias ? $page_type_alias ."/" : ""); ?>" class="textbox" style="width:200px;" />
					<input type="text" name="page_alias" id="page_alias" value="<?php echo $page_alias; ?>" class="textbox" style="width:385px;" />
				</td>
			</tr>
			<tr class="seo_tr">
				<td colspan="2"></td>
			</tr>
			
			<tr>
				<td colspan="2">
					<label for="page_name_<?php echo LOCALESHORT; ?>"><?php echo $locale['504']; ?> <span>*</span></label>
					<?php foreach ($languages as $key => $value) { ?>
					<?php if ($lang_say>1) { ?><span class="local_name lang_<?php echo $value['languages_short']; ?>"><?php echo $value['languages_name']; ?></span><?php } ?>
					<input type="text" name="page_name[<?php echo $value['languages_short']; ?>]" id="page_name_<?php echo $value['languages_short']; ?>" value="<?php echo $page_name[$value['languages_short']]; ?>" class="textbox" style="width:98%;" /><br />
					<?php } // foreach languages ?>
				</td>
			</tr>
			<tr>
				<td colspan="2">
					<label for="page_content_<?php echo LOCALESHORT; ?>"><?php echo $locale['509']; ?> <span>*</span></label>
					<?php foreach ($languages as $key => $value) { ?>
					<?php if ($lang_say>1) { ?><span class="local_name lang_<?php echo $value['languages_short']; ?>"><?php echo $value['languages_name']; ?></span><?php } ?>
							<textarea id='editor<?php echo $value['languages_id']; ?>' name="page_content[<?php echo $value['languages_short']; ?>]" cols='95' rows='15' class='textbox' style='width:613px'><?php echo $page_content[$value['languages_short']]; ?></textarea><br />
					<?php
						if (!$settings['tinymce_enabled']) {
					?>
							<input type='button' value='<?php echo $locale['431']; ?>' class='button' onclick=\"insertText('page_content', '&lt;!--PAGEBREAK--&gt;');\" />
							<input type='button' value='&lt;?php?&gt;' class='button' onclick=\"addText('page_content', '&lt;?php\\n', '\\n?&gt;');\" />
							<input type='button' value='&lt;p&gt;' class='button' onclick=\"addText('page_content', '&lt;p&gt;', '&lt;/p&gt;');\" />
							<input type='button' value='&lt;br /&gt;' class='button' onclick=\"insertText('page_content', '&lt;br /&gt;');\" />
							<?php
								echo display_html("inputform", "page_content", true);
							?>
					<?php
						} // Yesli Text Editor Net
					?>
					<?php
						}
					?>
				</td>
			</tr>
			<tr>
				<td class='tbl'>
					<label for="page_access"><?php echo $locale['511']; ?></label>
					<select name='page_access' class='selectbox' style='width:200px;'>
						<?php echo $access_opts; ?>
					</select>
				</td>
			</tr>
			<tr>
				<td class='tbl'>
				<?php
					if ($_GET['action']=="add") {
				?>
					<label><input type='checkbox' name='add_link' value='1'<?php echo $addlink; ?> />  <?php echo $locale['514']; ?></label><br />
				<?php
					}
				?>

				<?php
					if ($settings['comments_enabled']!=="0") {
				?>
					<label><input type='checkbox' name='page_comments' value='1'<?php echo $comments; ?> /> <?php echo $locale['512']; ?></label><br />
				<?php
					}
				?>

				<?php
					if ($settings['ratings_enabled']!=="0") {
				?>
					<label><input type='checkbox' name='page_ratings' value='1'<?php echo $ratings; ?> /> <?php echo $locale['513']; ?></label><br />
				<?php
					}
				?>
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
	$viewcompanent = viewcompanent("custom_pages", "name");
	$seourl_component = $viewcompanent['components_id'];


	$result = dbquery("SELECT 
								page_id,
								page_name,
								page_order,
								page_status,
								seourl_url
		FROM ". DB_CUSTOM_PAGES ."
		LEFT JOIN ". DB_SEOURL ." ON seourl_filedid=page_id AND seourl_component=". $seourl_component ."
		ORDER BY page_order");

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
				$page_name = unserialize($data['page_name']);
	?>
			<tr id="listItem_<?php echo $data['page_id']; ?>">
				<td class="list"><img src="<?php echo IMAGES; ?>arrow.png" alt="<?php echo $locale['410']; ?>" class="handle" /></td>
				<td class="name"><a href="<?php echo FUSION_SELF . $aidlink; ?>&action=edit&id=<?php echo $data['page_id']; ?>" title="<?php echo $page_name[LOCALESHORT]; ?>"><?php echo $page_name[LOCALESHORT]; ?></a></td>
				<td class="status">
					<a href="<?php echo FUSION_SELF . $aidlink; ?>&action=status&id=<?php echo $data['page_id']; ?>&status=<?php echo $data['page_status']; ?>" title="<?php echo ($data['page_id'] ? $locale['411'] : $locale['412']); ?>"><img src="<?php echo IMAGES; ?>status/status_<?php echo $data['page_status']; ?>.png" alt="<?php echo ($data['page_id'] ? $locale['411'] : $locale['412']); ?>"></a>
				</td>
				<td class="num"><?php echo $data['page_order']; ?></td>
				<td class="links">
					<a href="<?php echo BASEDIR . $data['seourl_url']; ?>" target="_blank" title="<?php echo $locale['413']; ?>"><img src="<?php echo IMAGES; ?>view.png" alt="<?php echo $locale['413']; ?>"></a>
					<a href="<?php echo FUSION_SELF . $aidlink; ?>&action=edit&id=<?php echo $data['page_id']; ?>" title="<?php echo $locale['414']; ?>"><img src="<?php echo IMAGES; ?>edit.png" alt="<?php echo $locale['414']; ?>"></a>
					<a href="<?php echo FUSION_SELF . $aidlink; ?>&action=del&id=<?php echo $data['page_id']; ?>" title="<?php echo $locale['415']; ?>" onclick="return DeleteOk();"><img src="<?php echo IMAGES; ?>delete.png" alt="<?php echo $locale['415']; ?>"></a>
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