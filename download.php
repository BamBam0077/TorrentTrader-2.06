<?
//
//  TorrentTrader v2.x
//	This file was last updated: 29/Aug/2007
//	
//	http://www.torrenttrader.org
//
//

require_once("backend/functions.php");
dbconn();

//check permissions
if ($site_config["MEMBERSONLY"]){
	loggedinonly();
	
	if($CURUSER["can_download"]=="no")
		show_error_msg("Error","You do not have permission to download torrents",1);
}

$id = (int)$_GET["id"];

if (!$id)
	show_error_msg("ID not found", "You can't download, if you don't tell me what you want!",1);

$res = mysql_query("SELECT filename, banned, external, announce FROM torrents WHERE id =".intval($id));
$row = mysql_fetch_array($res);
$trackerurl = $row['announce'];

$torrent_dir = $site_config["torrent_dir"];

$fn = "$torrent_dir/$id.torrent";

if (!$row)
	show_error_msg("File not found", "No file has been found with that ID!",1);
if ($row["banned"] == "yes")
	show_error_msg("Error", "Torrent is banned.", 1);
if (!is_file($fn))
	show_error_msg("File not found", "The ID has been found on the Database, but the torrent has gone!<BR><BR>Check Server Paths and CHMODs Are Correct!",1);
if (!is_readable($fn))
	show_error_msg("File not found", "The ID and torrent were found, but the torrent is NOT readable!",1);

$name = $row['filename'];
$friendlyurl = str_replace("http://","",$site_config[SITEURL]);
$friendlyname = str_replace(".torrent","",$name);
$friendlyext = ".torrent";
$name = $friendlyname ."[". $friendlyurl ."]". $friendlyext;

mysql_query("UPDATE torrents SET hits = hits + 1 WHERE id = $id");

require_once "backend/benc.php";

//if user dont have a passkey generate one, only if tracker is set to members only
if ($site_config["MEMBERSONLY"]){
	if (strlen($CURUSER['passkey']) != 32) {
		$rand = array_sum(explode(" ", microtime()));
		$CURUSER['passkey'] = md5($CURUSER['username'].$rand.$CURUSER['secret'].($rand*mt_rand()));
		mysql_query("UPDATE users SET passkey='$CURUSER[passkey]' WHERE id=$CURUSER[id]");
	}
}

if ($row["external"]!='yes' && $site_config["MEMBERSONLY"]){// local torrent so add passkey
	$dict = bdec_file($fn, (1024*1024));

	$dict['value']['announce']['value'] = "$site_config[SITEURL]/announce.php?passkey=$CURUSER[passkey]";
	$dict['value']['announce']['string'] = strlen($dict['value']['announce']['value']).":".$dict['value']['announce']['value'];
	$dict['value']['announce']['strlen'] = strlen($dict['value']['announce']['string']);
	unset($dict['value']['announce-list']);


	header('Content-Disposition: attachment; filename="'.$name.'"');

	header("Content-Type: application/x-bittorrent");

	print(benc($dict)); 

}else{// external torrent so no passkey needed
	header('Content-Disposition: attachment; filename="'.$name.'"');

	header("Content-Type: application/x-bittorrent");

	readfile($fn); 
}

mysql_close();
?>