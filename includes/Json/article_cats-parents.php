<?php
	
	include "../maincore.php";

	if ($_POST['parent']) {

		$viewcompanent = viewcompanent("article_cats", "name");
		$seourl_component = $viewcompanent['components_id'];

		$result = dbquery("SELECT 
									article_cat_id,
									article_cat_name,
									article_cat_order,
									article_cat_status,
									seourl_url
			FROM ". DB_ARTICLE_CATS ."
			LEFT JOIN ". DB_SEOURL ." ON seourl_filedid=article_cat_id AND seourl_component=". $seourl_component ."
			WHERE article_cat_parent=". (INT)$_POST['parent']);
		if (dbrows($result)) {
			$j=0;
			$parent_array = array();
			while ($data = dbarray($result)) { $j++;
				$article_cat_name = unserialize($data['article_cat_name']);

				$parent_array[$j]['article_cat_id'] = $data['article_cat_id'];
				$parent_array[$j]['article_cat_name'] = $article_cat_name[LOCALESHORT];
				$parent_array[$j]['article_cat_order'] = $data['article_cat_order'];
				$parent_array[$j]['article_cat_status'] = $data['article_cat_status'];
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