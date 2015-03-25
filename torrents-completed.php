<?php
//
//  TorrentTrader v2.x
//	This file was last updated: 3/Sept/2007
//	
//	http://www.torrenttrader.org
//
//
require "backend/functions.php";
dbconn(false);

if ($site_config["MEMBERSONLY"]){
	loggedinonly();

	if($CURUSER["view_torrents"]=="no")
		show_error_msg("Error","You do not have permission to view torrents",1);
}

$id = (int)$_GET["id"];

if (!$id)
	show_error_msg("Error","Where is the ID?",1);	

$r1 = mysql_query("SELECT name,external FROM torrents WHERE id='$id'");
$a1 = mysql_fetch_assoc($r1);
$torrentname = $a1["name"];

if ($a1["external"] =='yes'){
	show_error_msg("Error","This torrent is externally tracked",1);
}

stdhead("Completed Downloads");

begin_frame("Completed Downloads", center);

echo "<h1>" . $torrentname . "</h1>\n";

$r1 = mysql_query("SELECT * FROM completed WHERE torrentid='$id'");

if (mysql_num_rows($r1) == 0){

	echo "<br><CENTER><b>No downloads recorded yet.</b></CENTER><br>\n";	

}else{

	echo "<CENTER><table cellspacing=0 cellpadding=3 class=table_table><tr><td align=center class=table_head>" . USERNAME . "</font></td>";
	echo "<td align=center class=table_head>" . CURRENTLY_SEEDING . "</td>";
	echo "<td align=center class=table_head>Date Completed</td>";
	echo "<td align=center class=table_head>" . RATIO . "</td></tr>";

	while ($a1 = mysql_fetch_assoc($r1)){
		$userid = $a1["userid"];

		$r2 = mysql_query("SELECT username, ip, downloaded, uploaded, privacy FROM users WHERE id='$userid'");
		$a2 = mysql_fetch_assoc($r2);
		$username = $a2["username"];
		$privacy = $a2["privacy"];
		$ip = $a2["ip"];

		if($a2["downloaded"] > 0)
			$sr = $a2["uploaded"] / $a2["downloaded"];
		else
			$sr = 0;

		$r3 = mysql_query("SELECT seeder FROM peers WHERE userid='$userid' AND torrent='$id'");

		if (empty($username))
			$username = "Unknown";
	
		echo "<tr><td class=table_col1><a href=account-details.php?id=" . $userid . ">" . $username . "</a></td>\n";
		echo "<td align=center class=table_col2>\n";

		if (mysql_num_rows($r3) > 0)
			echo "<font color=green>" . YES . "</font>";
		else
			echo "<font color=red>" . NO . "</font>";

		echo "</td>\n";
		
		echo "<td align=center class=table_col1>" .  utc_to_tz($a1["date"]) . "</td>\n";
		echo "<td align=center class=table_col2>" . number_format($sr, 2) . "</td>\n";
		echo "\n</tr>\n";
	}

	echo "</table></CENTER>";
}

echo "<p align=center><a href=torrents-details.php?id=" . $id . ">Back to details</a></p>";

end_frame();
stdfoot();

?>