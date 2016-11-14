<?php

	require_once "../includes/maincore.php";

	if (!checkrights("PO") || !defined("iAUTH") || !isset($_GET['aid']) || $_GET['aid'] != iAUTH) { redirect("../index.php"); }

	include LOCALE.LOCALESET."admin/polls.php";

	if ($_GET['action']!="order") {
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
					dbquery("UPDATE ". DB_POLLS ." SET poll_order='". ($position+1) ."' WHERE poll_id='". $item ."'");
				}
			}

			header("Content-Type: text/html; charset=". $locale['charset'] ."\n");
			echo "<div id='close-message'>\n";
			echo "	<div class='success'>". $locale['success_007'] ."</div>\n";
			echo "</div>\n";

		}


	} else if ($_GET['action']=="status") {

		$poll_id = (INT)$_GET['id'];
		$poll_status = (INT)$_GET['status'];
		$poll_status = ($poll_status ? 0 : 1);

		$result = dbquery("UPDATE ". DB_POLLS ." SET
														poll_status='". $poll_status ."'
		WHERE poll_id='". $poll_id ."'");

		redirect(FUSION_SELF.$aidlink."&status=". ($poll_status ? "active" : "deactive") ."&id=". $poll_id, false);

	} else if ($_GET['action']=="del") {

		$result = dbquery("DELETE FROM ". DB_POLLS ." WHERE poll_id='". (INT)$_GET['id'] ."'");
		$result = dbquery("DELETE FROM ". DB_POLL_VOTES ." WHERE vote_poll_id='". (INT)$_GET['id'] ."'");

		redirect(FUSION_SELF . $aidlink ."&status=del&id=". (INT)$_GET['id']);

	} else if ($_GET['action']=="add" || $_GET['action']=="edit") {

		if ($_POST['save']) {

			$poll_title = stripinput($_POST['poll_title']);
			$poll_opt = stripinput($_POST['poll_opt']);
			$poll_access = isnum($_POST['poll_access']) ? (INT)$_POST['poll_access'] : 0;
			$poll_status = isnum($_POST['poll_status']) ? (INT)$_POST['poll_status'] : 1;
			// $poll_order = "";
			$close_poll = (INT)$_POST['close_poll'];
			$poll_started = FUSION_TODAY;
			$poll_ended = ($close_poll ? FUSION_TODAY : "");

		} else if ($_GET['action']=="edit") {

			$result = dbquery("SELECT
										poll_title,
										poll_opt_0,
										poll_opt_1,
										poll_opt_2,
										poll_opt_3,
										poll_opt_4,
										poll_opt_5,
										poll_opt_6,
										poll_opt_7,
										poll_opt_8,
										poll_opt_9,
										poll_started,
										poll_ended,
										poll_access,
										poll_status,
										poll_order
			FROM ". DB_POLLS ."
			WHERE poll_id='" .(INT)$_GET['id'] ."'
			LIMIT 1");
			if (dbrows($result)) {
				$data = dbarray($result);

				$poll_title = unserialize($data['poll_title']);
				$poll_opt[0] = unserialize($data['poll_opt_0']);
				$poll_opt[1] = unserialize($data['poll_opt_1']);
				$poll_opt[2] = unserialize($data['poll_opt_2']);
				$poll_opt[3] = unserialize($data['poll_opt_3']);
				$poll_opt[4] = unserialize($data['poll_opt_4']);
				$poll_opt[5] = unserialize($data['poll_opt_5']);
				$poll_opt[6] = unserialize($data['poll_opt_6']);
				$poll_opt[7] = unserialize($data['poll_opt_7']);
				$poll_opt[8] = unserialize($data['poll_opt_8']);
				$poll_opt[9] = unserialize($data['poll_opt_9']);
				$poll_started = $data['poll_started'];
				$poll_ended = $data['poll_ended'];
				$poll_access = $data['poll_access'];
				$poll_status = $data['poll_status'];
				$poll_order = $data['poll_order'];

			} else {
				redirect(FUSION_SELF . $aidlink);
			} // yesli yest result

		} else {

			$poll_title = "";
			$poll_opt[0] = "";
			$poll_opt[1] = "";
			$poll_opt[2] = "";
			$poll_opt[3] = "";
			$poll_opt[4] = "";
			$poll_opt[5] = "";
			$poll_opt[6] = "";
			$poll_opt[7] = "";
			$poll_opt[8] = "";
			$poll_opt[9] = "";

			$poll_started = FUSION_TODAY;
			$poll_ended = ($close_poll ? FUSION_TODAY : "");
			$poll_access = "";
			$poll_status = "";
			$poll_order = "";

		} // POST save


		if ($_POST['save']) {

			foreach ($languages as $key => $value) {
				if (empty($poll_title[$value['languages_short']])) { $error .= "<div class='error'>". $locale['error_001'] ." - ". $value['languages_name'] ."</div>\n"; }
			}
			foreach ($languages as $key => $value) {
				if (empty($poll_opt[0][$value['languages_short']])) { $error .= "<div class='error'>". $locale['error_002'] ." 1 - ". $value['languages_name'] ."</div>\n"; }
			}
			foreach ($languages as $key => $value) {
				if (empty($poll_opt[1][$value['languages_short']])) { $error .= "<div class='error'>". $locale['error_002'] ." 2 - ". $value['languages_name'] ."</div>\n"; }
			}



			if (isset($error)) {

				echo "	<div class='admin-message'>\n";
				echo "		<div id='close-message'>". $error ."</div>\n";
				echo "	</div>\n";

				$news_image = "";

			} else {







				if ($_GET['action']=="edit") {


					$result = dbquery(
						"UPDATE ". DB_POLLS ." SET
															poll_title='". serialize($poll_title) ."',
															poll_opt_0='". serialize($poll_opt[0]) ."',
															poll_opt_1='". serialize($poll_opt[1]) ."',
															poll_opt_2='". serialize($poll_opt[2]) ."',
															poll_opt_3='". serialize($poll_opt[3]) ."',
															poll_opt_4='". serialize($poll_opt[4]) ."',
															poll_opt_5='". serialize($poll_opt[5]) ."',
															poll_opt_6='". serialize($poll_opt[6]) ."',
															poll_opt_7='". serialize($poll_opt[7]) ."',
															poll_opt_8='". serialize($poll_opt[8]) ."',
															poll_opt_9='". serialize($poll_opt[9]) ."',
															poll_started='". $poll_started ."',
															poll_ended='". $poll_ended ."',
															poll_access='". $poll_access ."',
															poll_status='". $poll_status ."',
															poll_order='". $poll_order ."'
						WHERE poll_id='". (INT)$_GET['id'] ."'"
					);
					$poll_id = (INT)$_GET['id'];

				} else {

					$result = dbquery(
						"INSERT INTO ". DB_POLLS ." (
															poll_title,
															poll_opt_0,
															poll_opt_1,
															poll_opt_2,
															poll_opt_3,
															poll_opt_4,
															poll_opt_5,
															poll_opt_6,
															poll_opt_7,
															poll_opt_8,
															poll_opt_9,
															poll_started,
															poll_ended,
															poll_access,
															poll_status,
															poll_order
						) VALUES (
															'". serialize($poll_title) ."',
															'". serialize($poll_opt[0]) ."',
															'". serialize($poll_opt[1]) ."',
															'". serialize($poll_opt[2]) ."',
															'". serialize($poll_opt[3]) ."',
															'". serialize($poll_opt[4]) ."',
															'". serialize($poll_opt[5]) ."',
															'". serialize($poll_opt[6]) ."',
															'". serialize($poll_opt[7]) ."',
															'". serialize($poll_opt[8]) ."',
															'". serialize($poll_opt[9]) ."',
															'". $poll_started ."',
															'". $poll_ended ."',
															'". $poll_access ."',
															'". $poll_status ."',
															'". $poll_order ."'
						)"
					);
					$poll_id = mysql_insert_id();

				} // UPDATE ILI INSERT



				////////// redirect
				if ($_GET['action']=="edit") {
					redirect(FUSION_SELF . $aidlink ."&status=edit&id=". $poll_id, false);
				} else {
					redirect(FUSION_SELF . $aidlink ."&status=add&id=". $poll_id, false);
				} ////////// redirect


			} // Yesli Error


		} // POST save






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
				<td colspan="2">
					<label for="poll_title_<?php echo LOCALESHORT; ?>"><?php echo $locale['501']; ?></label>
					<?php foreach ($languages as $key => $value) { ?>
					<span class="local_name lang_<?php echo $value['languages_short']; ?>"><?php echo $value['languages_name']; ?></span>
					<input type="text" name="poll_title[<?php echo $value['languages_short']; ?>]" id="poll_title_<?php echo $value['languages_short']; ?>" value="<?php echo $poll_title[$value['languages_short']]; ?>" class="textbox" style="width:656px;" /><br />
					<?php } // foreach languages ?>
				</td>
			</tr>

			<tr>
			<?php $tr_say=0; for ($opt_i=0; $opt_i < 10; $opt_i++) { $opt_say++; $tr_say++; ?>
				<td>
					<label for="poll_opt_<?php echo $opt_i; ?>_<?php echo LOCALESHORT; ?>"><?php echo $locale['502']; ?> <?php echo $opt_say; ?></label>
					<?php foreach ($languages as $key => $value) { ?>
					<span class="local_name lang_<?php echo $value['languages_short']; ?>"><?php echo $value['languages_name']; ?></span>
					<input type="text" name="poll_opt[<?php echo $opt_i; ?>][<?php echo $value['languages_short']; ?>]" id="poll_opt_<?php echo $opt_i; ?>_<?php echo $value['languages_short']; ?>" value="<?php echo $poll_opt[$opt_i][$value['languages_short']]; ?>" class="textbox" style="width:312px;" /><br />
					<?php } // foreach languages ?>
				</td>

				<?php if ($tr_say==2) {
					echo "</tr>\n<tr>\n";
					$tr_say = 0;
				} // tr_say ?>

			<?php  } // for end ?>
			</tr>

			<tr>
				<td>
					<label for="poll_started"><?php echo $locale['504']; ?></label><br />
					<span><?php echo  ($poll_started ? date("d.m.Y", $poll_started) : $locale['506']); ?></span>
				</td>
				<td>
					<label for="poll_ended"><?php echo $locale['505']; ?></label><br />
					<span><?php echo  ($poll_ended ? date("d.m.Y", $poll_ended) : $locale['506']); ?></span>
				</td>
			</tr>
			<tr>
				<td>
					<label for="news_access"><?php echo $locale['507']; ?></label>
					<select name="news_access" id="news_access" class="selectbox" style="width:200px;">
						<?php echo $access_opts; ?>
					</select>
				</td>
				<td>
					<?php if (!$poll_ended && $_GET['action']!="add") { ?>
					<label><input type="checkbox" name="close_poll" value="1"<?php echo ($close_poll ? " checked" : ""); ?> /> <?php echo $locale['503']; ?></label>
					<?php } ?>
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



<?php
	} else {

	if ($_GET['status']) {
		if ($_GET['status']=="add") {

			$message = "<div class='success'>". $locale['success_002'] ." ID: ". intval($_GET['id']) ."</div>\n";
			// $message .= "<div class='success'>". $locale['success_001'] ."<a href='". BASEDIR . $_GET['url'] ."' target='_blank'>". $_GET['url'] ."</a></div>\n";

		} elseif ($_GET['status']=="edit") {

			$message = "<div class='success'>". $locale['success_003'] ." ID: ". intval($_GET['id']) ."</div>\n";
			// $message .= "<div class='success'>". $locale['success_001'] ."<a href='". BASEDIR . $_GET['url'] ."' target='_blank'>". $_GET['url'] ."</a></div>\n";

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
	$result = dbquery("SELECT 
								poll_id,
								poll_title,
								poll_order,
								poll_status
		FROM ". DB_POLLS ."
		ORDER BY poll_order");

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
				$poll_name = unserialize($data['poll_title']);
	?>
			<tr id="listItem_<?php echo $data['poll_id']; ?>">
				<td class="list"><img src="<?php echo IMAGES; ?>arrow.png" alt="<?php echo $locale['410']; ?>" class="handle" /></td>
				<td class="name"><a href="<?php echo FUSION_SELF . $aidlink; ?>&action=edit&id=<?php echo $data['poll_id']; ?>" title="<?php echo $poll_name[LOCALESHORT]; ?>"><?php echo $poll_name[LOCALESHORT]; ?></a></td>
				<td class="status">
					<a href="<?php echo FUSION_SELF . $aidlink; ?>&action=status&id=<?php echo $data['poll_id']; ?>&status=<?php echo $data['poll_status']; ?>" title="<?php echo ($data['poll_id'] ? $locale['411'] : $locale['412']); ?>"><img src="<?php echo IMAGES; ?>status/status_<?php echo $data['poll_status']; ?>.png" alt="<?php echo ($data['poll_id'] ? $locale['411'] : $locale['412']); ?>"></a>
				</td>
				<td class="num"><?php echo $data['poll_order']; ?></td>
				<td class="links">
					<a href="<?php echo FUSION_SELF . $aidlink; ?>&action=edit&id=<?php echo $data['poll_id']; ?>" title="<?php echo $locale['414']; ?>"><img src="<?php echo IMAGES; ?>edit.png" alt="<?php echo $locale['414']; ?>"></a>
					<a href="<?php echo FUSION_SELF . $aidlink; ?>&action=del&id=<?php echo $data['poll_id']; ?>" title="<?php echo $locale['415']; ?>" onclick="return DeleteOk();"><img src="<?php echo IMAGES; ?>delete.png" alt="<?php echo $locale['415']; ?>"></a>
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