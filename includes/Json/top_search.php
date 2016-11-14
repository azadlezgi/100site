<?php
	
include "../maincore.php";

if ($_POST['search_query']) {

	$search_query = stripinput($_POST['search_query']);

	$catalog_array = array();
	
		// echo "<pre>";
		// print_r($limit);
		// echo "</pre>";
		// echo "<hr>";

	$viewcompanent = viewcompanent("catalog", "name");
	$seourl_component = $viewcompanent['components_id'];

	$result = dbquery(
		"SELECT
								catalog_id,
								catalog_name,
								catalog_images,
								catalog_price,
								catalog_valyuta,
								seourl_url
		FROM ". DB_CATALOG ."
		LEFT JOIN ". DB_SEOURL ." ON seourl_filedid=catalog_id AND seourl_component=". $seourl_component ."
		WHERE catalog_status='1'
		AND catalog_name LIKE '%". $search_query ."%'
		AND ". groupaccess('catalog_access') ."
		ORDER BY catalog_order
		LIMIT 0, ". $settings['catalog_per_page'] 
	);
	if (dbrows($result)) {
			$j=0;
			while ($data = dbarray($result)) { $j++;
				$catalog_array[$j]['catalog_id'] = $data['catalog_id'];
				$catalog_array[$j]['catalog_name'] = $data['catalog_name'];
				$catalog_images = $data['catalog_images'];
				if ( $catalog_images ) {
					$catalog_images = explode(",", $catalog_images);
					$catalog_array[$j]['catalog_images'] = IMAGES_C_T . $catalog_images[0];
				} else {
					$catalog_array[$j]['catalog_images'] = IMAGES . "imagenotfound.jpg";
				}
				$catalog_array[$j]['catalog_price'] = viewcena($data['catalog_price'], $data['catalog_valyuta']);
				$catalog_array[$j]['seourl_url'] = $data['seourl_url'];
			} // db whille
		} // db query

		echo json_encode($catalog_array);
		// echo "<pre>";
		// print_r($catalog_array);
		// echo "</pre>";
		// echo "<hr>";

	} // if post
?>