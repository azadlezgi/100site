<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) 2002 - 2011 Nick Jones
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: search_catalog_include_button.php
| Author: Robert Gaudyn (Wooya)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
if (!defined("IN_FUSION")) { die("Access Denied"); }

include LOCALE.LOCALESET."search/catalog.php";

$form_elements['catalog']['enabled'] = array("datelimit", "fields1", "fields2", "fields3", "sort", "order1", "order2", "chars");
$form_elements['catalog']['disabled'] = array();
$form_elements['catalog']['display'] = array();
$form_elements['catalog']['nodisplay'] = array();

$radio_button['catalog'] = "<label><input type='radio' name='stype' value='catalog'".($_GET['stype'] == "catalog" ? " checked='checked'" : "")." onclick=\"display(this.value)\" /> ".$locale['a400']."</label>";
?>