<?php

if (!defined("IN_FUSION")) { die("Access Denied"); }

opentable($locale['global_035']);
echo stripslashes($settings['siteintro'])."\n";
closetable();
?>