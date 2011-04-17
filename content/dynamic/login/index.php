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

/*-- TODO ---------------------------//
Add the javascript validation into the display page using the class
//-----------------------------------*/

if(!empty($LoggedUser['ID'])) {
	header('Location: index.php');
	die();
}


// Check if IP is banned
if($BanID = site_ban_ip($_SERVER['REMOTE_ADDR'])) {
	error('Your IP has been banned.');
}


$Validate=NEW VALIDATE;

// Normal login
elseif(isset($_POST['username'])) {
	$Validate->SetFields('username',true,'regex','You did not enter a valid username.',array('regex'=>'/^[a-z0-9_?]{1,20}$/i'));
	$Validate->SetFields('password','1','string','You entered an invalid password.',array('maxlength'=>'40','minlength'=>'6'));

	$DB->query("SELECT ID, Attempts, Bans, BannedUntil FROM login_attempts WHERE IP='".db_string($_SERVER['REMOTE_ADDR'])."'");
	list($AttemptID,$Attempts,$Bans,$BannedUntil)=$DB->next_record();

	// Function to log a user's login attempt
	function log_attempt($UserID) {
		global $DB, $AttemptID, $Attempts, $Bans, $BannedUntil, $Time;
		if($AttemptID) { // User has attempted to log in recently
			$Attempts++;
			if ($Attempts>5) { // Only 6 allowed login attempts, ban user's IP
				$BannedUntil=time_plus(60*60*6);
				$DB->query("UPDATE login_attempts SET
					LastAttempt='".sqltime()."',
					Attempts='".db_string($Attempts)."',
					BannedUntil='".db_string($BannedUntil)."',
					Bans=Bans+1 
					WHERE ID='".db_string($AttemptID)."'");
				
					if ($Bans>9) { // Automated bruteforce prevention
						$IP = ip2unsigned($_SERVER['REMOTE_ADDR']);
						$DB->query("SELECT Reason FROM ip_bans WHERE ".$IP." BETWEEN FromIP AND ToIP");
						if($DB->record_count() > 0) {
							//Ban exists already, only add new entry if not for same reason
							list($Reason) = $DB->next_record(MYSQLI_BOTH, false);
							if($Reason != "Automated ban per >60 failed login attempts") {
								$DB->query("UPDATE ip_bans
									SET Reason = CONCAT('Automated ban per >60 failed login attempts AND ', Reason)
									WHERE FromIP = ".$IP." AND ToIP = ".$IP);
							}
						} else {
							//No ban
							$DB->query("INSERT INTO ip_bans
								(FromIP, ToIP, Reason) VALUES
								('$IP','$IP', 'Automated ban per >60 failed login attempts')");
						}
					}
			} else {
				// User has attempted fewer than 6 logins
				$DB->query("UPDATE login_attempts SET
					LastAttempt='".sqltime()."',
					Attempts='".db_string($Attempts)."',
					BannedUntil='0000-00-00 00:00:00' 
					WHERE ID='".db_string($AttemptID)."'");
			}
		} else { // User has not attempted to log in recently
			$Attempts=1;
			$DB->query("INSERT INTO login_attempts 
				(UserID,IP,LastAttempt,Attempts) VALUES 
				('".db_string($UserID)."','".db_string($_SERVER['REMOTE_ADDR'])."','".sqltime()."',1)");
		}
	} // end log_attempt function
	
	// If user has submitted form
	if(isset($_POST['username']) && !empty($_POST['username']) && isset($_POST['password']) && !empty($_POST['password'])) {
		$Err=$Validate->ValidateForm($_POST);

		if(!$Err) {
			// Passes preliminary validation (username and password "look right")
			$DB->query("SELECT
				ID,
				PermissionID,
				CustomPermissions,
				PassHash,
				Secret,
				Enabled
				FROM users_main WHERE Username='".db_string($_POST['username'])."' 
				AND Username<>''");
			list($UserID,$PermissionID,$CustomPermissions,$PassHash,$Secret,$Enabled)=$DB->next_record();
			if (strtotime($BannedUntil)<time()) {
				if ($UserID && $PassHash==make_hash($_POST['password'],$Secret)) {
					if ($Enabled == 1) {
						$SessionID = make_secret();
						$Cookie = $Enc->encrypt($Enc->encrypt($SessionID.'|~|'.$UserID));

						if(isset($_POST['keeplogged']) && $_POST['keeplogged']) {
							$KeepLogged = 1;
							setcookie('session', $Cookie,time()+60*60*24*365,'/','',false);
						} else {
							$KeepLogged = 0;
							setcookie('session', $Cookie,0,'/','',false);
						}
						
						if(is_array($LoggedUser['CustomPermissions'])) {
							$CustomPerms = $LoggedUser['CustomPermissions'];
						} else {
							$CustomPerms = array();
						}
						
						//TODO: another tracker might enable this for donors, I think it's too stupid to bother adding that
						// Because we <3 our staff
						$Permissions = get_permissions($PermissionID);
						$CustomPermissions = unserialize($CustomPermissions);
						if (
							isset($Permissions['Permissions']['site_disable_ip_history']) || 
							isset($CustomPermissions['Permissions']['site_disable_ip_history'])
						) { $_SERVER['REMOTE_ADDR'] = '127.0.0.1'; }
						
						
						
						$DB->query("INSERT INTO users_sessions
							(UserID, SessionID, KeepLogged, Browser, OperatingSystem, IP, LastUpdate)
							VALUES ('$UserID', '".db_string($SessionID)."', '$KeepLogged', '$Browser','$OperatingSystem', '".db_string($_SERVER['REMOTE_ADDR'])."', '".sqltime()."')");

						$Cache->begin_transaction('users_sessions_'.$UserID);
						$Cache->insert_front($SessionID,array(
								'SessionID'=>$SessionID,
								'Browser'=>$Browser,
								'OperatingSystem'=>$OperatingSystem,
								'IP'=>$_SERVER['REMOTE_ADDR'],
								'LastUpdate'=>sqltime()
								));
						$Cache->commit_transaction(0);
						
						$Sql = "UPDATE users_main 
							SET 
								LastLogin='".sqltime()."',
								LastAccess='".sqltime()."'";
						
						$Sql .= "	WHERE ID='".db_string($UserID)."'";

						$DB->query($Sql);
						
						if($Attempts > 0) {
							$DB->query("DELETE FROM login_attempts WHERE ID='".db_string($AttemptID)."'");
						}

						if (!empty($_COOKIE['redirect'])) {
							$URL = $_COOKIE['redirect'];
							setcookie('redirect','',time()-60*60*24,'/','',false);
							header('Location: '.$URL);
							die();
						} else {
							header('Location: index.php');
							die();
						}
					} else {
						log_attempt($UserID);
						if ($Enabled==2) {
							
							header('location:login.php?action=disabled');
						} elseif ($Enabled==0) {
							$Err="Your account has not been confirmed.<br />Please check your email.";
						}
						setcookie('keeplogged','',time()+60*60*24*365,'/','',false);
					}
				} else {
					log_attempt($UserID);
					
					$Err="Your username or password was incorrect.";
					setcookie('keeplogged','',time()+60*60*24*365,'/','',false);
				}
				
			} else {
				log_attempt($UserID);
				setcookie('keeplogged','',time()+60*60*24*365,'/','',false);
			}

		} else {
			log_attempt('0');
			setcookie('keeplogged','',time()+60*60*24*365,'/','',false);
		}
	}
}
?>

	<span id="no-cookies" class="hidden warning">You appear to have cookies disabled.<br /><br /></span>
	<noscript><span class="warning">You appear to have javascript disabled.</span><br /><br /></noscript> 
<?
if(strtotime($BannedUntil)<time() && !$BanID) {
?>
	<form id="loginform" method="post" action="index.php?c=login">
<?

	if(!empty($BannedUntil) && $BannedUntil != '0000-00-00 00:00:00') {
		$DB->query("UPDATE login_attempts SET BannedUntil='0000-00-00 00:00:00', Attempts='0' WHERE ID='".db_string($AttemptID)."'");
		$Attempts = 0;
	}
	if(isset($Err)) {
?>
	<span class="warning"><?=$Err?><br /><br /></span>
<? } ?>
<? if ($Attempts > 0) { ?>
	You have <span class="info"><?=(6-$Attempts)?></span> attempts remaining.<br /><br />
	<strong>WARNING:</strong> You will be banned for 6 hours after your login attempts run out!<br /><br />
<? } ?>
	<table>
		<tr>
		<?
		if($Settings['login_type'] == '0'){
		?>
			<td>Username&nbsp;</td>
			<td colspan="2"><input type="text" name="username" id="username" class="inputtext" required="required" maxlength="20" pattern="[A-Za-z0-9_?]{1,20}" autofocus="autofocus" /></td>
		<?
		}
		?>
		</tr>
		<tr>
			<td>Password&nbsp;</td>
			<td colspan="2"><input type="password" name="password" id="password" class="inputtext" required="required" maxlength="40" pattern=".{6,40}" /></td>
		</tr>
		<tr>
			<td></td>
			<td>
				<input type="checkbox" id="keeplogged" name="keeplogged" value="1"<? if(isset($_REQUEST['keeplogged']) && $_REQUEST['keeplogged']) { ?> checked="checked"<? } ?> />
				<label for="keeplogged">Remember me</label>
			</td>
			<td><input type="submit" name="login" value="Login" class="submit" /></td>
		</tr>
	</table>
	</form>
<?
} else {
	if($BanID) {
?>
	<span class="warning">Your IP is banned indefinitely.</span>
<? } else { ?>
	<span class="warning">You are banned from logging in for another <?=time_diff($BannedUntil)?>.</span>
<?
	}
}

if ($Attempts > 0) {
?>
	<br /><br />
	Lost your password? <a href="login.php?act=recover">Recover it here!</a>
<? } ?>
<script type="text/javascript">
cookie.set('cookie_test',1,1);
if (cookie.get('cookie_test') != null) {
	cookie.del('cookie_test');
} else {
	$('#no-cookies').show();
}
</script>
