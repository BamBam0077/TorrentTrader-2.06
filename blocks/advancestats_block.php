<?
begin_block("".STATS."");

$date_time = get_date_time(gmtime()-(3600*24)); // the 24hrs is the hours you want listed
$registered = number_format(get_row_count("users"));
$ncomments = number_format(get_row_count("comments"));
$nmessages = number_format(get_row_count("messages"));
$trackers = number_format(get_row_count("announce"));
$ntor = number_format(get_row_count("torrents"));
$totaltoday = number_format(get_row_count("users", "WHERE users.last_access>='$date_time'"));
$regtoday = number_format(get_row_count("users", "WHERE users.added>='$date_time'"));
$todaytor = number_format(get_row_count("torrents", "WHERE torrents.added>='$date_time'"));
$guests = number_format(getguests());
$seeders = get_row_count("peers", "WHERE seeder='yes'");
$leechers = get_row_count("peers", "WHERE seeder='no'");
$members = number_format(get_row_count("users", "WHERE UNIX_TIMESTAMP('" . get_date_time() . "') - UNIX_TIMESTAMP(users.last_access) < 900"));
$totalonline = $members + $guests;

$result = mysql_query("SELECT SUM(downloaded) AS totaldl FROM users"); 
while ($row = mysql_fetch_array ($result)) { 
	$totaldownloaded = $row["totaldl"]; 
} 

$result = mysql_query("SELECT SUM(uploaded) AS totalul FROM users"); 
while ($row = mysql_fetch_array ($result)) { 
	$totaluploaded      = $row["totalul"]; 
}
$localpeers = $leechers+$seeders;

echo "<div align=left>";
echo "<B>".TORRENTS."</B>";
echo "<br><small>".TRACKING.":<B> " . $ntor . " ".TORRENTSS."</b></small>";
echo "<br><small>".NEW_TODAY.":<B> " . $todaytor . "</b></small>";
echo "<br><small>".TRACKERS.":<B> " . $trackers . "</b></small>";
echo "<br /><small>".SEEDS.":<b> " . number_format($seeders) . "</b></small>";
echo "<br /><small>".LEECH.":<b> " . number_format($leechers) . "</b></small>";
echo "<br /><small>".PEERS.":<b> " . number_format($localpeers) . "</b></small>";
ECHO "<br><small>".DOWNLOADED.":<B> " . mksize($totaldownloaded) . "</b></small>";
ECHO "<br><small>".UPLOADED.":<B> " . mksize($totaluploaded) . "</b></small>";
echo "<br><br><B>".MEMBERS."</B>";
echo "<br><small>".WE_HAVE.":<B> " . $registered . " ".MEMBER."</b></small>";
echo "<br><small>".NEW_TODAY.":<B> " . $regtoday . "</b></small>";
echo "<br><small>".VISITORS_TODAY.": <B>" . $totaltoday . "</b></small>";
echo "<br><br><B>".ONLINE."</B>";
echo "<br><small>".TOTAL_ONLINE.":<B> " . $totalonline . "</b></small>";
echo "<br><small>".MEMBER.":<B> " . $members . "</b></small>";
echo "<br><small>".GUESTS_ONLINE.":<B> " . $guests . "</b></small>";
echo "<br><small>".COMMENTS." ".POSTED.":<B> " . $ncomments . "</b></small>";
echo "<br><small>".MESSAGES_SENT.":<B> " . $nmessages . "</b></small>";
echo "<br><br></div>";
end_block();
?>
