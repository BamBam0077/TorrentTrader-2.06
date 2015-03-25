<?php
//
//  TorrentTrader v2.x
//  This file was last updated: 22/Sep/2008 by TorrentialStorm
//	
//	http://www.torrenttrader.org
//
//
error_reporting(E_ALL ^ E_NOTICE);
// register_globals on *fix*
if (!ini_get("register_globals")) {
    import_request_variables('GPC');
}

//If running on a windows server you "may" have to use absolute paths here

$BASEPATH = str_replace("backend", "", dirname(__FILE__));
$BACKEND = dirname(__FILE__);

require_once("$BACKEND/mysql.php"); //Get MYSQL Connection Info
require_once("$BACKEND/config.php");  //Get Site Settings and Vars ($site_config)
require("$BACKEND/tzs.php"); // Get Timezones
require_once("$BACKEND/cache.php"); // Caching



function dbconn($autoclean = false) {
	global $mysql_host, $mysql_user, $mysql_pass, $mysql_db, $THEME, $LANGUAGE, $site_config;

	if (!ob_get_level()) {
		if (extension_loaded('zlib') && !ini_get('zlib.output_compression'))
			ob_start('ob_gzhandler');
		else
			ob_start();
	}

	header("Content-Type: text/html;charset=$site_config[CHARSET]");

	if (!function_exists("mysql_connect"))
		die("MySQL support not available.");

    if (!@mysql_connect($mysql_host, $mysql_user, $mysql_pass))
    {
      die('DATABASE: mysql_connect: ' . mysql_error());
    }
     mysql_select_db($mysql_db)
        or die('DATABASE: mysql_select_db: ' . mysql_error());

	unset($mysql_pass); //security

    userlogin(); //Get user info	

	//Get language and theme
	$CURUSER = $GLOBALS["CURUSER"];
	if ($CURUSER)  {
		$ss_a = @mysql_fetch_array(@mysql_query("SELECT uri FROM stylesheets WHERE id=$CURUSER[stylesheet]"));
        if ($ss_a)
            $THEME = $ss_a["uri"];
        else {
            $ss_a = @mysql_fetch_array(@mysql_query("select uri from stylesheets where id=$site_config[default_theme]"));
            $THEME = $ss_a['uri'];
        }
        $lng_a = @mysql_fetch_array(@mysql_query("select uri from languages where id=$CURUSER[language]"));
        if ($lng_a)
            $LANGUAGE = $lng_a["uri"];
        else {
            $lng_a = @mysql_fetch_array(@mysql_query("select uri from languages where id=$site_config[default_language]"));
            $LANGUAGE = $lng_a['uri'];
        }
	}else{//not logged in so get default theme/language
		$ss_a = mysql_fetch_array(mysql_query("select uri from stylesheets where id='" . $site_config['default_theme'] . "'")) or die(mysql_error());
		if ($ss_a)
			$THEME = $ss_a["uri"];
		$lng_a = mysql_fetch_array(mysql_query("select uri from languages where id='" . $site_config['default_language'] . "'")) or die(mysql_error());
		if ($lng_a)
			$LANGUAGE = $lng_a["uri"];
	}
	require_once("languages/$LANGUAGE");



	if ($autoclean)
		autoclean();
}

// Main Cleanup
function autoclean() {
	global $site_config;
    require_once("cleanup.php");

    $now = gmtime();

    $res = mysql_query("SELECT last_time FROM tasks WHERE task='cleanup'");
    $row = mysql_fetch_array($res);
    if (!$row) {
        mysql_query("INSERT INTO tasks (task, last_time) VALUES ('cleanup',$now)");
        return;
    }
    $ts = $row[0];
    if ($ts + $site_config["autoclean_interval"] > $now)
        return;
    mysql_query("UPDATE tasks SET last_time=$now WHERE task='cleanup' AND last_time = $ts");
    if (!mysql_affected_rows())
        return;

    do_cleanup();
}

// IP Validation
function validip($ip)
{
	if (!empty($ip) && $ip == long2ip(ip2long($ip)))
	{
		$reserved_ips = array (
				array('0.0.0.0','2.255.255.255'),
				array('10.0.0.0','10.255.255.255'),
				array('127.0.0.0','127.255.255.255'),
				array('169.254.0.0','169.254.255.255'),
				array('172.16.0.0','172.31.255.255'),
				array('192.0.2.0','192.0.2.255'),
				array('192.168.0.0','192.168.255.255'),
				array('255.255.255.0','255.255.255.255')
		);

		foreach ($reserved_ips as $r)
		{
				$min = ip2long($r[0]);
				$max = ip2long($r[1]);
				if ((ip2long($ip) >= $min) && (ip2long($ip) <= $max)) return false;
		}
		return true;
	}
	else return false;
}

function getip() {
   if (isset($_SERVER)) {
     if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && validip($_SERVER['HTTP_X_FORWARDED_FOR'])) {
       $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
     } elseif (isset($_SERVER['HTTP_CLIENT_IP']) && validip($_SERVER['HTTP_CLIENT_IP'])) {
       $ip = $_SERVER['HTTP_CLIENT_IP'];
     } else {
       $ip = $_SERVER['REMOTE_ADDR'];
     }
   } else {
     if (getenv('HTTP_X_FORWARDED_FOR') && validip(getenv('HTTP_X_FORWARDED_FOR'))) {
       $ip = getenv('HTTP_X_FORWARDED_FOR');
     } elseif (getenv('HTTP_CLIENT_IP') && validip(getenv('HTTP_CLIENT_IP'))) {
       $ip = getenv('HTTP_CLIENT_IP');
     } else {
       $ip = getenv('REMOTE_ADDR');
     }
   }
   return $ip;
}

function userlogin() {
	if (getip() == "") return;
    global $CURUSER;
    unset($GLOBALS["CURUSER"]);

	$ip = getip(); //GET USERS IP

	//Check IP bans    
	$nip = ip2long($ip);
	$res = mysql_query("SELECT * FROM bans WHERE $nip >= first AND $nip <= last")  or die(mysql_error());
	if (mysql_num_rows($res) > 0) {
		$row = mysql_fetch_array($res);
		header("HTTP/1.0 403 Forbidden");
		echo "<html><head><title>Forbidden</title></head><body><h1>Forbidden</h1>Unauthorized IP address.<br />".
		"Reason for banning: $row[comment]</body></html>";
		die;
	}


	//Check The Cookie and get CURUSER details
	if (strlen($_COOKIE["pass"]) != 32)
        return;

	//Get User Details And Permissions
    $res = mysql_query("SELECT * FROM users INNER JOIN groups ON users.class=groups.group_id WHERE MD5(CONCAT(users.id, '".getip()."', users.secret, users.password, users.secret)) = ".sqlesc($_COOKIE["pass"])." AND users.enabled='yes' AND users.status = 'confirmed'") or die(mysql_error());
    $row = mysql_fetch_array($res);

	if (!$row)
        return;

	$where = where ($_SERVER["SCRIPT_FILENAME"], $row["id"], 0);
    mysql_query("UPDATE users SET last_access='" . get_date_time() . "', ip=".sqlesc($ip).", page=".sqlesc($where)." WHERE id=" . $row["id"]) or die(mysql_error());


    $GLOBALS["CURUSER"] = $row;
	unset($row);
}

function logincookie($id, $password, $secret, $updatedb = 1, $expires = 0x7fffffff) {
    $md5 = md5($id.getip().$secret.$password.$secret);
    setcookie("pass", $md5, $expires, "/");

    if ($updatedb)
        mysql_query("UPDATE users SET last_login = '".get_date_time()."' WHERE id = $id");
}

function logoutcookie() {
	setcookie("pass", "null", time(), "/");
}

function stdhead($title = "") {
	global $site_config, $CURUSER, $THEME, $LANGUAGE;  //Define globals
 
	//require_once("cleanup.php");  //temp cleanup linkage :D
	//docleanup();//TEMP CLEANUP CALL

	//site online check
	if (!$site_config["SITE_ONLINE"]){
		if ($CURUSER["level"]!=="Administrator") {
			echo '<BR><BR><BR><CENTER>'. stripslashes($site_config["OFFLINEMSG"]) .'</CENTER><BR><BR>';
			die;
		}else{
			echo '<BR><BR><BR><CENTER><B><FONT COLOR=RED>SITE OFFLINE, ADMIN ONLY VIEWING! DO NOT LOGOUT</FONT></B><BR>If you logout please edit backend/config.php and set SITE_ONLINE to true </CENTER><BR><BR>';
		}
	}
	//end check

    if (!$CURUSER)
		guestadd();

    if ($title == "")
        $title = $site_config['SITENAME'];
    else
        $title = $site_config['SITENAME']. " : ". htmlspecialchars($title);

	require_once("themes/" . $THEME . "/block.php");
	require_once("themes/" . $THEME . "/header.php");
}

function stdfoot() {
	global $site_config, $CURUSER, $THEME, $LANGUAGE;
	require_once("themes/" . $THEME . "/footer.php");
	mysql_close();
}

function leftblocks() {
    global $site_config, $CURUSER, $THEME, $LANGUAGE, $TTCache;  //Define globals
    
    if (($blocks=$TTCache->get("blocks_left", 900)) === false) {
        $res = mysql_query("SELECT * FROM blocks WHERE position='left' AND enabled=1 ORDER BY sort");
        $i = 0;
        $blocks = array();
        while ($result = mysql_fetch_array($res)) {
                $blocks[] = $result["name"];
        }
        $TTCache->Set("blocks_left", $blocks, 900);
    }

    foreach ($blocks as $blockfilename){
        include("blocks/".$blockfilename."_block.php");
    }
}

function rightblocks() {
    global $site_config, $CURUSER, $THEME, $LANGUAGE, $TTCache;  //Define globals
    
    if (($blocks=$TTCache->get("blocks_right", 900)) === false) {
        $res = mysql_query("SELECT * FROM blocks WHERE position='right' AND enabled=1 ORDER BY sort");
        $i = 0;
        $blocks = array();
        while ($result = mysql_fetch_array($res)) {
                $blocks[] = $result["name"];
        }
        $TTCache->Set("blocks_right", $blocks, 900);
    }

    foreach ($blocks as $blockfilename){
        include("blocks/".$blockfilename."_block.php");
    }
}

function middleblocks() {
    global $site_config, $CURUSER, $THEME, $LANGUAGE, $TTCache;  //Define globals
    
    if (($blocks=$TTCache->get("blocks_middle", 900)) === false) {
        $res = mysql_query("SELECT * FROM blocks WHERE position='middle' AND enabled=1 ORDER BY sort");
        $i = 0;
        $blocks = array();
        while ($result = mysql_fetch_array($res)) {
                $blocks[] = $result["name"];
        }
        $TTCache->Set("blocks_middle", $blocks, 900);
    }

    foreach ($blocks as $blockfilename){
        include("blocks/".$blockfilename."_block.php");
    }
}

function show_error_msg($title, $message, $wrapper = "1") {
    if ($wrapper=="1") {
		stdhead($title);
		//echo "<b>DEBUG: stdhead Wrapper ON/Kill php gen further</b>";//remove later
	}
		begin_frame("<font color=red>". htmlspecialchars($title) ."</font>");
		print("<p><CENTER><B>" . stripslashes(sqlesc($message)) . "</B></CENTER></p>\n");
		end_frame();

    if ($wrapper=="1"){
		stdfoot();
		die();
	}
}

// New (TorrentialStorm 19/Feb/2008 @ 13:15)
function health($leechers, $seeders) {
	if (($leechers == 0 && $seeders == 0) || ($leechers > 0 && $seeders == 0))
		return 0;
	elseif ($seeders > $leechers)
		return 10;

	$ratio = $seeders / $leechers * 100;
	if ($ratio > 0 && $ratio < 15)
		return 1;
	elseif ($ratio >= 15 && $ratio < 25)
		return 2;
	elseif ($ratio >= 25 && $ratio < 35)
		return 3;
	elseif ($ratio >= 35 && $ratio < 45)
		return 4;
	elseif ($ratio >= 45 && $ratio < 55)
		return 5;
	elseif ($ratio >= 55 && $ratio < 65)
		return 6;
	elseif ($ratio >= 65 && $ratio < 75)
		return 7;
	elseif ($ratio >= 75 && $ratio < 85)
		return 8;
	elseif ($ratio >= 85 && $ratio < 95)
		return 9;
	else
		return 10;
}


//secure vars
function sqlesc($x) {
   if (get_magic_quotes_gpc()) {
       $x = stripslashes($x);
   }
   if (!is_numeric($x)) {
       $x = "'".mysql_real_escape_string($x)."'";
   }
   return $x;
}


function unesc($x) {
	if (get_magic_quotes_gpc())
		return stripslashes($x);
	return $x;
}

function mkglobal($vars) {
    if (!is_array($vars))
        $vars = explode(":", $vars);
    foreach ($vars as $v) {
        if (isset($_GET[$v]))
            $GLOBALS[$v] = stripslashes($_GET[$v]);
        elseif (isset($_POST[$v]))
            $GLOBALS[$v] = stripslashes($_POST[$v]);
        else
            return 0;
    }
    return 1;
}

function hash_pad($hash) {
    return str_pad($hash, 20);
}

function hash_where($name, $hash) {
    $shhash = preg_replace('/ *$/s', "", $hash);
    return "($name = " . sqlesc($hash) . " OR $name = " . sqlesc($shhash) . ")";
}

function file_ungzip($fromFile){
	$zp = @gzopen($fromFile, "r");
	while(!@gzeof($zp)) { $string .= @gzread($zp, 4096); }
	@gzclose($zp);
	return $string;
}

function mksize($bytes) {
	if ($bytes < 1000 * 1024)
		return number_format($bytes / 1024, 2) . " KB";
	if ($bytes < 1000 * 1048576)
		return number_format($bytes / 1048576, 2) . " MB";
	if ($bytes < 1000 * 1073741824)
		return number_format($bytes / 1073741824, 2) . " GB";
	return number_format($bytes / 1099511627776, 2) . " TB";
}

function escape_url($url) {
	$ret = '';
	for($i = 0; $i < strlen($url); $i+=2)
	$ret .= '%'.$url[$i].$url[$i + 1];
	return $ret;
}

function torrent_scrape_url($scrape, $hash) {
	if (function_exists("curl_exec")) {
		$ch = curl_init();
		$timeout = 5;
		curl_setopt ($ch, CURLOPT_URL, $scrape.'?info_hash='.escape_url($hash));
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		$fp = curl_exec($ch);
		curl_close($ch);
	} else {
		ini_set('default_socket_timeout',10); 
		$fp = @file_get_contents($scrape.'?info_hash='.escape_url($hash));
	}
	$ret = array();
	if(!$fp) {
		$ret['seeds'] = -1;
		$ret['peers'] = -1;
	}else{
		$stats = BDecode($fp);
		$binhash = addslashes(pack("H*", $hash));
		$seeds = $stats['files'][$binhash]['complete'];
		$peers = $stats['files'][$binhash]['incomplete'];
		$downloaded = $stats['files'][$binhash]['downloaded'];
		$ret['seeds'] = $seeds;
		$ret['peers'] = $peers;
		$ret['downloaded'] = $downloaded;
	}
	return $ret;
}

function mkprettytime($s) {
    if ($s < 0)
        $s = 0;
    $t = array();
    foreach (array("60:sec","60:min","24:hour","0:day") as $x) {
        $y = explode(":", $x);
        if ($y[0] > 1) {
            $v = $s % $y[0];
            $s = floor($s / $y[0]);
        }
        else
            $v = $s;
        $t[$y[1]] = $v;
    }

    if ($t["day"])
        return $t["day"] . "d " . sprintf("%02d:%02d:%02d", $t["hour"], $t["min"], $t["sec"]);
    if ($t["hour"])
        return sprintf("%d:%02d:%02d", $t["hour"], $t["min"], $t["sec"]);
        return sprintf("%d:%02d", $t["min"], $t["sec"]);
}

function gmtime() {
    return strtotime(get_date_time());
}

function loggedinonly() {
		global $CURUSER;
		if (!$CURUSER) {
			header("Refresh: 0; url=account-login.php?returnto=" . urlencode($_SERVER["REQUEST_URI"]));
			exit();
		}
}

function validfilename($name) {
    return preg_match('/^[^\0-\x1f:\\\\\/?*\xff#<>|]+$/si', $name);
}

function validemail($email) {
//    return preg_match('/^[\w.-]+@([\w.-]+\.)+[a-z]{2,6}$/is', $email);
    return preg_match('/^([a-z0-9._-](\+[a-z0-9])*)+@[a-z0-9.-]+\.[a-z]{2,6}$/i', $email);
}

function urlparse($m) {
    $t = $m[0];
    if (preg_match(',^\w+://,', $t))
        return "<a href=\"$t\">$t</a>";
    return "<a href=\"http://$t\">$t</a>";
}

function parsedescr($d, $html) {
    if (!$html)
    {
      $d = htmlspecialchars($d);
      $d = str_replace("\n", "\n<br />", $d);
    }
    return $d;
}

/* OLD
function mksecret($len = 20) {
    $ret = "";
    for ($i = 0; $i < $len; $i++)
        $ret .= chr(mt_rand(0, 255));
    return $ret;
}*/

// New (TorrentialStorm)
function mksecret($len = 20) {
	$chars = array_merge(range(0, 9), range("A", "Z"), range("a", "z"));
	shuffle($chars);
	$x = count($chars) - 1;
	for ($i = 1; $i <= $len; $i++)
		$str .= $chars[mt_rand(0, $x)];
	return $str;
}

function deletetorrent($id) {
	global $site_config;

	$row = @mysql_fetch_array(@mysql_query("SELECT image1,image2 FROM torrents WHERE id=$id"));
	
	foreach(explode(".","peers.comments.ratings") as $x)
		mysql_query("DELETE FROM $x WHERE torrent = $id");

	if (file_exists("".$site_config["torrent_dir"]."/$id.torrent"))
		unlink("".$site_config["torrent_dir"]."/$id.torrent");

	if ($row["image1"]) {
		$img1 = "".$site_config["torrent_dir"]."/images/".$row["image1"]."";
		$del = unlink($img1);
	}

	if ($row["image2"]) {
		$img2 = "".$site_config["torrent_dir"]."/images/".$row["image2"]."";
		$del = unlink($img2);
	}

	@unlink($site_config["nfo_dir"]."/$id.nfo");

	mysql_query("DELETE FROM torrents WHERE id = $id");
}

function deleteaccount($userid) {
		mysql_query("DELETE FROM users WHERE id = $userid");
		mysql_query("DELETE FROM warnings WHERE userid = $userid");
		mysql_query("DELETE FROM ratings WHERE user = $userid");
}

function genrelist() {
    $ret = array();
    $res = mysql_query("SELECT id, name, parent_cat FROM categories ORDER BY parent_cat ASC, sort_index ASC");
    while ($row = mysql_fetch_array($res))
        $ret[] = $row;
    return $ret;
}

function langlist() {
    $ret = array();
    $res = mysql_query("SELECT id, name, image FROM torrentlang ORDER BY sort_index, id");
    while ($row = mysql_fetch_array($res))
        $ret[] = $row;
    return $ret;
}

function is_valid_id($id){
	return is_numeric($id) && ($id > 0) && (floor($id) == $id);
}

function sql_timestamp_to_unix_timestamp($s){
	return mktime(substr($s, 11, 2), substr($s, 14, 2), substr($s, 17, 2), substr($s, 5, 2), substr($s, 8, 2), substr($s, 0, 4));
}

function write_log($text){
	$text = sqlesc($text);
	$added = sqlesc(get_date_time());
	mysql_query("INSERT INTO log (added, txt) VALUES($added, $text)") or sqlerr();
}

function get_elapsed_time($ts){
  $mins = floor((gmtime() - $ts) / 60);
  $hours = floor($mins / 60);
  $mins -= $hours * 60;
  $days = floor($hours / 24);
  $hours -= $days * 24;
  $weeks = floor($days / 7);
  $days -= $weeks * 7;
  $t = "";
  if ($weeks > 0)
    return "$weeks wk" . ($weeks > 1 ? "s" : "");
  if ($days > 0)
    return "$days day" . ($days > 1 ? "s" : "");
  if ($hours > 0)
    return "$hours hr" . ($hours > 1 ? "s" : "");
  if ($mins > 0)
    return "$mins min" . ($mins > 1 ? "s" : "");
  return "< 1 min";
}

function hex2bin($hexdata) {
	$bindata = "";
	for ($i=0;$i<strlen($hexdata);$i+=2) {
		$bindata.=chr(hexdec(substr($hexdata,$i,2)));
	}
	return $bindata;
}

function guestadd() {
    $ip = $_SERVER["REMOTE_ADDR"];
	$sql = mysql_query("SELECT time FROM guests WHERE ip='$ip'");
    $ctime = gmtime();
    if (mysql_fetch_row($sql))
	{
		@mysql_query("UPDATE guests SET ip='$ip', time='$ctime' WHERE ip='$ip'");
    } else {
		@mysql_query("INSERT INTO guests (ip, time) VALUES ('$ip', '$ctime')");
    }
}

function getguests() {
    $ip = $_SERVER["REMOTE_ADDR"];
    $past = gmtime()-2400;
	@mysql_query("DELETE FROM guests WHERE time < $past");
	$guests = number_format(get_row_count("guests"));
	return $guests;
}

function time_ago($addtime) {
   $addtime = get_elapsed_time(sql_timestamp_to_unix_timestamp($addtime));
   return $addtime;
}

function CutName ($vTxt, $Car) {
	while(strlen($vTxt) > $Car) {
		return substr($vTxt, 0, $Car) . "...";
	} return $vTxt;
}

function searchfield($s) {
    return preg_replace(array('/[^a-z0-9]/si', '/^\s*/s', '/\s*$/s', '/\s+/s'), array(" ", "", "", " "), $s);
}

function get_row_count($table, $suffix = "") {
  if ($suffix)
    $suffix = " $suffix";
  ($r = mysql_query("SELECT COUNT(*) FROM $table$suffix")) or die(mysql_error());
  ($a = mysql_fetch_row($r)) or die(mysql_error());
  return $a[0];
}

function sqlerr($query = "") {
	stdhead();
	begin_frame("MYSQL Error");
	print("<BR><b>MySQL error occured</b>.\n<br />Query: " . $query . "<br />\nError: (" . mysql_errno() . ") " . mysql_error());
	end_frame();
	stdfoot();
	die;
}

function get_dt_num(){
	return gmdate("YmdHis");
}


function get_date_time($timestamp = 0){
	if ($timestamp)
	return date("Y-m-d H:i:s", $timestamp);
	else
	  return gmdate("Y-m-d H:i:s");
}

// Convert UTC to user's timezone
function utc_to_tz ($timestamp=0) {
	GLOBAL $CURUSER;
	if (!is_numeric($timestamp))
		$timestamp = sql_timestamp_to_unix_timestamp($timestamp);
	if ($timestamp == 0)
		$timestamp = gmtime();

	$timestamp = $timestamp + ($CURUSER['tzoffset']*60);
	return date("Y-m-d H:i:s", $timestamp);
}

function utc_to_tz_time ($timestamp=0) {
	GLOBAL $CURUSER;

	if (!is_numeric($timestamp))
		$timestamp = sql_timestamp_to_unix_timestamp($timestamp);
	if ($timestamp == 0)
		$timestamp = gmtime();

	$timestamp = $timestamp + ($CURUSER['tzoffset']*60);
	return $timestamp;
}

function encodehtml($s, $linebreaks = true) {
	  $s = str_replace("<", "&lt;", str_replace("&", "&amp;", $s));
	  if ($linebreaks)
		$s = nl2br($s);
	  return $s;
}


function format_urls($s){
	return preg_replace(
    "/(\A|[^=\]'\"a-zA-Z0-9])((http|ftp|https|ftps|irc):\/\/[^<>\s]+)/i",
    "\\1<a href=http://anonym.to/?\\2 target=_blank>\\2</a>", $s);
}

function format_comment($text)
{
	global $site_config, $smilies;

	$s = $text;

	$s = htmlspecialchars($s);
	$s = stripslashes($s);
	$s = format_urls($s);

	// [*]
	$s = preg_replace("/\[\*\]/", "<li>", $s);

	// [b]Bold[/b]
	$s = preg_replace("/\[b\]((\s|.)+?)\[\/b\]/", "<b>\\1</b>", $s);

	// [i]Italic[/i]
	$s = preg_replace("/\[i\]((\s|.)+?)\[\/i\]/", "<i>\\1</i>", $s);

	// [u]Underline[/u]
	$s = preg_replace("/\[u\]((\s|.)+?)\[\/u\]/", "<u>\\1</u>", $s);

	// [u]Underline[/u]
	$s = preg_replace("/\[u\]((\s|.)+?)\[\/u\]/i", "<u>\\1</u>", $s);

	// [img]http://www/image.gif[/img]
	$s = preg_replace("/\[img\](http:\/\/[^\s'\"<>]+(\.gif|\.jpg|\.png|\.bmp|\.jpeg))\[\/img\]/i", "<img border=0 src=\"\\1\">", $s);

	// [img=http://www/image.gif]
	$s = preg_replace("/\[img=(http:\/\/[^\s'\"<>]+(\.gif|\.jpg|\.png|\.bmp|\.jpeg))\]/i", "<img border=0 src=\"\\1\">", $s);

	// [color=blue]Text[/color]
	$s = preg_replace(
		"/\[color=([a-zA-Z]+)\]((\s|.)+?)\[\/color\]/i",
		"<font color=\\1>\\2</font>", $s);

	// [color=#ffcc99]Text[/color]
	$s = preg_replace(
		"/\[color=(#[a-f0-9][a-f0-9][a-f0-9][a-f0-9][a-f0-9][a-f0-9])\]((\s|.)+?)\[\/color\]/i",
		"<font color=\\1>\\2</font>", $s);

	// [url=http://www.example.com]Text[/url]
	$s = preg_replace(
		"/\[url=((http|ftp|https|ftps|irc):\/\/[^<>\s]+?)\]((\s|.)+?)\[\/url\]/i",
		"<a href=http://anonym.to/?\\1 target=_blank>\\3</a>", $s);

	// [url]http://www.example.com[/url]
	$s = preg_replace(
		"/\[url\]((http|ftp|https|ftps|irc):\/\/[^<>\s]+?)\[\/url\]/i",
		"<a href=http://anonym.to/?\\1 target=_blank>\\1</a>", $s);

	// [size=4]Text[/size]
	$s = preg_replace(
		"/\[size=([1-7])\]((\s|.)+?)\[\/size\]/i",
		"<font size=\\1>\\2</font>", $s);

	// [font=Arial]Text[/font]
	$s = preg_replace(
		"/\[font=([a-zA-Z ,]+)\]((\s|.)+?)\[\/font\]/i",
		"<font face=\"\\1\">\\2</font>", $s);

	//[quote]Text[/quote]
	while (preg_match("/\[quote\]\s*((\s|.)+?)\s*\[\/quote\]\s*/i", $s))
	$s = preg_replace(
		"/\[quote\]\s*((\s|.)+?)\s*\[\/quote\]\s*/i",
		"<p class=sub><b>Quote:</b></p><table class=main border=1 cellspacing=0 cellpadding=10><tr><td style='border: 1px black dotted'>\\1</td></tr></table><br />", $s);

	//[quote=Author]Text[/quote]
	while (preg_match("/\[quote=(.+?)\]\s*((\s|.)+?)\s*\[\/quote\]\s*/i", $s))
	$s = preg_replace(
		"/\[quote=(.+?)\]\s*((\s|.)+?)\s*\[\/quote\]\s*/i",
		"<p class=sub><b>\\1 wrote:</b></p><table class=main border=1 cellspacing=0 cellpadding=10><tr><td style='border: 1px black dotted'>\\2</td></tr></table><br />", $s);

	// [spoiler]Text[/spoiler]
	$r = substr(md5($text), 0, 4);
	$i = 0;
	while (preg_match("/\[spoiler\]\s*((\s|.)+?)\s*\[\/spoiler\]\s*/i", $s)) {
		$s = preg_replace("/\[spoiler\]\s*((\s|.)+?)\s*\[\/spoiler\]\s*/i",
		"<BR><img src='images/plus.gif' id='pic$r$i' title='Spoiler' onclick='klappe_torrent(\"$r$i\")'><div id='k$r$i' style='display: none;'>\\1<BR></div>", $s);
		$i++;
	}

	// [spoiler=Heading]Text[/spoiler]
	while (preg_match("/\[spoiler=(.+?)\]\s*((\s|.)+?)\s*\[\/spoiler\]\s*/i", $s)) {
		$s = preg_replace("/\[spoiler=(.+?)\]\s*((\s|.)+?)\s*\[\/spoiler\]\s*/i",
		"<BR><img src='images/plus.gif' id='pic$r$i' title='Spoiler' onclick='klappe_torrent(\"$r$i\")'><b>\\1</b><div id='k$r$i' style='display: none;'>\\2<BR></div>", $s);
		$i++;
	}
                
     //[hr]
        $s = preg_replace("/\[hr\]/i", "<hr>", $s);

     //[hr=#ffffff] [hr=red]
        $s = preg_replace("/\[hr=((#[a-f0-9][a-f0-9][a-f0-9][a-f0-9][a-f0-9][a-f0-9])|([a-zA-z]+))\]/i", "<hr color=\"\\1\"/>", $s);

        //[swf]http://somesite.com/test.swf[/swf]
        $s = preg_replace("/\[swf\]((www.|http:\/\/|https:\/\/)[^\s]+(\.swf))\[\/swf\]/i",
        "<param name=movie value=\\1/><embed width=470 height=310 src=\\1></embed>", $s);

        //[swf=http://somesite.com/test.swf]
        $s = preg_replace("/\[swf=((www.|http:\/\/|https:\/\/)[^\s]+(\.swf))\]/i",
        "<param name=movie value=\\1/><embed width=470 height=310 src=\\1></embed>", $s);

	// Linebreaks
	$s = nl2br($s);

	// Maintain spacing
	$s = str_replace("  ", " &nbsp;", $s);

	// Smilies
	require_once("smilies.php");
	reset($smilies);
	while (list($code, $url) = each($smilies))
		$s = str_replace($code, "<img border=0 src=" . $site_config['SITEURL'] . "/images/smilies/$url>", $s);

	$r = mysql_query("SELECT * FROM censor");
	while($rr=mysql_fetch_row($r))
		$s = preg_replace("/".preg_quote($rr[0])."/i", $rr[1], $s);

	return $s;
}



function torrenttable($res) {
	global $site_config, $CURUSER, $THEME, $LANGUAGE;  //Define globals

	if ($site_config["MEMBERSONLY_WAIT"] && $site_config["MEMBERSONLY"] && in_array($CURUSER["class"], explode(",",$site_config["WAIT_CLASS"]))) {
		$gigs = $CURUSER["uploaded"] / (1024*1024*1024);
		$ratio = (($CURUSER["downloaded"] > 0) ? ($CURUSER["uploaded"] / $CURUSER["downloaded"]) : 0);
		if ($ratio < 0 || $gigs < 0) $wait = $site_config["WAITA"];
		elseif ($ratio < $site_config["RATIOA"] || $gigs < $site_config["GIGSA"]) $wait = $site_config["WAITA"];
		elseif ($ratio < $site_config["RATIOB"] || $gigs < $site_config["GIGSB"]) $wait = $site_config["WAITB"];
		elseif ($ratio < $site_config["RATIOC"] || $gigs < $site_config["GIGSC"]) $wait = $site_config["WAITC"];
		elseif ($ratio < $site_config["RATIOD"] || $gigs < $site_config["GIGSD"]) $wait = $site_config["WAITD"];
		else $wait = 0;
	}

	// Columns
	$cols = explode(",", $site_config["torrenttable_columns"]);
	$cols = array_map("strtolower", $cols);
	$cols = array_map("trim", $cols);
	$colspan = count($cols);
	// End
	
	// Expanding Area
	$expandrows = array();
	if (!empty($site_config["torrenttable_expand"])) {
		$expandrows = explode(",", $site_config["torrenttable_expand"]);
		$expandrows = array_map("strtolower", $expandrows);
		$expandrows = array_map("trim", $expandrows);
	}
	// End
	
	echo '<table align=center cellpadding="0" cellspacing="0" class="ttable_headinner" width=99%><tr>';

	foreach ($cols as $col) {
		switch ($col) {
			case 'category':
				echo "<td class=ttable_head>".TYPE."</td>";
			break;
			case 'name':
				echo "<td class=ttable_head>".NAME."</td>";
			break;
			case 'dl':
				echo "<td class=ttable_head>DL</td>";
			break;
			case 'uploader':
				echo "<td class=ttable_head>".UPLOADER."</td>";
			break;
			case 'comments':
				echo "<td class=ttable_head>Comm</td>";
			break;
			case 'nfo':
				echo "<td class=ttable_head>NFO</td>";
			break;
			case 'size':
				echo "<td class=ttable_head>".SIZE."</td>";
			break;
			case 'completed':
				echo "<td class=ttable_head>C</td>";
			break;
			case 'seeders':
				echo "<td class=ttable_head>S</td>";
			break;
			case 'leechers':
				echo "<td class=ttable_head>L</td>";
			break;
			case 'health':
				echo "<td class=ttable_head>".HEALTH."</td>";
			break;
			case 'external':
				if ($site_config["ALLOWEXTERNAL"])
					echo "<td class=ttable_head>L/E</td>";
			break;
			case 'added':
				echo "<td class=ttable_head>".DATE_ADDED."</td>";
			break;
			case 'speed':
				echo "<td class=ttable_head>".SPEED."</td>";
			break;
			case 'wait':
				if ($wait)
					echo "<td class=ttable_head>".WAIT."</td>";
			break;
			case 'rating':
				echo "<td class=ttable_head>".RATINGS."</td>";
			break;
		}
	}
	if ($wait && !in_array("wait", $cols))
		echo "<td class=ttable_head>".WAIT."</td>";
	
	echo "</tr>";

	while ($row = mysql_fetch_assoc($res)) {
		$id = $row["id"];

		print("<tr>\n");

	$x = 1;

	foreach ($cols as $col) {
		switch ($col) {
			case 'category':
				print("<td class=ttable_col$x align=center valign=middle>");
				if (!empty($row["cat_name"])) {
					print("<a href=\"torrents.php?cat=" . $row["category"] . "\">");
					if (!empty($row["cat_pic"]) && $row["cat_pic"] != "")
						print("<img border=\"0\"src=\"" . $site_config['SITEURL'] . "/images/categories/" . $row["cat_pic"] . "\" alt=\"" . $row["cat_name"] . "\" />");
					else
						print($row["cat_parent"].": ".$row["cat_name"]);
					print("</a>");
				} else
					print("-");
				print("</td>\n");
			break;
			case 'name':
				$char1 = 35; //cut name length 
				$smallname = htmlspecialchars(CutName($row["name"], $char1));
				$dispname = "<b>".$smallname."</b>";

				$last_access = $CURUSER["last_browse"];
				$time_now = gmtime();
				if ($last_access > $time_now || !is_numeric($last_access))
					$last_access = $time_now;
				if (sql_timestamp_to_unix_timestamp($row["added"]) >= $last_access)
					$dispname .= "<b><font color=red> - ("._NEW."!)</font></b>";

				if ($row["freeleech"] == 1)
					$dispname .= " <img src='images/free.gif' border='0'>";
				print("<td class=ttable_col$x nowrap>".(count($expandrows)?"<a href=\"javascript: klappe_torrent('t".$row['id']."')\"><img border=\"0\" src=\"".$site_config["SITEURL"]."/images/plus.gif\" id=\"pict".$row['id']."\" alt=\"Show/Hide\" class=\"showthecross\"></a>":"")."&nbsp;<a title=\"".$row["name"]."\" href=\"torrents-details.php?id=$id&amp;hit=1\">$dispname</a>");
			break;
			case 'dl':
				print("<td class=ttable_col$x align=center><a href=\"download.php?id=$id&name=" . rawurlencode($row["filename"]) . "\"><img src=" . $site_config['SITEURL'] . "/images/icon_download.gif border=0 alt=\"Download .torrent\"></a></td>");
			break;
			case 'uploader':
				echo "<td class=ttable_col$x align=center>";
				if (($row["anon"] == "yes" || $row["privacy"] == "strong") && $CURUSER["id"] != $row["owner"] && $CURUSER["edit_torrents"] != "yes")
					echo "Anonymous";
				elseif ($row["username"])
					echo "<a href='account-details.php?id=$row[owner]'>$row[username]</a>";
				else
					echo "Unknown";
				echo "</td>";
			break;
			case 'comments':
				print("<td class=ttable_col$x align=center><font size=1 face=Verdana><a href=comments.php?type=torrent&id=$id>" . $row["comments"] . "</a></td>\n");
			break;
			case 'nfo':
				if ($row["nfo"] == "yes")
					print("<td class=ttable_col$x align=center><a href=nfo-view.php?id=$row[id]><img  src=" . $site_config['SITEURL'] . "/images/icon_nfo.gif border=0 alt='View NFO'></a></td>");
				else
					print("<td class=ttable_col$x align=center>-</td>");
			break;
			case 'size':
				print("<td class=ttable_col$x align=center>".mksize($row["size"])."</td>\n");
			break;
			case 'completed':
				print("<td class=ttable_col$x align=center><font color=orange><B>".number_format($row["times_completed"])."</B></font></td>");
			break;
			case 'seeders':
				print("<td class=ttable_col$x align=center><b><font color=green><B>".number_format($row["seeders"])."</b></font></td>\n");
			break;
			case 'leechers':
				print("<td class=ttable_col$x align=center><font color=red><B>" . $row["leechers"] . "</b></font></td>\n");
			break;
			case 'health':
				print("<td class=ttable_col$x align=center><img src=".$site_config["SITEURL"]."/images/health_".health($row["leechers"], $row["seeders"]).".gif></td>\n");
			break;
			case 'external':
				if ($site_config["ALLOWEXTERNAL"]){
					if ($row["external"]=='yes')
						print("<td class=ttable_col$x align=center>E</td>\n");
					else
						print("<td class=ttable_col$x align=center>L</td>\n");
				}
			break;
			case 'added':
				print("<td class=ttable_col$x align=center>".date("d-m-Y<\\B\\R>H:i:s", utc_to_tz_time($row['added']))."</td>");
			break;
			case 'speed':
				if ($row["external"] != "yes" && $row["leechers"] >= 1){
					$speedQ = mysql_query("SELECT (SUM(downloaded)) / (UNIX_TIMESTAMP('".get_date_time()."') - UNIX_TIMESTAMP(started)) AS totalspeed FROM peers WHERE seeder = 'no' AND torrent = '$id'ORDER BY started ASC") or die(mysql_error());
					$a = mysql_fetch_assoc($speedQ);
					$totalspeed = mksize($a["totalspeed"]) . "/s";
				} else
					$totalspeed = "--";
			print("<td class=ttable_col$x align=center>$totalspeed</td>");
			break;
			case 'wait':
				if ($wait){
					$elapsed = floor((gmtime() - strtotime($row["added"])) / 3600);
					if ($elapsed < $wait && $row["external"] != "yes") {
						$color = dechex(floor(127*($wait - $elapsed)/48 + 128)*65536);
						print("<td class=ttable_col$x align=center><a href=\"faq.php\"><font color=\"$color\">" . number_format($wait - $elapsed) . " h</font></a></td>\n");
					} else
						print("<td class=ttable_col$x align=center>--</td>\n");
				}
			break;
			case 'rating':
				if (!$row["rating"])
					$rating = "--";
				else
					$rating = "<a title='$row[rating]/5'>".ratingpic($row["rating"])."</a>";
					//$rating = ratingpic($row["rating"]);
                     //$srating .= "$rpic (" . $row["rating"] . " out of 5) " . $row["numratings"] . " users have rated this torrent";
				print("<td class=ttable_col$x align=center>$rating</td>");
			break;
		}
		if ($x == 2)
			$x--;
		else
			$x++;
	}

	
		//Wait Time Check
		if ($wait && !in_array("wait", $cols)) {
			$elapsed = floor((gmtime() - strtotime($row["added"])) / 3600);
			if ($elapsed < $wait && $row["external"] != "yes") {
				$color = dechex(floor(127*($wait - $elapsed)/48 + 128)*65536);
				print("<td class=ttable_col$x align=center><a href=\"faq.php\"><font color=\"$color\">" . number_format($wait - $elapsed) . " h</font></a></td>\n");
			} else
				print("<td class=ttable_col$x align=center>--</td>\n");
			$colspan++;
			if ($x == 2)
				$x--;
			else
				$x++;
		}
		
		print("</tr>\n");

		//Expanding area
		if (count($expandrows)) {
			print("<tr><td class=ttable_col$x colspan=$colspan><div id=\"kt".$row['id']."\" style=\"margin-left: 70px; display: none;\">");
			print("<table width=97% border=0 cellspacing=0 cellpadding=0>");
			foreach ($expandrows as $expandrow) {
				switch ($expandrow) {
					case 'size':
						print("<tr><td><B>".SIZE."</B>: ".mksize($row['size'])."</td></tr>");
					break;
					case 'speed':
						if ($row["external"] != "yes" && $row["leechers"] >= 1){
							$speedQ = mysql_query("SELECT (SUM(downloaded)) / (UNIX_TIMESTAMP('".get_date_time()."') - UNIX_TIMESTAMP(started)) AS totalspeed FROM peers WHERE seeder = 'no' AND torrent = '$id'ORDER BY started ASC") or die(mysql_error());
							$a = mysql_fetch_assoc($speedQ);
							$totalspeed = mksize($a["totalspeed"]) . "/s";
							print("<tr><td><B>Speed:</B> $totalspeed</td></tr>");
						}
					break;
					case 'added':
						print("<tr><td><B>".DATE_ADDED.":</B> ".date("d-m-Y \\a\\t H:i:s", utc_to_tz_time($row['added']))."</td></tr>");
					break;
					case 'tracker':
					if ($row["external"] == "yes")
						print("<tr><td><B>".TRACKER.":</B> ".htmlspecialchars($row["announce"])."</td></tr>");
					break;
					case 'completed':
						print("<tr><td><B>".COMPLETED."</B>: ".$row['times_completed']."</td></tr>");
					break;
				}
			}
				print("</table></div></td></tr>\n");
		}
		//End Expanding Area


	}

	print("</table><BR>\n");

}

function pager($rpp, $count, $href, $opts = array()) {
    $pages = ceil($count / $rpp);

    if (!$opts["lastpagedefault"])
        $pagedefault = 0;
    else {
        $pagedefault = floor(($count - 1) / $rpp);
        if ($pagedefault < 0)
            $pagedefault = 0;
    }

    if (isset($_GET["page"])) {
        $page = 0 + $_GET["page"];
        if ($page < 0)
            $page = $pagedefault;
    }
    else
        $page = $pagedefault;

    $pager = "";

    $mp = $pages - 1;
    $as = "<b>&lt;&lt;&nbsp;".PREV."</b>";
    if ($page >= 1) {
        $pager .= "<a href=\"{$href}page=" . ($page - 1) . "\">";
        $pager .= $as;
        $pager .= "</a>";
    }
    else
        $pager .= $as;
    $pager .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
    $as = "<b>".NEXT."&nbsp;&gt;&gt;</b>";
    if ($page < $mp && $mp >= 0) {
        $pager .= "<a href=\"{$href}page=" . ($page + 1) . "\">";
        $pager .= $as;
        $pager .= "</a>";
    }
    else
        $pager .= $as;

    if ($count) {
        $pagerarr = array();
        $dotted = 0;
        $dotspace = 3;
        $dotend = $pages - $dotspace;
        $curdotend = $page - $dotspace;
        $curdotstart = $page + $dotspace;
        for ($i = 0; $i < $pages; $i++) {
            if (($i >= $dotspace && $i <= $curdotend) || ($i >= $curdotstart && $i < $dotend)) {
                if (!$dotted)
                    $pagerarr[] = "...";
                $dotted = 1;
                continue;
            }
            $dotted = 0;
            $start = $i * $rpp + 1;
            $end = $start + $rpp - 1;
            if ($end > $count)
                $end = $count;
            $text = "$start&nbsp;-&nbsp;$end";
            if ($i != $page)
                $pagerarr[] = "<a href=\"{$href}page=$i\"><b>$text</b></a>";
            else
                $pagerarr[] = "<b>$text</b>";
        }
        $pagerstr = join(" | ", $pagerarr);
        $pagertop = "<p align=\"center\">$pager<br />$pagerstr</p>\n";
        $pagerbottom = "<p align=\"center\">$pagerstr<br />$pager</p>\n";
    }
    else {
        $pagertop = "<p align=\"center\">$pager</p>\n";
        $pagerbottom = $pagertop;
    }

    $start = $page * $rpp;

    return array($pagertop, $pagerbottom, "LIMIT $start,$rpp");
}

function commenttable($res) {
	global $site_config, $CURUSER, $THEME, $LANGUAGE;  //Define globals
	
	while ($row = mysql_fetch_assoc($res)) {

		$postername = htmlspecialchars($row["username"]);
		if ($postername == "") {
			$postername = "Deluser";
			$title = "Deleted Account";
			$avatar = "";
			$usersignature = "";
			$userdownloaded = "";
			$useruploaded = "";
		}else {
			$privacylevel = $row["privacy"];
			$avatar = htmlspecialchars($row["avatar"]);
			$title =  htmlspecialchars($row["title"]);
			$usersignature = stripslashes(format_comment($row["signature"]));
			$userdownloaded = mksize($row["downloaded"]);
			$useruploaded = mksize($row["uploaded"]);
		}

		if ($row["downloaded"] > 0)
			$userratio = number_format($row["uploaded"] / $row["downloaded"], 2);
		else
			$userratio = "---";

		if (!$avatar)
			$avatar = "".$site_config["SITEURL"]."/images/default_avatar.gif";

		$commenttext = format_comment($row["text"]);

		print("<table border=0 width=100% cellpadding=4>\n");

		print("<tr><td colspan=2 align=right class=table_col1>");

		if($CURUSER["edit_torrents"]=="yes" || $CURUSER["edit_forum"]=="yes" || $CURUSER['id'] == $row['user']){
			print("[<a href=comments.php?id=" . $row["id"] . "&type=torrent&edit=1>".EDIT."</a>]&nbsp;");
		}
		if($CURUSER["delete_torrents"]=="yes" || $CURUSER["delete_forum"]=="yes"){
			print("[<a href=comments.php?id=" . $row["id"] . "&type=torrent&delete=1>".DEL."</a>]&nbsp;");
		}

		print("[<a href=report.php?comment=" . $row["id"] . ">".REPORT."</a>]&nbsp;");

		print("".POSTED.": ".date("d-m-Y \\a\\t H:i:s", utc_to_tz_time($row['added'])));
		print("</td></tr>");

		if ($privacylevel != "strong" || ($CURUSER["control_panel"] == "yes")) {
			print("<tr><td valign=top width=150 align=left class=table_col2><center><b>$postername</b><br><i>$title</i></center><br>".UPLOADED.": $useruploaded<br>".DOWNLOADED.": $userdownloaded<br>".RATIO.": $userratio<br><br><center><img width=80 height=80 src=$avatar></center><br></td>");
		}else{
			print("<tr><td valign=top width=150 align=left class=table_col2><center><b>$postername</b><br><i>$title</i></center><br>Uploaded: ---<br>Downloaded: ---<br>Ratio: ---<br><br><center><img width=80 height=80 src=$avatar></center><br></td>");
		}
		print("<td valign=top width='75%' class=table_col2>$commenttext</td>");
		print("</tr></table><BR>\n");
	}
}

function where ($scriptname = "index", $userid, $update=1){
	if (!is_valid_id($userid))
		die;
	if (preg_match("/torrents-details/i", $scriptname))
		$where = "Browsing Torrents Details...";
	elseif (preg_match("/torrents/i", $scriptname))
		$where = "Browsing Torrent Lists...";
	elseif (preg_match("/account-details/i", $scriptname))
		$where = "Browsing Account Details...";
	elseif (preg_match("/torrents-upload/i", $scriptname))
		$where = "Uploading Torrent..";
	elseif (preg_match("/account/i", $scriptname))
		$where = "Browsing User Control Panel...";
	elseif (preg_match("/torrents-search/i", $scriptname))
		$where = "Searching...";
	elseif (preg_match("/forums/i", $scriptname))
		$where = "Browsing Forums...";
	elseif (preg_match("/index/i", $scriptname))
		$where = "Browsing Homepage...";
	else
		$where = "Unknown Location...";

	if ($update) {
		$query = sprintf("UPDATE users SET page=".sqlesc($where)." WHERE id ='%s'", mysql_real_escape_string($userid));
		$result = mysql_query($query);

		if (!$result)
			die;
	}
		return $where;
}

function get_user_class_name($i){
	$res=mysql_query("SELECT level FROM groups WHERE group_id=".$i."");
	$row=mysql_fetch_row($res);
	return $row[0];
}

function get_user_class(){
  global $CURUSER;
  return $CURUSER["class"];
}

function get_ratio_color($ratio) {
	if ($ratio < 0.1) return "#ff0000";
	if ($ratio < 0.2) return "#ee0000";
	if ($ratio < 0.3) return "#dd0000";
	if ($ratio < 0.4) return "#cc0000";
	if ($ratio < 0.5) return "#bb0000";
	if ($ratio < 0.6) return "#aa0000";
	if ($ratio < 0.7) return "#990000";
	if ($ratio < 0.8) return "#880000";
	if ($ratio < 0.9) return "#770000";
	if ($ratio < 1) return "#660000";
	return "#000000";
}

function ratingpic($num) {
	GLOBAL $site_config;
    $r = round($num * 2) / 2;
	if ($r != $num) {
		$n = $num-$r;
		if ($n < .25)
			$n = 0;
		elseif ($n >= .25 && $n < .75)
			$n = .5;
		$r += $n;
	}
    if ($r < 1 || $r > 5)
        return;

    return "<img src=\"".$site_config["SITEURL"]."/images/rating/$r.gif\" border=\"0\" alt=\"rating: $num/5\" />";
}

function DateDiff ($start, $end) {
	if (!is_numeric($start))
		$start = sql_timestamp_to_unix_timestamp($start);
	if (!is_numeric($end))
		$end = sql_timestamp_to_unix_timestamp($end);
	return ($end - $start);
}

function classlist() {
    $ret = array();
    $res = mysql_query("SELECT * FROM groups ORDER BY group_id ASC");
    while ($row = mysql_fetch_array($res))
        $ret[] = $row;
    return $ret;
}

?>
