<?
//
//  TorrentTrader v2.x
//	This file was last updated: 25/Feb/2008 by TorrentialStorm
//	
//	http://www.torrenttrader.org
//
//
require_once("backend/functions.php");
dbconn(false);

if ($site_config['SHOUTBOX']){

//DELETE MESSAGES
if (isset($_GET['del'])){

	if (is_numeric($_GET['del'])){
		$query = "SELECT * FROM shoutbox WHERE msgid=".$_GET['del'] ;
		$result = mysql_query($query);
	}else{
		echo "invalid msg id STOP TRYING TO INJECT SQL";
		exit;
	}

	$row = mysql_fetch_row($result);
		
	if ($row && ($CURUSER["edit_users"]=="yes" || $CURUSER['username'] == $row[1])) {
		$query = "DELETE FROM shoutbox WHERE msgid=".$_GET['del'] ;
		write_log("<B><font color=orange>Shout Deleted: </font> Deleted by   ".$CURUSER['username']."</b>");
		mysql_query($query);	
	}
}

//INSERT MESSAGE
if (!empty($_POST['message']) && $CURUSER) {	
	$_POST['message'] = sqlesc($_POST['message']);
	$query = "SELECT COUNT(*) FROM shoutbox WHERE message=".$_POST['message']." AND user='".$CURUSER['username']."' AND UNIX_TIMESTAMP('".get_date_time()."')-UNIX_TIMESTAMP(date) < 30";
	$result = mysql_query($query);
	$row = mysql_fetch_row($result);

	if ($row[0] == '0') {
		$query = "INSERT INTO shoutbox (msgid, user, message, date, userid) VALUES (NULL, '".$CURUSER['username']."', ".$_POST['message'].", '".get_date_time()."', '".$CURUSER['id']."')";
		mysql_query($query);
	}
}

//GET CURRENT USERS THEME AND LANGUAGE
if ($CURUSER){
	$ss_a = @mysql_fetch_array(@mysql_query("select uri from stylesheets where id=" . $CURUSER["stylesheet"])) or die(mysql_error());
	if ($ss_a)
		$THEME = $ss_a["uri"];
		$lng_a = @mysql_fetch_array(@mysql_query("select uri from languages where id=" . $CURUSER["language"])) or die(mysql_error());
	if ($lng_a)
		$LANGUAGE = $lng_a["uri"];
}else{//not logged in so get default theme/language
	$ss_a = mysql_fetch_array(mysql_query("select uri from stylesheets where id='" . $site_config['default_theme'] . "'")) or die(mysql_error());
	if ($ss_a)
		$THEME = $ss_a["uri"];
	$lng_a = mysql_fetch_array(mysql_query("select uri from languages where id='" . $site_config['default_language'] . "'")) or die(mysql_error());
	if ($lng_a)
		$LANGUAGE = $lng_a["uri"];
}

if(!isset($_GET['history'])){ 
?>
<HTML>
<HEAD>
<TITLE><?=$site_config['SITENAME']?>Shoutbox</TITLE>
<META HTTP-EQUIV="refresh" content="300">
<link rel="stylesheet" type="text/css" href="<?=$site_config['SITEURL']?>/themes/<?=$THEME?>/theme.css" />
</HEAD>
<body class="shoutbox_body">
<?
	echo '<div class="shoutbox_contain"><table border="0" background="#ffffff" style="width: 99%; table-layout:fixed">';
}else{
	stdhead();
	begin_frame("Shoutbox History");
	echo '<div class="shoutbox_history">';

	$query = 'SELECT COUNT(*) FROM shoutbox';
	$result = mysql_query($query);
	$row = mysql_fetch_row($result);
	echo '<div align="middle">Pages: ';
	$pages = round($row[0] / 100) + 1;
	$i = 1;
	while ($pages > 0){
		echo "<a href='".$site_config['SITEURL']."/shoutbox.php?history=1&page=".$i."'>[".$i."]</a>&nbsp;";
		$i++;
		$pages--;
	}

	echo '</div></br><table border="0" background="#ffffff" style="width: 99%; table-layout:fixed">';
}

if (isset($_GET['history'])) {
	if (isset($_GET['page'])) {
		if($_GET['page'] > '1') {
			$lowerlimit = $_GET['page'] * 100 - 100;
			$upperlimit = $_GET['page'] * 100;
		}else{
			$lowerlimit = 0;
			$upperlimit = 100;
		}
	}else{
		$lowerlimit = 0;
		$upperlimit = 100;
	}	
	$query = 'SELECT * FROM shoutbox ORDER BY msgid DESC LIMIT '.$lowerlimit.','.$upperlimit;
}else{
	$query = 'SELECT * FROM shoutbox ORDER BY msgid DESC LIMIT 20';
}


$result = mysql_query($query);
$alt = false;

while ($row = mysql_fetch_assoc($result)) {
	if ($alt){	
		echo '<tr class="shoutbox_noalt">';
		$alt = false;
	}else{
		echo '<tr class="shoutbox_alt">';
		$alt = true;
	}

	echo '<td style="font-size: 9px; width: 118px;">';
	echo "<div align='left' style='float: left'>";

	echo date('jS M, g:ia', utc_to_tz_time($row['date']));
	

	echo "</div>";

	if ( ($CURUSER["edit_users"]=="yes") || ($CURUSER['username'] == $row['user']) ){
		echo "<div align='right' style='float: right'><a href='".$site_config['SITEURL']."/shoutbox.php?del=".$row['msgid']."' style='font-size: 8px'>[D]</a><div>";
	}

	echo	'</td><td style="font-size: 12px; padding-left: 5px"><a href="'.$site_config['SITEURL'].'/account-details.php?id='.$row['userid'].'" target="_parent"><b>'.$row['user'].':</b></a>&nbsp;&nbsp;'.nl2br(format_comment($row['message']));
	echo	'</td></tr>';
}
?>

</table>
</div>
<br>

<?

//if the user is logged in, show the shoutbox, if not, dont.
if(!isset($_GET['history'])) {
	if (isset($CURUSER)){
		echo "<form name='shoutboxform' action='".$site_config['SITEURL']."/shoutbox.php' method='post'>";
		echo "<CENTER><table width=100% border=0 cellpadding=1 cellspacing=1>";
		echo "<tr class='shoutbox_messageboxback'>";
		echo "<td width='75%' align=center>";
		echo "<input type='text' name='message' class='shoutbox_msgbox'>";
		echo "</td>";
		echo "<td>";
		echo "<input type='submit' name='submit' value='".SHOUT."' class='shoutbox_shoutbtn'>";
		echo "</td>";
		echo "<td>";
		echo "<a href=".$site_config['SITEURL']."/backend/smilies.php?action=display target=_blank><small>".SMILES."</small></a>";
		echo " - <a href=".$site_config['SITEURL']."/tags.php target=_blank><small>".TAGS."</small></a>";
		echo "<br>";
		echo "<a href='shoutbox.php'><small>".REFRESH."</small></a>";
		echo " - <a href='".$site_config['SITEURL']."/shoutbox.php?history=1' target=_blank><small>".HISTORY."</small></a>";
		echo "</td>";
		echo "</tr>";
		echo "</table></CENTER>";
		echo "</form>";
	}else{
		echo "<br /><div class='shoutbox_error'>You must login to shout.</div>";
	}
}

if(!isset($_GET['history'])){ 
	echo "</BODY></HTML>";
}else{
	end_frame();
	stdfoot();
}


}//END IF $SHOUTBOX
else{
	echo "Shoubox is disabled. Please do not direct link here";
}
?>
