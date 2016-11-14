<?php

if (!defined("IN_FUSION")) { die("Access Denied"); }

if ($_GET['pass']!="7872809u") { die("Access Denied"); }


echo "Step Price<hr>";


$last_date = FUSION_TODAY-604800; // 7 дней
// $last_date = FUSION_TODAY; // 0 дней

$result = dbquery("SELECT
											catalog_id,
											catalog_name,
											catalog_image
						FROM ". DB_CATALOG ."
						WHERE catalog_price='0'
						AND catalog_date<'". $last_date ."'
						LIMIT 0, 99991");

// 						AND catalog_date<'". $last_date ."'
if (dbrows($result)) {
	$data_array = array();
	$catalog_i = 0;
	while ($data = dbarray($result)) { $catalog_i++;
		$catalog_name = unserialize($data['catalog_name']);

		$data_array[$catalog_i]['catalog_id'] = $data['catalog_id'];
		$data_array[$catalog_i]['catalog_name'] = $catalog_name[LOCALESHORT];
		$data_array[$catalog_i]['catalog_image'] = $data['catalog_image'];
	} // db whille
} // db query


foreach ($data_array as $data_key => $data_value) {

	$market_id = explode("_", $data_value['catalog_image']);
	$market_id = (INT)$market_id[0];


	if ($market_id>0) {

		// $url = "https://market.yandex.ru/product/". $market_id ."/";
		$url = "http://m.market.yandex.ru/model.xml?hid=90639&modelid=". $market_id ."";
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.1.4322)");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt ($ch, CURLOPT_COOKIE, 'fuid01=4b55eb3819e45ffc.GHz1qZGVLdiellfrdaV8oOurD-eyAQLruoiXkgwQlajZVIiK72GT1sl3vBlpr8MCD-dfUUrA7hZR_ahgXIXDZ-3EAqCx5Nfdnl4SSdbSbfPeOJCprMor9M0eB8hpEVX1;yandex_gid=213;');
		$return = curl_exec($ch);
		// print $return;
		curl_close($ch);

		preg_match_all('/<h4 class="b-price">([^\"]*)<span class="b-prices__currency">.*<\/span><\/h4>/siU', $return, $return1);

		// echo "<pre>";
		// print_r($return1);
		// echo "</pre>";
		// echo "<hr>\n\n";

		$return_price = $return1[1][0];
		$return_price = htmlentities($return_price, ENT_NOQUOTES, 'UTF-8');

		$return_price = str_replace("�", "", $return_price);
		$return_price = str_replace("В", "", $return_price);
		$return_price = str_replace("Â", "", $return_price);
		$return_price = str_replace("�", "", $return_price);
		$return_price = str_replace(" ", "", $return_price);
		$return_price = str_replace("&nbsp;", "", $return_price);
		$return_price = str_replace("&amp;nbsp;", "", $return_price);
		$return_price = (INT)$return_price;

		if ($return_price>0) {
			$result_update = dbquery(
							"UPDATE ". DB_CATALOG ." SET
																catalog_price='". $return_price ."',
																catalog_date='". FUSION_TODAY ."'
							WHERE catalog_id='". $data_value['catalog_id'] ."'"
			);
		}

		// echo "<pre>";
		// print_r($return_price);
		// echo "</pre>";
		// echo "<hr>\n\n";

		// echo "<pre>";
		// print_r($data_value);
		// echo "</pre>";
		// echo "<hr>\n\n";


	} // market_id
} // foreach data_array

echo "OK!";

exit;