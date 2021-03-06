<?
//
//  TorrentTrader v2.x
//    This file was last updated: 19/Mar/2008 by TorrentialStorm
//    
//    http://www.torrenttrader.org
//
//
error_reporting(E_ALL ^ E_NOTICE);
require_once("backend/mysql.php");
require_once("backend/config.php");

@mysql_connect($mysql_host, $mysql_user, $mysql_pass) or err('dbconn: mysql_connect: ' . mysql_error());
@mysql_select_db($mysql_db) or err('dbconn: mysql_select_db: ' . mysql_error());

$MEMBERSONLY = $site_config["MEMBERSONLY"];
$MEMBERSONLY_WAIT = $site_config["MEMBERSONLY_WAIT"];

//START FUNCTIONS
function ip2compact ($ip, $port) {
	return pack("N", ip2long($ip)).pack("n", $port);
}

function unesc($x) {
    if (get_magic_quotes_gpc())
        return stripslashes($x);
    return $x;
}

function hex2bin($hexdata) {
  $bindata = "";
  for ($i=0;$i<strlen($hexdata);$i+=2) {
    $bindata.=chr(hexdec(substr($hexdata,$i,2)));
  }
  return $bindata;
}

function is_valid_id($id)
{
  return is_numeric($id) && ($id > 0) && (floor($id) == $id);
}

function validip($ip)
{
    if (!empty($ip) && ip2long($ip)!=-1)
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

function hash_pad($hash) {
    return str_pad($hash, 20);
}

function hash_where($name, $hash) {
    $shhash = preg_replace('/ *$/s', "", $hash);
    return "($name = " . sqlesc($hash) . " OR $name = " . sqlesc($shhash) . ")";

}

function sqlesc($x) {
    return "'".mysql_real_escape_string($x)."'";
}

function err($msg)
{
    benc_resp(array("failure reason" => array(type => "string", value => $msg)));
    exit();
}

function benc($obj) {
    if (!is_array($obj) || !isset($obj["type"]) || !isset($obj["value"]))
        return;
    $c = $obj["value"];
    switch ($obj["type"]) {
        case "string":
            return benc_str($c);
        case "integer":
            return benc_int($c);
        case "list":
            return benc_list($c);
        case "dictionary":
            return benc_dict($c);
        default:
            return;
    }
}

function benc_str($s) {
    return strlen($s) . ":$s";
}

function benc_int($i) {
    return "i" . $i . "e";
}

function benc_list($a) {
    $s = "l";
    foreach ($a as $e) {
        $s .= benc($e);
    }
    $s .= "e";
    return $s;
}

function benc_dict($d) {
    $s = "d";
    $keys = array_keys($d);
    sort($keys);
    foreach ($keys as $k) {
        $v = $d[$k];
        $s .= benc_str($k);
        $s .= benc($v);
    }
    $s .= "e";
    return $s;
}

function benc_resp($d)
{
    benc_resp_raw(benc(array(type => "dictionary", value => $d)));
}

function benc_resp_raw($x) {

header("Content-Type: text/plain");

header("Pragma: no-cache");

if ($_SERVER["HTTP_ACCEPT_ENCODING"] == "gzip") {

header("Content-Encoding: gzip");

echo gzencode($x, 9, FORCE_GZIP);

} else

print($x);

}

function gmtime()
{
    return strtotime(get_date_time());
}

function get_date_time($timestamp = 0)
{
  if ($timestamp)
    return date("Y-m-d H:i:s", $timestamp);
  else
    return gmdate("Y-m-d H:i:s");
}

function portblacklisted($port)
{
    // direct connect
    if ($port >= 411 && $port <= 413) return true;

    // kazaa
    if ($port == 1214) return true;

    // gnutella
    if ($port >= 6346 && $port <= 6347) return true;

    // emule
    if ($port == 4662) return true;

    // winmx
    if ($port == 6699) return true;

    return false;
}

//////////////////////// NOW WE DO THE ANNOUNCE CODE ////////////////////////

// BLOCK ACCESS WITH WEB BROWSERS
$agent = $_SERVER["HTTP_USER_AGENT"];
if (preg_match("/^Mozilla\\//", $agent) || preg_match("/^Opera\\//", $agent) || preg_match("/^Links /", $agent) || preg_match("/^Lynx\\//", $agent))
   err("torrent not registered with this tracker");



//GET DETAILS OF PEERS ANNOUNCE
foreach (array("passkey","info_hash","peer_id","ip","event") as $x) {
    if (get_magic_quotes_gpc())
        $GLOBALS[$x] = stripslashes($_GET[$x]);
    else
        $GLOBALS[$x] = $_GET[$x];
}

foreach (array("port","downloaded","uploaded","left") as $x)
    $GLOBALS[$x] = 0 + $_GET[$x];

if (strpos($passkey, "?")) {
    $tmp = substr($passkey, strpos($passkey, "?"));
    $passkey = substr($passkey, 0, strpos($passkey, "?"));
    $tmpname = substr($tmp, 1, strpos($tmp, "=")-1);
    $tmpvalue = substr($tmp, strpos($tmp, "=")+1);
    $GLOBALS[$tmpname] = $tmpvalue;
}

foreach (array("passkey","info_hash","peer_id","port","downloaded","uploaded","left") as $x)

if (!isset($x))
    err("Missing key: $x");

$no_peer_id = (int) $_GET["no_peer_id"];
$compact = (int) $_GET["compact"];

    if (strlen($GLOBALS['info_hash']) == 20)
        $GLOBALS['info_hash'] = bin2hex($GLOBALS['info_hash']);
    else if (strlen($GLOBALS['info_hash']) != 40)
        err("Invalid info hash value.");
    $GLOBALS['info_hash'] = strtolower($GLOBALS['info_hash']);

	if ($MEMBERSONLY){
		if (strlen($passkey) != 32)
			err("Invalid passkey (" . strlen($passkey) . " - $passkey)");
	}

$ip = getip(); 

foreach(array("num want", "numwant", "num_want") as $k)
{
    if (isset($_GET[$k]))
    {
        $rsize = 0 + $_GET[$k];
        break;
    }
}

//PORT CHECK
if (!$port || $port > 0xffff)
    err("invalid port");

//TRACKER EVENT CHECK
if (!isset($event))
    $event = "";

$seeder = ($left == 0) ? "yes" : "no";

//Agent Ban
$agentarray = array_map("trim", explode(",", $site_config["BANNED_AGENTS"]));
$useragent = substr($peer_id, 0, 8);
foreach($agentarray as $bannedclient)
if (@strpos($useragent, $bannedclient) !== false)
	err("Client is banned");
//End Agent Bans

if (portblacklisted($port))
	err("Port $port is blacklisted.");

$userfields = "id, class, uploaded, downloaded, ip, passkey"; //user details to get

$peerfields = "seeder, UNIX_TIMESTAMP(last_action) AS ez, peer_id, ip, port, uploaded, downloaded, userid, passkey"; //peers details to get

$torrentfields = "id, name, info_hash, category, banned, freeleech, seeders + leechers AS numpeers, UNIX_TIMESTAMP(added) AS ts, seeders, leechers, times_completed"; //torrent details to get

$userid = 0;
if ($MEMBERSONLY){
	//check passkey is valid, and get users details
	$res = mysql_query("SELECT $userfields FROM users WHERE passkey=".sqlesc($passkey)." AND enabled = 'yes' ORDER BY last_access DESC LIMIT 1") or err("Cannot Get User Details");
	$user = mysql_fetch_array($res);
	if (!$user)
		err("Cannot locate a user with that passkey!");
	$userid = $user["id"]; //etc
}


//check torrent is valid and get torrent fields
$res = mysql_query("SELECT $torrentfields FROM torrents WHERE ".hash_where("info_hash", $info_hash)) or err("Cannot Get Torrent Details");
$torrent = mysql_fetch_array($res);

if (!$torrent)
    err("Torrent not found on this tracker - hash = " . $info_hash);
if ($torrent["banned"]=='yes')
    err("Torrent has been banned - hash = " . $info_hash);
$torrentid = $torrent["id"];


//Now get data from peers table
$peerlimit = 50;
$numpeers = $torrent["numpeers"];
if ($numpeers > $peerlimit){
    $limit = "ORDER BY RAND() LIMIT $peerlimit";
}else{
    $limit = "";
}
$res = mysql_query("SELECT $peerfields FROM peers WHERE torrent = $torrentid $limit") or err("Error Selecting Peers");

//DO SOME BENC STUFF TO THE PEERS CONNECTION
$resp = "d8:completei$torrent[seeders]e10:downloadedi$torrent[times_completed]e10:incompletei$torrent[leechers]e";
$resp .= benc_str("interval") . "i" . $site_config['announce_interval'] . "e" . benc_str("min interval") . "i300e" . benc_str("peers");
unset($self);
while ($row = mysql_fetch_assoc($res))
{
    $row["peer_id"] = hash_pad($row["peer_id"]);

    if ($row["peer_id"] === $peer_id)
    {
        $self = $row;
        continue;
    }

	if (!$compact || $no_peer_id) {
		$peers .= "d" .
        benc_str("ip") . benc_str($row["ip"]);
        if (!$no_peer_id)
			$peers .= benc_str("peer id") . benc_str($row["peer_id"]);
        $peers .= benc_str("port") . "i" . $row["port"] . "ee";
	}else
		$peers .= benc_str(ip2compact($row["ip"], $row["port"]));
}
if (!$compact || $no_peer_id)
	$resp .= "l{$peers}e";
else
	$resp .= benc_str($peers);
$resp .= "ee";

$selfwhere = "torrent = $torrentid AND " . hash_where("peer_id", $peer_id);



// FILL $SELF WITH DETAILS FROM PEERS TABLE (CONNECTING PEERS DETAILS)
if (!isset($self)){

	//check passkey isnt leaked
	if ($MEMBERSONLY) {
		$valid = @mysql_fetch_row(@mysql_query("SELECT COUNT(*) FROM peers WHERE torrent=$torrentid AND passkey=" . sqlesc($passkey)));

		if ($valid[0] >= 1 && $seeder == 'no')
			err("Connection limit exceeded! You may only leech from one location at a time.");

		if ($valid[0] >= 3 && $seeder == 'yes')
			err("Connection limit exceeded!");
	}

	$res = mysql_query("SELECT $peerfields FROM peers WHERE $selfwhere");
	$row = mysql_fetch_assoc($res);
	if ($row){
	        $self = $row;
	}
}
// END $SELF FILL


if (!isset($self)){ //IF PEER IS NOT IN PEERS TABLE DO THE WAIT TIME CHECK
	if ($MEMBERSONLY_WAIT && $MEMBERSONLY){
		//wait time check
		if($left > 0 && in_array($user["class"], explode(",",$site_config["WAIT_CLASS"]))){ //check only leechers and lowest user class
			$gigs = $user["uploaded"] / (1024*1024*1024);
			$elapsed = floor((gmtime() - $torrent["ts"]) / 3600); 
			$ratio = (($user["downloaded"] > 0) ? ($user["uploaded"] / $user["downloaded"]) : 1); 
			if ($ratio == 0 && $gigs == 0) $wait = $site_config["WAITA"];
			elseif ($ratio < $site_config["RATIOA"] || $gigs < $site_config["GIGSA"]) $wait = $site_config["WAITA"];
			elseif ($ratio < $site_config["RATIOB"] || $gigs < $site_config["GIGSB"]) $wait = $site_config["WAITB"];
			elseif ($ratio < $site_config["RATIOC"] || $gigs < $site_config["GIGSC"]) $wait = $site_config["WAITC"];
			elseif ($ratio < $site_config["RATIOD"] || $gigs < $site_config["GIGSD"]) $wait = $site_config["WAITD"];
			else $wait = 0;
		if ($elapsed < $wait)
			err("Wait Time (" . ($wait - $elapsed) . " hours) - Visit ".$site_config["SITEURL"]." for more info");
		}
	}
	$sockres = @fsockopen($ip, $port, $errno, $errstr, 5);
	if (!$sockres)
		$connectable = "no";
	else
		$connectable = "yes";
	@fclose($sockres);

}else{
    $upthis = max(0, $uploaded - $self["uploaded"]);
    $downthis = max(0, $downloaded - $self["downloaded"]);

    if (($upthis > 0 || $downthis > 0) && is_valid_id($userid)){ //  (LIVE STATS!)
		if ($torrent["freeleech"] == 1){
			mysql_query("UPDATE users SET uploaded = uploaded + $upthis WHERE id=$userid") or err("Tracker error: Unable to update stats");
		}else{
			mysql_query("UPDATE users SET uploaded = uploaded + $upthis, downloaded = downloaded + $downthis WHERE id=$userid") or err("Tracker error: Unable to update stats");
		}
    }
}//END WAIT AND STATS UPDATE

$updateset = array();

////////////////// NOW WE DO THE TRACKER EVENT UPDATES ///////////////////

if ($event == "stopped") { // UPDATE "STOPPED" EVENT
        mysql_query("DELETE FROM peers WHERE $selfwhere");
        if (mysql_affected_rows()){
            if ($self["seeder"] == "yes")
                $updateset[] = "seeders = seeders - 1";
            else
                $updateset[] = "leechers = leechers - 1";
        }
}

if ($event == "completed") { // UPDATE "COMPLETED" EVENT    
    $updateset[] = "times_completed = times_completed + 1";

	if ($MEMBERSONLY)
		mysql_query("INSERT INTO completed (userid, torrentid, date) VALUES ($userid, $torrentid, '".get_date_time()."')");
}//END COMPLETED

if (isset($self)){// NO EVENT? THEN WE MUST BE A NEW PEER OR ARE NOW SEEDING A COMPLETED TORRENT
    
    mysql_query("UPDATE peers SET ip = " . sqlesc($ip) . ", passkey = " . sqlesc($passkey) . ", port = $port, uploaded = $uploaded, downloaded = $downloaded, to_go = $left, last_action = '".get_date_time()."', client = " . sqlesc($agent) . ", seeder = '$seeder' WHERE $selfwhere");

    if (mysql_affected_rows() && $self["seeder"] != $seeder){
        if ($seeder == "yes"){
            $updateset[] = "seeders = seeders + 1";
            $updateset[] = "leechers = leechers - 1";
        } else {
            $updateset[] = "seeders = seeders - 1";
            $updateset[] = "leechers = leechers + 1";
        }
    }

} else {

    $ret = mysql_query("INSERT INTO peers (connectable, torrent, peer_id, ip, passkey, port, uploaded, downloaded, to_go, started, last_action, seeder, userid, client) VALUES ('$connectable', $torrentid, " . sqlesc($peer_id) . ", " . sqlesc($ip) . ", " . sqlesc($passkey) . ", $port, $uploaded, $downloaded, $left, '".get_date_time()."', '".get_date_time()."', '$seeder', '$userid', " . sqlesc($agent) . ")");
    
    if ($ret){
        if ($seeder == "yes")
            $updateset[] = "seeders = seeders + 1";
        else
            $updateset[] = "leechers = leechers + 1";
    }
}

//////////////////    END TRACKER EVENT UPDATES ///////////////////

// SEEDED, LETS MAKE IT VISIBLE THEN
if ($seeder == "yes") {
    if ($torrent["banned"] != "yes") // DONT MAKE BANNED ONES VISIBLE
        $updateset[] = "visible = 'yes'";
    $updateset[] = "last_action = '".get_date_time()."'";
}

// NOW WE UPDATE THE TORRENT AS PER ABOVE
if (count($updateset))
    mysql_query("UPDATE torrents SET " . join(",", $updateset) . " WHERE id=$torrentid") or err("Tracker error: Unable to update torrent");

// NOW BENC THE DATA AND SEND TO CLIENT???
benc_resp_raw($resp);

mysql_close();
?>