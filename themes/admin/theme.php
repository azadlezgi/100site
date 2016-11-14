<?php

if (!defined("IN_FUSION")) { die("Access Denied"); }

define("THEME_BULLET", "<span class='bullet'></span>");

require_once INCLUDES."theme_functions_include.php";

function render_page($license = false) {
	
global $settings, $main_style, $locale, $mysql_queries_time, $languages, $currency, $aidlink, $userdata;


add_to_footer ("<script type='text/javascript' src='". ADMINTHEME ."js/myscripts.js'></script>");
?>

<div class="main-body">
	<div class="main-bg">

		<!--==============================header=================================-->
		<header>
			<div class="main">
				<div class="showlogo">
					<a href="<?php echo $settings['siteurl']; ?>" target="_blank"><img src="<?php echo (file_exists(IMAGES . $settings['sitebanner']) ? IMAGES . $settings['sitebanner'] : "/themes/admin/images/nologo.png"); ?>" alt="<?php echo $settings['sitename']; ?>" style="border:none;"></a>
				</div>
				<?php // echo showlogo(); ?>
				<?php
					if (iMEMBER) {
						echo "<div class='user_info_panel'>\n";
						$msg_count = dbcount("(message_id)", DB_MESSAGES, "message_to='". $userdata['user_id'] ."' AND message_read='0' AND message_folder='0'");

						echo "<div class='user_name'>\n";
						echo "	<label>". $userdata['user_name'] ."</label>\n";
						echo "	<img src='". IMAGES ."avatars/noavatar50.png' alt='". $userdata['user_name'] ."'>\n";
						echo "	<div class='clear'></div>\n";
						echo "</div>\n";
						echo "<ul>\n";
						echo "	<li class='edit_profile'><a href='". BASEDIR ."edit_profile.php' target='_blank' rel='nofollow'>". $locale['global_120'] ."</a></li>\n";
						echo "	<li class='messages'><a href='". BASEDIR ."messages.php' target='_blank' rel='nofollow'>". $locale['global_121'] . ($msg_count ? "<strong>". $msg_count ."</strong>" : "") ."</a></li>\n";
						echo "	<li class='members'><a href='". BASEDIR ."members.php' target='_blank' rel='nofollow'>". $locale['global_122'] ."</a></li>\n";

						if (iADMIN && (iUSER_RIGHTS != "" || iUSER_RIGHTS != "C")) {
							echo "	<li class='admin'><a href='". ADMIN ."index.php". $aidlink ."'>". $locale['global_123']."</a></li>\n";
						}
						echo "	<li class='login'><a href='". BASEDIR ."login.php?logout=yes' class='side'>". $locale['global_124'] ."</a></li>\n";
						echo "</ul>\n";
					}
				?>
				<div class="clear"></div>
			</div>
		</header>
		<!--==============================//header=================================-->
		
		<!--==============================content================================-->
		<section>
			<div class="main">
				<aside>
					<?php echo LEFT; ?>
					<?php echo RIGHT; ?>
				</aside>
				<article>
					<?php echo U_CONTENT; ?>
					<?php echo CONTENT; ?>
					<?php echo L_CONTENT; ?>
				</article>
				<div class="clear"></div>
			</div>
		</section>
		<!--==============================//content================================-->

		<!--==============================footer=================================-->
		<footer>
			<div class="main">
				<?php echo showrendertime(); ?>
				<?php echo showcopyright(); ?>
				<div class="clear"></div>
			</div>
		</footer>
		<!--==============================//footer=================================-->
	</div>
</div>

<?php
	/*foreach ($mysql_queries_time as $query) {
		echo $query[0]." QUERY: ".$query[1]."<br />";
	}*/
} // function render_page

/* New in v7.02 - render comments */
function render_comments($c_data, $c_info){
	global $locale, $settings;
	opentable($locale['c100']);
	if (!empty($c_data)){
		echo "<div class='comments floatfix'>\n";
			$c_makepagenav = '';
			if ($c_info['c_makepagenav'] !== FALSE) { 
			echo $c_makepagenav = "<div style='text-align:center;margin-bottom:5px;'>".$c_info['c_makepagenav']."</div>\n"; 
		}
			foreach($c_data as $data) {
			$comm_count = "<a href='".FUSION_REQUEST."#c".$data['comment_id']."' id='c".$data['comment_id']."' name='c".$data['comment_id']."'>#".$data['i']."</a>";
			echo "<div class='tbl2 clearfix floatfix'>\n";
			if ($settings['comments_avatar'] == "1") { echo "<span class='comment-avatar'>".$data['user_avatar']."</span>\n"; }
			echo "<span style='float:right' class='comment_actions'>".$comm_count."\n</span>\n";
			echo "<span class='comment-name'>".$data['comment_name']."</span>\n<br />\n";
			echo "<span class='small'>".$data['comment_datestamp']."</span>\n";
	if ($data['edit_dell'] !== false) { echo "<br />\n<span class='comment_actions'>".$data['edit_dell']."\n</span>\n"; }
			echo "</div>\n<div class='tbl1 comment_message'>".$data['comment_message']."</div>\n";
		}
		echo $c_makepagenav;
		if ($c_info['admin_link'] !== FALSE) {
			echo "<div style='float:right' class='comment_admin'>".$c_info['admin_link']."</div>\n";
		}
		echo "</div>\n";
	} else {
		echo $locale['c101']."\n";
	}
	closetable();   
} // function render_comments

function render_news($subject, $news, $info) {

	echo "<div class='render_news'>\n";
	echo "	<h1>". $subject ."</h1>\n";
	echo "	<div class='main-body'>". $info['cat_image'] . $news ."</div>\n";
	echo "	<div class='newsposter'>";
	echo newsposter($info," &middot;").newscat($info," &middot;").newsopts($info,"&middot;").itemoptions("N",$info['news_id']);
	echo "	</div>\n";
	echo "</div>\n";

} // function render_news

function render_article($subject, $article, $info) {
	
	echo "<div class='render_article'>\n";
	echo "	<h1>". $subject ."</h1>\n";
	echo "	<div class='main-body'>". ($info['article_breaks'] == "y" ? nl2br($article) : $article)."</div>\n";
	echo "	<div class='articleposter'>";
	echo articleposter($info," &middot;").articlecat($info," &middot;").articleopts($info,"&middot;").itemoptions("A",$info['article_id']);
	echo "	</div>\n";
	echo "</div>\n";

} // function render_article

function opentable($title) {
?>
	<div class='render_page'>
		<h1><?php echo $title; ?></h1>
		<div class='main-body'>
<?php
} // function opentable

function closetable() {
?>
		</div>
	</div>
<?php
} // function closetable

function openside($title, $collapse = false, $state = "on") {
	global $panel_collapse; $panel_collapse = $collapse;
?>
	<div class='openside'>
		<?php if ($title) { ?><h4><?php echo $title; ?></h4><?php } ?>
		<div class='side-body'>
			<?php if ($collapse == true) { echo panelstate($state, $boxname); } ?>
<?php
} // function openside

function closeside() {
	global $panel_collapse;
	if ($panel_collapse == true) { echo "</div>\n"; }
?>
		</div>
	</div>
<?php
} // function closeside
?>