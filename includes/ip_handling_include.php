<?php

if (!defined("IN_FUSION")) { die("Access Denied"); }

// Uncompress an IPv6 address
function uncompressIPv6($ip, $count=7) {
	if (strpos($ip, "::") !== FALSE) {
		$ip = str_replace("::", str_repeat(":", $count + 2 - substr_count($ip, ":")), $ip);
	}
	$tmp_ip = explode(":", $ip);
	foreach ($tmp_ip as &$value) {
		$value = str_pad($value, 4, '0', STR_PAD_LEFT);	
	}
	return implode(":", $tmp_ip);
}

// Check if users full or partial ip is blacklisted and set USER_IP and USER_IP_TYPE
if (strpos(FUSION_IP, ".")) {
	if (strpos(FUSION_IP, ":") === FALSE) {
		// IPv4
		define("USER_IP_TYPE", 4);
		define("USER_IP", FUSION_IP);
		$check_value = "blacklist_ip_type='4' AND blacklist_ip REGEXP '^";
		$check_value .= str_replace(".", ".(", USER_IP, $i);
		$check_value .= str_repeat(")?", $i);
		$check_value .= "$'";
	} else {
		// Mixed IPv4 and IPv6
		define("USER_IP_TYPE", 5);
		$last_pos = strrpos(FUSION_IP, ":");
		$ipv4 = substr(FUSION_IP, $last_pos+1);
		$ipv6 = substr(FUSION_IP, 0, $last_pos);
		$ipv6 = uncompressIPv6($ipv6, 5);
		define("USER_IP", $ipv6.":".$ipv4);
		$check_value = "(blacklist_ip_type='4' AND blacklist_ip REGEXP '^";
		$check_value .= str_replace(".", ".(", $ipv4, $i);
		$check_value .= str_repeat(")?", $i);
		$check_value .= "$') OR (blacklist_ip_type='6' AND blacklist_ip REGEXP '^";
		$check_value .= str_replace(":", ":(", $ipv6, $i);
		$check_value .= str_repeat(")?", $i);
		$check_value .= "$') OR (blacklist_ip_type='5' AND blacklist_ip='".USER_IP."')";
		unset($ipv4, $ipv6, $last_pos);
	}
} else {
	// IPv6
	define("USER_IP_TYPE", 6);
	define("USER_IP", uncompressIPv6(FUSION_IP, 7));
	$check_value = "blacklist_ip_type='6' AND blacklist_ip REGEXP '^";
	$check_value .= str_replace(":", ":(", USER_IP, $i);
	$check_value .= str_repeat(")?", $i);
	$check_value .= "$'";
}
if (dbcount("(blacklist_id)", DB_BLACKLIST, $check_value)) {
	redirect("http://www.google.com/");
}
unset($check_value);
?>