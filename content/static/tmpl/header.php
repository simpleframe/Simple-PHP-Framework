<?php

/*
|=======================================================|
|  Simple-PHP5-Framework                                |
|  Requires == MySQL 5.3+                               |
|  Requires == PHP 5.3.0+                               |
|  Requires == Memcached 1.4.5+                         |
|  https://github.com/simpleframe/Simple-PHP-Framework  |
|  (c) 2010 by NoName                                   |
|  Free to use and modifiy and share                    |
|  Not for Sale                                         |
|=======================================================|
*/
// Header includes logo / quick toolbox for staff and user info section

$Class = $LoggedUser['Class'];
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	<html xmlns='http://www.w3.org/1999/xhtml'>
		<head>
			<meta http-equiv='Content-Language' content='en-us' />
			<meta http-equiv='Content-Type' content='text/html; charset=utf-8' />
			<title><?=$PageTitle?></title>
				<link rel='stylesheet' type='text/css' href='<?=$Settings['domain']?>/static/css/style.css' />
				<script type='text/javascript' src='<?=$Settings['domain']?>/static/javascript/sizzle.js'></script>
				<script type='text/javascript' src='<?=$Settings['domain']?>/static/javascript/main.js'></script>
				<script type='text/javascript' src='<?=$Settings['domain']?>/static/javascript/ajax.js'></script>
				<script type='text/javascript' src='<?=$Settings['domain']?>/static/javascript/extra.js'></script>
				
		</head>
		<body>
			<div class='header'>
				<div class='logo'>
					LOGO HERE
				</div>
				<div class='user_info'>
				
				</div>