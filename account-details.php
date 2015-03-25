<?
//
//  TorrentTrader v2.x
//	This file was last updated: 26/December/2007
//	
//	http://www.torrenttrader.org
//
//
require_once("backend/functions.php");
dbconn(false);
loggedinonly();

if($CURUSER["view_users"]=="no")
	show_error_msg("Error","You do not have permission to view users",1);


stdhead("User CP");

$id = (int)$_GET["id"];

if (!is_valid_id($id))
  show_error_msg("Can't show details", "Bad ID.",1);

$r = @mysql_query("SELECT * FROM users WHERE id=$id") or die(mysql_error());
$user = mysql_fetch_array($r) or  show_error_msg("Can't show details", "No user with ID $id.",1);

//add invites check here

if (($user["enabled"] == "no" || ($user["status"] == "pending")) && $CURUSER["class"] < 4)
	show_error_msg("Error", "Unable to access these details at this time, this user is not currently active<BR><BR>This user may have had their account disabled.",1);

//get all vars first

//$country
$res = mysql_query("SELECT name FROM countries WHERE id=$user[country] LIMIT 1") or die(mysql_error());
if (mysql_num_rows($res) == 1){
	$arr = mysql_fetch_assoc($res);
	$country = "$arr[name]";
}

//$ratio
if ($user["downloaded"] > 0) {
    $ratio = $user["uploaded"] / $user["downloaded"];
}else{
	$ratio = "---";
}

//$numtorrents
$res = mysql_query("SELECT COUNT(*) FROM torrents WHERE owner=$id") or die(mysql_error());
$arr = mysql_fetch_row($res);
$numtorrents = $arr[0];

//$numcomments
$res = mysql_query("SELECT COUNT(*) FROM comments WHERE user=$id") or die(mysql_error());
$arr = mysql_fetch_row($res);
$numcomments = $arr[0];

$avatar = htmlspecialchars($user["avatar"]);
	if (!$avatar) {
		$avatar = "".$site_config["SITEURL"]."/images/default_avatar.gif";
	}

function peerstable($res){
	$ret = "<table align=center cellpadding=\"3\" cellspacing=\"0\" class=\"table_table\" width=\"95%\" border=\"1\"><tr><td class=table_head>" . NAME . "</td><td class=table_head align=center>" . SIZE . "</td><td class=table_head align=center>" . UPLOADED . "</td>\n<td class=table_head align=center>" . DOWNLOADED . "</td><td class=table_head align=center>" . RATIO . "</td></tr>\n";

	while ($arr = mysql_fetch_assoc($res)){
		$res2 = mysql_query("SELECT name,size FROM torrents WHERE id=$arr[torrent] ORDER BY name");
		$arr2 = mysql_fetch_assoc($res2);
		if ($arr["downloaded"] > 0){
			$ratio = number_format($arr["uploaded"] / $arr["downloaded"], 2);
		}else{
			$ratio = "---";
		}
		$ret .= "<tr><td class=table_col1><a href=torrents-details.php?id=$arr[torrent]&amp;hit=1><b>" . htmlspecialchars($arr2[name]) . "</b></a></td><td align=center class=table_col2>" . mksize($arr2["size"]) . "</td><td align=center class=table_col1>" . mksize($arr["uploaded"]) . "</td><td align=center class=table_col2>" . mksize($arr["downloaded"]) . "</td><td align=center class=table_col1>$ratio</td></tr>\n";
  }
  $ret .= "</table>\n";
  return $ret;
}


//Layout 
stdhead("User Details for " . $user["username"]);

begin_frame("User Details for " . $user["username"] . "");

if ($user["privacy"] != "strong" || ($CURUSER["control_panel"] == "yes")) {
	?>
	<table align="center" border="0" cellpadding="6" cellspacing="1" width="100%">
	<tr>
		<td width="50%" class="alt1"><B>Profile</B></td>
		<td width="50%" class="alt1"><B>Additional Info</B></td>
	</tr>

	<tr valign="top">
		<td align="left" class="alt2">
		User Name: <?=htmlspecialchars($user["username"])?><BR>
		User Class: <?=get_user_class_name($user["class"])?><BR>
		Title: <I><?=htmlspecialchars($user["title"])?></I><BR>
		Joined: <?=htmlspecialchars(utc_to_tz($user["added"]))?><BR>
		Last Visit: <?=htmlspecialchars(utc_to_tz($user["last_access"]))?><BR>
		Last Seen(Location): <?=htmlspecialchars($user["page"]);?><BR>
		</td>
		
		<td align="left">
		Age: <?=htmlspecialchars($user["age"])?><BR>
		Client: <?=htmlspecialchars($user["client"])?><BR>
		Country: <?=$country?><BR>
		Donated: $<?=htmlspecialchars($user["donated"])?><BR>
		Warnings: <?=htmlspecialchars($user["warned"])?><BR>
		<?if ($user["privacy"] == "strong"){ echo "Privacy: <b>Strong</b><BR>"; }?>
		</td>	
	</tr>

	<tr>
		<td width="50%"><B>Statistics</B></td>
		<td width="50%"><B>Other</B></td>
	</tr>

	<tr valign="top">
		<td align="left">
		Uploaded: <?=mksize($user["uploaded"])?><BR>
		Downloaded: <?=mksize($user["downloaded"])?><BR>
		Ratio: <?=$ratio?><BR>
		Avg Daily DL: <?=mksize($user["downloaded"] / (DateDiff($user["added"], time()) / 86400))?><BR>
		Avg Daily UL: <?=mksize($user["uploaded"] / (DateDiff($user["added"], time()) / 86400))?><BR>
		Torrents Posted: <?=$numtorrents?><BR>
		Comments Posted: <?=$numcomments?><BR>
		</td>
		
		<td align="left">
		<img src=<?=$avatar?>><BR>	
		<a href=mailbox.php?compose&id=<?=$user["id"]?>>Send PM</a><BR>
		<!-- <a href=#>View Forum Posts</a><BR>
		<a href=#>View Comments</a><BR> -->
		<a href=report.php?user=<?=$user["id"]?>>Report Member</a><BR>
		</td>
	</tr>
	
	<?
	//team
	$res = mysql_query("SELECT name,image FROM teams WHERE id=$user[team] LIMIT 1") or die(mysql_error());
	if (mysql_num_rows($res) == 1) { 
		$arr = mysql_fetch_assoc($res); 
		echo "<tr><td colspan=2 align=left><B>Team Member Of:</B><BR>";
		echo"<img src='".htmlspecialchars($arr["image"])."'><BR>".sqlesc($arr["name"])."<BR><BR><a href=teams-view.php>[View Teams]</a></td></tr>"; 
	}  
	?>
	
	</table>
	<?
}else{
	echo "<B>This member has elected to keep their details private</B><br><br><a href=#>Report Member</a><BR>";
}

end_frame();

if ($user["privacy"] != "strong" || ($CURUSER["control_panel"] == "yes")) {
	begin_frame("Local Activity");

	$res = mysql_query("SELECT torrent,uploaded,downloaded FROM peers WHERE userid='$id' AND seeder='yes'");
	if (mysql_num_rows($res) > 0)
	  $seeding = peerstable($res);

	$res = mysql_query("SELECT torrent,uploaded,downloaded FROM peers WHERE userid='$id' AND seeder='no'");
	if (mysql_num_rows($res) > 0)
	  $leeching = peerstable($res);

	if ($seeding)
		print("<B>" . CURRENTLY_SEEDING . ":</B><BR>$seeding<BR><BR>");

	if ($leeching)
		print("<B>" . CURRENTLY_LEECHING . ":</B><BR>$leeching<BR><BR>");

	if (!$leeching && !$seeding)
		print("<B>This member currently has no active transfers<BR><BR>");

	end_frame();


	begin_frame("Uploaded Torrents");
	//page numbers
	$page = $_GET['page'];
	$perpage = 25;
	if ($CURUSER['control_panel'] != "yes")
		$where = "AND anon='no'";
	$res = mysql_query("SELECT COUNT(*) FROM torrents WHERE owner='$id' $where") or die(mysql_error());
	$row = mysql_fetch_array($res);
	$count = $row[0];
	unset($where);

	$orderby = "ORDER BY id DESC";

	//get sql info
	if ($count) {
		list($pagertop, $pagerbottom, $limit) = pager($perpage, $count, "account-details.php?id=$id&" . $addparam);
		$query = "SELECT torrents.id, torrents.category, torrents.leechers, torrents.nfo, torrents.seeders, torrents.name, torrents.times_completed, torrents.size, torrents.added, torrents.comments, torrents.numfiles, torrents.filename, torrents.owner, torrents.external, torrents.freeleech, categories.name AS cat_name, categories.parent_cat AS cat_parent, categories.image AS cat_pic, users.username, users.privacy, torrents.anon, IF(torrents.numratings < 2, NULL, ROUND(torrents.ratingsum / torrents.numratings, 1)) AS rating, torrents.announce FROM torrents LEFT JOIN categories ON category = categories.id LEFT JOIN users ON torrents.owner = users.id WHERE owner = $id $orderby $limit";
		$res = mysql_query($query) or die(mysql_error());
	}else{
		unset($res);
	}

	if ($count) {
		print($pagerbottom);
		torrenttable($res);
		print($pagerbottom);
	}else {
		print("<B>This member has not uploaded any torrents<BR><BR>");
	}

	end_frame();
}



if($CURUSER["edit_users"]=="yes"){
	begin_frame("Staff Only Information");

	$avatar = htmlspecialchars($user["avatar"]);
	$signature = htmlspecialchars($user["signature"]);
	$uploaded = $user["uploaded"];
	$downloaded = $user["downloaded"];
	$enabled = $user["enabled"] == 'yes';
	$warned = $user["warned"] == 'yes';
	$forumbanned = $user["forumbanned"] == 'yes';
	$modcomment = htmlspecialchars($user["modcomment"]);

	print("<form method=post action=admin-modtasks.php>\n");
	print("<input type=hidden name='action' value='edituser'>\n");
	print("<input type=hidden name='userid' value='$id'>\n");
	print("<table border=0 cellspacing=0 cellpadding=3>\n");
	print("<tr><td>Title</td><td align=left><input type=text size=67 name=title value=\"$user[title]\"></tr>\n");
	print("<tr><td>Email</td><td align=left><input type=text size=67 name=email value=\"$user[email]\"></tr>\n");
	print("<tr><td>Signature</td><td align=left><textarea type=text cols=50 rows=10 name=signature>".htmlspecialchars($user["signature"])."</textarea></tr>\n");
	print("<tr><td>Uploaded</td><td align=left><input type=text size=30 name=uploaded value=\"$user[uploaded]\">&nbsp;&nbsp;".mksize($user[uploaded])."</tr>\n");
	print("<tr><td>Downloaded</td><td align=left><input type=text size=30 name=downloaded value=\"$user[downloaded]\">&nbsp;&nbsp;".mksize($user[downloaded])."</tr>\n");
	print("<tr><td>Avatar URL</td><td align=left><input type=text size=67 name=avatar value=\"$avatar\"></tr>\n");
	print("<tr><td>IP Address</td><td align=left><input type=text size=20 name=ip value=\"$user[ip]\"></tr>\n");
	print("<tr><td>Invites</td><td align=left><input type=text size=4 name=invites value=".$user["invites"]."></tr>\n");

	if ($CURUSER["class"] > $user["class"]){
		print("<tr><td>Class</td><td align=left><select name=class>\n");
		$maxclass = $CURUSER["class"];
		for ($i = 1; $i < $maxclass; ++$i)
		print("<option value=$i" . ($user["class"] == $i ? " selected" : "") . ">$prefix" . get_user_class_name($i) . "\n");
		print("</select></td></tr>\n");
	}


	print("<tr><td>US$&nbsp;Donated</td><td align=left><input type=text size=4 name=donated value=$user[donated]></tr>\n");
	print("<tr><td>Password</td><td align=left><input type=password size=67 name=password value=\"$user[password]\"></tr>\n");
	print("<tr><td>Change Password:</td><td align=left><input type=checkbox name=chgpasswd value='yes'/></td></tr>");
	print("<tr><td>Mod Comment</td><td align=left><textarea cols=50 rows=10 name=modcomment>$modcomment</textarea></td></tr>\n");
	print("<tr><td>Account:</td><td align=left><input name=enabled value=yes type=radio" . ($enabled ? " checked" : "") . ">Enabled <input name=enabled value=no type=radio" . (!$enabled ? " checked" : "") . ">Disabled</td></tr>\n");
	print("<tr><td>Warned: </td><td align=left><input name=warned value=yes type=radio" . ($warned ? " checked" : "") . ">Yes <input name=warned value=no type=radio" . (!$warned ? " checked" : "") . ">No</td></tr>\n");
	print("<tr><td>Forum Banned: </td><td align=left><input name=forumbanned value=yes type=radio" . ($forumbanned ? " checked" : "") . ">Yes <input name=forumbanned value=no type=radio" . (!$forumbanned ? " checked" : "") . ">No</td></tr>\n");
	print("<tr><td>Passkey: </td><td align=left>$user[passkey]<BR><input name=resetpasskey value=yes type=checkbox>Reset passkey (Any active torrents must be downloaded again to continue leeching/seeding)</td></tr>\n");
	print("<tr><td colspan=2 align=center><input type=submit class=btn value='Submit'></td></tr>\n");
	print("</table>\n");
	print("</form>\n");
	  
	end_frame();
}

if($CURUSER["edit_users"]=="yes"){
	begin_frame("Bans & Warnings");
	
	$rqq = "SELECT * FROM warnings WHERE userid=$id ORDER BY id DESC";
	$res = mysql_query($rqq);

	if (mysql_num_rows($res) > 0){

		?>
		<B>Warnings:</b><BR>
		<CENTER><table align=center cellpadding="1" cellspacing="0" class="table_table" width="80%" border="1">
		<tr>
		<td class=table_head align=center>Added</td>
		<td class=table_head align=center>Expiry</td>
		<td class=table_head align=center>Reason</td>
		<td class=table_head align=center>Warned By</td>
		<td class=table_head align=center>Type</td>
		</tr>
		<?

		while ($arr = MYSQL_FETCH_ARRAY($res)){
			if ($arr["warnedby"] == 0) {
				$wusername = "System";
			} else {
				$res2 = mysql_query("SELECT id,username FROM users WHERE id = ".$arr['warnedby']."") or die(mysql_error());
				$arr2 = mysql_fetch_array($res2);

				$wusername = htmlspecialchars($arr2["username"]);
			}
			$arr['added'] = utc_to_tz($arr['added']);
			$arr['expiry'] = utc_to_tz($arr['expiry']);

			$addeddate = substr($arr['added'], 0, strpos($arr['added'], " "));
			$expirydate = substr($arr['expiry'], 0, strpos($arr['expiry'], " "));
			print("<tr><td class=table_col1 align=center>$addeddate</td><td class=table_col2 align=center>$expirydate</td><td class=table_col1>".format_comment($arr['reason'])."</td><td class=table_col2 align=center><a href=account-details.php?id=".$arr2['id'].">".$wusername."</a></td><td class=table_col1 align=center>".$arr['type']."</td></tr>\n");
		 }

		echo "</table></CENTER>\n";
	}else{
		echo "<CENTER><B>This member currently has no warnings</B></CENTER>\n";
	}
	

	print("<form method=post action=admin-modtasks.php>\n");
	print("<input type=hidden name='action' value='addwarning'>\n");
	print("<input type=hidden name='userid' value='$id'>\n");
	echo "<BR><BR><CENTER><table border=0><tr><td align=right><B>Reason:</B> </td><td align=left><textarea cols=40 rows=5 name=reason></textarea></td></tr>";
	echo "<tr><td align=right><B>Expiry:</B> </td><td align=left><input type=text size=4 name=expiry>(days)</td></tr>";
	echo "<tr><td align=right><B>Type:</B> </td><td align=left><input type=text size=10 name=type></td></tr>";
	echo "<tr><td colspan=2 align=center><input type=submit value='Add Warning'></td></tr></table></CENTER></form>";

	if($CURUSER["level"]=="Administrator"){
		print("<hr><CENTER><form method=post action=admin-modtasks.php>\n");
		print("<input type=hidden name='action' value='deleteaccount'>\n");
		print("<input type=hidden name='userid' value='$id'>\n");
		print("<input type=hidden name='username' value='".$user["username"]."'>\n");
		echo "<B>Reason:</B><input type=text size=30 name=delreason>";
		echo "&nbsp;<input type=submit value='Delete Account'></form></CENTER>";
	}

	end_frame();
}

stdfoot();

?>