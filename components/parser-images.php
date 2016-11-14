<?php

if (!defined("IN_FUSION")) { die("Access Denied"); }

if ($_GET['pass']!="7872809u") { die("Access Denied"); }





echo "Step IMAGES<hr>";

$resultarticle = dbquery("SELECT
											article_id,
											article_name,
											article_image
						FROM ". DB_ARTICLES ."");
if (dbrows($resultarticle)) {
	$say = 0;
	$data_array = array();
	while ($dataarticle = dbarray($resultarticle)) { $say++;
		$article_name = unserialize($dataarticle['article_name']);

		$data_array[$say]['article_id'] = $dataarticle['article_id'];
		$data_array[$say]['article_name'] = $article_name[LOCALESHORT];
		$data_array[$say]['article_images'] = $dataarticle['article_image'];
	} // db whille
} // db query


foreach ($data_array as $data_key => $data_value) {

	// echo "<pre>";
	// print_r($data_value);
	// echo "</pre>";
	// echo "<hr>";

	$article_image_avay = $_SERVER['DOCUMENT_ROOT'] ."/sites/site_stroy/uploads/articles/". $data_value['article_images'];
	$article_image_avay_t = $_SERVER['DOCUMENT_ROOT'] ."/sites/site_stroy/uploads/articles/thumbs/". $data_value['article_images'];
	$article_image_patch_t = IMAGES_A_T . $data_value['article_images'];
	$article_image_name = $data_value['article_images'];
	if ($article_image_name) {

		if (file_exists( $article_image_avay )) {

			// copy($article_image_avay, $article_image_patch);
			// copy($article_image_avay_t, $article_image_patch_t);

			// unlink($article_image_avay);
			// unlink($article_image_avay_t);

			echo "<font color='green'>Файл существует!</font><br>";
		} else {
			echo "<font color='red'>Файл не существует!</font><br>";
		}

	} // Photo Upload

} // foreach data_array




echo "OK!";