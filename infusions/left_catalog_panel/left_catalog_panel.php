<?php if (!defined("IN_FUSION")) { die("Access Denied"); } ?>
<div class="left_catalog">
	<?php openside("<i class='fa fa-bars'></i> Каталог"); ?>
	<ul class="left_catalog_links">
<?php
	$views_shyas = 30;
	if ( (FUSION_URI!="/catalog") && (preg_match("(\/catalog\/)", FUSION_URI)) ) {
		$viewcompanent = viewcompanent("article_cats", "name");
		$seourl_component = $viewcompanent['components_id'];

		// $seourl_url = substr(FUSION_URI, 1);
		$seourl_url = FUSION_URI;
		$seourl_url = explode("/catalog/", $seourl_url);
		$seourl_url_sub = explode("/", $seourl_url[1]);
		if ($seourl_url_sub[0]) { $seourl_url = $seourl_url_sub[0]; }
		else { $seourl_url = $seourl_url[1]; }
		$seourl_url = explode("?", $seourl_url);
		$seourl_url = $seourl_url[0];


		$resulseourl = dbquery("SELECT
												seourl_filedid,
												seourl_url
										FROM ". DB_SEOURL ."
										WHERE seourl_url='catalog/". $seourl_url ."'
										AND seourl_component=". $seourl_component ."");
		$dataseourl = dbarray($resulseourl);


		// echo "<pre>";
		// print_r($seourl_url);
		// echo "</pre>";
		// echo "<hr>";

		$viewcompanent = viewcompanent("articles", "name");
		$seourl_component = $viewcompanent['components_id'];

		$resultarticle = dbquery("SELECT
												article_id,
												article_name,
												seourl_url
										FROM ". DB_ARTICLES ."
										LEFT JOIN ". DB_SEOURL ." ON seourl_filedid=article_id AND seourl_component=". $seourl_component ."
										WHERE article_cat='". $dataseourl['seourl_filedid'] ."'
										AND article_status='1'
										AND article_date<'". FUSION_TODAY ."'
										ORDER BY `article_order`");
		if (dbrows($resultarticle)) {
			$say=0;
			while ($dataarticle = dbarray($resultarticle)) { $say++;
				$article_name = unserialize($dataarticle['article_name']);
				echo "<li class='". str_replace("/", "_", $dataarticle['seourl_url']) . (FUSION_URI=="/". $dataarticle['seourl_url'] ? " active" : "") . ($say==$views_shyas ? " views_shyas" : "") ."'". ($say>$views_shyas ? " style='display:none;'" : "") ."><a href='". $dataarticle['seourl_url'] ."'>". $article_name[LOCALESHORT] ." <i class='fa fa-angle-right'></i></a></li>\n";
			}
		}
	} else {

		$res_art_count = dbquery("SELECT
												article_id,
												article_cat
										FROM ". DB_ARTICLES ."
										WHERE article_status='1'
										AND article_date<'". FUSION_TODAY ."'");
		if (dbrows($res_art_count)) {
			$art_count_array = array();
			while ($data_art_count = dbarray($res_art_count)) {
				$art_count_array[$data_art_count['article_id']] = $data_art_count['article_cat'];
			}
		}

		// echo "<pre>";
		// print_r($art_count_array);
		// echo "</pre>";
		// echo "<hr>";


		$viewcompanent = viewcompanent("article_cats", "name");
		$seourl_component = $viewcompanent['components_id'];

		$resultarticle_cat = dbquery("SELECT
												article_cat_id,
												article_cat_name,
												seourl_url
										FROM ". DB_ARTICLE_CATS ."
										LEFT JOIN ". DB_SEOURL ." ON seourl_filedid=article_cat_id AND seourl_component=". $seourl_component ."
										ORDER BY `article_cat_order`");
		if (dbrows($resultarticle_cat)) {
			$say=0;
			while ($dataarticle_cat = dbarray($resultarticle_cat)) {
				if ( in_array($dataarticle_cat['article_cat_id'], $art_count_array) ) { $say++;
				$article_cat_name = unserialize($dataarticle_cat['article_cat_name']);
					echo "<li class='". str_replace("/", "_", $dataarticle_cat['seourl_url']) . (FUSION_URI=="/". $dataarticle_cat['seourl_url'] ? " active" : "") . ($say==$views_shyas ? " views_shyas" : "") ."'". ($say>$views_shyas ? " style='display:none;'" : "") .">". $article_count ."<a href='". $dataarticle_cat['seourl_url'] ."'>". $article_cat_name[LOCALESHORT] ." <i class='fa fa-angle-right'></i></a></li>\n";
				}
			}
		}
	}
?>
	</ul>
	<?php if ($say>$views_shyas) { ?>
	<a href="#" class="view_all">ещё<i class='fa fa-caret-down'></i></a>
	<?php
add_to_footer ("<script type='text/javascript'>
	<!--
	$(document).ready(function() {
		$( '.left_catalog .view_all' ).click(function() {
			$( '.left_catalog ul li' ).removeClass( 'views_shyas' );
			$( '.left_catalog ul li' ).show( 'slow' );
			$( '.left_catalog .view_all' ).css( 'display', 'none' );
			return false;
		});
	});
	//-->
</script>");
	} ?>
	<?php closeside(); ?>
</div>