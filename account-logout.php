<?
//
//  TorrentTrader v2.x
//	This file was last updated: 21/June/2007
//	
//	http://www.torrenttrader.org
//
//
// Logout of site, clear cookie and return to index
require_once("backend/functions.php");
dbconn();
logoutcookie();
Header("Location: index.php");
?>