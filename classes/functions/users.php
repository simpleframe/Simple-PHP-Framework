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

// Get permissions and make array out of it
list($Classes, $ClassColors, $ClassLevels) = $Cache->get_value('classes');
if(!$Classes || !$ClassColors || !$ClassLevels) {
	$DB->query('SELECT ID, Color, Name, Level FROM permissions ORDER BY Level');
	$Classes = $DB->to_array('ID');
	$ClassColors = $DB->to_array('Color');
	$ClassLevels = $DB->to_array('Level');
	$Cache->cache_value('classes', array($Classes, $ClassColors, $ClassLevels), 0);
}
$Debug->set_flag('Loaded permissions');

$Debug->set_flag('Load LoggedUser');
// Check cookie if there is one continue loading user info

if (isset($_COOKIE['session'])) { $LoginCookie=$Enc->decrypt($_COOKIE['session']); }
if(isset($LoginCookie)) {
	list($SessionID, $LoggedUser['ID'])=explode("|~|",$Enc->decrypt($LoginCookie));
	$LoggedUser['ID'] = (int)$LoggedUser['ID'];

	$UserID=$LoggedUser['ID']; //TODO: UserID should not be LoggedUser

	if (!$LoggedUser['ID'] || !$SessionID) {
		logout();
	}
	
	$UserSessions = $Cache->get_value('users_sessions_'.$UserID);
	if(!is_array($UserSessions)) {
		$DB->query("SELECT
			SessionID,
			Browser,
			OperatingSystem,
			IP,
			LastUpdate
			FROM users_sessions
			WHERE UserID='$UserID'
			ORDER BY LastUpdate DESC");
		$UserSessions = $DB->to_array('SessionID',MYSQLI_ASSOC);
		$Cache->cache_value('users_sessions_'.$UserID, $UserSessions, 0);
	}

	if (!array_key_exists($SessionID,$UserSessions)) {
		logout();
	}
	
	// Check if user is enabled
	$Enabled = $Cache->get_value('enabled_'.$LoggedUser['ID']);
	if($Enabled === false) {
		$DB->query("SELECT Enabled FROM users_main WHERE ID='$LoggedUser[ID]'");
		list($Enabled)=$DB->next_record();
		$Cache->cache_value('enabled_'.$LoggedUser['ID'], $Enabled, 0);
	}
	if ($Enabled==2) {
		
		logout();
	}

	

	// Up/Down stats
	$UserStats = $Cache->get_value('user_stats_'.$LoggedUser['ID']);
	if(!is_array($UserStats)) {
		$DB->query("SELECT Uploaded AS BytesUploaded, Downloaded AS BytesDownloaded, RequiredRatio FROM users_main WHERE ID='$LoggedUser[ID]'");
		$UserStats = $DB->next_record(MYSQLI_ASSOC);
		$Cache->cache_value('user_stats_'.$LoggedUser['ID'], $UserStats, 3600);
	}

	// Get info such as username
	$LightInfo = user_info($LoggedUser['ID']);
	$HeavyInfo = user_heavy_info($LoggedUser['ID']);

	// Get user permissions
	$Permissions = get_permissions($LightInfo['PermissionID']);

	// Create LoggedUser array
	$LoggedUser = array_merge($HeavyInfo, $LightInfo, $Permissions, $UserStats);

	$LoggedUser['RSS_Auth']=md5($LoggedUser['ID'].RSS_HASH.$LoggedUser['torrent_pass']);

	//$LoggedUser['RatioWatch'] as a bool to disable things for users on Ratio Watch
	$LoggedUser['RatioWatch'] = (
		$LoggedUser['RatioWatchEnds'] != '0000-00-00 00:00:00' &&
		time() < strtotime($LoggedUser['RatioWatchEnds']) &&
		($LoggedUser['BytesDownloaded']*$LoggedUser['RequiredRatio'])>$LoggedUser['BytesUploaded']
	);

	if($LoggedUser['Donor']) {
		$DonorPerms = get_permissions('20');
	} else {
		$DonorPerms['Permissions'] = array();
	}

	if(is_array($LoggedUser['CustomPermissions'])) {
		$CustomPerms = $LoggedUser['CustomPermissions'];
	} else {
		$CustomPerms = array();
	}

	//Load in the permissions
	$LoggedUser['Permissions'] = array_merge($LoggedUser['Permissions'], $DonorPerms['Permissions'], $CustomPerms);
	
	//Change necessary triggers in external components
	$Cache->CanClear = check_perms('admin_clear_cache');
	
	// Because we <3 our staff
	if (check_perms('site_disable_ip_history')) { $_SERVER['REMOTE_ADDR'] = '127.0.0.1'; }

	// Update LastUpdate every 10 minutes
	if(strtotime($UserSessions[$SessionID]['LastUpdate'])+600<time()) {
		$DB->query("UPDATE users_main SET LastAccess='".sqltime()."' WHERE ID='$LoggedUser[ID]'");
		
		$DB->query("UPDATE users_sessions SET IP='".$_SERVER['REMOTE_ADDR']."', Browser='".$Browser."', OperatingSystem='".$OperatingSystem."', LastUpdate='".sqltime()."' WHERE UserID='$LoggedUser[ID]' AND SessionID='".db_string($SessionID)."'");
		$Cache->begin_transaction('users_sessions_'.$UserID);
		$Cache->delete_row($SessionID);
		$Cache->insert_front($SessionID,array(
				'SessionID'=>$SessionID,
				'Browser'=>$Browser,
				'OperatingSystem'=>$OperatingSystem,
				'IP'=>$_SERVER['REMOTE_ADDR'],
				'LastUpdate'=>sqltime()
				));
		$Cache->commit_transaction(0);
	}
	
	// Notifications
	/*Not Ready To Setup Yet
	if(isset($LoggedUser['Permissions']['site_torrents_notify'])) {
		$LoggedUser['Notify'] = $Cache->get_value('notify_filters_'.$LoggedUser['ID']);
		if(!is_array($LoggedUser['Notify'])) {
			$DB->query("SELECT ID, Label FROM users_notify_filters WHERE UserID='$LoggedUser[ID]'");
			$LoggedUser['Notify'] = $DB->to_array('ID');
			$Cache->cache_value('notify_filters_'.$LoggedUser['ID'], $LoggedUser['Notify'], 2592000);
		}
	}
	*/
	// We've never had to disable the wiki privs of anyone.
	if ($LoggedUser['DisableWiki']) {
		unset($LoggedUser['Permissions']['site_edit_wiki']);
	}
	
	// IP changed
	if($LoggedUser['IP']!=$_SERVER['REMOTE_ADDR'] && !check_perms('site_disable_ip_history')) {
		
		if(site_ban_ip($_SERVER['REMOTE_ADDR'])) {
			error('Your IP has been banned.');
		}

		if(!check_perms('site_disable_ip_history')) {
			$CurIP = db_string($LoggedUser['IP']);
			$NewIP = db_string($_SERVER['REMOTE_ADDR']);

			$DB->query("UPDATE users_history_ips SET
					EndTime='".sqltime()."'
					WHERE EndTime IS NULL
					AND UserID='$LoggedUser[ID]'
					AND IP='$CurIP'");
			
			$DB->query("INSERT IGNORE INTO users_history_ips
					(UserID, IP, StartTime) VALUES
					('$LoggedUser[ID]', '$NewIP', '".sqltime()."')");

			$ipcc = geoip($_SERVER['REMOTE_ADDR']);
			$DB->query("UPDATE users_main SET IP='$NewIP', ipcc='".$ipcc."' WHERE ID='$LoggedUser[ID]'");
			$Cache->begin_transaction('user_info_heavy_'.$LoggedUser['ID']);
			$Cache->update_row(false, array('IP' => $_SERVER['REMOTE_ADDR']));
			$Cache->commit_transaction(0);
			
			
		}
	}
	
	if(empty($LoggedUser['Username'])) {
		logout(); // Ghost
	}
}


$Debug->set_flag('end user handling');

$Debug->set_flag('start user function definitions');

// Get cached user info, is used for the user loading the page and usernames all over the site
function user_info($UserID) {
	global $DB, $Cache;
	$UserInfo = $Cache->get_value('user_info_'.$UserID);
	// the !isset($UserInfo['Paranoia']) can be removed after a transition period
	if(empty($UserInfo) || empty($UserInfo['ID']) || !isset($UserInfo['Paranoia'])) {


		$DB->query("SELECT
			m.ID,
			m.Username,
			m.PermissionID,
			m.Paranoia,
			i.Donor,
			i.Warned,
			i.Avatar,
			m.Enabled,
			m.Title,
			i.CatchupTime,
			m.Visible
			FROM users_main AS m
			INNER JOIN users_info AS i ON i.UserID=m.ID
			WHERE m.ID='$UserID'");
		if($DB->record_count() == 0) { // Deleted user, maybe?
			$UserInfo = array('ID'=>'','Username'=>'','PermissionID'=>0,'Artist'=>false,'Donor'=>false,'Warned'=>'0000-00-00 00:00:00','Avatar'=>'','Enabled'=>0,'Title'=>'', 'CatchupTime'=>0, 'Visible'=>'1');

		} else {
			$UserInfo = $DB->next_record(MYSQLI_ASSOC, array('Title'));
			$UserInfo['CatchupTime'] = strtotime($UserInfo['CatchupTime']);
		}
		$Cache->cache_value('user_info_'.$UserID, $UserInfo, 2592000);
	}
	if(strtotime($UserInfo['Warned']) < time()) {
		$UserInfo['Warned'] = '0000-00-00 00:00:00';
		$Cache->cache_value('user_info_'.$UserID, $UserInfo, 2592000);
	}

	return $UserInfo;
}

// Only used for current user
function user_heavy_info($UserID) {
	global $DB, $Cache;
	$HeavyInfo = $Cache->get_value('user_info_heavy_'.$UserID);

	if(empty($HeavyInfo)) {

		$DB->query("SELECT
			m.Invites,
			m.TorrentKey,
			m.IP,
			m.CustomPermissions,
			i.AuthKey,
			i.StyleID,
			i.StyleURL,
			i.DisableInvites,
			i.DisablePosting,
			i.DisableUpload,
			i.DisableWiki,
			i.DisableAvatar,
			i.DisablePM,
			i.DisableRequests,
			i.SiteOptions,
			i.DownloadAlt,
			i.LastReadNews
			FROM users_main AS m
			INNER JOIN users_info AS i ON i.UserID=m.ID
			WHERE m.ID='$UserID'");
		$HeavyInfo = $DB->next_record(MYSQLI_ASSOC, array('CustomPermissions', 'SiteOptions'));

		if (!empty($HeavyInfo['CustomPermissions'])) {
			$HeavyInfo['CustomPermissions'] = unserialize($HeavyInfo['CustomPermissions']);
		}

		if(!empty($HeavyInfo['SiteOptions'])) {
			$HeavyInfo['SiteOptions'] = unserialize($HeavyInfo['SiteOptions']);
			$HeavyInfo = array_merge($HeavyInfo, $HeavyInfo['SiteOptions']);
		}
		unset($HeavyInfo['SiteOptions']);

		$Cache->cache_value('user_info_heavy_'.$UserID, $HeavyInfo, 0);
	}
	return $HeavyInfo;
}


// Function Logout
function logout() {
	global $SessionID, $LoggedUser, $DB, $Cache;
	setcookie('session','',time()-60*60*24*365,'/','',false);
	setcookie('keeplogged','',time()-60*60*24*365,'/','',false);
	setcookie('session','',time()-60*60*24*365,'/','',false);
	if($SessionID) {
		$DB->query("DELETE FROM users_sessions WHERE UserID='$LoggedUser[ID]' AND SessionID='".db_string($SessionID)."'");
		$Cache->begin_transaction('users_sessions_'.$LoggedUser['ID']);
		$Cache->delete_row($SessionID);
		$Cache->commit_transaction(0);
	}
	$Cache->delete_value('user_info_'.$LoggedUser['ID']);
	$Cache->delete_value('user_stats_'.$LoggedUser['ID']);
	$Cache->delete_value('user_info_heavy_'.$LoggedUser['ID']);

	header('Location: index.php?c=login');
	
	die();
}

// Enforce Login Function - Check to make sure user is logged into site
function enforce_login() {
	global $SessionID, $LoggedUser;
	if (!$SessionID || !$LoggedUser) {
		setcookie('redirect',$_SERVER['REQUEST_URI'],time()+60*30,'/','',false);
		logout();
	}
}


// Get Permission Info
function get_permissions($PermissionID) {
	global $DB, $Cache;
	$Permission = $Cache->get_value('perm_'.$PermissionID);
	if(empty($Permission)) {
		$DB->query("SELECT p.Level AS Class, p.Values as Permissions FROM permissions AS p WHERE ID='$PermissionID'");
		$Permission = $DB->next_record(MYSQLI_ASSOC, array('Permissions'));
		$Permission['Permissions'] = unserialize($Permission['Permissions']);
		$Cache->cache_value('perm_'.$PermissionID, $Permission, 2592000);
	}
	return $Permission;
}

// Check Permission Function
function check_perms($PermissionName,$MinClass = 0) {
	global $LoggedUser;
	return (isset($LoggedUser['Permissions'][$PermissionName]) && $LoggedUser['Permissions'][$PermissionName] && $LoggedUser['Class']>=$MinClass)?true:false;
}

// Disable User mainly for auto disabling such as thru tracker or cleanup system
function disable_users($UserIDs, $AdminComment, $BanReason = 1) {
	global $Cache, $DB;
	if(!is_array($UserIDs)) {
		$UserIDs = array($UserIDs);
	}
	$DB->query("UPDATE users_info AS i JOIN users_main AS m ON m.ID=i.UserID
		SET m.Enabled='2',
		m.can_leech='0',
		i.AdminComment = CONCAT('".sqltime()." - ".($AdminComment ? $AdminComment : 'Disabled by system')."\n\n', i.AdminComment),
		i.BanDate='".sqltime()."',
		i.BanReason='".$BanReason."',
		i.RatioWatchDownload='0',
		i.RatioWatchEnds='0000-00-00 00:00:00'
		WHERE m.ID IN(".implode(',',$UserIDs).") ");
	$Cache->decrement('stats_user_count',$DB->affected_rows());
	foreach($UserIDs as $UserID) {
		$Cache->delete_value('enabled_'.$UserID);
		$Cache->delete_value('user_info_'.$UserID);
	}

}



?>
