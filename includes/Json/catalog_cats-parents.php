<?php
	
	include "../maincore.php";

	if ($_POST['parent']) {

		$viewcompanent = viewcompanent("catalog_cats", "name");
		$seourl_component = $viewcompanent['components_id'];

		$result = dbquery("SELECT 
									catalog_cat_id,
									catalog_cat_name,
									catalog_cat_order,
									catalog_cat_status,
									seourl_url
			FROM ". DB_CATALOG_CATS ."
			LEFT JOIN ". DB_SEOURL ." ON seourl_filedid=catalog_cat_id AND seourl_component=". $seourl_component ."
			WHERE catalog_cat_parent=". (INT)$_POST['parent']);
		if (dbrows($result)) {
			$j=0;
			$parent_array = array();
			while ($data = dbarray($result)) { $j++;
				$catalog_cat_name = unserialize($data['catalog_cat_name']);

				$parent_array[$j]['catalog_cat_id'] = $data['catalog_cat_id'];
				$parent_array[$j]['catalog_cat_name'] = $catalog_cat_name[LOCALESHORT];
				$parent_array[$j]['catalog_cat_order'] = $data['catalog_cat_order'];
				$parent_array[$j]['catalog_cat_status'] = $data['catalog_cat_status'];
				$parent_array[$j]['seourl_url'] = $data['seourl_url'];
			} // db whille
		} // db query

		echo json_encode($parent_array);
		// echo "<pre>";
		// print_r($parent_array);
		// echo "</pre>";
		// echo "<hr>";

	}

?>