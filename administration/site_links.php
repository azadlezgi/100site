<?php

require_once "../includes/maincore.php";

if (!checkrights("SL") || !defined("iAUTH") || !isset($_GET['aid']) || $_GET['aid'] != iAUTH) { redirect("../index.php"); }

require_once THEMES."templates/admin_header.php";
include LOCALE.LOCALESET."admin/sitelinks.php";


if ((isset($_GET['action']) && $_GET['action'] == "delete") && (isset($_GET['link_id']) && isnum($_GET['link_id']))) {

	$data = dbarray(dbquery("SELECT link_order FROM ".DB_SITE_LINKS." WHERE link_id='".$_GET['link_id']."'"));
	$result = dbquery("UPDATE ". DB_SITE_LINKS ." SET link_order=link_order-1 WHERE link_order>'".$data['link_order']."'");
	$result = dbquery("UPDATE ". DB_SITE_LINKS ." SET link_parent='0' WHERE link_parent = '". (INT)$_GET['link_id'] ."'");
	$result = dbquery("DELETE FROM ".DB_SITE_LINKS." WHERE link_id='".$_GET['link_id']."'");
	
	redirect(FUSION_SELF . $aidlink ."&status=del&id=". (INT)$_GET['link_id']);

} else if ($_GET['action']=="add" || $_GET['action']=="edit") {




	if (isset($_POST['savelink'])) {
		$link_name = stripinput($_POST['link_name']);
		$link_url = stripinput($_POST['link_url']);
		$link_parent = stripinput($_POST['link_parent']);
		$link_visibility = isnum($_POST['link_visibility']) ? $_POST['link_visibility'] : "0";
		$link_position = isset($_POST['link_position']) ? $_POST['link_position'] : "0";
		$link_window = isset($_POST['link_window']) ? $_POST['link_window'] : "0";
		$link_order = isnum($_POST['link_order']) ? $_POST['link_order'] : "";
		if ($link_name && $link_url) {
			if ((isset($_GET['action']) && $_GET['action'] == "edit") && (isset($_GET['link_id']) && isnum($_GET['link_id']))) {
				$old_link_order = dbresult(dbquery("SELECT link_order FROM ".DB_SITE_LINKS." WHERE link_id='".$_GET['link_id']."'"), 0);
				if ($link_order > $old_link_order) {
					$result = dbquery("UPDATE ".DB_SITE_LINKS." SET link_order=link_order-1 WHERE link_order>'$old_link_order' AND link_order<='$link_order'");
				} elseif ($link_order < $old_link_order) {
					$result = dbquery("UPDATE ".DB_SITE_LINKS." SET link_order=link_order+1 WHERE link_order<'$old_link_order' AND link_order>='$link_order'");
				}
				$result = dbquery("UPDATE ".DB_SITE_LINKS." SET 
																		link_name='". serialize($link_name) ."',
																		link_url='". $link_url ."',
																		link_parent='". $link_parent ."',
																		link_visibility='". $link_visibility ."',
																		link_position='". $link_position ."',
																		link_window='". $link_window ."',
																		link_order='". $link_order ."'
								WHERE link_id='".$_GET['link_id']."'");
				redirect(FUSION_SELF . $aidlink ."&status=edit&id=". $_GET['link_id'], false);
			} else {
				if (!$link_order) { $link_order = dbresult(dbquery("SELECT MAX(link_order) FROM ".DB_SITE_LINKS.""), 0) + 1; }
				$result = dbquery("UPDATE ".DB_SITE_LINKS." SET link_order=link_order+1 WHERE link_order>='$link_order'");
				$result = dbquery("INSERT INTO ". DB_SITE_LINKS ." (
																		link_name,
																		link_url,
																		link_parent,
																		link_visibility,
																		link_position,
																		link_window,
																		link_order
								) VALUES (
																		'". serialize($link_name) ."',
																		'". $link_url ."',
																		'". $link_parent ."',
																		'". $link_visibility ."',
																		'". $link_position ."',
																		'". $link_window ."',
																		'". $link_order ."'
								)");

				$link_id = mysql_insert_id();
				// redirect(FUSION_SELF.$aidlink."&status=sn");
				redirect(FUSION_SELF . $aidlink ."&status=add&id=". $link_id, false);
			}
		} else {
			redirect(FUSION_SELF.$aidlink);
		}
	}

	if ((isset($_GET['action']) && $_GET['action'] == "edit") && (isset($_GET['link_id']) && isnum($_GET['link_id']))) {
		$result = dbquery(
							"SELECT
											link_name,
											link_url,
											link_parent,
											link_visibility,
											link_order,
											link_position,
											link_window
							FROM ". DB_SITE_LINKS ."
							WHERE link_id='". (INT)$_GET['link_id'] ."'"
		);
		if (dbrows($result)) {
			$data = dbarray($result);
			$link_id = (INT)$_GET['link_id'];
			$link_name = unserialize($data['link_name']);
			$link_url = $data['link_url'];
			$link_parent = $data['link_parent'];
			$link_visibility = $data['link_visibility'];
			$link_order = $data['link_order'];
			$pos1_check = ($data['link_position']=="1" ? " checked='checked'" : "");
			$pos2_check = ($data['link_position']=="2" ? " checked='checked'" : "");
			$pos3_check = ($data['link_position']=="3" ? " checked='checked'" : "");
			$window_check = ($data['link_window']=="1" ? " checked='checked'" : "");
			$formaction = FUSION_SELF.$aidlink."&amp;action=edit&amp;link_id=".$_GET['link_id'];
			opentable($locale['401']);
		} else {
			redirect(FUSION_SELF.$aidlink);
		}
	} else {
		$link_id = "";
		$link_name = "";
		$link_url = "";
		$link_parent = "";
		$link_visibility = "";
		$link_order = "";
		$pos1_check = "";
		$pos2_check = " checked='checked'";
		$pos3_check = "";
		$window_check = "";
		$formaction = FUSION_SELF.$aidlink;
		opentable($locale['400']);
	}


// parent_opts BEGIN
$parent_opts = "<option value='0'". ($link_parent == "" ? " selected='selected'" : "") .">---</option>\n";
$sel = "";
$result_parent = dbquery(
					"SELECT 
								link_id,
								link_name
					FROM ". DB_SITE_LINKS ."
					WHERE link_parent='0'"
			);
if (dbrows($result_parent)) {
	while($data_parent = dbarray($result_parent)) {
		$parent_opts .= "<option value='". $data_parent['link_id'] ."'". ($link_parent == $data_parent['link_id'] ? " selected='selected'" : "") .">". unserialize($data_parent['link_name'])[LOCALESHORT] ."</option>\n";
	}
}
// parent_opts END


$visibility_opts = ""; $sel = "";
$user_groups = getusergroups();
while(list($key, $user_group) = each($user_groups)){
	$sel = ($link_visibility == $user_group['0'] ? " selected='selected'" : "");
	$visibility_opts .= "<option value='".$user_group['0']."'$sel>".$user_group['1']."</option>\n";
}
require_once INCLUDES."bbcode_include.php";


		echo "<a href='". FUSION_SELF . $aidlink ."' class='go_back btn btn-primary'>". $locale['011'] ."</a><br />\n";
?>

<form name='layoutform' method='post' action='<?php echo FUSION_SELF . $aidlink . ($_GET['action'] ? "&action=". $_GET['action'] : "") . ($_GET['link_id'] ? "&link_id=". (INT)$_GET['link_id'] : ""); ?>'>
	<table cellpadding='0' cellspacing='0' width='100%' class='center'>
		<tbody>
			<tr>
				<td class='tbl'>
					<span class='polya_name'><?php echo $locale['420']; ?></span>
					<?php
						foreach ($languages as $key => $value) {
							if ($languages_count>1) { echo "<span class='local_name lang_". $value['languages_short'] ."'>". $value['languages_name'] ."</span>\n"; }
					?>
							<input type='text' name="link_name[<?php echo $value['languages_short']; ?>]" value="<?php echo $link_name[$value['languages_short']]; ?>" class='textbox' maxlength="500" style='width:50%;' /><br />
							<?php echo display_bbcodes("240px;", "link_name[". $value['languages_short'] ."]", "layoutform", "b|i|u|color|img"); ?>
					<?php
						}
					?>
				</td>
			</tr>
			<tr>
				<td class='tbl'>
					<span class='polya_name'><?php echo $locale['421']; ?></span>
					<input type='text' name='link_url' value='<?php echo $link_url; ?>' maxlength='200' class='textbox' style='width:90%;' />
				</td>
			</tr>
			<tr>
				<td class='tbl'>
					<span class='polya_name'><?php echo $locale['430']; ?></span>
					<select name='link_parent' class='textbox' style='width:150px;'>
						<?php echo $parent_opts; ?>
					</select>
				</td>
			</tr>
			<tr>
				<td class='tbl'>
					<span class='polya_name'><?php echo $locale['422']; ?></span>
					<select name='link_visibility' class='textbox' style='width:150px;'>
						<?php echo $visibility_opts; ?>
					</select>
				</td>
			</tr>
			<tr>
				<td class='tbl'>
					<span class='polya_name'><?php echo $locale['423']; ?></span>
					<input type='text' name='link_order'  value='<?php echo $link_order; ?>' maxlength='3' class='textbox' style='width:40px;' />
				</td>
			</tr>
			<tr>
				<td class='tbl'>
					<span class='polya_name'><?php echo $locale['424']; ?></span>
					<label><input type='radio' name='link_position' value='1'<?php echo $pos1_check; ?> /> <?php echo $locale['425']; ?></label><br />
					<label><input type='radio' name='link_position' value='2'<?php echo $pos2_check; ?> /> <?php echo $locale['426']; ?></label><br />
					<label><input type='radio' name='link_position' value='3'<?php echo $pos3_check; ?> /> <?php echo $locale['427']; ?></label><hr />
					<label><input type='checkbox' name='link_window' value='1'<?php echo $window_check; ?> /> <?php echo $locale['428']; ?></label>
				</td>
			</tr>
			<tr>
				<td class='tbl'>
					<input type='submit' name='savelink' value='<?php echo $locale['429']; ?>' class='button' />
				</td>
			</tr>
		</tbody>
	</table>
</form>

<?php
closetable();



	} else {

	if ($_GET['status']) {
		if ($_GET['status']=="add") {

			$message = "<div class='success'>". $locale['success_002'] ." ID: ". intval($_GET['id']) ."</div>\n";
			//$message .= "<div class='success'>". $locale['success_001'] ."<a href='". BASEDIR . $_GET['url'] ."' target='_blank'>". $_GET['url'] ."</a></div>\n";

		} elseif ($_GET['status']=="edit") {

			$message = "<div class='success'>". $locale['success_003'] ." ID: ". intval($_GET['id']) ."</div>\n";
			//$message .= "<div class='success'>". $locale['success_001'] ."<a href='". BASEDIR . $_GET['url'] ."' target='_blank'>". $_GET['url'] ."</a></div>\n";

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


add_to_head("<script type='text/javascript' src='".INCLUDES."jquery/jquery-ui.js'></script>");
add_to_head("<link rel='stylesheet' href='".THEMES."templates/site_links.css' type='text/css' media='all' />");
add_to_head("<script type='text/javascript'>
$(document).ready(function() {
	$('.site-links').sortable({
		handle : '.handle',
		placeholder: 'state-highlight',
		connectWith: '.connected',
		scroll: true,
		axis: 'y',
		update: function () {
			var ul = $(this),
				order = ul.sortable('serialize'),
				i = 0;
			$('#info').load('site_links_updater.php".$aidlink."&'+order);
			ul.find('.num').each(function(i) {
				$(this).text(i+1);
			});
			ul.find('li').removeClass('tbl2').removeClass('tbl1');
			ul.find('li:odd').addClass('tbl2');
			ul.find('li:even').addClass('tbl1');
			window.setTimeout('closeDiv();',2500);
		}
	});
});
</script>");


	opentable($locale['402']);

	echo "<a href='". FUSION_SELF . $aidlink ."&action=add' class='add_page btn btn-primary'>". $locale['010'] ."</a><br />\n";


	echo "<div id='info'></div>\n";
	echo "<div style='width:100%;' class='panels tbl-border center floatfix'><div class='tbl2'>\n";
	echo "<div style='float:left; padding-left:30px;'><strong>".$locale['440']."</strong></div>\n";
	echo "<div style='float:right; width:100px; text-align:center;'><strong>".$locale['443']."</strong></div>\n";
	echo "<div style='float:right; width:15%; text-align:center;'><strong>".$locale['442']."</strong></div>\n";
	echo "<div style='float:right; width:15%; text-align:center;'><strong>".$locale['441']."</strong></div>\n";
	echo "<div style='clear:both;'></div>\n</div>\n";
	echo "<ul id='site-links' style='list-style: none;' class='site-links connected'>\n";

	$result = dbquery("SELECT 
									link_id,
									link_name,
									link_url,
									link_parent,
									link_visibility,
									link_order,
									link_position
						FROM ". DB_SITE_LINKS ."
						ORDER BY link_order");
	if (dbrows($result)) {
		$i = 0;
		while($data = dbarray($result)) {
			$row_color = ($i % 2 == 0 ? "tbl1" : "tbl2");
			echo "<li id='listItem_".$data['link_id']."' class='". $row_color . ($data['link_parent']==0 ? " parent" : " sub") ."'>\n";
			echo "<div style='float:left; width:30px;'><img src='".IMAGES."arrow.png' alt='move' class='handle' /></div>\n";
			echo "<div style='float:left;' class='link_name'>\n";
			if ($data['link_parent']!=0) { echo "&nbsp;&nbsp;&nbsp;"; }
			if ($data['link_position'] == 3) echo "<i>";
			if (unserialize($data['link_name'])[LOCALESHORT] != "---" && $data['link_url'] == "---") {
				echo "<strong>".parseubb(unserialize($data['link_name'])[LOCALESHORT], "b|i|u|color|img")."</strong>\n";
			} else if (unserialize($data['link_name'])[LOCALESHORT] == "---" && $data['link_url'] == "---") {
				echo "<hr />\n";
			} else {
				if (strstr($data['link_url'], "http://") || strstr($data['link_url'], "https://")) {
					echo "".parseubb(unserialize($data['link_name'])[LOCALESHORT], "b|i|u|color|img")."\n";
				} else {
					echo "".parseubb(unserialize($data['link_name'])[LOCALESHORT], "b|i|u|color|img")."\n";
				}
			}
			if ($data['link_position'] == 3) echo "</i>";
			echo "</div>\n";
			echo "<div style='float:right; width:100px; text-align:center;'>";
			echo "<a href='". BASEDIR.$data['link_url'] ."' target='_blank' title='". unserialize($data['link_name'])[LOCALESHORT] ."'><img src='". IMAGES ."view.png' alt='". unserialize($data['link_name'])[LOCALESHORT] ."'></a> \n";
			echo "<a href='". FUSION_SELF . $aidlink ."&amp;action=edit&amp;link_id=". $data['link_id'] ."' title='". $locale['444'] ."'><img src='". IMAGES ."edit.png' alt='". $locale['444'] ."'></a> \n";
			echo "<a href='". FUSION_SELF . $aidlink ."&amp;action=delete&amp;link_id=". $data['link_id'] ."' onclick=\"return confirm('".$locale['460']."');\" title='". $locale['445'] ."'><img src='". IMAGES ."delete.png' alt='". $locale['445'] ."'></a> \n";
			echo "</div>\n";
			echo "<div class='num' style='float:right; width:15%; text-align:center;'>".$data['link_order']."</div>\n";
			echo "<div style='float:right; width:15%; text-align:center;'>".getgroupname($data['link_visibility'])."</div>\n";
			echo "<div style='clear:both;'></div>\n";
			echo "</li>\n";
			$i++;
		}
	echo "</ul>\n</div>";
	} else {
		echo "<div style='text-align:center;margin-top:5px'>".$locale['446']."</div>\n";
	}
	closetable();

}

require_once THEMES."templates/footer.php";
?>
