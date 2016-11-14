<?php

if (!defined("IN_FUSION")) { die("Access Denied"); }

function check_panel_status($side) {

	global $settings;

	$exclude_list = "";

	if ($side == "left") {
		if ($settings['exclude_left'] != "") {
			$exclude_list = explode("\r\n", $settings['exclude_left']);
		}
	} elseif ($side == "upper") {
		if ($settings['exclude_upper'] != "") {
			$exclude_list = explode("\r\n", $settings['exclude_upper']);
		}
	} elseif ($side == "lower") {
		if ($settings['exclude_lower'] != "") {
			$exclude_list = explode("\r\n", $settings['exclude_lower']);
		}
	} elseif ($side == "right") {
		if ($settings['exclude_right'] != "") {
			$exclude_list = explode("\r\n", $settings['exclude_right']);
		}
	}

	if (is_array($exclude_list)) {
		$script_url = explode("/", $_SERVER['PHP_SELF']);
		$url_count = count($script_url);
		$base_url_count = substr_count(BASEDIR, "/")+1;
		$match_url = "";
		while ($base_url_count != 0) {
			$current = $url_count - $base_url_count;
			$match_url .= "/".$script_url[$current];
			$base_url_count--;
		}
		if (!in_array($match_url, $exclude_list) && !in_array($match_url.(FUSION_QUERY ? "?".FUSION_QUERY : ""), $exclude_list)) {
			return true;
		} else {
			return false;
		}
	} else {
		return true;
	}
}


function showlogo() {
	global $settings;
	ob_start();
	
	echo "<div class='showlogo'>\n";	
	if ($settings['sitebanner']) {
		echo "<a href='". $settings['siteurl'] ."'><img src='". BASEDIR . $settings['sitebanner'] ."' alt='". $settings['sitename'] ."' style='border:none;' /></a>\n";
	} else {
		echo "<a href='". $settings['siteurl'] ."'>". $settings['sitename'] ."</a>\n";
	}	
	echo "</div>\n";
	
	$output = ob_get_contents();
	ob_end_clean();
	return $output;
}


function showbanners1() {
	global $settings;
	ob_start();
	echo "<div class='showbanners1'>\n";
	if ($settings['sitebanner1']) {
		eval("?>".stripslashes($settings['sitebanner1'])."\n<?php ");
	}
	echo "</div>\n";
	$output = ob_get_contents();
	ob_end_clean();
	return $output;
}


function showbanners2() {
	global $settings;
	ob_start();
	echo "<div class='showbanners2'>\n";
	if ($settings['sitebanner2']) {
		eval("?>".stripslashes($settings['sitebanner2'])."\n<?php ");
	}
	echo "</div>\n";
	$output = ob_get_contents();
	ob_end_clean();
	return $output;
}


function showsublinks($sep = "&middot;", $ul_class = "") {
	$srespar = dbquery(
		"SELECT link_parent FROM ".DB_SITE_LINKS."
		WHERE link_parent!='0' AND link_url='". START_PAGE ."' AND (link_position='2' || link_position='3')"
	);
	if (dbrows($srespar)) {
		$sdatapar = dbarray($srespar);
	} // db query

	$sres = dbquery(
		"SELECT link_id, link_name, link_url, link_parent, link_window, link_visibility FROM ".DB_SITE_LINKS."
		WHERE link_parent='0' AND (link_position='2' || link_position='3') ORDER BY link_order"
	);
	$num_rows = mysql_num_rows($sres); 
	if (dbrows($sres)) {
		$i = 0;
		$li_class = "";
		$res = "<ul". ($ul_class ? " class='". $ul_class ."'" : "") .">\n";
		while ($sdata = dbarray($sres)) { $i++;
			if (checkgroup($sdata['link_visibility'])) {
				$li_class = "li_class". $i;
				if ($i == 1) { $li_class .= " first-link"; }
				if ($i == $num_rows) { $li_class .= " last-link"; }
				if (START_PAGE == $sdata['link_url']) { $li_class .= " current-link"; }
				if ($sdatapar['link_parent'] == $sdata['link_id']) { $li_class .= " parent-link"; }
				$href = ($sdata['link_url']=="---" ? "href='javascript:void(0)'" : "href='". (preg_match("!^(ht|f)tp(s)?://!i", $sdata['link_url']) ? $sdata['link_url'] : BASEDIR.$sdata['link_url']) ."'" );
				$target_blank = ($sdata['link_window']==1 ? " target='_blank'" : "");

				$res .= "<li". ($li_class ? " class='". $li_class ."'" : "") .">". $sep ."<a ". $href . $target_blank ."><span></span><b>".parseubb(unserialize($sdata['link_name'])[LOCALESHORT], "b|i|u|color|img")."</b></a><div class='indiqator'></div>";

				$sressub = dbquery(
					"SELECT link_id, link_name, link_url, link_parent, link_window, link_visibility FROM ".DB_SITE_LINKS."
					WHERE link_parent!='0' AND link_parent='". $sdata['link_id'] ."' AND (link_position='2' || link_position='3') ORDER BY link_order"
				);
				$num_rowssub = mysql_num_rows($sressub); 
				if (dbrows($sressub)) {
					$isub = 0;
					$li_classsub = "";
					$num_rowssub = mysql_num_rows($sressub);
					$res .= "<ul class='subul_class". $i ."'>\n";
					while ($sdatasub = dbarray($sressub)) { $isub++;
						if (checkgroup($sdatasub['link_visibility'])) {
							$li_classsub = "subli_class". $isub;
							if ($isub == 1) { $li_classsub .= " first-link"; }
							if ($isub == $num_rowssub) { $li_classsub .= " last-link"; }
							if (START_PAGE == $sdatasub['link_url']) { $li_classsub .= " current-link"; }
							$hrefsub = ($sdatasub['link_url']=="---" ? "href='javascript:void(0)'" : "href='". (preg_match("!^(ht|f)tp(s)?://!i", $sdatasub['link_url']) ? $sdatasub['link_url'] : BASEDIR.$sdatasub['link_url']) ."'" );
							$target_blanksub = ($sdatasub['link_window']==1 ? " target='_blank'" : "");
							$res .= "<li". ($li_classsub ? " class='". $li_classsub ."'" : "") .">". $sep ."<a ". $hrefsub . $target_blanksub .">".parseubb(unserialize($sdatasub['link_name'])[LOCALESHORT], "b|i|u|color|img")."</a></li>\n";
						} // checkgroup
					} // while
					$res .= "</ul>\n";
				} // dbquery

				$res .= "</li>\n";

			} // checkgroup
		} // while
		$res .= "</ul>\n";
		$res .= "<div class='clear'></div>\n";
		return $res;
	}
}

function showsubdate() {
	global $settings;
	return ucwords(showdate($settings['subheaderdate'], time()));
}

function newsposter($info, $sep = "", $class = "") {
	global $locale; $res = "";
	$link_class = $class ? " class='$class' " : "";
	$res = THEME_BULLET." <span ".$link_class.">".profile_link($info['user_id'], $info['user_name'], $info['user_status'])."</span> ";
	$res .= $locale['global_071'].showdate("newsdate", $info['news_date']);
	$res .= $info['news_ext'] == "y" || $info['news_allow_comments'] ? $sep."\n" : "\n";
	return "<!--news_poster-->".$res;
}

function newsopts($info, $sep, $class = "") {
	global $locale, $settings; $res = "";
	$link_class = $class ? " class='$class' " : "";
	if (!isset($_GET['readmore']) && $info['news_ext'] == "y") $res = "<a href='news.php?readmore=".$info['news_id']."'".$link_class.">".$locale['global_072']."</a> ".$sep." ";
	if ($info['news_allow_comments'] && $settings['comments_enabled'] == "1") { $res .= "<a href='news.php?readmore=".$info['news_id']."#comments'".$link_class.">".$info['news_comments'].($info['news_comments'] == 1 ? $locale['global_073b'] : $locale['global_073'])."</a> ".$sep." "; }
	if ($info['news_ext'] == "y" || ($info['news_allow_comments'] && $settings['comments_enabled'] == "1")) { $res .= $info['news_reads'].$locale['global_074']."\n ".$sep; }
	$res .= "<a href='print.php?type=N&amp;item_id=".$info['news_id']."'><img src='".get_image("printer")."' alt='".$locale['global_075']."' style='vertical-align:middle;border:0;' /></a>\n";
	return "<!--news_opts-->".$res;
}

function newscat($info, $sep = "", $class = "") {
	global $locale; $res = "";
	$link_class = $class ? " class='$class' " : "";
	$res .= $locale['global_079'];
	if ($info['cat_id']) {
		$res .= "<a href='news_cats.php?cat_id=".$info['cat_id']."'$link_class>".$info['cat_name']."</a>";
	} else {
		$res .= "<a href='news_cats.php?cat_id=0'$link_class>".$locale['global_080']."</a>";
	}
	return "<!--news_cat-->".$res." $sep ";
}

function articleposter($info, $sep = "", $class = "") {
	global $locale, $settings; $res = "";
	$link_class = $class ? " class='$class' " : "";
	$res = THEME_BULLET." ".$locale['global_070']."<span ".$link_class.">".profile_link($info['user_id'], $info['user_name'], $info['user_status'])."</span>\n";
	$res .= $locale['global_071'].showdate("newsdate", $info['article_date']);
	$res .= ($info['article_allow_comments'] && $settings['comments_enabled'] == "1" ? $sep."\n" : "\n");
	return "<!--article_poster-->".$res;
}

function articleopts($info, $sep) {
	global $locale, $settings; $res = "";
	if ($info['article_allow_comments'] && $settings['comments_enabled'] == "1") { $res = "<a href='articles.php?article_id=".$info['article_id']."#comments'>".$info['article_comments'].($info['article_comments'] == 1 ? $locale['global_073b'] : $locale['global_073'])."</a> ".$sep."\n"; }
	$res .= $info['article_reads'].$locale['global_074']." ".$sep."\n";
	$res .= "<a href='print.php?type=A&amp;item_id=".$info['article_id']."'><img src='".get_image("printer")."' alt='".$locale['global_075']."' style='vertical-align:middle;border:0;' /></a>\n";
	return "<!--article_opts-->".$res;
}

function articlecat($info, $sep = "", $class = "") {
	global $locale; $res = "";
	$link_class = $class ? " class='$class' " : "";
	$res .= $locale['global_079'];
	if ($info['cat_id']) {
		$res .= "<a href='articles.php?cat_id=".$info['cat_id']."'$link_class>".$info['cat_name']."</a>";
	} else {
		$res .= "<a href='articles.php?cat_id=0'$link_class>".$locale['global_080']."</a>";
	}
	return "<!--article_cat-->".$res." $sep ";
}

function itemoptions($item_type, $item_id) {
	global $locale, $aidlink; $res = "";
	if ($item_type == "N") {
		if (iADMIN && checkrights($item_type)) { $res .= "<!--article_news_opts--> &middot; <a href='".ADMIN."news.php".$aidlink."&amp;action=edit&amp;news_id=".$item_id."'><img src='".get_image("edit")."' alt='".$locale['global_076']."' title='".$locale['global_076']."' style='vertical-align:middle;border:0;' /></a>\n"; }
	} elseif ($item_type == "A") {
	if (iADMIN && checkrights($item_type)) { $res .= "<!--article_admin_opts--> &middot; <a href='".ADMIN."articles.php".$aidlink."&amp;action=edit&amp;article_id=".$item_id."'><img src='".get_image("edit")."' alt='".$locale['global_076']."' title='".$locale['global_076']."' style='vertical-align:middle;border:0;' /></a>\n"; }
	}
	return $res;
}

function showrendertime($queries = true) {
	global $locale, $mysql_queries_count, $settings;

	if ($settings['rendertime_enabled'] == 1 || ($settings['rendertime_enabled'] == 2 && iADMIN)) {
		$res = "<div class='showrendertime'>\n";
		$res .= sprintf($locale['global_172'], substr((get_microtime() - START_TIME),0,4));
		$res .= ($queries ? " - $mysql_queries_count ".$locale['global_173'] : "") ."\n";
		$res .= "</div>\n";
		return $res;
	} else {
		return "";
	}
}

function showcopyright($class = "", $nobreak = false) {
	$link_class = " class='". ($class ? $class : "copy") ."'";
	$res = "<div".$link_class.">Powered by <a href='http://www.issohost.com/'>IssoHost</a> copyright &copy; 2007 - ".date("Y")."</div>\n";
	return $res;
}

function showcounter() {
	global $locale, $settings;
	if ($settings['visitorcounter_enabled']) {
		return "<!--counter-->".number_format($settings['counter'])." ".($settings['counter'] == 1 ? $locale['global_170'] : $locale['global_171']);
	} else {
		return "";
	}
}

function panelbutton($state, $bname) {
	$bname = preg_replace("/[^a-zA-Z0-9\s]/", "_", $bname);
	if (isset($_COOKIE["fusion_box_".$bname])) {
		if ($_COOKIE["fusion_box_".$bname] == "none") {
			$state = "off";
		} else {
			$state = "on";
		}
	}
	return "<img src='".get_image("panel_".($state == "on" ? "off" : "on"))."' id='b_".$bname."' class='panelbutton' alt='' onclick=\"javascript:flipBox('".$bname."')\" />";
}

function panelstate($state, $bname) {
	$bname = preg_replace("/[^a-zA-Z0-9\s]/", "_", $bname);
	if (isset($_COOKIE["fusion_box_".$bname])) {
		if ($_COOKIE["fusion_box_".$bname] == "none") {
			$state = "off";
		} else {
			$state = "on";
		}
	}
	return "<div id='box_".$bname."'".($state == "off" ? " style='display:none'" : "").">\n";
}

// v6 compatibility
function opensidex($title, $state = "on") {

	openside($title, true, $state);

}

function closesidex() {

	closeside();

}

function tablebreak() {
	return true;
}
?>