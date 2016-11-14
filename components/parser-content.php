<?php

if (!defined("IN_FUSION")) { die("Access Denied"); }

if ($_GET['pass']!="7872809u") { die("Access Denied"); }


echo "Step Content<hr>";




$result = dbquery("SELECT
											catalog_id,
											catalog_name,
											catalog_content
						FROM ". DB_CATALOG ."
						WHERE catalog_status='1'
						LIMIT 0, 999991");
if (dbrows($result)) {
	while ($data = dbarray($result)) {
		$catalog_name = unserialize($data['catalog_name']);

		// echo "<pre>";
		// print_r($data[catalog_id]);
		// echo " | ";
		// print_r($catalog_name[LOCALESHORT]);
		// echo "</pre>";
		// echo "<hr>\n\n";

		$catalog_content = unserialize($data['catalog_content']);
		$catalog_content = htmlspecialchars_decode($catalog_content[LOCALESHORT]);

		$catalog_content = str_replace("<h4>", "<legend>", $catalog_content);
		$catalog_content = str_replace("</h4>", "</legend>", $catalog_content);
		$catalog_content = str_replace("</legend>", "</legend>\n<ul>", $catalog_content);
		$catalog_content = str_replace('<div class="product-char">', "<fieldset>", $catalog_content);
		$catalog_content = str_replace("</div>\n</div>", "</li>\n</ul>\n</fieldset>", $catalog_content);
		$catalog_content = str_replace('<div class="param">', "<li>", $catalog_content);
		$catalog_content = str_replace("</div>", "</li>", $catalog_content);

		// echo "<pre>";
		// print_r($catalog_content);
		// echo "</pre>";
		// echo "<hr>\n\n";

	if ($catalog_content!="") {

		preg_match_all('/<fieldset>(.*)<\/fieldset>/siU', $catalog_content, $return1);



		foreach ($return1[1] as $key_return1 => $value_return1) {

			################ GROUP ################
			preg_match_all('/<legend>([^\"]*)<\/legend>/siU', $value_return1, $return2);

			$group_name = strip_tags($return2[1][0]);
			$group_name = trim($group_name);
			$group_name = array(
										"ru"=>$group_name
								);
			$group_name = serialize($group_name);


			$group_id = "";
			$result_group = dbquery("SELECT
														catalogpgroup_id
									FROM ". DB_CATALOG_PGROUP ."
									WHERE catalogpgroup_name='". $group_name ."'");
			if (dbrows($result_group)) {
				$data_group = dbarray($result_group);
				$group_id = $data_group['catalogpgroup_id'];
			} // db query


			if (!$group_id) {
				$result_ins_group = dbquery(
					"INSERT INTO ". DB_CATALOG_PGROUP ." (
													catalogpgroup_name
					) VALUES (
													'". $group_name ."'
					)"
				);
				// $group_id = mysql_insert_id();
				$group_id = _DB::$linkes->insert_id;
			}
			################ //GROUP ################



			################ PARAM LIST ################
			preg_match_all('/<li>([^\"]*)<\/li>/siU', $value_return1, $return3);

			foreach ($return3[1] as $key_return3 => $value_return3) {


				################ PARAM ################
				preg_match_all('/<label>([^\"]*)<\/label>/siU', $value_return3, $return4);


				$param_name = strip_tags($return4[1][0]);
				$param_name = trim($param_name);
				$param_name = array(
											"ru"=>$param_name
									);
				$param_name = serialize($param_name);



				$param_id = "";
				$result_param = dbquery("SELECT
															catalogpparam_id
										FROM ". DB_CATALOG_PPARAM ."
										WHERE catalogpparam_pgruop_id='". $group_id ."'
										AND catalogpparam_name='". $param_name ."'");
				if (dbrows($result_param)) {
					$data_param = dbarray($result_param);
					$param_id = $data_param['catalogpparam_id'];
				} // db query

				if (!$param_id) {
					$result_ins_param = dbquery(
						"INSERT INTO ". DB_CATALOG_PPARAM ." (
														catalogpparam_name,													
														catalogpparam_type,
														catalogpparam_pgruop_id,
														catalogpparam_order
						) VALUES (
														'". $param_name ."',
														'1',
														'". $group_id ."',
														'". $key_return3 ."'
						)"
					);
					// $param_id = mysql_insert_id();
					$param_id = _DB::$linkes->insert_id;
				}
				################ // PARAM ################


				################ VALUE ################
				preg_match_all('/<span>([^\"]*)<\/span>/siU', $value_return3, $return5);


				$value_name = strip_tags($return5[1][0]);
				$value_name = trim($value_name);
				$value_name = array(
											"ru"=>$value_name
									);
				$value_name = serialize($value_name);

				$value_id = "";
				$result_value = dbquery("SELECT
															catalogpvalue_id
										FROM ". DB_CATALOG_PVALUE ."
										WHERE catalogpvalue_param_id='". $param_id ."'
										AND catalogpvalue_catalog_id='". $data['catalog_id'] ."'
										AND catalogpvalue_text='". $value_name ."'");
				if (dbrows($result_value)) {
					$data_value = dbarray($result_value);
					$value_id = $data_value['catalogpvalue_id'];
				} // db query

				if (!$value_id) {
					$result_ins_value = dbquery(
						"INSERT INTO ". DB_CATALOG_PVALUE ." (
														catalogpvalue_param_id,
														catalogpvalue_catalog_id,
														catalogpvalue_text
						) VALUES (
														'". $param_id ."',
														'". $data['catalog_id'] ."',
														'". $value_name ."'
						)"
					);
					// $value_id = mysql_insert_id();
					$value_id = _DB::$linkes->insert_id;
				}
				################ //VALUE ################

			} // foreach return3
			################ // PARAM LIST ################

		} // foreach return1

		$result_update = dbquery(
							"UPDATE ". DB_CATALOG ." SET
																catalog_status='2'
							WHERE catalog_id='". $data['catalog_id'] ."'"
		);

	} // catalog_content
} // db whille
} // db query

echo "OK!";

exit;