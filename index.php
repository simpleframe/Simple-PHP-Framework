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

// Comment out for production use
error_reporting(E_ALL);
// Debug Info and Shiz
$StartTime = microtime(true); //To track how long a page takes to create
ob_start(); //Start a buffer, mainly in case there is a mysql error

// All content is served directly from this page thru the content folder either dynamic or static.
// define( , ''); all variables needed to load configure files
define('SERVER_ROOT', '/var/www/');

// require_once(""); all files needed for $DB, $Cache, $Debug, etc. require_once(""); for content required on an individual page is loaded on that page to reduce loading
// unneeded functions saves time and resources
require_once(SERVER_ROOT.'classes/debug/debug.php');
$Debug = new DEBUG;
$Debug->handle_errors();
require_once(SERVER_ROOT.'classes/includes/configure.php');
require_once(SERVER_ROOT.'classes/functions/main.php');
require_once(SERVER_ROOT.'classes/mysql/db.php');
$DB = new DB_MYSQL;
require_once(SERVER_ROOT.'classes/memcached/cache.php');
$Cache = new CACHE;
require_once(SERVER_ROOT.'classes/encrypt/crypt.php');
$Enc = new CRYPT;
require_once(SERVER_ROOT.'classes/useragents/ua.php');
$UA = new USER_AGENT;
$Browser = $UA->browser($_SERVER['HTTP_USER_AGENT']);
$OperatingSystem = $UA->operating_system($_SERVER['HTTP_USER_AGENT']);
// Uncomment to make use of the Last.FM API PHP Parser
//require_once(SERVER_ROOT.'classes/last_fm/lastfm.php');
require_once(SERVER_ROOT.'classes/functions/time.php');
require_once(SERVER_ROOT.'classes/functions/users.php');
require_once(SERVER_ROOT.'classes/nbbc/nbbc.php');
require_once(SERVER_ROOT.'classes/validate/validate.php');
require_once(SERVER_ROOT.'classes/templates/templates.php');
// Uncomment if you intend to create a file under functions/content/dynamic for each and every page you intend to have other wise your page will DIE in error
// require_once(SERVER_ROOT.'classes/functions/content/dynamic/'.$content.'.php);
$Debug->set_flag('Includes Loaded');


if($Settings['site_online'] != '1' && check_perms('site_view_offline') == false ) { die('Site Off Line'); }

// Short Hand Enforce Login to redirect to login
// Uncomment to make use of login System
/*
if (!$SessionID || !$LoggedUser) {
	include(SERVER_ROOT.'content/dynamic/login/index.php');
	die();
}
$Debug->set_flag('Logged In Check');
*/

// include(''); for header section and any other content you want loaded to the top of the page
$Debug->set_flag('Load Header');
if(isset($_GET['c']) && $_GET['c']){
	$PageTitle = $Settings['site_name'] . ' :: ' . $_GET['c'];
}
else
	$PageTitle = $Settings['site_name'] . ' :: ' . 'Home';
	
include(SERVER_ROOT.'content/static/tmpl/header.php');
$Debug->set_flag('Load Menu');
include(SERVER_ROOT.'content/static/tmpl/menu.php');

// Content loading section intending on using $_GET['']; variables so incorporating ajax loading of the content is made easier not sure yet which manner to load code yet
// could be a switch(){} statment or could be a include(''); which would require naming of content files based on $_GET['']; variables
$Debug->set_flag('Load Content');
if(isset($_GET['c']) && $_GET['c']){
	if(isset($_GET['c']) && $_GET['c'] != '') {
		$Debug->set_flag('Loaded '.$_GET['c'].'');
		$content = $_GET['c'];
		include(SERVER_ROOT.'content/dynamic/'.$content.'/index.php');
		
	}
}
else {
	$Debug->set_flag('Loaded Index Content');
	include(SERVER_ROOT.'content/dynamic/index/index.php');
}

// include(''); for side bar left column could be made dynamic if you want to or intend to load different content onto or into the side bar could also be made a right
// column by switching this to below the content and altering the CSS
// Two Choices Here - Either put all the pages you want to display side bar on in $SideBarPages array or comment out $SideBarPages and if(in_array lines or third choice
// is to reverse the in_array statment to be !in_array and include the pages you want it hidden on in the array which is what im doing here for my own uses
$SideBarPages = array(' ');
if(!in_array($_GET['c'], $SideBarPages))
	include(SERVER_ROOT.'content/static/tmpl/sidebar.php');
$Debug->set_flag('Loaded Sidebar');


// include(''); for footer section including $Debug Table output
$Debug->set_flag('Loaded Footer');
include(SERVER_ROOT.'content/static/tmpl/footer.php');

/* Required in the absence of session_start() for providing that pages will change 
upon hit rather than being browser cache'd for changing content. */
header('Cache-Control: no-cache, must-revalidate, post-check=0, pre-check=0');
header('Pragma: no-cache');
//Flush to user
ob_end_flush();
$Debug->set_flag('set headers and send to user');
//Attribute profiling
$Debug->profile();

?>