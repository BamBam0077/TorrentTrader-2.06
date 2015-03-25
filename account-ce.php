<?
//
//  TorrentTrader v2.x
//	This file was last updated: 22/June/2007
//	
//	http://www.torrenttrader.org
//
//
require_once("backend/functions.php");
dbconn(false);

$id = 0 + $_GET["id"];
$md5 = $_GET["secret"];
$email = $_GET["email"];

stdhead();

if (!$id || !$md5 || !$email)
	show_error_msg("Couldn't change the email", "Error retrieving ID, KEY or Email.",1);


$res = mysql_query("SELECT editsecret FROM users WHERE id = $id");
$row = mysql_fetch_array($res);

if (!$row)
	show_error_msg("Couldn't change the email", "No user found wanting to change the email.",1);

$sec = hash_pad($row["editsecret"]);
if (preg_match('/^ *$/s', $sec))
	show_error_msg("Couldn't change the email", "No match found.",1);
if ($md5 != md5($sec . $email . $sec))
	show_error_msg("Couldn't change the email", "No md5.",1);

mysql_query("UPDATE users SET editsecret='', email=" . sqlesc($email) . " WHERE id=$id AND editsecret=" . sqlesc($row["editsecret"]));

if (!mysql_affected_rows())
	show_error_msg("Couldn't change the email", "No affected rows.",1);

header("Refresh: 0; url=$SITEURL/account.php");

stdfoot();
?>