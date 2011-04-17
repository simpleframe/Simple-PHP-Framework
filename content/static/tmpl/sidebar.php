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

// Side Bar include file empty or uninclude from index to remove content	
?>

<div class='sidebar'>
	<h1>Side Bar</h1>
	
	<div class='sidebar_heading'>
		Tool Example
	</div>
	<div class='sidebar_box'>
		Tool Box Content
		example
		Could be a twitter box
		or stats or whatever
		or staff tool quick 
		access.
	</div>
	<br/>
	<? // It is possible to have different sidebar content if your like me and you prefer to have all your content split up you can use an include(FILE) but if your
	   // one of those folks who prefers to have all your content in the fewest possible files this a.) probably isnt the source for you to start with and b.) you
	   // can just simply use either a bunch of if statments for each possible $_GET['c'] page name you want or you can use a switch function  ?>
	<?if(!isset($_GET['c'])) {
		//include(SERVER_ROOT.'content/static/tmpl/sidebar.index.php');
	}
	?>
	
</div>