<?php

	require_once "maincore.php";

	$cur_result = dbquery("SELECT
										*
		FROM ". DB_CURRENCY ."
		ORDER BY currency_id ASC");
	if (dbrows($cur_result)) {
		while ($cur_data = dbarray($cur_result)) {
			$cur_data_array[] = $cur_data;
		} // db while
	} // db query

	foreach ($cur_data_array as $cur_key_rur => $cur_value_rur) {
		if ($cur_value_rur[currency_code]=="RUR") {
			$cur_valyuta_rur = $cur_value_rur[currency_value];

			// echo "<pre>";
			// print_r($cur_valyuta_rur);
			// echo "</pre>";
			// echo "<hr>";
		} // if
	} // foreach


	$new_xml_rur = simplexml_load_file("http://www.cbr.ru/scripts/XML_daily.asp");


	foreach ($new_xml_rur as $new_key_rur => $new_value_rur) {
		if ($new_value_rur[ID]=="R01235") {
			$new_valyuta_rur = str_replace(",", ".", $new_value_rur->Value);
			$new_valyuta_rur = trim($new_valyuta_rur);

			// echo "<pre>";
			// print_r($new_valyuta_rur);
			// echo "</pre>";
			// echo "<hr>";

		} // if
	} // foreach


	if ($cur_valyuta_rur!=$new_valyuta_rur) {

		##### DB UPDATE BEGIN
		$upd_result_rur = dbquery("UPDATE ". DB_CURRENCY ." SET
															currency_value='". $new_valyuta_rur ."'
		WHERE currency_code='RUR'");
		##### DB UPDATE END

		$message .="<u>Update currencies - Изменения курс валют <b>Ruble - Рубль</b></u><br /><br />\n";
		$message .="<b>Was - Было</b><br />\n";
		$message .="Current - Курс <b>". $cur_valyuta_rur ."</b><br /><br />\n";
		$message .="<b>Became - Стало</b><br />\n";
		$message .="Current - Курс <b>". $new_valyuta_rur ."</b><br /><hr />\n";

	}


	foreach ($cur_data_array as $cur_key_eur => $cur_value_eur) {
		if ($cur_value_eur[currency_code]=="EUR") {
			$cur_valyuta_eur = $cur_value_eur[currency_value];

			// echo "<pre>";
			// print_r($cur_valyuta_eur);
			// echo "</pre>";
			// echo "<hr>";
		} // if
	} // foreach


	$new_xml_eur = simplexml_load_file("http://spreadsheets.google.com/feeds/list/0Av2v4lMxiJ1AdE9laEZJdzhmMzdmcW90VWNfUTYtM2c/4/public/basic");
	$new_valyuta_eur = str_replace("_cokwr:", "", $new_xml_eur->entry->content);
	$new_valyuta_eur = trim($new_valyuta_eur);

	// echo "<pre>";
	// print_r($new_valyuta_eur);
	// echo "</pre>";
	// echo "<hr>";


	if ($cur_valyuta_eur!=$new_valyuta_eur) {

		##### DB UPDATE BEGIN
		$upd_result_eur = dbquery("UPDATE ". DB_CURRENCY ." SET
															currency_value='". $new_valyuta_eur ."'
		WHERE currency_code='EUR'");
		##### DB UPDATE END

		$message .="<u>Update currencies - Изменения курс валют <b>Euro - Евро</b></u><br /><br />\n";
		$message .="<b>Was - Было</b><br />\n";
		$message .="Current - Курс <b>". $cur_valyuta_eur ."</b><br /><br />\n";
		$message .="<b>Became - Стало</b><br />\n";
		$message .="Current - Курс <b>". $new_valyuta_eur ."</b><br /><hr />\n";

	}


	### SEND MAIL BEGIN
	if ($message) {

		$mailname = "Current Updater";
		$email = "no-reply@".$settings['site_host'];
		$subject = "Update currencies - Изменения курс валют!";
		require_once INCLUDES ."sendmail_include.php";
		sendemail($settings['siteusername'], $settings['siteemail'], $mailname, $email, $subject, $message, "html");

		// echo $message;

	} // Yesli message
	### SEND MAIL END

	echo "OK";
?>