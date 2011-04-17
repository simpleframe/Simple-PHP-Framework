<?php

/*
|========================================|
|  Pirate Media Source Code              |
|  Requires == MySQL 5.3+                |
|  Requires == PHP 5.3.0+                |
|  Requires == PHP-CLI 5.3.0+
|  Requires == Memcached 1.4.5+          |
|  http://piratemedia.info for support   |
|  (c) 2010 by NoName                    |
|  Free to use and modifiy and share     |
|  Not for Sale                          |
|========================================|
*/

// Configuration Variables Site Wide
$Settings['site_online']         = '1'; // 0 for off 1 for on
$Settings['site_name']           = 'Pirate Media'; // Site name
$Settings['signup_online']       = '1'; // 0 for off 1 to allow open signups
$Settings['domain']              = 'http://mixedsalt.info'; // tld for the site
$Settings['tracker_domain']      = 'http://mixedsalt.info'; // tld with subdomain for tracker if running on seperate server
$Settings['static_domain']       = 'http://mixedsalt.info'; // tld for static content - same as domain unless you intend to serve static files off different server
$Settings['site_funds']          = '15'; // Amount of Funds site requires used for donation percentage and triggers associated with it
$Settings['password_salt']       = '5AEthBFPVK9IEgk91RvSN9Zse8M33PpnecRwk4XhAAOHAcZD5cenNHtTBy5y2UH';  // Random Key used for site wide salting of passwords
define('DEBUG_MODE', true); // Turns on and off debug mode

// Configuration Variables for Tracker
$Settings['tracker_online']      = '1'; // 0 for off 1 for on
$Settings['tracker_connect']     = '1'; // 0 for off 1 to check users for connectablity
$Settings['announce_interval']   = 60*30; // Interval which to allow announces to happen

// Configuration Variables for Users and their Settings
$Settings['users_limit']         = '5000'; // Maximum number of enabled users
$Settings['users_max_invites']   = '5'; // Maximum number of invites for users to have before system stops giving them out (Per User Limit)
$Settings['users_site_invites']  = '5000'; // Maximum number of invites all users can have before system stops giving them out (Site Wide Limit)
// User Class Definitions One line per userclass
//define(USER_CLASS_NAME , 'LEVEL') // Define Userclass and Level 
define(CLASS_USER,  '0');
define(CLASS_PUSER, '1');
define(CLASS_SUSER, '2');
define(CLASS_VUSER, '3');
define(CLASS_LUSER, '4');
define(CLASS_MUSER, '5');
define(CLASS_AUSER, '6');
define(CLASS_SYSOP, '7');

// Configuration Variables for MySQL Server
define('MYSQL_SERVER',   'localhost'); // Usually localhost unless you use a seperate server for your databases
define('MYSQL_SOCKET',   '/var/run/mysqld/mysqld.sock'); // Usually /var/run/mysqld/mysqld.sock
define('MYSQL_PORT',     '3306'); // Usually 3306 unless you run your DB on a differnt port for security reasons
define('MYSQL_USER',     'piratemedia'); // The username you specified access to when setting up your DB
define('MYSQL_PASS',     'hEXrUrbvxPueNanB'); // The password you gave above username
define('MYSQL_DATABASE', 'piratemedia'); // The database name you made for the site

// Configuration Variables for Memcached Server
define('MEMCACHE_HOST', 'localhost'); // Usually localhost - unix sockets are safer
define('MEMCACHE_PORT', 11211); // Default port is 11211 unless you specified a port in your memcached config or startup command

// Configuration Variables For IRC Bot and Server
$Settings['ircbot_online']       = '1'; // 0 for off 1 for on
$Settings['ircbot_nick']         = 'PirateMedia'; // IRC Nick of IRC bot
$Settings['ircbot_pass']         = '7PEferBq43dj6wbT'; // IRC bot's nickserv pass
$Settings['ircbot_use_oper']     = '0'; // User oper for IRC Bot
$Settings['ircbot_opername']     = ''; // Oper name for IRC Bot
$Settings['ircbot_operpass']     = ''; // Oper pass for IRC Bot
$Settings['ircbot_server']       = 'irc.coexist.biz'; // IRC Server where IRC bot resides
$Settings['ircbot_port']         = '6667'; // IRC Server port where IRC bot resides
$Settings['ircbot_announce']     = '1'; // 0 for off 1 to send messages to IRC Bot
$Settings['ircbot_listen']       = 'localhost'; // Address where the bot is listening for messages
$Settings['ircbot_listen_port']  = '51623'; // Port where the bot is listening for messages
$Settings['ircbot_auto_voice']   = '0'; // 0 for off 1 to Make bot auto voice certain classes
$Settings['ircbot_voice_class']  = array('2', '3'); // Array of class levels to auto voice
$Settings['ircbot_auto_op']      = '0'; // 0 for off 1 to make bot auto op moderators
// Define Channels Used for sending IRC Bot Messages
//define(CHAN_NAME , '#CHANNEL');
define(CHAN_MAIN,     '#PIRATEMEDIA');           // Main Channel
define(CHAN_STAFF,    '#PIRATEMEDIA-Staff');     // Staff Channel
define(CHAN_HELP,     '#PIRATEMEDIA-Help');      // Support Channel
define(CHAN_DISABLED, '#PIRATEMEDIA-Disabled');  // Disabled Users Channel
define(CHAN_LAB,      '#PIRATEMEDIA-Lab');       // Lab Channel (For expiriments)
define(CHAN_ERROR,    '#PIRATEMEDIA-Error');     // Channel to funnel all errors to
define(CHAN_ADMIN,    '#PIRATEMEDIA-Admin');     // Channel for administration of the bot

// Configuration Variables for alerts
$Settings['new_message_alert']   = '0'; // 0 for off 1 for on
$Settings['staff_message_alert'] = '0'; // 0 for off 1 for on
$Settings['bug_report_alert']    = '0'; // 0 for off 1 for on
$Settings['user_ticket_alert']   = '0'; // 0 for off 1 for on
$Settings['flag_user_alert']     = '0'; // 0 for off 1 for on
$Settings['dupe_user_alert']     = '0'; // 0 for off 1 for on
$Settings['error_alert']         = '0'; // 0 for off 1 for on
$Settings['t_report_alert']      = '0'; // 0 for off 1 for on
$Settings['u_report_alert']      = '0'; // 0 for off 1 for on
$Settings['p_report_alert']      = '0'; // 0 for off 1 for on

// Configuration Variables for Login System
$Settings['login_online']        = '1'; // 0 for off 1 for on
$Settings['login_type']          = '0'; // 0 for standard login 1 for User specific login url
$Settings['login_log']           = '0'; // 0 for off 1 to Log each and every login by a user
$Settings['login_ip_log']        = '1'; // 0 for off 1 to Log IP changes for users when logging in
$Settings['login_ip_log_staff']  = '0'; // 0 for off 1 to Log IP changes for staff members when loggin in
$Settings['login_captcha']       = '0'; // 0 for off 1 for on

// Configuration Variables for Signup System
$Settings['signup_open']         = '1'; // 0 for off 1 to allow sign ups without an invite
$Settings['signup_type']         = '0'; // 0 for auto approval 1 to require staff approval of every account
$Settings['signup_checks']       = '0'; // 0 for none 1 to check user's ip/email/username/etc. against site

// Configuration Variables for Invite System
$Settings['invites_online']      = '0'; // 0 for off 1 for on
$Settings['invites_starting']    = '0'; // Number of invites to give to new users
$Settings['invites_promotion']   = '0'; // Number of invites to give to users on promotion
$Settings['invites_puser']       = '0'; // Number of invites to give to power users monthly
$Settings['invites_veteran']     = '0'; // Number of invites to give to verteran users monthly
$Settings['invites_legends']     = '0'; // Number of invites to give to ex-staff users monthly

// Configuration Variables for Fourms
$Settings['forums_online']       = '0'; // 0 for off 1 for on
$Settings['forums_restricted']   = '0'; // Restrict forums to specific class level and up
$Settings['forums_stats_on']     = '0'; // Show forum statistics at the bottom of the forum
$Settings['forums_reports_on']   = '0'; // Allow reporting of posts for bad content
$Settings['forums_avatars']      = '0'; // Show user avatars in info block 0 for off 1 for on
$Settings['forums_moderators']   = '0'; // 0 for off 1 to allow non-staff member forum moderators

// Configuration Variables for Browse(Torrents)
$Settings['browse_online']       = '0'; // 0 for off 1 for on
$Settings['browse_ajax']         = '0'; // 0 for standard 1 for ajax
$Settings['browse_icons']        = '0'; // 0 for off 1 for on

// Configuration Variables for Upload(Torrents)
$Settings['upload_online']       = '0'; // 0 for off 1 for on
$Settings['upload_min_class']    = '0'; // Minimum Class Level for upload
$Settings['upload_approve']      = '0'; // 0 for off 1 to require staff approval of uploads
$Settings['upload_notify']       = '0'; // 0 for off 1 to auto post to a staff forum
$Settings['upload_anon']         = '0'; // 0 for off 1 to allow anonoymous uploads
$Settings['upload_points']       = '0'; // Number of points to give for each upload

// Configuration Variables for Download(Torrent Files)
$Settings['download_online']     = '0'; // 0 for off 1 for on
$Settings['download_min_class']  = '0'; // Minimum Class Level for downloading torrent files
$Settings['download_log']        = '0'; // Keep a log for torrent files downloaded **NOT SNATCHING OF CONTENT** ===Logs only downloading of the torrent file===
$Settings['download_points']     = '0'; // 0 for off 1 to charge bonus points to download torrent files
$Settings['download_cost']       = '0'; // Number of points to charge per downloaded torrent
$Settings['download_auto_refund']= '0'; // 0 for off 1 to auto refund charged points if content not snatched

// Configuration Variables for Requests
$Settings['request_online']      = '0'; // 0 for off 1 for on
$Settings['request_min_class']   = '0'; // Minimum Class Level to use request system
$Settings['request_approve']     = '0'; // 0 for off 1 to require staff approval of requests
$Settings['request_notify']      = '0'; // 0 for off 1 to auto post to a staff forum
$Settings['request_anon']        = '0'; // 0 for off 1 to allow anonoymous requests
$Settings['request_points']      = '0'; // Number of points to give for filling request
$Settings['request_cost']        = '0'; // Number of points each request costs
$Settings['request_tax']         = '0'; // 0 for off 1 to charge a tax on requests
$Settings['request_tax_cost']    = '0'; // Number of points to tax off of requests

// Configuration Variables for Offers
$Settings['offers_online']       = '0'; // 0 for off 1 for on
$Settings['offers_min_class']    = '0'; // Minimum Class Level to use request system
$Settings['offers_approve']      = '0'; // 0 for off 1 to require staff approval of requests
$Settings['offers_notify']       = '0'; // 0 for off 1 to auto post offers to a staff forum
$Settings['offers_anon']         = '0'; // 0 for off 1 to allow anonoymous offers
$Settings['offers_points']       = '0'; // Number of points to give for making an offer
$Settings['offers_up_points']    = '0'; // Number of points to give for uploading an offer
$Settings['offers_tax']          = '0'; // 0 for off 1 to charge a tax on an offers points pool
$Settings['offers_tax_cost']     = '0'; // Number of points to tax off points pool for an offer


?>