<?php
 
require_once "../includes/maincore.php";

if (!checkrights("BB") || !defined("iAUTH") || !isset($_GET['aid']) || $_GET['aid'] != iAUTH) { redirect("../index.php"); }

require_once THEMES."templates/admin_header.php";
include LOCALE.LOCALESET."admin/bbcodes.php";

if (!isset($_GET['page']) || !isnum($_GET['page'])) { $_GET['page'] = 1; }

global $p_data;

//prevent e_notice warning for included bbcode vars
$textarea_name = ""; $inputform_name = "";

$navigation = "<table width='100%' cellpadding='0' cellspacing='1' class='tbl-border'>\n<tr>\n";
$navigation .= "<td width='50%' align='center' class='".($_GET['page']==1?"tbl2":"tbl1")."'>".($_GET['page']==1?"<strong>":"")."<a href='".FUSION_SELF.$aidlink."&amp;page=1'>".$locale['400']."</a>".($_GET['page']==1?"</strong>":"")."</td>\n";
$navigation .= "<td width='50%' align='center' class='".($_GET['page']==2?"tbl2":"tbl1")."'>".($_GET['page']==2?"<strong>":"")."<a href='".FUSION_SELF.$aidlink."&amp;page=2'>".$locale['401']."</a>".($_GET['page']==2?"</strong>":"")."</td>\n";
$navigation .= "</tr>\n</table>\n";
$navigation .= "<div style='margin:5px'></div>\n";

if ($_GET['page'] == 1) {
	if ((isset($_GET['action']) && $_GET['action'] == "mup") && (isset($_GET['bbcode_id']) && isnum($_GET['bbcode_id']))) {
		$data = dbarray(dbquery("SELECT bbcode_id FROM ".DB_BBCODES." WHERE bbcode_order='".intval($_GET['order'])."'"));
		$result = dbquery("UPDATE ".DB_BBCODES." SET bbcode_order=bbcode_order+1 WHERE bbcode_id='".$data['bbcode_id']."'");
		$result = dbquery("UPDATE ".DB_BBCODES." SET bbcode_order=bbcode_order-1 WHERE bbcode_id='".$_GET['bbcode_id']."'");
		redirect(FUSION_SELF.$aidlink);
	} elseif ((isset($_GET['action']) && $_GET['action'] == "mdown") && (isset($_GET['bbcode_id']) && isnum($_GET['bbcode_id']))) {
		$data = dbarray(dbquery("SELECT bbcode_id FROM ".DB_BBCODES." WHERE bbcode_order='".intval($_GET['order'])."'"));
		$result = dbquery("UPDATE ".DB_BBCODES." SET bbcode_order=bbcode_order-1 WHERE bbcode_id='".$data['bbcode_id']."'");
		$result = dbquery("UPDATE ".DB_BBCODES." SET bbcode_order=bbcode_order+1 WHERE bbcode_id='".$_GET['bbcode_id']."'");
		redirect(FUSION_SELF.$aidlink);
	} elseif (isset($_GET['enable']) && preg_match("/^([a-z0-9_-]){2,50}$/i", $_GET['enable']) && file_exists(INCLUDES."bbcodes/".$_GET['enable']."_bbcode_include_var.php") && file_exists(INCLUDES."bbcodes/".$_GET['enable']."_bbcode_include.php")) {
		if (substr($_GET['enable'], 0, 1)!='!') {
			$data2 = dbarray(dbquery("SELECT MAX(bbcode_order) AS xorder FROM ".DB_BBCODES));
			$order = ($data2['xorder']==0?1:($data2['xorder']+1));
			$result = dbquery("INSERT INTO ".DB_BBCODES." (bbcode_name, bbcode_order) VALUES ('".$_GET['enable']."', '".$order."')");
		} else {
			$result2 = dbcount("(bbcode_id)", DB_BBCODES);
			if (!empty($result2)) {
				$result3 = dbquery("UPDATE ".DB_BBCODES." SET bbcode_order=bbcode_order+1");
			}
			$result3 = dbquery("INSERT INTO ".DB_BBCODES." (bbcode_name, bbcode_order) VALUES ('".$_GET['enable']."', '1')");
		}
		redirect (FUSION_SELF.$aidlink);
	} elseif (isset($_GET['disable']) && isnum($_GET['disable'])) {
		$result = dbquery("DELETE FROM ".DB_BBCODES." WHERE bbcode_id='".$_GET['disable']."'");
		$result = dbquery("SELECT bbcode_order FROM ".DB_BBCODES." ORDER BY bbcode_order");
		$order = 1;
		while ($data = dbarray($result)) {
			$result2 = dbquery("UPDATE ".DB_BBCODES." SET bbcode_order='".$order."' WHERE bbcode_order='".$data['bbcode_order']."'");
			$order++;
		}
		redirect (FUSION_SELF.$aidlink);
	}

	$available_bbcodes = array();
	if ($handle_bbcodes = opendir(INCLUDES."bbcodes/")) {
		while (false !== ($file_bbcodes = readdir($handle_bbcodes))) {
			if (!in_array($file_bbcodes, array("..",".","index.php")) && !is_dir(INCLUDES."bbcodes/".$file_bbcodes)) {
				if (preg_match("/_include.php/i", $file_bbcodes) && !preg_match("/_var.php/i", $file_bbcodes) && !preg_match("/_save.php/i", $file_bbcodes) && !preg_match("/.js/i", $file_bbcodes)) {
					$bbcode_name = explode("_", $file_bbcodes);
					$available_bbcodes[] = $bbcode_name[0];
					unset($bbcode_name);
				}
			}
		}
		closedir($handle_bbcodes);
	}
	sort($available_bbcodes); $enabled_bbcodes = array();
	opentable($locale['402']);
	echo $navigation;
	$result = dbquery("SELECT * FROM ".DB_BBCODES." ORDER BY bbcode_order");
	if (dbrows($result)) {
		echo "<div style='width:100%;height:250px;overflow:auto'>\n";
		echo "<table width='100%' cellpadding='0' cellspacing='1' class='tbl-border'>\n<tr>\n";
		echo "<td width='1%' class='tbl2' style='white-space:nowrap'><strong>".$locale['403']."</strong></td>\n";
		echo "<td width='1%' class='tbl2' style='white-space:nowrap'><strong>".$locale['404']."</strong></td>\n";
		echo "<td class='tbl2'><strong>".$locale['405']."</strong></td>\n";
		echo "<td class='tbl2'><strong>".$locale['406']."</strong></td>\n";
		echo "<td align='center' colspan='2' width='1%' class='tbl2' style='white-space:nowrap'><strong>".$locale['407']."</strong></td>\n";
		echo "<td width='1%' class='tbl2' style='white-space:nowrap'></td>\n";
		echo "</tr>\n";
		$lp=0;
		$ps = 1; $i = 1;
		$numrows = dbcount("(bbcode_id)", DB_BBCODES);
		while ($data = dbarray($result)) {
			if ($numrows != 1) {
				$up = $data['bbcode_order'] - 1;
				$down = $data['bbcode_order'] + 1;
				if ($i == 1) {
					$up_down = " <a href='".FUSION_SELF.$aidlink."&amp;action=mdown&amp;bbcode_id=".$data['bbcode_id']."&amp;order=$down'><img src='".get_image("down")."' alt='".$locale['408']."' title='".$locale['408']."' style='border:0px;' /></a>\n";
				} else if ($i < $numrows) {
					$up_down = " <a href='".FUSION_SELF.$aidlink."&amp;action=mup&amp;bbcode_id=".$data['bbcode_id']."&amp;order=$up'><img src='".get_image("up")."' alt='".$locale['409']."' title='".$locale['409']."' style='border:0px;' /></a>\n";
					$up_down .= " <a href='".FUSION_SELF.$aidlink."&amp;action=mdown&amp;bbcode_id=".$data['bbcode_id']."&amp;order=$down'><img src='".get_image("down")."' alt='".$locale['408']."' title='".$locale['408']."' style='border:0px;' /></a>\n";
				} else {
					$up_down = " <a href='".FUSION_SELF.$aidlink."&amp;action=mup&amp;bbcode_id=".$data['bbcode_id']."&amp;order=$up'><img src='".get_image("up")."' alt='".$locale['409']."' title='".$locale['409']."' style='border:0px;' /></a>\n";
				}
			} else {
				$up_down = "";
			}
			$i++;

			$lp++;
			$enabled_bbcodes[] = $data['bbcode_name'];
			if (file_exists(INCLUDES."bbcodes/images/".$data['bbcode_name'].".png")) {
				$bbcode_image = "<img src='".INCLUDES."bbcodes/images/".$data['bbcode_name'].".png' alt='".$data['bbcode_name']."' style='border:1px solid black' />\n";
			} else if (file_exists(INCLUDES."bbcodes/images/".$data['bbcode_name'].".gif")) {
				$bbcode_image = "<img src='".INCLUDES."bbcodes/images/".$data['bbcode_name'].".gif' alt='".$data['bbcode_name']."' style='border:1px solid black' />\n";
			} else if (file_exists(INCLUDES."bbcodes/images/".$data['bbcode_name'].".jpg")) {
				$bbcode_image = "<img src='".INCLUDES."bbcodes/images/".$data['bbcode_name'].".jpg' alt='".$data['bbcode_name']."' style='border:1px solid black' />\n";
			} else {
				$bbcode_image = "-";
			}
			$cls = ($lp % 2 == 0 ? "tbl2" : "tbl1");
			echo "<tr>\n";
			if (file_exists(LOCALE.LOCALESET."bbcodes/".$data['bbcode_name'].".php")) {
				include (LOCALE.LOCALESET."bbcodes/".$data['bbcode_name'].".php");
			} elseif (file_exists(LOCALE."English/bbcodes/".$data['bbcode_name'].".php")) {
				include (LOCALE."English/bbcodes/".$data['bbcode_name'].".php");
      }
			include INCLUDES."bbcodes/".$data['bbcode_name']."_bbcode_include_var.php";
			echo "<td width='1%' class='$cls' style='white-space:nowrap'>".ucwords($data['bbcode_name'])."</td>\n";
			echo "<td align='center' width='1%' class='$cls' style='white-space:nowrap'>".$bbcode_image."</td>\n";
			echo "<td class='$cls'>".$__BBCODE__[0]['description']."</td>\n";
			echo "<td class='$cls'>".$__BBCODE__[0]['usage']."</td>\n";
			unset ($__BBCODE__);
			echo "<td align='center' width='1%' class='$cls' style='white-space:nowrap'>".$data['bbcode_order']."</td>\n";
			echo "<td align='center' width='1%' class='$cls' style='white-space:nowrap'>".$up_down."</td>\n";
			echo "<td align='center' width='1%' class='$cls' style='white-space:nowrap'><a href='".FUSION_SELF.$aidlink."&amp;disable=".$data['bbcode_id']."'>".$locale['410']."</a></td>\n";
			echo "</tr>\n";
		}
		echo "</table>\n";
		echo "</div>\n";
	} else {
		echo "<div style='text-align:center'>".$locale['411']."</div>\n";
	}
	closetable();

	$enabled = dbcount("(bbcode_id)", DB_BBCODES);
	opentable($locale['413']);
	if (count($available_bbcodes) != $enabled) {
		echo "<div style='width:100%;height:250px;overflow:auto'>\n";
		echo "<table width='100%' cellpadding='0' cellspacing='1' class='tbl-border'>\n<tr>\n";
		echo "<td width='1%' class='tbl2' style='white-space:nowrap'><strong>".$locale['403']."</strong></td>\n";
		echo "<td width='1%' class='tbl2' style='white-space:nowrap'><strong>".$locale['404']."</strong></td>\n";
		echo "<td class='tbl2'><strong>".$locale['405']."</strong></td>\n";
		echo "<td class='tbl2'><strong>".$locale['406']."</strong></td>\n";
		echo "<td width='1%' class='tbl2' style='white-space:nowrap'></td>\n";
		echo "</tr>\n";
		$xx=0;
		for ($lp=0; $lp < count($available_bbcodes); $lp++) {
			$__BBCODE__ = "";
			if (!in_array($available_bbcodes[$lp], $enabled_bbcodes)) {
				if (file_exists(INCLUDES."bbcodes/images/".$available_bbcodes[$lp].".png")) {
					$bbcode_image = "<img src='".INCLUDES."bbcodes/images/".$available_bbcodes[$lp].".png' alt='".$available_bbcodes[$lp]."' style='border:1px solid black' />\n";
				} else if (file_exists(INCLUDES."bbcodes/images/".$available_bbcodes[$lp].".gif")) {
					$bbcode_image = "<img src='".INCLUDES."bbcodes/images/".$available_bbcodes[$lp].".gif' alt='".$available_bbcodes[$lp]."' style='border:1px solid black' />\n";
				} else if (file_exists(INCLUDES."bbcodes/images/".$available_bbcodes[$lp].".jpg")) {
					$bbcode_image = "<img src='".INCLUDES."bbcodes/images/".$available_bbcodes[$lp].".jpg' alt='".$available_bbcodes[$lp]."' style='border:1px solid black' />\n";
				} else {
					$bbcode_image = "-";
				}
				if (file_exists(LOCALE.LOCALESET."bbcodes/".$available_bbcodes[$lp].".php")) {
					include (LOCALE.LOCALESET."bbcodes/".$available_bbcodes[$lp].".php");
				} elseif (file_exists(LOCALE."English/bbcodes/".$available_bbcodes[$lp].".php")) {
					include (LOCALE."English/bbcodes/".$available_bbcodes[$lp].".php");
				}
				include INCLUDES."bbcodes/".$available_bbcodes[$lp]."_bbcode_include_var.php";
				$cls = ($xx % 2 == 0 ? "tbl2" : "tbl1");
				echo "<tr>\n";
				echo "<td width='1%' class='$cls' style='white-space:nowrap'>".ucwords($available_bbcodes[$lp])."</td>\n";
				echo "<td align='center' width='1%' class='$cls' style='white-space:nowrap'>".$bbcode_image."</td>\n";
				echo "<td class='$cls'>".$__BBCODE__[0]['description']."</td>\n";
				echo "<td class='$cls'>".$__BBCODE__[0]['usage']."</td>\n";
				echo "<td align='center' width='1%' class='$cls' style='white-space:nowrap'><a href='".FUSION_SELF.$aidlink."&amp;enable=".$available_bbcodes[$lp]."'>".$locale['414']."</a></td>\n";
				echo "</tr>\n";
				unset ($__BBCODE__);
				$xx++;
			}
		}
		echo "</table>\n";
		echo "</div>\n";
	} else {
		echo "<div style='text-align:center'>".$locale['416']."</div>\n";
	}
	closetable();
} else if ($_GET['page'] == 2) {
	if (isset($_POST['post_test'])) {
		$test_message = stripinput($_POST['test_message']);
		$smileys_checked = isset($_POST['test_smileys']) || preg_match("#(\[code\](.*?)\[/code\]|\[geshi=(.*?)\](.*?)\[/geshi\]|\[php\](.*?)\[/php\])#si", $test_message) ? " checked='checked'" : "";
		opentable($locale['417']);
		if (!$smileys_checked) {
			echo parseubb(parsesmileys($test_message));
		} else {
			echo parseubb($test_message);
		}
		closetable();
	} else {
		$test_message = "";
		$smileys_checked = "";
	}
	include LOCALE.LOCALESET."comments.php";
	opentable($locale['401']);
	echo $navigation;
	echo "<form name='inputform' method='post' action='".FUSION_SELF.$aidlink."&amp;page=2'>\n";
	echo "<table cellspacing='0' cellpadding='0' class='center'>\n<tr>\n";
	echo "<td align='center' class='tbl'><textarea name='test_message' cols='60' rows='6' class='textbox' style='width:400px'>".$test_message."</textarea><br />\n";
	require_once INCLUDES."bbcode_include.php";
	echo display_bbcodes("400px", "test_message");
	echo "</td>\n</tr>\n<tr>\n";
	echo "<td align='center' class='tbl'><label><input type='checkbox' name='test_smileys' value='1' ".$smileys_checked." />".$locale['418']."</label><br /><br />\n";
	echo "<input type='submit' name='post_test' value='".$locale['401']."' class='button' /></td>\n";
	echo "</tr>\n</table>\n</form>\n";
	closetable();
}

require_once THEMES."templates/footer.php";
?>
