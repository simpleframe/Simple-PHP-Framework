<?php

/*
|========================================|
|  Pirate Media Source Code              |
|  Requires == MySQL 5.3+                |
|  Requires == PHP 5.3.0+                |
|  Requires == Memcached 1.4.5+          |
|  http://piratemedia.info for support   |
|  (c) 2010 by NoName                    |
|  Free to use and modifiy and share     |
|  Not for Sale                          |
|========================================|
*/

// Error Catcher
function error($Error, $Ajax=false) {
	global $Debug;
	require(SERVER_ROOT.'content/dynamic/error/index.php');
	$Debug->profile();
	die();
}

// Send Email
function send_email($To,$Subject,$Body,$From='noreply',$ContentType='text/plain') {
	global $Settings;
	$Headers='MIME-Version: 1.0'."\r\n";
	$Headers.='Content-type: '.$ContentType.'; charset=iso-8859-1'."\r\n";
	$Headers.='From: '.$Settings['site_name'] .' <'.$From.'@'.$Settings['domain'] .'>'."\r\n";
	$Headers.='Reply-To: '.$From.'@'.$Settings['domain'] ."\r\n";
	$Headers.='X-Mailer: Project Gazelle'."\r\n";
	$Headers.='Message-Id: <'.make_secret().'@'.$Settings['domain'] .">\r\n";
	$Headers.='X-Priority: 3'."\r\n";
	mail($To,$Subject,$Body,$Headers,"-f ".$From."@".$Settings['domain'] );
}

// Get Size Function
function get_size($Size, $Levels = 2) {
	$Units = array(' B',' KB',' MB',' GB',' TB',' PB',' EB',' ZB',' YB');
	$Size = (double) $Size;
	for($Steps = 0; abs($Size) >= 1024; $Size /= 1024, $Steps++) {}
	if(func_num_args() == 1 && $Steps >= 4) {
		$Levels++;
	}
	return number_format($Size,$Levels).$Units[$Steps];
}

// Ratio Function
function ratio($Dividend, $Divisor, $Color = true) {
	if($Divisor == 0 && $Dividend == 0) {
		return '--';
	} elseif($Divisor == 0) {
		return '<span class="r99">8</span>';
	}
	$Ratio = number_format(max($Dividend/$Divisor-0.005,0), 2); //Subtract .005 to floor to 2 decimals
	if($Color) {
		$Class = get_ratio_color($Ratio);
		if($Class) {
			$Ratio = '<span class="'.$Class.'">'.$Ratio.'</span>';
		}
	}
	return $Ratio;

}

// Return the <span class='RatioColor'> Edit Colors in style.css file
function get_ratio_color($Ratio) {
	if ($Ratio < 0.1) { return 'r00'; }
	if ($Ratio < 0.2) { return 'r01'; }
	if ($Ratio < 0.3) { return 'r02'; }
	if ($Ratio < 0.4) { return 'r03'; }
	if ($Ratio < 0.5) { return 'r04'; }
	if ($Ratio < 0.6) { return 'r05'; }
	if ($Ratio < 0.7) { return 'r06'; }
	if ($Ratio < 0.8) { return 'r07'; }
	if ($Ratio < 0.9) { return 'r08'; }
	if ($Ratio < 1) { return 'r09'; }
	if ($Ratio < 2) { return 'r10'; }
	if ($Ratio < 5) { return 'r20'; }
	return 'r50';
}

// Generate a random string
function make_secret($Length = 32) {
	$Secret = '';
	$Chars='abcdefghijklmnopqrstuvwxyz0123456789';
	for($i=0; $i<$Length; $i++) {
		$Rand = mt_rand(0, strlen($Chars)-1);
		$Secret .= substr($Chars, $Rand, 1);
	}
	return str_shuffle($Secret);
}

// Make Password / Login / Passkey hashes
function make_hash($Str,$Secret) {
	global $Settings;
	return sha1(md5($Secret).$Str.sha1($Secret).$Settings['password_salt']);
}

// Write a message to the system log
function write_log($Message) {
	global $DB;
	$DB->query('INSERT INTO log (Message, Time) VALUES (\''.db_string($Message).'\', \''.sqltime().'\')');
}

// This is preferable to htmlspecialchars because it doesn't screw up upon a double escape
function display_str($Str) {
	if ($Str === NULL || $Str === FALSE || is_array($Str)) {
		return '';
	}
	if ($Str!='' && !is_number($Str)) {
		$Str=make_utf8($Str);
		$Str=mb_convert_encoding($Str,"HTML-ENTITIES","UTF-8");
		$Str=preg_replace("/&(?![A-Za-z]{0,4}\w{2,3};|#[0-9]{2,5};)/m","&amp;",$Str);

		$Replace = array(
			"'",'"',"<",">",
			'&#128;','&#130;','&#131;','&#132;','&#133;','&#134;','&#135;','&#136;','&#137;','&#138;','&#139;','&#140;','&#142;','&#145;','&#146;','&#147;','&#148;','&#149;','&#150;','&#151;','&#152;','&#153;','&#154;','&#155;','&#156;','&#158;','&#159;'
		);

		$With=array(
			'&#39;','&quot;','&lt;','&gt;',
			'&#8364;','&#8218;','&#402;','&#8222;','&#8230;','&#8224;','&#8225;','&#710;','&#8240;','&#352;','&#8249;','&#338;','&#381;','&#8216;','&#8217;','&#8220;','&#8221;','&#8226;','&#8211;','&#8212;','&#732;','&#8482;','&#353;','&#8250;','&#339;','&#382;','&#376;'
		);

		$Str=str_replace($Replace,$With,$Str);
	}
	return $Str;
}

// Find out if a string is a number used mainly for DB inserts and $_GET['id']'s
function is_number($Str) {
	$Return = true;
	if ($Str < 0) { $Return = false; }
	// We're converting input to a int, then string and comparing to original
	$Return = ($Str == strval(intval($Str)) ? true : false);
	return $Return;
}

// Convert a string to utf8 "Not Really used to often in here"
function make_utf8($Str) {
	if ($Str!="") {
		if (is_utf8($Str)) { $Encoding="UTF-8"; }
		if (empty($Encoding)) { $Encoding=mb_detect_encoding($Str,'UTF-8, ISO-8859-1'); }
		if (empty($Encoding)) { $Encoding="ISO-8859-1"; }
		if ($Encoding=="UTF-8") { return $Str; }
		else { return @mb_convert_encoding($Str,"UTF-8",$Encoding); }
	}
}

// Determine if a string is already UTF8
function is_utf8($Str) {
	return preg_match('%^(?:
		[\x09\x0A\x0D\x20-\x7E]			 // ASCII
		| [\xC2-\xDF][\x80-\xBF]			// non-overlong 2-byte
		| \xE0[\xA0-\xBF][\x80-\xBF]		// excluding overlongs
		| [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2} // straight 3-byte
		| \xED[\x80-\x9F][\x80-\xBF]		// excluding surrogates
		| \xF0[\x90-\xBF][\x80-\xBF]{2}	 // planes 1-3
		| [\xF1-\xF3][\x80-\xBF]{3}		 // planes 4-15
		| \xF4[\x80-\x8F][\x80-\xBF]{2}	 // plane 16
		)*$%xs', $Str
	);
}

// Escape an entire array for output
// $Escape is either true, false, or a list of array keys to not escape
function display_array($Array, $Escape = array()) {
	foreach ($Array as $Key => $Val) {
		if((!is_array($Escape) && $Escape == true) || !in_array($Key, $Escape)) {
			$Array[$Key] = display_str($Val);
		}
	}
	return $Array;
}

// Determines if an IP is banned Used for login function and possible the tracker
function site_ban_ip($IP) {
	global $DB, $Cache;
	$IP = ip2unsigned($IP);
	$IPBans = $Cache->get_value('ip_bans');
	if(!is_array($IPBans)) {
		$DB->query("SELECT ID, FromIP, ToIP FROM ip_bans");
		$IPBans = $DB->to_array('ID');
		$Cache->cache_value('ip_bans', $IPBans, 0);
	}
	foreach($IPBans as $Index => $IPBan) {
		list($ID, $FromIP, $ToIP) = $IPBan;
		if($IP >= $FromIP && $IP <= $ToIP) {
			return true;
		}
	}
	return false;
}

// IP too Long IP
function ip2unsigned($IP) {
	return sprintf("%u", ip2long($IP));
}

// Geolocate an IP address. Two functions - a database one, and a dns one.
function geoip($IP) {
	static $IPs = array();
	if (isset($IPs[$IP])) {
		return $IPs[$IP];
	}
	$Long = ip2unsigned($IP);
	if($Long == 2130706433 || !$Long) {
		return false;
	}
	global $DB;
	$DB->query("SELECT Code FROM geoip_country WHERE $Long BETWEEN StartIP AND EndIP LIMIT 1");
	list($Country) = $DB->next_record();
	$IPs[$IP] = $Country;
	return $Country;
}


?>
