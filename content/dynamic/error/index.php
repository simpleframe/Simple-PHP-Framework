<?
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


$Errors = array('403','404','413','504');

if(!empty($_GET['e']) && in_array($_GET['e'],$Errors)) {
	//Webserver error i.e. http://sitename/madeupdocument.php
	include($_GET['e'].'.php');
} else {
	//Gazelle error (Come from the error() function)
	switch ($Error) {

		case '403':
			$Title = "Error 403";
			$Description = "You just tried to go to a page that you don't have enough permission to view.";
			break;
		case '404':
			$Title = "Error 404";
			$Description = "You just tried to go to a page that doesn't really exist.";
			break;
		case '0':
			$Title = "Invalid Input";
			$Description = "Something was wrong with the input provided with your request and the server is refusing to fulfill it.";
			break;
		case '-1':
			$Title = "Invalid request";
			$Description = "Something was wrong with your request and the server is refusing to fulfill it.";
			break;
		default:
			if(!empty($Error)) {
				$Title = 'Error';
				$Description = $Error;
			} else {
				$Title = "Unexpected Error";
				$Description = "You have encountered an unexpected error.";
			}
	}

	if(empty($Ajax)) {
?>
	<div class="thin">
		<h2><?=$Title?></h2>
		<div class="box pad">
			<p><?=$Description?></p>
		</div>
	</div>
<?
	} else {
		echo $Description;
	}
}
