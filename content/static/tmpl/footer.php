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

// Load Variables needed for page creation
$Load = sys_getloadavg();

?>
				<div class='disclaimer_box'>
					<p>
						Site and design &copy; <?=date("Y")?> <?=$Settings['site_name']?>
					</p>
					<p>
						<strong>Time:</strong> <?=number_format(((microtime(true)-$StartTime)*1000),5)?> ms
						<strong>Used:</strong> <?=get_size(memory_get_usage(true))?>
						<strong>Load:</strong> <?=number_format($Load[0],2).' '.number_format($Load[1],2).' '.number_format($Load[2],2)?>
						<strong>Date:</strong> <?=date('M d Y, H:i')?>

					</p>
				</div>
			</div>
			<br/>
			<div class='debug_info_table'>
				<?
				$Debug->set_flag('Loading Done');

				 if (check_perms('site_debug')) { 
					$Debug->flag_table();
					$Debug->error_table();
					$Debug->query_table();
					$Debug->cache_table();
				}
				if (DEBUG_MODE && check_perms('site_debug') && $LoggedUser['ID'] == '1') {
					$Debug->extension_table();
					$Debug->class_table();
					$Debug->constant_table();
				}
				?>
			</div>

			<div id="lightbox" class="lightbox hidden"></div>
			<div id="lightbox_ajax" class="lightbox_ajax hidden"></div>
			<div id="lightbox_frame" class="lightbox_frame hidden"></div>
			<div id="curtain" class="curtain hidden"></div>
			<!-- Extra divs, for stylesheet developers to add imagery -->
			<div id="extra1"><span></span></div>
			<div id="extra2"><span></span></div>
			<div id="extra3"><span></span></div>
			<div id="extra4"><span></span></div>
			<div id="extra5"><span></span></div>
			<div id="extra6"><span></span></div>
		
		</body>
		</html>