<?
//
//  TorrentTrader v2.x
//	This file was last updated: 24/Sep/2008 by TorrentialStorm
//	
//	http://www.torrenttrader.org
//
//

// VERY BASIC ADMINCP

require_once ("backend/functions.php");
require_once ("backend/bbcode.php");
dbconn(false);

loggedinonly();

if (!$CURUSER || $CURUSER["control_panel"]!="yes"){
 show_error_msg("Error","Sorry you do not have the rights to access this page!",1);
}




function navmenu(){
global $site_config;

//Get Last Cleanup
$res = mysql_query("SELECT last_time FROM tasks WHERE task = 'cleanup'");
$row = mysql_fetch_array($res);
if (!$row){
		$lastclean="never done...";
}else{
	$row[0]=gmtime()-$row[0]; $days=intval($row[0] / 86400);$row[0]-=$days*86400;
	$hours=intval($row[0] / 3600); $row[0]-=$hours*3600; $mins=intval($row[0] / 60);
	$secs=$row[0]-($mins*60);
	$lastclean = "$days days, $hours hrs, $mins minutes, $secs seconds ago.";
}

	begin_frame("Menu");
	print "Last cleanup performed: ".$lastclean." [<a href=admincp.php?action=forceclean>FORCE CLEAN</a>]<BR><BR>";

	if ($site_config["ttversion"] != "2-svn") {
		$file = @file_get_contents('http://www.torrenttrader.org/tt2version.php');
		if ($site_config['ttversion'] >= $file){
			echo "<BR><center><b>You have the latest Version of TorrentTrader Installed: v$site_config[ttversion]</b></center>";
		}else{
			echo "<BR><center><b><font color=red>NEW Version of TorrentTrader now available: v".$file." you have v".$site_config['ttversion']."<BR> Please visit <a href=http://www.torrenttrader.org>TorrentTrader.org</a> to upgrade.</font></b></center>";
		}
	}

	$res = mysql_query("SELECT VERSION() AS mysql_version");
    $row = mysql_fetch_array($res);
    $mysqlver = $row['mysql_version']; 

	$pending = get_row_count("users", "WHERE status='pending'");
	echo "<CENTER><b>Users Awaiting Validation:</b> <a href='admincp.php?action=confirmreg'>($pending)</a></CENTER><BR>";

	echo "<CENTER>Mysql Version: <B>" . $mysqlver . "</B><BR>PHP Version: <B>" . phpversion() . "</B></CENTER>";

?>
<p align="center"><table border="0" width="100%" cellspacing="0" cellpadding="0">

<TR>
<td align="center"><a href=admincp.php?action=avatars><img src="images/admin/avatar.gif" border=0 width=32 height=32><br>Avatar Log</a><BR></td>
<td align="center"><a href=admincp.php?action=backups><img src="images/admin/database.png" border=0 width=32 height=32><br>Backups</a><BR></td>
<td align="center"><a href=admincp.php?action=bannedtorrents><img src="images/admin/bannedtorrents.gif" border=0 width=32 height=32><br>Banned Torrents</a><BR></td>
<td align="center"><a href=admincp.php?action=blocks&do=view><img src="images/admin/banners.gif" border=0 width=32 height=32><br>Blocks</a><BR></td>
<td align="center"><a href=admincp.php?action=cheats><img src="images/admin/blocked.gif" border=0 width=32 height=32><br>Detect Possible Cheaters</a><BR></td>
</tr>

<tr><td colspan=5>&nbsp;</td></tr>

<TR>
<td align="center"><a href=admincp.php?action=emailbans><img src="images/admin/mail.gif" border=0 width=32 height=32><br>Email Bans</a><BR></td>
<td align="center"><a href=faq-manage.php><img src="images/admin/faq.png" border=0 width=32 height=32><br>FAQ</a><BR></td>
<td align="center"><a href=admincp.php?action=freetorrents><img src="images/admin/external.gif" border=0 width=32 height=32><br>Free Leech Torrents</a><BR></td>
<td align="center"><a href=admincp.php?action=lastcomm><img src="images/admin/forums.gif" border=0 width=32 height=32><br>Latest Comments</a><BR></td>
<td align="center"><a href=admincp.php?action=masspm><img src="images/admin/massmessage.gif" border=0 width=32 height=32><br>Mass PM</a><BR></td>
</tr>

<tr><td colspan=5>&nbsp;</td></tr>

<TR>
<td align="center"><a href=admincp.php?action=messagespy><img src="images/admin/messagespy.gif" border=0 width=32 height=32><br>Message Spy</a><BR></td>
<td align="center"><a href=admincp.php?action=news&do=view><img src="images/admin/news.png" border=0 width=32 height=32><br>News</a><BR></td>
<td align="center"><a href=admincp.php?action=peers><img src="images/admin/list_peers.png" border=0 width=32 height=32><br>Peers List</a><BR></td>
<td align="center"><a href=admincp.php?action=polls&do=view><img src="images/admin/uploadervote.gif" border=0 width=32 height=32><br>Polls</a><BR></td>
<td align="center"><a href=admincp.php?action=reports&do=view><img src="images/admin/requests.gif" border=0 width=32 height=32><br>Reports</a><BR></td>
</tr>

<tr><td colspan=5>&nbsp;</td></tr>

<TR>
<td align="center"><a href=admincp.php?action=rules&do=view><img src="images/admin/rules.gif" border=0 width=32 height=32><br>Rules</a><BR></td>
<td align="center"><a href=admincp.php?action=sitelog><img src="images/admin/log.gif" border=0 width=32 height=32><br>Site Log</a><BR></td>
<td align="center"><a href=teams-create.php><img src="images/admin/userssearch.gif" border=0 width=32 height=32><br>Teams</a><BR></td>
<td align="center"><a href=admincp.php?action=categories&do=view><img src="images/admin/categories.gif" border=0 width=32 height=32><br>Torrent Categories</a><BR></td>
<td align="center"><a href=admincp.php?action=torrentmanage><img src="images/admin/torrents.gif" border=0 width=32 height=32><br>Torrents</a><BR></td>
</tr>

<tr><td colspan=5>&nbsp;</td></tr>

<TR>
<td align="center"><a href=admincp.php?action=torrentlangs&do=view><img src="images/admin/langs.png" border=0 width=32 height=32><br>Torrent Languages</a><BR></td>
<td align="center"><a href=admincp.php?action=groups&do=view><img src="images/admin/usersgrp.gif" border=0 width=32 height=32><br>User Groups</a><BR></td>
<td align="center"><a href=admincp.php?action=users><img src="images/admin/users.gif" border=0 width=32 height=32><br>Users</a><BR></td>
<td align="center"><a href=admincp.php?action=warned><img src="images/admin/warnedaccounts.gif" border=0 width=32 height=32><br>Warned Users</a><BR></td>
<td align="center"><a href=admincp.php?action=whoswhere><img src="images/admin/ipchecker.gif" border=0 width=32 height=32><br>Who's Where</a><BR></td>
</tr>

<tr><td colspan=5>&nbsp;</td></tr>

<tr>
<td align="center"><a href=admincp.php?action=style><img src="images/admin/themes.gif" border=0 width=32 height=32><BR>Theme Management</a><BR></td>
<td align="center"><a href=admincp.php?action=censor><img src="images/admin/censor.png" border=0 width=32 height=32><BR>Word Censor</a><BR></td>
<td align="center"><a href=admincp.php?action=ipbans><img src="images/admin/blocked.gif" border=0 width=32 height=32><BR>Banned IPs</a><BR></td>
</tr>

<tr>

</table></p>
<?
	end_frame();
}


if (!$action){
	stdhead("Admin CP");
	navmenu();
	stdfoot();
}

/////////////////////// GROUPS MANAGEMENT ///////////////////////
if ($action=="groups" && $do=="view"){
	stdhead("Groups Management");
	navmenu();


	begin_frame("User Groups");
	print("<CENTER><a href=admincp.php?action=groups&do=add>Add New Group</a></CENTER>\n");

	print("<br><br>\n<table width=\"100%\" align=\"center\" border=1 class=table_table>\n");
	print("<tr>\n");
	print("<td class=table_head>Name</td>\n");
	print("<td class=table_head>Torrents<br>View/Edit/Del</td>\n");
	print("<td class=table_head>Members<br>View/Edit/Del</td>\n");
	print("<td class=table_head>News<br>View/Edit/Del</td>\n");
	print("<td class=table_head>Forum<br>View/Edit/Del</td>\n");
	print("<td class=table_head>Upload</td>\n");
	print("<td class=table_head>Download</td>\n");
	print("<td class=table_head>View CP</td>\n");
	print("<td class=table_head>Delete</td>\n");
	print("</tr>\n");

	$getlevel=mysql_query("SELECT * from groups ORDER BY group_id");
	while ($level=mysql_fetch_array($getlevel)) {
		 print("<tr>\n");
		 print("<td class=table_col1><a href=admincp.php?action=groups&do=edit&group_id=".$level["group_id"].">".$level["level"]."<a></td>\n");
		 print("<td class=table_col2>".$level["view_torrents"]."/".$level["edit_torrents"]."/".$level["delete_torrents"]."</td>\n");
		 print("<td class=table_col1>".$level["view_users"]."/".$level["edit_users"]."/".$level["delete_users"]."</td>\n");
		 print("<td class=table_col2>".$level["view_news"]."/".$level["edit_news"]."/".$level["delete_news"]."</td>\n");
		 print("<td class=table_col1>".$level["view_forum"]."/".$level["edit_forum"]."/".$level["delete_forum"]."</td>\n");
		 print("<td class=table_col2>".$level["can_upload"]."</td>\n");
		 print("<td class=table_col1>".$level["can_download"]."</td>\n");
		 print("<td class=table_col2>".$level["control_panel"]."</td>\n");
		 print("<td class=table_col1><a href=admincp.php?action=groups&do=delete&group_id=".$level["group_id"].">Del<a></td>\n");

		 print("</tr>\n");
	}

	print("</table><BR><BR>");
	end_frame();
	stdfoot();
}

if ($action=="groups" && $do=="edit"){
	$group_id=intval($_GET["group_id"]);
	$rlevel=mysql_query("SELECT * FROM groups WHERE group_id=$group_id");
	if (!$rlevel)
		show_error_msg("ERROR","No Goup with that ID found",1);

	$level=mysql_fetch_array($rlevel);

	stdhead("Groups Management");
	navmenu();


	begin_frame("Edit Group");
	?>
	<form action="admincp.php?action=groups&do=update&group_id=<?php echo $level["group_id"]; ?>" name="level" method="post">
	<table width="100%" align="center">
	<tr><td>Name:</td><td><input type="text" name="gname" value="<?php echo $level["level"];?>" size="40" /></td></tr>
	<tr><td>View Torrents:</td><td>  <?php echo YES;?> <input type="radio" name="vtorrent" value="yes" <?php if ($level["view_torrents"]=="yes") echo "checked" ?> />&nbsp;&nbsp; <?php echo NO;?> <input type="radio" name="vtorrent" value="no" <?php if ($level["view_torrents"]=="no") echo "checked" ?> /></td></tr>
	<tr><td>Edit Torrents:</td><td>  <?php echo YES;?> <input type="radio" name="etorrent" value="yes" <?php if ($level["edit_torrents"]=="yes") echo "checked" ?> />&nbsp;&nbsp; <?php echo NO;?> <input type="radio" name="etorrent" value="no" <?php if ($level["edit_torrents"]=="no") echo "checked" ?> /></td></tr>
	<tr><td>Delete Torrents:</td><td>  <?php echo YES;?> <input type="radio" name="dtorrent" value="yes" <?php if ($level["delete_torrents"]=="yes") echo "checked" ?> />&nbsp;&nbsp; <?php echo NO;?> <input type="radio" name="dtorrent" value="no" <?php if ($level["delete_torrents"]=="no") echo "checked" ?> /></td></tr>
	<tr><td>View Users:</td><td>  <?php echo YES;?> <input type="radio" name="vuser" value="yes" <?php if ($level["view_users"]=="yes") echo "checked" ?> />&nbsp;&nbsp; <?php echo NO;?> <input type="radio" name="vuser" value="no" <?php if ($level["view_users"]=="no") echo "checked" ?> /></td></tr>
	<tr><td>Edit Users:</td><td>  <?php echo YES;?> <input type="radio" name="euser" value="yes" <?php if ($level["edit_users"]=="yes") echo "checked" ?> />&nbsp;&nbsp; <?php echo NO;?> <input type="radio" name="euser" value="no" <?php if ($level["edit_users"]=="no") echo "checked" ?> /></td></tr>
	<tr><td>Delete Users:</td><td>  <?php echo YES;?> <input type="radio" name="duser" value="yes" <?php if ($level["delete_users"]=="yes") echo "checked" ?> />&nbsp;&nbsp; <?php echo NO;?> <input type="radio" name="duser" value="no" <?php if ($level["delete_users"]=="no") echo "checked" ?> /></td></tr>
	<tr><td>View News:</td><td>  <?php echo YES;?> <input type="radio" name="vnews" value="yes" <?php if ($level["view_news"]=="yes") echo "checked" ?> />&nbsp;&nbsp; <?php echo NO;?> <input type="radio" name="vnews" value="no" <?php if ($level["view_news"]=="no") echo "checked" ?> /></td></tr>
	<tr><td>Edit News:</td><td>  <?php echo YES;?> <input type="radio" name="enews" value="yes" <?php if ($level["edit_news"]=="yes") echo "checked" ?> />&nbsp;&nbsp; <?php echo NO;?> <input type="radio" name="enews" value="no" <?php if ($level["edit_news"]=="no") echo "checked" ?> /></td></tr>
	<tr><td>Delete News:</td><td> <?php echo YES;?> <input type="radio" name="dnews" value="yes" <?php if ($level["delete_news"]=="yes") echo "checked" ?> />&nbsp;&nbsp; <?php echo NO;?> <input type="radio" name="dnews" value="no" <?php if ($level["delete_news"]=="no") echo "checked" ?> /></td></tr>
	<tr><td>View Forums:</td><td>  <?php echo YES;?> <input type="radio" name="vforum" value="yes" <?php if ($level["view_forum"]=="yes") echo "checked" ?> />&nbsp;&nbsp; <?php echo NO;?> <input type="radio" name="vforum" value="no" <?php if ($level["view_forum"]=="no") echo "checked" ?> /></td></tr>
	<tr><td>Edit In Forums:</td><td>  <?php echo YES;?> <input type="radio" name="eforum" value="yes" <?php if ($level["edit_forum"]=="yes") echo "checked" ?> />&nbsp;&nbsp; <?php echo NO;?> <input type="radio" name="eforum" value="no" <?php if ($level["edit_forum"]=="no") echo "checked" ?> /></td></tr>
	<tr><td>Delete In Forums:</td><td>  <?php echo YES;?> <input type="radio" name="dforum" value="yes" <?php if ($level["delete_forum"]=="yes") echo "checked" ?> />&nbsp;&nbsp; <?php echo NO;?> <input type="radio" name="dforum" value="no" <?php if ($level["delete_forum"]=="no") echo "checked" ?> /></td></tr>
	<tr><td>Can Upload:</td><td>  <?php echo YES;?> <input type="radio" name="upload" value="yes" <?php if ($level["can_upload"]=="yes") echo "checked" ?> />&nbsp;&nbsp; <?php echo NO;?> <input type="radio" name="upload" value="no" <?php if ($level["can_upload"]=="no") echo "checked" ?> /></td></tr>
	<tr><td>Can Download:</td><td>  <?php echo YES;?> <input type="radio" name="down" value="yes" <?php if ($level["can_download"]=="yes") echo "checked" ?> />&nbsp;&nbsp; <?php echo NO;?> <input type="radio" name="down" value="no" <?php if ($level["can_download"]=="no") echo "checked" ?> /></td></tr>
	<tr><td>Can View CP:</td><td>  <?php echo YES;?> <input type="radio" name="admincp" value="yes" <?php if ($level["control_panel"]=="yes") echo "checked" ?> />&nbsp;&nbsp; <?php echo NO;?> <input type="radio" name="admincp" value="no" <?php if ($level["control_panel"]=="no") echo "checked" ?> /></td></tr>
	<?php
	print("\n<tr><td align=\"center\" class=\"header\"><input type=\"submit\" name=\"write\" value=\"CONFIRM\" /></td></tr>");
	print("</table></form><BR><BR>");
	end_frame();
	stdfoot();
}

if ($action=="groups" && $do=="update"){
		stdhead("Groups Management");
		navmenu();

		begin_frame("Update");

		 $update=array();
		 $update[]="level='".mysql_escape_string($_POST["gname"])."'";
		 $update[]="view_torrents='".$_POST["vtorrent"]."'";
		 $update[]="edit_torrents='".$_POST["etorrent"]."'";
		 $update[]="delete_torrents='".$_POST["dtorrent"]."'";
		 $update[]="view_users='".$_POST["vuser"]."'";
		 $update[]="edit_users='".$_POST["euser"]."'";
		 $update[]="delete_users='".$_POST["duser"]."'";
		 $update[]="view_news='".$_POST["vnews"]."'";
		 $update[]="edit_news='".$_POST["enews"]."'";
		 $update[]="delete_news='".$_POST["dnews"]."'";
		 $update[]="view_forum='".$_POST["vforum"]."'";
		 $update[]="edit_forum='".$_POST["eforum"]."'";
		 $update[]="delete_forum='".$_POST["dforum"]."'";
		 $update[]="can_upload='".$_POST["upload"]."'";
		 $update[]="can_download='".$_POST["down"]."'";
		 $update[]="control_panel='".$_POST["admincp"]."'";
		 $strupdate=implode(",",$update);

		 $group_id=intval($_GET["group_id"]);
		 mysql_query("UPDATE groups SET $strupdate WHERE group_id=$group_id") or die(mysql_error());

		echo "<BR><center><b>Updated OK</b></center><BR>";
		end_frame();
		stdfoot();	
}

if ($action=="groups" && $do=="delete"){
		//Needs to be secured!!!!
		$group_id=intval($_GET["group_id"]);
		if (($group_id=="1") || ($group_id=="7"))
			show_error_msg("ERROR","You cannot delete this group!",1);

		stdhead("Groups Management");

		navmenu();

		begin_frame("Delete");
		mysql_query("DELETE FROM groups WHERE group_id=$group_id") or die(mysql_error());
		echo "<BR><center><b>Deleted OK</b></center><BR>";
		end_frame();
		stdfoot();	
}


if ($action=="groups" && $do=="add") {
	stdhead("Groups Management");

	navmenu();

	begin_frame("Add New Group");
	?>
	<form action="admincp.php?action=groups&do=addnew" name="level" method="post">
	<table width="100%" align="center">
	<tr><td>Group Name:</td><td><input type="text" name="gname" value="" size="40" /></td></tr>
	<tr><td>Copy Settings From: </td><td><select name="getlevel" size="1">
	<?
	$rlevel=mysql_query("SELECT DISTINCT group_id, level FROM groups ORDER BY group_id");

	while($level=mysql_fetch_array($rlevel)) {
		print("\n<option value=".$level["group_id"].">".$level["level"]."</option>");
	}
	print("\n</select></td></tr>");
	print("\n<tr><td align=\"center\" class=\"header\"><input type=\"submit\" name=\"confirm\" value=\"Confirm\" /></td></tr>");
	print("</table></form><BR><BR>");
	end_frame();
	stdfoot();	
}

if ($action=="groups" && $do=="addnew") {
	
	stdhead("Groups Management");

	navmenu();

	begin_frame("Add New Group");

	$group_id=intval($_POST["getlevel"]);

	$rlevel=mysql_query("SELECT * FROM groups WHERE group_id=$group_id") or die(mysql_error());
	$level=mysql_fetch_array($rlevel);
	if (!$level)
	   show_error_msg("Error","Invalid ID",1);

	$update=array();
	$update[]="level='".mysql_escape_string($_POST["gname"])."'";
	$update[]="view_torrents='".$level["view_torrents"]."'";
	$update[]="edit_torrents='".$level["edit_torrents"]."'";
	$update[]="delete_torrents='".$level["delete_torrents"]."'";
	$update[]="view_users='".$level["view_users"]."'";
	$update[]="edit_users='".$level["edit_users"]."'";
	$update[]="delete_users='".$level["delete_users"]."'";
	$update[]="view_news='".$level["view_news"]."'";
	$update[]="edit_news='".$level["edit_news"]."'";
	$update[]="delete_news='".$level["delete_news"]."'";
	$update[]="view_forum='".$level["view_forum"]."'";
	$update[]="edit_forum='".$level["edit_forum"]."'";
	$update[]="delete_forum='".$level["delete_forum"]."'";
	$update[]="can_upload='".$level["can_upload"]."'";
	$update[]="can_download='".$level["can_download"]."'";
	$update[]="control_panel='".$level["control_panel"]."'";
	$strupdate=implode(",",$update);
	$group_id=intval($_GET["group_id"]);
	mysql_query("INSERT INTO groups SET $strupdate") or die(mysql_error());

	echo "<BR><center><b>Added OK</b></center><BR>";
	end_frame();
	stdfoot();	
}

#====================================#
#		Theme Management		#
#====================================#

if ($action == "style") {
	if ($do == "add") {
		stdhead();
		navmenu();
		if ($_POST) {
			if (empty($_POST['name']))
				$error .= "Theme name was empty.<BR>";
			if (empty($_POST['uri']))
				$error .= "Folder name was empty.";
			if ($error)
				show_error_msg("Error", "Theme NOT added.<BR>Reason: $error", 1);
			if (mysql_query("INSERT INTO stylesheets (name, uri) VALUES ('$_POST[name]', '$_POST[uri]')"))
				show_error_msg("Success", "Theme '$_POST[name]' added.", 0);
			elseif (mysql_errno() == 1062)
				show_error_msg("Failed", "Theme already exists.", 0);
			else
				show_error_msg("Failed", "Theme NOT added. Database error: ".mysql_error(), 0);
		}
		begin_frame("Add Theme", "center");
		?>
		<table align='center' width='80%' bgcolor='#cecece' cellspacing='2' cellpadding='2' style='border: 1px solid black'>
		<form action='admincp.php' method='post'>
		<input type='hidden' name='action' value='style'>
		<input type='hidden' name='do' value='add'>
		<tr>
		<td>Name of the new Theme:</td>
		<td align='right'><input type='text' name='name' size='30' maxlength='30' value='<?=$name?>'></td>
		</tr>
		<tr>
		<td>Folder Name (case SenSiTive):</td>
		<td align='right'><input type='text' name='uri' size='30' maxlength='30' value='<?=$uri?>'></td>
		</tr>
		<tr>
		<td colspan='2' align='center'>
		<input type='submit' value='Add new theme'>
		<input type='reset' value='Reset'>
		</td>
		</tr>
		</table>
		<br>Please note: All themes must be uploaded to the /themes/ folder.  Please make sure all folder names are EXACT.
		<?
		end_frame();
		stdfoot();
	} elseif ($do == "del") {
		if (is_array($ids))
			$ids = implode(",", $ids);
		
		mysql_query("DELETE FROM stylesheets WHERE id IN ($ids)");
		header("Refresh: 1;url=admincp.php?action=style");
		stdhead();
		show_error_msg("Success", "Theme(s) deleted.<BR><BR>Redirecting...");
		stdfoot();
	}elseif ($do == "add2") {
		stdhead();

		$add = $_POST["add"];
		$a = 0;
		foreach ($add as $theme) {
			if ($theme['add'] != 1) { $a++; continue; }
			if (!mysql_query("INSERT INTO stylesheets (name, uri) VALUES(".sqlesc($theme['name']).", ".sqlesc($theme['uri']).")")) {
				if (mysql_errno() == 1062)
					$error .= htmlspecialchars($theme['name'])." - Already exists.<BR>";
				else
					$error .= htmlspecialchars($theme['name']).": Database error: ".mysql_error()." (".mysql_errno().")<BR>";
			}else
				$added .= htmlspecialchars($theme['name'])."<BR>";
		}
		if ($a == count($add)) {
			header("Refresh: 3;url=admincp.php?action=style");
			show_error_msg("Error", "Nothing Selected.<BR><a href='admincp.php?action=style'>Click Here</a> to go back or wait 3 seconds to be redirected.", 1);
		}

		header("Refresh: 3;url=admincp.php?action=style");
		if ($added)
			show_error_msg("Success", "The following themes were added:<BR>$added<BR><BR>Redirecting...", 0);
		if ($error)
			show_error_msg("Failed", "The following themes were NOT added:<BR>$error", 0);
		stdfoot();
		
	}else{
		stdhead("Theme Management");
		navmenu();
		begin_frame("Theme Management", "center");
		$res = mysql_query("SELECT * FROM stylesheets");
		echo "<center><a href='admincp.php?action=style&do=add'>Add Theme</a><!-- - <b>Click a theme to edit</b>--></center><BR>";
		echo "Current Themes:<form method='POST' action='admincp.php?action=style&do=del'><table width='60%' class=table_table align='center'>".
			"<tr><td class=table_head><B>ID</B></td><td class=table_head><B>Name</B></td><td class=table_head><B>Folder Name</B></td><td width='5%' class=table_head>&nbsp;</td></tr>";
		while ($row=mysql_fetch_assoc($res)) {
			if (!is_dir("themes/$row[uri]"))
				$row['uri'] .= " <B>- Directory doesn't exist.</B>";
			echo "<tr><td class=table_col1 align=center>$row[id]</td><td class=table_col2 align=center>$row[name]</td><td class=table_col1 align=center>$row[uri]</td><td class=table_col2 align=center><input name='ids[]' type='checkbox' value='$row[id]'></td></tr>";
		}
		mysql_free_result($res);
		echo "</table><p align='center'><input type='button' value='Check All' onclick='this.value=check(form)'>&nbsp;<input type='Submit' value='Delete Selected'></p></form>";
		
		echo "<p>Themes in themes/ but not database:<BR><form action='admincp.php?action=style&do=add2' method='POST'><table width='60%' class=table_table align='center'>".
			"<tr><td class=table_head align=center><B>Name</B></td><td class=table_head align=center><B>Folder Name</B></td><td width='5%' class=table_head align=center>&nbsp;</td></tr>";
		$dh = opendir("themes/");
		$i=0;
		while (($file = readdir($dh)) !== false) {
			if ($file == "." || $file == ".." || !is_dir("themes/$file"))
				continue;
			if (is_file("themes/$file/header.php")) {
					$res = mysql_query("SELECT id FROM stylesheets WHERE uri = '$file' ");
					if (mysql_num_rows($res) == 0) {
						echo "<tr><td class=table_col1 align=center><input type='text' name='add[$i][name]' value='$file'></td><td class=table_col2 align=center>$file<input type='hidden' name='add[$i][uri]' value='$file'></td><td class=table_col1 align=center><input type='checkbox' name='add[$i][add]' value='1'></td></tr>";
						$i++;
					}
				}
		}
		if (!$i) echo "<tr><td class=table_col1 align=center colspan=3>Nothing to show.</td></tr>";
		echo "</table><p align='center'>".($i?"<input type='submit' value='Add Selected'>":"")."</p></form></p>";
		end_frame();
		stdfoot();
	}
}

/////////////////////// NEWS ///////////////////////
if ($action=="news" && $do=="view"){
	stdhead("News Management");
	navmenu();

	begin_frame("News");
	echo "<CENTER><a href=admincp.php?action=news&do=add><B>Add News Item</B></a></CENTER><br>";

	$res = mysql_query("SELECT * FROM news ORDER BY added DESC") or die(mysql_error());
	if (mysql_num_rows($res) > 0){
		
		while ($arr = mysql_fetch_array($res)) {
			$newsid = $arr["id"];
			$body = $arr["body"];
			$title = $arr["title"];
			$userid = $arr["userid"];
			$added = $arr["added"] . " GMT (" . (get_elapsed_time(sql_timestamp_to_unix_timestamp($arr["added"]))) . " ago)";

			$res2 = mysql_query("SELECT username FROM users WHERE id = $userid") or die(mysql_error());
			$arr2 = mysql_fetch_array($res2);
			
			$postername = $arr2["username"];
			
			if ($postername == "")
				$by = "Unknown";
			else
				$by = "<a href=account-details.php?id=$userid><b>$postername</b></a>";
			
			print("<table border=0 cellspacing=0 cellpadding=0><tr><td>");
			print("$added&nbsp;---&nbsp;by&nbsp$by");
			print(" - [<a href=?action=news&do=edit&newsid=$newsid><b>Edit</b></a>]");
			print(" - [<a href=?action=news&do=delete&newsid=$newsid><b>Delete</b></a>]");
			print("</td></tr>\n");

			print("<tr valign=top><td class=comment><b>$title</b><br>$body</td></tr></table><BR>\n");
		}

	}else{
	 echo "No News Posted";
	}

	end_frame();
	stdfoot();
}

if ($action=="news" && $do=="takeadd"){
	$body = $_POST["body"];
	
	if (!$body)
		show_error_msg("Error","The news item cannot be empty!",1); 

	$title = $_POST['title'];

	if (!$title)
		show_error_msg("Error","The news title cannot be empty!",1);
	
	$added = $_POST["added"];

	if (!$added)
		$added = sqlesc(get_date_time());

	mysql_query("INSERT INTO news (userid, added, body, title) VALUES (".

	$CURUSER['id'] . ", $added, " . sqlesc($body) . ", " . sqlesc($title) . ")") or die(mysql_error());

	if (mysql_affected_rows() == 1)
		show_error_msg("Completed","News item was added successfully.",1);
	else
		show_error_msg("Error","Unable to add news",1);
}

if ($action=="news" && $do=="add"){
	stdhead("News Management");
	navmenu();

	begin_frame("Add News");
	print("<CENTER><form method=post action=admincp.php name=news>\n");
	print("<input type=hidden name=action value=news>\n");
	print("<input type=hidden name=do value=takeadd>\n");

	print("<center><B>News Title:</B> <input type=text name=title><br>\n");

	echo "<BR>".textbbcode("news","body")."<br>";

	print("<br><br><div align=center><input type=submit value='Submit' class=btn></div>\n");

	print("</form><br><br></CENTER>\n");
	end_frame();
	stdfoot();
}

if ($action=="news" && $do=="edit"){
	stdhead("News Management");
	navmenu();

	$newsid = (int)$_GET["newsid"];
	
	if (!is_valid_id($newsid))
		show_error_msg("Error","Invalid news item ID.",1);

	$res = mysql_query("SELECT * FROM news WHERE id=$newsid") or die(mysql_error());

	if (mysql_num_rows($res) != 1)
		show_error_msg("Error", "No news item with ID $newsid.",1);

	$arr = mysql_fetch_array($res);

	if ($_SERVER['REQUEST_METHOD'] == 'POST'){
  		$body = $_POST['body'];

		if ($body == "")
    		show_error_msg("Error", "Body cannot be empty!",1);

		$title = $_POST['title'];

		if ($title == "")
			show_error_msg("Error", "Title cannot be empty!",1);

		$body = sqlesc($body);

		$editedat = sqlesc(get_date_time());

		mysql_query("UPDATE news SET body=$body, title='$title' WHERE id=$newsid") or die(mysql_error());

		$returnto = $_POST['returnto'];

		if ($returnto != "")
			header("Location: $returnto");
		else
			show_error_msg("Completed","News item was edited successfully.",0);
	} else {
		$returnto = $_GET['returnto'];
		begin_frame("Edit News");
		print("<form method=post action=?action=news&do=edit&newsid=$newsid name=news>\n");
		print("<CENTER>");
		print("<input type=hidden name=returnto value=$returnto>\n");
		print("<B>News Title: </B><input type=text name=title value=\"".$arr['title']."\"><BR><BR>\n");
		echo "<BR>".textbbcode("news","body","".$arr["body"]."")."<br>";
		print("<BR><input type=submit value='Okay' class=btn>\n");
		print("</CENTER>\n");
		print("</form>\n");
	}
	end_frame();
	stdfoot();
}

if ($action=="news" && $do=="delete"){
	stdhead("News Management");
	navmenu();

	$newsid = (int)$_GET["newsid"];
	
	if (!is_valid_id($newsid))
		show_error_msg("Error","Invalid news item ID",1);

	mysql_query("DELETE FROM news WHERE id=$newsid") or die(mysql_error());
	
	show_error_msg("Completed","News item was deleted successfully.",1);
}

///////////////// BLOCKS MANAGEMENT /////////////
if ($action=="blocks" && $do=="view") {
    stdhead("Blocks Management");

    navmenu();

    begin_frame("View Blocks");

    $enabled = mysql_query("SELECT named, name, description, position, sort FROM blocks WHERE enabled=1 ORDER BY position, sort") or show_error_msg("Error","Database Query failed: " . mysql_error());
    $disabled = mysql_query("SELECT named, name, description, position, sort FROM blocks WHERE enabled=0 ORDER BY position, sort") or show_error_msg("Error","Database Query failed: " . mysql_error());
    
    print("<table align=\"center\" width=\"600\"><tr><td>");
    print("<table class=\"tablebg\" cellspacing=\"1\" align=\"center\" width=\"100%\">".
            "<tr>".
                "<td class=\"rowTabHead\" align=\"center\"><font size=\"2\"><b>Enabled Blocks</b></font></td>".
            "</tr>".
        "</table><br />".
        "<table class=\"tablebg\" cellspacing=\"1\" align=\"center\" width=\"100%\">".
            "<tr>".
                "<td class=\"rowTabHead\" align=\"center\">Name</td>".
                "<td class=\"rowTabHead\" align=\"center\">Description</td>".
                "<td class=\"rowTabHead\" align=\"center\">Position</td>".
                "<td class=\"rowTabHead\" align=\"center\">Sort<br />Order</td>".
                "<td class=\"rowTabHead\" align=\"center\">Preview</td>".
            "</tr>");
        while($blocks = mysql_fetch_assoc($enabled)){
        if(!$setclass){
            $class="row2";$setclass=true;}
        else{
            $class="row1";$setclass=false;}
    
            print("<tr>".
                        "<td class=$class valign=\"top\">".$blocks["named"]."</td>".
                        "<td class=$class>".$blocks["description"]."</td>".
                        "<td class=$class align=\"center\">".$blocks["position"]."</td>".
                        "<td class=$class align=\"center\">".$blocks["sort"]."</td>".
                        "<td class=$class align=\"center\">[<a href=\"blocks-edit.php?preview=true&name=".$blocks["name"]."#".$blocks["name"]."\" target=\"_blank\">preview</a>]</td>".
                    "</tr>");
        }
    print("<tr><td colspan=\"5\" class=\"rowTabHead\" align=\"center\"><form action=blocks-edit.php><input type=\"submit\" class=\"btn\" value=\"Edit\" /></td></tr>");
    print("</table></form>");
    print("</td></tr></table>");    
    
    print("<hr>");
    $setclass=false;
    print("<table align=\"center\" width=\"600\"><tr><td>");
    print("<table class=\"tablebg\" cellspacing=\"1\" align=\"center\" width=\"100%\">".
            "<tr>".
                "<td class=\"rowTabHead\" align=\"center\"><font size=\"2\"><b>Disabled Blocks</b></font></td>".
            "</tr>".
        "</table><br />".
        "<table class=\"tablebg\" cellspacing=\"1\" align=\"center\" width=\"100%\">".
            "<tr>".
                "<td class=\"rowTabHead\" align=\"center\">Name</td>".
                "<td class=\"rowTabHead\" align=\"center\">Description</td>".
                "<td class=\"rowTabHead\" align=\"center\">Position</td>".
                "<td class=\"rowTabHead\" align=\"center\">Sort<br />Order</td>".
                "<td class=\"rowTabHead\" align=\"center\">Preview</td>".
            "</tr>");
        while($blocks = mysql_fetch_assoc($disabled)){
        if(!$setclass){
            $class="row2";$setclass=true;}
        else{
            $class="row1";$setclass=false;}
    
            print("<tr>".
                        "<td class=$class valign=\"top\">".$blocks["named"]."</td>".
                        "<td class=$class>".$blocks["description"]."</td>".
                        "<td class=$class align=\"center\">".$blocks["position"]."</td>".
                        "<td class=$class align=\"center\">".$blocks["sort"]."</td>".
                        "<td class=$class align=\"center\">[<a href=\"blocks-edit.php?preview=true&name=".$blocks["name"]."#".$blocks["name"]."\" target=\"_blank\">preview</a>]</td>".
                    "</tr>");
        }
    print("<tr><td colspan=\"5\" class=\"rowTabHead\" align=\"center\" valign=\"bottom\"><form action=blocks-edit.php><input type=\"submit\" class=\"btn\" value=\"Edit\" /></td></tr>");
    print("</table></form>");
    print("</td></tr></table>");    
    end_frame();
    stdfoot();    
}


////////// categories /////////////////////
if ($action=="categories" && $do=="view"){
	stdhead("Categories Management");
	navmenu();

	begin_frame("Torrent Categories");
	echo "<CENTER><a href=admincp.php?action=categories&do=add><B>Add New Category</B></a></CENTER><br>";

	print("<i>Please note that if no image is specified, the category name will be displayed</i><br><br>");

	echo("<center><table width=95% class=table_table>");
	echo("<td width=10 class=table_head><B>Sort</B></td><td class=table_head><B>Parent Cat</B></td><td class=table_head><B>Sub Cat</B></td><td class=table_head><B>Image</B></td><td width=30 class=table_head></td>");
	$query = "SELECT * FROM categories ORDER BY parent_cat ASC, sort_index ASC";
	$sql = mysql_query($query);
	while ($row = mysql_fetch_array($sql)) {
		$id = $row['id'];
		$name = $row['name'];
		$priority = $row['sort_index'];
		$parent = $row['parent_cat'];

		print("<tr><td class=table_col1>$priority</td><td class=table_col2>$parent</td><td class=table_col1>$name</a></td><td class=table_col2 align=center>");
		if (isset($row["image"]) && $row["image"] != "")
			print("<img border=\"0\"src=\"" . $site_config['SITEURL'] . "/images/categories/" . $row["image"] . "\" alt=\"" . $row["name"] . "\" />");
		else
			print("-");	
		print("</td><td class=table_col1><a href=admincp.php?action=categories&do=edit&id=$id>[EDIT]</a> <a href=admincp.php?action=categories&do=delete&id=$id>[DELETE]</a></td></tr>");
	}
	echo("</table></center>");
	end_frame();
	stdfoot();
}


if ($action=="categories" && $do=="edit"){
	stdhead("Categories Management");
	navmenu();

	$id = (int)$_GET["id"];
	
	if (!is_valid_id($id))
		show_error_msg("Error","Invalid ID.",1);

	$res = mysql_query("SELECT * FROM categories WHERE id=$id") or die(mysql_error());

	if (mysql_num_rows($res) != 1)
		show_error_msg("Error", "No category with ID $id.",1);

	$arr = mysql_fetch_array($res);

	if ($_GET["save"] == '1'){
  		$parent_cat = $_POST['parent_cat'];
		if ($parent_cat == "")
    		show_error_msg("Error", "Parent Cat cannot be empty!",1);

		$name = $_POST['name'];
		if ($name == "")
			show_error_msg("Error", "Sub cat cannot be empty!",1);

		$sort_index = $_POST['sort_index'];
		$image = $_POST['image'];

		$parent_cat = sqlesc($parent_cat);
		$name = sqlesc($name);
		$sort_index = sqlesc($sort_index);
		$image = sqlesc($image);

		mysql_query("UPDATE categories SET parent_cat=$parent_cat, name=$name, sort_index=$sort_index, image=$image WHERE id=$id") or die(mysql_error());

		show_error_msg("Completed","category was edited successfully.",0);

	} else {
		begin_frame("Edit Category");
		print("<form method=post action=?action=categories&do=edit&id=$id&save=1>\n");
		print("<CENTER><table border=0 cellspacing=0 cellpadding=5>\n");
		print("<tr><td align=left><B>Parent Category: </B><input type=text name=parent_cat value=\"".$arr['parent_cat']."\"> All Subcats with EXACTLY the same parent cat are grouped</td></tr>\n");
		print("<tr><td align=left><B>Sub Category: </B><input type=text name=name value=\"".$arr['name']."\"></td></tr>\n");
		print("<tr><td align=left><B>Sort: </B><input type=text name=sort_index value=\"".$arr['sort_index']."\"></td></tr>\n");
		print("<tr><td align=left><B>Image: </B><input type=text name=image value=\"".$arr['image']."\"> single filename</td></tr>\n");
		print("<tr><td align=center><input type=submit value='Submit' class=btn></td></tr>\n");
		print("</table></CENTER>\n");
		print("</form>\n");
	}
	end_frame();
	stdfoot();
}

if ($action=="categories" && $do=="delete"){
	stdhead("Categories Management");
	navmenu();

	$id = (int)$_GET["id"];

	if ($_GET["sure"] == '1'){

		if (!is_valid_id($id))
			show_error_msg("Error","Invalid news item ID",1);

		$newcatid = $_POST["newcat"];

		mysql_query("UPDATE torrents SET category=$newcatid WHERE category=$id") or die(mysql_error()); //move torrents to a new cat

		mysql_query("DELETE FROM categories WHERE id=$id") or die(mysql_error()); //delete old cat
		
		show_error_msg("Completed","Category Deleted OK",1);

	}else{
		begin_frame("Delete Category");
		print("<form method=post action=?action=categories&do=delete&id=$id&sure=1>\n");
		print("<CENTER><table border=0 cellspacing=0 cellpadding=5>\n");
		print("<tr><td align=left><B>Category ID to move all Torrents To: </B><input type=text name=newcat> (Cat ID)</td></tr>\n");
		print("<tr><td align=center><input type=submit value='Submit' class=btn></td></tr>\n");
		print("</table></CENTER>\n");
		print("</form>\n");
	}
	end_frame();
	stdfoot();
}

if ($action=="categories" && $do=="takeadd"){
  		$name = $_POST['name'];
		if ($name == "")
    		show_error_msg("Error", "Sub Cat cannot be empty!",1);

		$parent_cat = $_POST['parent_cat'];
		if ($parent_cat == "")
			show_error_msg("Error", "Parent Cat cannot be empty!",1);

		$sort_index = $_POST['sort_index'];
		$image = $_POST['image'];

		$parent_cat = sqlesc($parent_cat);
		$name = sqlesc($name);
		$sort_index = sqlesc($sort_index);
		$image = sqlesc($image);

	mysql_query("INSERT INTO categories (name, parent_cat, sort_index, image) VALUES ($name, $parent_cat, $sort_index, $image)") or die(mysql_error());

	if (mysql_affected_rows() == 1)
		show_error_msg("Completed","Category was added successfully.",1);
	else
		show_error_msg("Error","Unable to add category",1);
}

if ($action=="categories" && $do=="add"){
	stdhead("Category Management");
	navmenu();

	begin_frame("Add Category");
	print("<CENTER><form method=post action=admincp.php>\n");
	print("<input type=hidden name=action value=categories>\n");
	print("<input type=hidden name=do value=takeadd>\n");

	print("<table border=0 cellspacing=0 cellpadding=5>\n");

	print("<tr><td align=left><B>Parent Category:</B> <input type=text name=parent_cat></td></tr>\n");
	print("<tr><td align=left><B>Sub Category:</B> <input type=text name=name></td></tr>\n");
	print("<tr><td align=left><B>Sort:</B> <input type=text name=sort_index></td></tr>\n");
	print("<tr><td align=left><B>Image:</B> <input type=text name=image></td></tr>\n");

	print("<br><br><div align=center><input type=submit value='Submit' class=btn></div></td></tr>\n");

	print("</table></form><br><br></CENTER>\n");
	end_frame();
	stdfoot();
}


if ($action=="whoswhere"){
	stdhead("Where are members");
	navmenu();

	begin_frame("Last 100 page views");
	print("<CENTER><table class=table_table width=80%><tr><td class=table_head>User</td><td class=table_head>Page</td><td class=table_head>Accessed</td></tr>");
	$res = mysql_query("SELECT id, username, page, last_access FROM users ORDER BY last_access DESC LIMIT 100");
	while ($arr = mysql_fetch_assoc($res))
	print("<tr><td class=table_col1><a href=account-details.php?id=$arr[id]><b>$arr[username]</b></a></td><td class=table_col2>".htmlspecialchars($arr["page"])."</td><td  class=table_col1>$arr[last_access]</td></tr>");
	print("</table></CENTER>");
	end_frame();

	stdfoot();
}

if ($action=="peers"){
	stdhead("Peers List");
	navmenu();

	begin_frame("Peers List");

	$count1 = number_format(get_row_count("peers"));

	print("<center>We have $count1 peers</center><br>");

	$res4 = mysql_query("SELECT COUNT(*) FROM peers $limit") or die(mysql_error());
	$row4 = mysql_fetch_array($res4);

	$count = $row4[0];
	$peersperpage = 50;

	list($pagertop, $pagerbottom, $limit) = pager($peersperpage, $count, "admincp.php?action=peers&");

	print("$pagertop");

	$sql = "SELECT * FROM peers ORDER BY started DESC $limit";
	$result = mysql_query($sql);

	if( mysql_num_rows($result) != 0 ) {
		print'<CENTER><table width=100% border=1 cellspacing=0 cellpadding=3 class=table_table>';
		print'<tr>';
		print'<td class=table_head align=center>User</td>';
		print'<td class=table_head align=center>Torrent</td>';
		print'<td class=table_head align=center>IP</td>';
		print'<td class=table_head align=center>Port</td>';
		print'<td class=table_head align=center>Upl.</td>';
		print'<td class=table_head align=center>Downl.</td>';
		print'<td class=table_head align=center>Peer-ID</td>';
		print'<td class=table_head align=center>Conn.</td>';
		print'<td class=table_head align=center>Seeding</td>';
		print'<td class=table_head align=center>Started</td>';
		print'<td class=table_head align=center>Last<br>Action</td>';
		print'</tr>';

		while($row = mysql_fetch_assoc($result)) {
			if ($site_config['MEMBERSONLY']) {
				$sql1 = "SELECT id, username FROM users WHERE id = $row[userid]";
				$result1 = mysql_query($sql1);
				$row1 = mysql_fetch_assoc($result1);
			}

			if ($row1['username'])
				print'<tr><td class=table_col1><a href="account-details.php?id=' . $row['userid'] . '">' . $row1['username'] . '</a></td>';
			else
				print'<tr><td class=table_col1>'.$row[ip].'</td>';

			$sql2 = "SELECT id, name FROM torrents WHERE id = $row[torrent]";
			$result2 = mysql_query($sql2);

			while ($row2 = mysql_fetch_assoc($result2)) {

				$smallname =substr(htmlspecialchars($row2["name"]) , 0, 40);
					if ($smallname != htmlspecialchars($row2["name"])) {
						$smallname .= '...';
					}

				print'<td class=table_col1><a href="torrents-details.php?id=' . $row['torrent'] . '">' . $smallname . '</td>';
				print'<td align=center class=table_col1>' . $row['ip'] . '</td>';
				print'<td align=center class=table_col1>' . $row['port'] . '</td>';

				if ($row['uploaded'] < $row['downloaded'])
					print'<td align=center class=table_col1><font color=red>' . mksize($row['uploaded']) . '</font></td>';
				else
					if ($row['uploaded'] == '0')
						print'<td align=center class=table_col1>' . mksize($row['uploaded']) . '</td>';
					else
						print'<td align=center class=table_col1><font color=green>' . mksize($row['uploaded']) . '</font></td>';
				print'<td align=center class=table_col1>' . mksize($row['downloaded']) . '</td>';
				print'<td align=center class=table_col1>' . $row['peer_id'] . '</td>';
				if ($row['connectable'] == 'yes')
					print'<td align=center class=table_col1><font color=green>' . $row['connectable'] . '</font></td>';
				else
					print'<td align=center class=table_col1><font color=red>' . $row['connectable'] . '</font></td>';
				if ($row['seeder'] == 'yes')
					print'<td align=center class=table_col1><font color=green>' . $row['seeder'] . '</font></td>';
				else
					print'<td align=center class=table_col1><font color=red>' . $row['seeder'] . '</font></td>';
				print'<td align=center class=table_col1>' . $row['started'] . '</td>';
				print'<td align=center class=table_col1>' . $row['last_action'] . '</td>';
				print'</tr>';
			}
		}
		print'</table>';
		print("$pagerbottom</CENTER>");
	}else{
		print'<B><CENTER>No Peers</CENTER></B><BR>';
	}
	end_frame();

	stdfoot();
}


if ($action=="lastcomm"){
	stdhead("Latest Comments");
	navmenu();

	$res = mysql_query("SELECT COUNT(*) FROM comments WHERE torrent > '0'") or die(mysql_error());
	$arr = mysql_fetch_row($res);
	$count = $arr[0];

	list($pagertop, $pagerbottom, $limit) = pager(20, $count, "admincp.php?action=lastcomm&");

	begin_frame("Last Comments");

	echo $pagertop;

	$res = mysql_query("SELECT comments.id, comments.added, comments.user, comments.torrent, comments.text, torrents.name as tnome, users.username as unome FROM comments LEFT JOIN users ON users.id = comments.user LEFT JOIN torrents ON torrents.id = comments.torrent ORDER BY comments.id DESC $limit") or die(mysql_error());

	while ($arr = mysql_fetch_assoc($res)) {
		$userid = $arr["user"];
		$username = $arr["unome"];
		$data = $arr["added"];
		$tid = $arr["torrent"];
		$tnome = stripslashes($arr["tnome"]);
		$comentario = stripslashes(format_comment($arr["text"]));
		$cid = $arr["id"];
		echo "<table align=center cellpadding=1 cellspacing=0 style='border-collapse: collapse' bordercolor=#B5B5B5 width=100% border=1><tr><td class=ttable_col1 align=center>Torrent: <a href=\"torrents-details.php?id=$tid\">".$tnome."</a></td></tr><tr><td class=ttable_col2>".$comentario."</td></tr><tr><td class=ttable_col1 align=center>Posted in <B>".$data."</B> by <a href=\"account-details.php?id=".$userid."\">".$username."</a><!--  [ <a href=\"edit-comments.php?cid=".$cid."\">edit</a> | <a href=\"edit-comments.php?action=delete&cid=".$cid."\">delete</a> ] --></td></tr></table><br>";

	}
	echo $pagerbottom;
	end_frame();
	stdfoot();
}


if ($action=="messagespy"){
	stdhead("Message Spy");
	navmenu();

	$res2 = mysql_query("SELECT COUNT(*) FROM messages");
	$row = mysql_fetch_array($res2);
	$count = $row[0];

	$perpage = 50;

	list($pagertop, $pagerbottom, $limit) = pager($perpage, $count, "admincp.php?action=messagespy&");

	begin_frame("Message Spy");

	echo $pagertop;
	
	$res = mysql_query("SELECT * FROM messages WHERE location='in' ORDER BY id DESC $limit") or die(mysql_error());

	print("<CENTER><table border=1 cellspacing=0 cellpadding=3 class=table_table>\n");

	print("<tr><td class=table_head align=left>Sender</td><td class=table_head align=left>Receiver</td><td class=table_head align=left>Text</td><td class=table_head align=left>Date</td></tr>\n");

	while ($arr = mysql_fetch_assoc($res)){
		$res2 = mysql_query("SELECT username FROM users WHERE id=" . $arr["receiver"]) or die(mysql_error());

		$arr2 = mysql_fetch_assoc($res2);
		$receiver = "<a href=account-details.php?id=" . $arr["receiver"] . "><b>" . $arr2["username"] . "</b></a>";

		$res3 = mysql_query("SELECT username FROM users WHERE id=" . $arr["sender"]) or die(mysql_error());
		$arr3 = mysql_fetch_assoc($res3);

		$sender = "<a href=account-details.php?id=" . $arr["sender"] . "><b>" . $arr3["username"] . "</b></a>";
		if( $arr["sender"] == 0 )
			$sender = "<font color=red><b>System</b></font>";
		$msg = format_comment($arr["msg"]);

		$added = utc_to_tz($arr["added"]);

		print("<tr><td align=left class=table_col1>$sender</td><td align=left class=table_col2>$receiver</td><td align=left class=table_col1>$msg</td><td align=left class=table_col2>$added</td></TR>");
	}

	print("</table></CENTER><BR>");

	print($pagerbottom);

	end_frame();
	stdfoot();
}


if ($action=="torrentmanage"){
	stdhead("Torrent Management");
	navmenu();

	$search = trim($search);

	if ($search != '' ){
		$where = "WHERE name LIKE " . sqlesc("%$search%") . "";
	}

	
	$res2 = mysql_query("SELECT COUNT(*) FROM torrents $where");
	$row = mysql_fetch_array($res2);
	$count = $row[0];

	$perpage = 50;

	list($pagertop, $pagerbottom, $limit) = pager($perpage, $count, "admincp.php?action=torrentmanage&");

	begin_frame("Torrent Management");

	print("<CENTER><form method=get action=?>\n");
	print("<input type=hidden name=action value=torrentmanage>\n");
	print("" . SEARCH . ": <input type=text size=30 name=search>\n");
	print("<input type=submit value='Search'>\n");
	print("</form></CENTER>\n");

	echo $pagertop;
	?>
	<CENTER><table align=center cellpadding="0" cellspacing="0" class="table_table" width="100%" border="1">
	<tr>
	<td class=table_head align=center>Name</td>
	<td class=table_head align=center>Visible</td>
	<td class=table_head align=center>Banned</td>
	<td class=table_head align=center>Seeders</td>
	<td class=table_head align=center>Leechers</td>
	<td class=table_head align=center>External?</td>
	<td class=table_head align=center>Edit?</td>
	</tr>
	<?
	$rqq = "SELECT id, name, seeders, leechers, visible, banned, external FROM torrents $where ORDER BY name $limit";
	$resqq = mysql_query($rqq);

	while ($row = mysql_fetch_array($resqq)){
		extract ($row);

		$char1 = 35; //cut name length 
		$smallname = CutName(htmlspecialchars($row["name"]), $char1);

		echo "<tr><td class=table_col1><a href=\"torrents-details.php?id=$row[id]\">" . $smallname . "</a></td><td class=table_col2>$row[visible]</td><td class=table_col1>$row[banned]</td><td class=table_col2>$row[seeders]</td><td class=table_col1>$row[leechers]</td><td class=table_col2>$row[external]</td><td class=table_col1><a href=\"torrents-edit.php?returnto=" . urlencode($_SERVER["REQUEST_URI"]) . "&amp;id=" . $row["id"] . "\"><font size=1 face=Verdana>EDIT</a></td></tr>\n";
	}

	echo "</table></CENTER>\n";

	print($pagerbottom);

	end_frame();
	stdfoot();
}


if ($action == "users") {
	if ($do == "delete") {
		if (!@count($_POST['userids']))
			show_error_msg("Error", "Nothing selected.<BR><a href='admincp.php?action=users'>Click here</a> to go back.", 1);
		$userids = implode(", ",array_map("intval", $_POST['userids']));
		$r = mysql_query("SELECT id, username FROM users WHERE id IN ($userids)");
		while($rr=mysql_fetch_row($r))
			write_log("Account '$rr[1]' (ID: $rr[0]) was deleted by $CURUSER[username]");
		mysql_query("DELETE FROM users WHERE id IN ($userids)");
		$aff = mysql_affected_rows();
		header("Refresh: 3;url=admincp.php?action=users");
		show_error_msg("Users Deleted", "$aff user".($aff==1?'':'s')." deleted.<BR><a href='admincp.php?action=users'>Click here</a> to go back.", 1);
	}

	stdhead("Users Management");
	navmenu();

	$search = trim($search);

	if ($search != '' ){
		$where = "WHERE username LIKE " . sqlesc("%$search%") . " AND status='confirmed'";
	}

	
	$res2 = mysql_query("SELECT COUNT(*) FROM users $where");
	$row = mysql_fetch_array($res2);
	$count = $row[0];

	$perpage = 50;

	list($pagertop, $pagerbottom, $limit) = pager($perpage, $count, "admincp.php?action=users&");

	begin_frame("Users Management");

	print("<CENTER><form method=get action=?>\n");
	print("<input type=hidden name=action value=users>\n");
	print("" . SEARCH . ": <input type=text size=30 name=search>\n");
	print("<input type=submit value='Search'>\n");
	print("</form></CENTER>\n");

	echo $pagertop;
	?>
	<CENTER><table align=center cellpadding="0" cellspacing="0" class="table_table" width="100%" border="1">
	<tr>
	<td class=table_head align=center>Username</td>
	<td class=table_head align=center>Class</td>
	<td class=table_head align=center>Email</td>
	<td class=table_head align=center>IP</td>
	<td class=table_head align=center>Added</td>
	<td class=table_head align=center>Last Visit</td>
	<td class=table_head align=center>Delete?</td>
	</tr>
	<?
	
	$rqq = "SELECT * FROM users $where ORDER BY username $limit";
	$resqq = mysql_query($rqq);
	echo "<form action='admincp.php?action=users' method='POST'><input type='hidden' name='do' value='delete'>";
	while ($row = mysql_fetch_array($resqq)){
		echo "
		<tr><td class=table_col1 align=center><a href=account-details.php?id=$row[id]>$row[username]</a></td>
		<td class=table_col2 align=center>".get_user_class_name($row['class'])."</td>
		<td class=table_col1 align=center>$row[email]</td>
		<td class=table_col2 align=center>$row[ip]</td>
		<td class=table_col1 align=center>".utc_to_tz($row['added'])."</td>
		<td class=table_col2 align=center>$row[last_access]</td>
		<td class=table_col1 align=center><input type=checkbox name='userids[]' value='$row[id]'></td>
		</tr>\n";
	}

	echo "</table><BR><input type='button' value='Check All' onclick='this.value=check(form)'>&nbsp;<input type='submit' value='Delete checked'></form></CENTER>\n";

	print($pagerbottom);

	end_frame();
	stdfoot();
}


if ($action == "sitelog") {
	stdhead("Site Log");
	navmenu();

	$search = trim($search);

	if ($search != '' ){
		$where = "WHERE txt LIKE " . sqlesc("%$search%") . "";
	}

	
	$res2 = mysql_query("SELECT COUNT(*) FROM log $where");
	$row = mysql_fetch_array($res2);
	$count = $row[0];

	$perpage = 50;

	list($pagertop, $pagerbottom, $limit) = pager($perpage, $count, "admincp.php?action=sitelog&");

	begin_frame("Site Log");

	print("<CENTER><form method=get action=?>\n");
	print("<input type=hidden name=action value=sitelog>\n");
	print("" . SEARCH . ": <input type=text size=30 name=search>\n");
	print("<input type=submit value='Search'>\n");
	print("</form></CENTER>\n");

	echo $pagertop;
	?>
	<CENTER><table align=center cellpadding="0" cellspacing="0" class="table_table" width="100%" border="1">
	<tr>
	<td class=table_head align=center>Date</td>
	<td class=table_head align=center>Time</td>
	<td class=table_head align=center>Event</td>
	<!-- <td class=table_head align=center>Delete</td> -->
	</tr>
	<?
	
	$rqq = "SELECT id, added, txt FROM log $where ORDER BY id DESC $limit";
	$res = mysql_query($rqq);

	
	 while ($arr = MYSQL_FETCH_ARRAY($res)){
		$arr['added'] = utc_to_tz($arr['added']);
		$date = substr($arr['added'], 0, strpos($arr['added'], " "));
		$time = substr($arr['added'], strpos($arr['added'], " ") + 1);
		print("<tr><td class=table_col1>$date</td><td class=table_col2>$time</td><td class=table_col1 align=left>".stripslashes($arr[txt])."</td><!--<td class=table_col2><a href='staffcp.php?act=view_log&do=del_log&lid=$arr[id]' title='delete this entry'>delete</a></td>--></tr>\n");
	 }

	echo "</table></CENTER>\n";

	print($pagerbottom);

	end_frame();
	stdfoot();
}

if ($action == "cheats") {
	stdhead("Possible Cheater Detection");
	navmenu();

	if ($daysago && $megabts){

		$timeago = 84600 * $daysago; //last 7 days
		$bytesover = 1048576 * $megabts; //over 500MB Upped

		$result = mysql_query("select * FROM users WHERE UNIX_TIMESTAMP('" . get_date_time() . "') - UNIX_TIMESTAMP(added) < '$timeago' AND status='confirmed' AND uploaded > '$bytesover' ORDER BY uploaded DESC "); 
		$num = mysql_num_rows($result); // how many uploaders

		begin_frame("Possible Cheater Detection");
		echo "<p>" . $num . " Users with found over last ".$daysago." days with more than ".$megabts." MB (".$bytesover.") Bytes Uploaded.</p>";

		$zerofix = $num - 1; // remove one row because mysql starts at zero

		if ($num > 0){
		echo "<table align=center class=table_table>";
		echo "<tr>";
		 echo "<td class=table_head>No.</td>";
		 echo "<td class=table_head>" . USERNAME . "</td>";
		 echo "<td class=table_head>" . UPLOADED . "</td>";
		 echo "<td class=table_head>" . DOWNLOADED . "</td>";
		 echo "<td class=table_head>" . RATIO . "</td>";
		 echo "<td class=table_head>" . TORRENTS_POSTED . "</td>";
		 echo "<td class=table_head>AVG Daily Upload</td>";
		 echo "<td class=table_head>" . ACCOUNT_SEND_MSG . "</td>";
		 echo "<td class=table_head>Joined</td>";
		echo "</tr>";

		for ($i = 0; $i <= $zerofix; $i++) {
			 $id = mysql_result($result, $i, "id");
			 $username = mysql_result($result, $i, "username");
			 $added = mysql_result($result, $i, "added");
			 $uploaded = mysql_result($result, $i, "uploaded");
			 $downloaded = mysql_result($result, $i, "downloaded");
			 $donated = mysql_result($result, $i, "donated");
			 $warned = mysql_result($result, $i, "warned");
			 $joindate = "" . get_elapsed_time(sql_timestamp_to_unix_timestamp($added)) . " ago";
			 $upperquery = "SELECT added FROM torrents WHERE owner = $id";
			 $upperresult = mysql_query($upperquery);
			 $seconds = mkprettytime(utc_to_tz_time() - utc_to_tz_time($added));
			 $days = explode("d ", $seconds);

			 if(sizeof($days) > 1) {
				 $dayUpload  = $uploaded / $days[0];
				 $dayDownload = $downloaded / $days[0];
			}
		 
		  $torrentinfo = mysql_fetch_array($upperresult);
		 
		  $numtorrents = mysql_num_rows($upperresult);
		   
		  if ($downloaded > 0){
		   $ratio = $uploaded / $downloaded;
		   $ratio = number_format($ratio, 3);
		   $color = get_ratio_color($ratio);
		   if ($color)
		   $ratio = "<font color=$color>$ratio</font>";
		   }
		  else
		   if ($uploaded > 0)
			$ratio = "Inf.";
		   else
			$ratio = "---";
		  
		 
		 $counter = $i + 1;
		 
		 echo "<tr>";
		  echo "<td align=center class=table_col1>$counter.</td>";
		  echo "<td class=table_col2><a href=account-details.php?id=$id>$username</a></td>";
		  echo "<td class=table_col1>" . mksize($uploaded). "</td>";
		  echo "<td class=table_col2>" . mksize($downloaded) . "</td>";
		  echo "<td class=table_col1>$ratio</td>";
		  if ($numtorrents == 0) echo "<td class=table_col2><font color=red>$numtorrents torrents</font></td>";
		  else echo "<td class=table_col2>$numtorrents torrents</td>";

		  echo "<td class=table_col1>" . mksize($dayUpload) . "</td>";

		  echo "<td align=center class=table_col2><a href=mailbox.php?compose&$id>PM</a></td>";
		  echo "<td class=table_col1>" . $joindate . "</td>";
		 echo "</tr>";

		 
		 }
		echo "</table><br><br>";
		end_frame();
		}

		if ($num == 0)
		{
		end_frame();
		}

	}else{
	begin_frame("Possible Cheater Detection");?>
	<CENTER><form action='admincp.php?action=cheats' method='post'>
		Number of days joined: <input type='text' size='4' maxlength='4' name='daysago'> Days<br /><br />
		MB Uploaded: <input type='text' size='6' maxlength='6' name='megabts'> MB<br />
		<input type='submit' value='   Submit   ' style='background:#eeeeee'>
		</form></CENTER><?
	end_frame();
	}
	stdfoot();
}


if ($action=="emailbans"){
	stdhead("Email Bans");
	navmenu();

	$remove = $_GET['remove'];

	if (is_valid_id($remove)){
		mysql_query("DELETE FROM email_bans WHERE id=$remove") or die(mysql_error());
		write_log("Email Ban $remove was removed by ($CURUSER[username])");
	}

	if ($_GET["add"] == '1'){
		$mail_domain = trim($_POST["mail_domain"]);
		$comment = trim($_POST["comment"]);

		if (!$mail_domain || !$comment){
			show_error_msg("Error", "Missing form data.",0);
			stdfoot();
			die;
		}
		$mail_domain= sqlesc($mail_domain);
		$comment = sqlesc($comment);
		$added = sqlesc(get_date_time());

		mysql_query("INSERT INTO email_bans (added, addedby, mail_domain, comment) VALUES($added, $CURUSER[id], $mail_domain, $comment)") or die(mysql_error());

		write_log("Email Ban $mail_domain was added by ($CURUSER[username])");
		show_error_msg("Complete", "Email Ban Added",0);
		stdfoot();
		die;
	}

	begin_frame("Emails Or Domains Adress Bans");
	print("You can block specific email addresses or domains from signing up to your tracker<BR><BR><BR><b>&nbsp;Add Emails OR Domains Ban</b>\n");
	print("<table border=0 cellspacing=0 cellpadding=5 align=center>\n");
	print("<form method=post action=admincp.php?action=emailbans&add=1>\n");
	print("<tr><td align=right>Email Address OR Domain To Ban</td><td><input type=text name=mail_domain size=40></td>\n");
	print("<tr><td align=right>Comment</td><td><input type=text name=comment size=40></td>\n");
	print("<tr><td colspan=2 align=center><input type=submit value='Add Ban'></td></tr>\n");
	print("</form>\n</table>\n<br>");
	//}

	$res2 = mysql_query("SELECT count(id) FROM email_bans") or die(mysql_error());
	$row = mysql_fetch_array($res2);
	$url = " .$_SERVER[PHP_SELF]";
	$count = $row[0];
	$perpage = 40;list($pagertop, $pagerbottom, $limit) = pager($perpage, $count, $url);
	print("<BR><b>&nbsp;Current Email Bans ($count)</b>\n");

	if ($count == 0){
		print("<p align=center><b>Nothing found</b></p><br>\n");
	}else{
		echo $pagertop;
		print("<table border=0 cellspacing=0 cellpadding=5 width=90% align=center class=table_table>\n");
		print("<tr><td class=table_head>Added</td><td  class=table_head align=left>Mail Address Or Domain</td>"."<td class=table_head align=left>Banned By</td><td  class=table_head align=left>Comment</td><td class=table_head>Remove</td></tr>\n");
		$res = mysql_query("SELECT * FROM email_bans ORDER BY added DESC $limit") or die(mysql_error());

		while ($arr = mysql_fetch_assoc($res)){
			$r2 = mysql_query("SELECT username FROM users WHERE id=$arr[userid]") or die(mysql_error());
			$a2 = mysql_fetch_assoc($r2);

			$r4 = mysql_query("SELECT username,id FROM users WHERE id=$arr[addedby]") or die(mysql_error());
			$a4 = mysql_fetch_assoc($r4);
			print("<tr><td class=table_col1>".utc_to_tz($arr['added'])."</td><td align=left class=table_col2>$arr[mail_domain]</td><td align=left class=table_col1><a href=account-details.php?id=$a4[id]>$a4[username]"."</a></td><td align=left class=table_col2>$arr[comment]</td><td class=table_col1><a href=admincp.php?action=emailbans&remove=$arr[id]>Remove</a></td></tr>\n");
		}

		print("</table>\n");

		echo $pagerbottom;
		echo "<br>";
	}
	end_frame();
	stdfoot();
}

if ($action=="polls" && $do=="view"){
	stdhead("Polls Management");
	navmenu();
	begin_frame("Polls Management");

	echo "<CENTER><a href=admincp.php?action=polls&do=add>Add New Poll</a></CENTER>";
	echo "<CENTER><a href=admincp.php?action=polls&do=results>View Poll Results</a></CENTER>";

	echo "<BR><BR><b>Polls</b> (Top poll is current)<BR>";

	$query = mysql_query("SELECT id,question,added FROM polls ORDER BY added DESC") or die(mysql_error());

	while($row = MYSQL_FETCH_ARRAY($query)){
		echo "<a href=admincp.php?action=polls&do=add&subact=edit&pollid=$row[id]>".stripslashes($row["question"])."</a> - ".utc_to_tz($row['added'])." - <a href=admincp.php?action=polls&do=delete&id=$row[id]>Delete</a><BR>\n\n";
	}

	end_frame();

	stdfoot();
}


/////////////
if ($action=="polls" && $do=="results"){
	stdhead("Polls");
	navmenu();
	begin_frame("Results");
	echo "<table class=\"table_table\" align=\"center\" cellpadding=\"0\" cellspacing=\"0\" width=\"95%\">";
	echo '<tr>';
	echo '<td class="table_head" align="left">Username</td>';
	echo '<td class="table_head" align="left">Question</td>';
	echo '<td class="table_head" align="left">Voted</td>';
	echo '</tr>';

	$poll = mysql_query("SELECT * FROM pollanswers ORDER BY pollid DESC");

	while ($res = mysql_fetch_assoc($poll)) {
		$user = mysql_fetch_assoc(mysql_query("SELECT username,id FROM users WHERE id = '".$res['userid']."'"));
		$option = "option".$res["selection"];
		if ($res["selection"] < 255) {
			$vote = mysql_fetch_assoc(mysql_query("SELECT ".$option." FROM polls WHERE id = '".$res['pollid']."'"));
		} else {
			$vote["option255"] = "Blank vote";
		}
		$sond = mysql_fetch_assoc(mysql_query("SELECT question FROM polls WHERE id = '".$res['pollid']."'"));
		
		echo '<tr>';
		echo '<td class="table_col1" align="left"><b>';
		echo '<a href=./account-details.php?id='.$user["id"].'>';
		echo '&nbsp;&nbsp;'.$user['username'];
		echo '</a>';
		echo '</b></td>';
		echo '<td class="table_col2" align="center">';
		echo '&nbsp;&nbsp;'.$sond['question'];
		echo '</td>';
		echo '<td class="table_col1" align="center">';
		echo $vote["$option"];
		echo '</td>';
		echo '</tr>';
	}

	echo '</table>';
	end_frame();
	stdfoot();
}


if ($action=="polls" && $do=="delete"){
	stdhead("Delete Poll");
	navmenu();

	$id = (int)$_GET["id"];
	
	if (!is_valid_id($id))
		show_error_msg("Error","Invalid news item ID",1);

	mysql_query("DELETE FROM polls WHERE id=$id") or die(mysql_error());
	mysql_query("DELETE FROM pollanswers WHERE  pollid=$id") or die(mysql_error());
	
	show_error_msg("Completed","Poll and answers deleted",1);
}

if ($action=="polls" && $do=="add"){
	stdhead("Polls");
	navmenu();

	$pollid = (int)$_GET["pollid"];

	if ($subact == "edit"){
		$res = mysql_query("SELECT * FROM polls WHERE id = $pollid");
		$poll = mysql_fetch_array($res);
	}

	begin_frame("Polls");
	?>
	<table border=0 cellspacing=0 cellpadding=3>
	<form method=post action=admincp.php?action=polls&do=save>
	<tr><td class=rowhead>Question <font color=red>*</font></td><td align=left><input name=question size=60 maxlength=255 value="<?=$poll['question']?>"></td></tr>
	<tr><td class=rowhead>Option 1 <font color=red>*</font></td><td align=left><input name=option0 size=60 maxlength=40 value="<?=$poll['option0']?>"><br></td></tr>
	<tr><td class=rowhead>Option 2 <font color=red>*</font></td><td align=left><input name=option1 size=60 maxlength=40 value="<?=$poll['option1']?>"><br></td></tr>
	<tr><td class=rowhead>Option 3</td><td align=left><input name=option2 size=60 maxlength=40 value="<?=$poll['option2']?>"><br></td></tr>
	<tr><td class=rowhead>Option 4</td><td align=left><input name=option3 size=60 maxlength=40 value="<?=$poll['option3']?>"><br></td></tr>
	<tr><td class=rowhead>Option 5</td><td align=left><input name=option4 size=60 maxlength=40 value="<?=$poll['option4']?>"><br></td></tr>
	<tr><td class=rowhead>Option 6</td><td align=left><input name=option5 size=60 maxlength=40 value="<?=$poll['option5']?>"><br></td></tr>
	<tr><td class=rowhead>Option 7</td><td align=left><input name=option6 size=60 maxlength=40 value="<?=$poll['option6']?>"><br></td></tr>
	<tr><td class=rowhead>Option 8</td><td align=left><input name=option7 size=60 maxlength=40 value="<?=$poll['option7']?>"><br></td></tr>
	<tr><td class=rowhead>Option 9</td><td align=left><input name=option8 size=60 maxlength=40 value="<?=$poll['option8']?>"><br></td></tr>
	<tr><td class=rowhead>Option 10</td><td align=left><input name=option9 size=60 maxlength=40 value="<?=$poll['option9']?>"><br></td></tr>
	<tr><td class=rowhead>Option 11</td><td align=left><input name=option10 size=60 maxlength=40 value="<?=$poll['option10']?>"><br></td></tr>
	<tr><td class=rowhead>Option 12</td><td align=left><input name=option11 size=60 maxlength=40 value="<?=$poll['option11']?>"><br></td></tr>
	<tr><td class=rowhead>Option 13</td><td align=left><input name=option12 size=60 maxlength=40 value="<?=$poll['option12']?>"><br></td></tr>
	<tr><td class=rowhead>Option 14</td><td align=left><input name=option13 size=60 maxlength=40 value="<?=$poll['option13']?>"><br></td></tr>
	<tr><td class=rowhead>Option 15</td><td align=left><input name=option14 size=60 maxlength=40 value="<?=$poll['option14']?>"><br></td></tr>
	<tr><td class=rowhead>Option 16</td><td align=left><input name=option15 size=60 maxlength=40 value="<?=$poll['option15']?>"><br></td></tr>
	<tr><td class=rowhead>Option 17</td><td align=left><input name=option16 size=60 maxlength=40 value="<?=$poll['option16']?>"><br></td></tr>
	<tr><td class=rowhead>Option 18</td><td align=left><input name=option17 size=60 maxlength=40 value="<?=$poll['option17']?>"><br></td></tr>
	<tr><td class=rowhead>Option 19</td><td align=left><input name=option18 size=60 maxlength=40 value="<?=$poll['option18']?>"><br></td></tr>
	<tr><td class=rowhead>Option 20</td><td align=left><input name=option19 size=60 maxlength=40 value="<?=$poll['option19']?>"><br></td></tr>
	<tr><td class=rowhead>Sort</td><td>
	<input type=radio name=sort value=yes <?=$poll["sort"] != "no" ? " checked" : "" ?>>Yes
	<input type=radio name=sort value=no <?=$poll["sort"] == "no" ? " checked" : "" ?>> No
	</td></tr>
	<tr><td colspan=2 align=center><input type=submit value=<?=$pollid?"'Edit poll'":"'Create poll'"?> style='height: 20pt'></td></tr>
	</table>
	<p><font color=red>*</font> required</p>
	<input type=hidden name=pollid value=<?=$poll["id"]?>>
	<input type=hidden name=subact value=<?=$pollid?'edit':'create'?>>
	</form>
	<?
	end_frame();
	stdfoot();
}

if ($action=="polls" && $do=="save"){

	$subact = $_POST["subact"];
	$pollid = (int)$_POST["pollid"];

	$question = $_POST["question"];
	$option0 = $_POST["option0"];
	$option1 = $_POST["option1"];
	$option2 = $_POST["option2"];
	$option3 = $_POST["option3"];
	$option4 = $_POST["option4"];
	$option5 = $_POST["option5"];
	$option6 = $_POST["option6"];
	$option7 = $_POST["option7"];
	$option8 = $_POST["option8"];
	$option9 = $_POST["option9"];
	$option10 = $_POST["option10"];
	$option11 = $_POST["option11"];
	$option12 = $_POST["option12"];
	$option13 = $_POST["option13"];
	$option14 = $_POST["option14"];
	$option15 = $_POST["option15"];
	$option16 = $_POST["option16"];
	$option17 = $_POST["option17"];
	$option18 = $_POST["option18"];
	$option19 = $_POST["option19"];
	$sort = (int)$_POST["sort"];

	if (!$question || !$option0 || !$option1)
		show_error_msg("Error", "Missing form data!");

	if ($subact == "edit"){

		if (!is_valid_id($pollid))
			show_error_msg("Error","Invalid ID.",1);

		mysql_query("UPDATE polls SET " .
		"question = " . sqlesc($question) . ", " .
		"option0 = " . sqlesc($option0) . ", " .
		"option1 = " . sqlesc($option1) . ", " .
		"option2 = " . sqlesc($option2) . ", " .
		"option3 = " . sqlesc($option3) . ", " .
		"option4 = " . sqlesc($option4) . ", " .
		"option5 = " . sqlesc($option5) . ", " .
		"option6 = " . sqlesc($option6) . ", " .
		"option7 = " . sqlesc($option7) . ", " .
		"option8 = " . sqlesc($option8) . ", " .
		"option9 = " . sqlesc($option9) . ", " .
		"option10 = " . sqlesc($option10) . ", " .
		"option11 = " . sqlesc($option11) . ", " .
		"option12 = " . sqlesc($option12) . ", " .
		"option13 = " . sqlesc($option13) . ", " .
		"option14 = " . sqlesc($option14) . ", " .
		"option15 = " . sqlesc($option15) . ", " .
		"option16 = " . sqlesc($option16) . ", " .
		"option17 = " . sqlesc($option17) . ", " .
		"option18 = " . sqlesc($option18) . ", " .
		"option19 = " . sqlesc($option19) . ", " .
		"sort = " . sqlesc($sort) . " " .
    "WHERE id = $pollid");
	}else{
  	mysql_query("INSERT INTO polls VALUES(0" .
		", '" . get_date_time() . "'" .
    ", " . sqlesc($question) .
    ", " . sqlesc($option0) .
    ", " . sqlesc($option1) .
    ", " . sqlesc($option2) .
    ", " . sqlesc($option3) .
    ", " . sqlesc($option4) .
    ", " . sqlesc($option5) .
    ", " . sqlesc($option6) .
    ", " . sqlesc($option7) .
    ", " . sqlesc($option8) .
    ", " . sqlesc($option9) .
 		", " . sqlesc($option10) .
		", " . sqlesc($option11) .
		", " . sqlesc($option12) .
		", " . sqlesc($option13) .
		", " . sqlesc($option14) .
		", " . sqlesc($option15) .
		", " . sqlesc($option16) .
		", " . sqlesc($option17) .
		", " . sqlesc($option18) .
		", " . sqlesc($option19) . 
    ", " . sqlesc($sort) .
  	")");
	}

	stdhead();
	navmenu();
	show_error_msg("OK","Poll Updates Complete",0);
	stdfoot();
	die;
}

if ($action=="backups"){
	stdhead("Backups");
	navmenu();
	begin_frame("Backups");
	echo "<a href=backup-database.php>Backup Database</a> (or create a CRON task on ".$site_config["SITEURL"]."/backup-database.php)";
	end_frame();
	stdfoot();
	die;
}

if ($action=="forceclean"){
	$now = gmtime();
	mysql_query("UPDATE tasks SET last_time=$now WHERE task='cleanup'");
	require_once("backend/cleanup.php");
	do_cleanup();
	show_error_msg("Complete","Force Clean Completed",1);
	die;
}

if ($action=="torrentlangs" && $do=="view"){
	stdhead("Torrent Languages");
	navmenu();
	begin_frame("Torrent Languages");
	echo "<CENTER><a href=admincp.php?action=torrentlangs&do=add><B>Add New Language</B></a></CENTER><br>";

	print("<i>Please that language image is optional</i><br><br>");

	echo("<center><table width=95% class=table_table>");
	echo("<td width=10 class=table_head><B>Sort</B></td><td class=table_head><B>Name</B></td><td class=table_head><B>Image</B></td><td width=30 class=table_head></td>");
	$query = "SELECT * FROM torrentlang ORDER BY sort_index ASC";
	$sql = mysql_query($query);
	while ($row = mysql_fetch_array($sql)) {
		$id = $row['id'];
		$name = $row['name'];
		$priority = $row['sort_index'];

		print("<tr><td class=table_col1>$priority</td><td class=table_col2>$name</td><td class=table_col1 align=center>");
		if (isset($row["image"]) && $row["image"] != "")
			print("<img border=\"0\"src=\"" . $site_config['SITEURL'] . "/images/languages/" . $row["image"] . "\" alt=\"" . $row["name"] . "\" />");
		else
			print("-");	
		print("</td><td class=table_col1><a href=admincp.php?action=torrentlangs&do=edit&id=$id>[EDIT]</a> <a href=admincp.php?action=torrentlangs&do=delete&id=$id>[DELETE]</a></td></tr>");
	}
	echo("</table></center>");
	end_frame();
	stdfoot();
	die;
}


if ($action=="torrentlangs" && $do=="edit"){
	stdhead("Torrent Language Management");
	navmenu();

	$id = (int)$_GET["id"];
	
	if (!is_valid_id($id))
		show_error_msg("Error","Invalid ID.",1);

	$res = mysql_query("SELECT * FROM torrentlang WHERE id=$id") or die(mysql_error());

	if (mysql_num_rows($res) != 1)
		show_error_msg("Error", "No Language with ID $id.",1);

	$arr = mysql_fetch_array($res);

	if ($_GET["save"] == '1'){
  	
		$name = $_POST['name'];
		if ($name == "")
			show_error_msg("Error", "Language cat cannot be empty!",1);

		$sort_index = $_POST['sort_index'];
		$image = $_POST['image'];

		$name = sqlesc($name);
		$sort_index = sqlesc($sort_index);
		$image = sqlesc($image);

		mysql_query("UPDATE torrentlang SET name=$name, sort_index=$sort_index, image=$image WHERE id=$id") or die(mysql_error());

		show_error_msg("Completed","Language was edited successfully.",0);

	} else {
		begin_frame("Edit Language");
		print("<form method=post action=?action=torrentlangs&do=edit&id=$id&save=1>\n");
		print("<CENTER><table border=0 cellspacing=0 cellpadding=5>\n");
		print("<tr><td align=left><B>Name: </B><input type=text name=name value=\"".$arr['name']."\"></td></tr>\n");
		print("<tr><td align=left><B>Sort: </B><input type=text name=sort_index value=\"".$arr['sort_index']."\"></td></tr>\n");
		print("<tr><td align=left><B>Image: </B><input type=text name=image value=\"".$arr['image']."\"> single filename</td></tr>\n");
		print("<tr><td align=center><input type=submit value='Submit' class=btn></td></tr>\n");
		print("</table></CENTER>\n");
		print("</form>\n");
	}
	end_frame();
	stdfoot();
}

if ($action=="torrentlangs" && $do=="delete"){
	stdhead("Torrent Language Management");
	navmenu();

	$id = (int)$_GET["id"];

	if ($_GET["sure"] == '1'){

		if (!is_valid_id($id))
			show_error_msg("Error","Invalid Language item ID",1);

		$newcatid = $_POST["newcat"];

		mysql_query("UPDATE torrents SET torrentlang=$newlangid WHERE torrentlang=$id") or die(mysql_error()); //move torrents to a new cat

		mysql_query("DELETE FROM torrentlang WHERE id=$id") or die(mysql_error()); //delete old cat
		
		show_error_msg("Completed","Language Deleted OK",1);

	}else{
		begin_frame("Delete Language");
		print("<form method=post action=?action=torrentlangs&do=delete&id=$id&sure=1>\n");
		print("<CENTER><table border=0 cellspacing=0 cellpadding=5>\n");
		print("<tr><td align=left><B>Language ID to move all Languages To: </B><input type=text name=newlangid> (Lang ID)</td></tr>\n");
		print("<tr><td align=center><input type=submit value='Submit' class=btn></td></tr>\n");
		print("</table></CENTER>\n");
		print("</form>\n");
	}
	end_frame();
	stdfoot();
}

if ($action=="torrentlangs" && $do=="takeadd"){
  		$name = $_POST['name'];
		if ($name == "")
    		show_error_msg("Error", "Name cannot be empty!",1);

		$sort_index = $_POST['sort_index'];
		$image = $_POST['image'];

		$name = sqlesc($name);
		$sort_index = sqlesc($sort_index);
		$image = sqlesc($image);

	mysql_query("INSERT INTO torrentlang (name, sort_index, image) VALUES ($name, $sort_index, $image)") or die(mysql_error());

	if (mysql_affected_rows() == 1)
		show_error_msg("Completed","Language was added successfully.",1);
	else
		show_error_msg("Error","Unable to add Language",1);
}

if ($action=="torrentlangs" && $do=="add"){
	stdhead("Torrent Language Management");
	navmenu();

	begin_frame("Add Language");
	print("<CENTER><form method=post action=admincp.php>\n");
	print("<input type=hidden name=action value=torrentlangs>\n");
	print("<input type=hidden name=do value=takeadd>\n");

	print("<table border=0 cellspacing=0 cellpadding=5>\n");

	print("<tr><td align=left><B>Name:</B> <input type=text name=name></td></tr>\n");
	print("<tr><td align=left><B>Sort:</B> <input type=text name=sort_index></td></tr>\n");
	print("<tr><td align=left><B>Image:</B> <input type=text name=image></td></tr>\n");

	print("<br><br><div align=center><input type=submit value='Submit' class=btn></div></td></tr>\n");

	print("</table></form><br><br></CENTER>\n");
	end_frame();
	stdfoot();
}

if ($action=="avatars"){
	stdhead("Avatar Log");
	navmenu();

	begin_frame("Avatar Log");

	$query = mysql_query("SELECT count(*) FROM users WHERE enabled='yes' AND avatar !=''");
	$count = mysql_fetch_row($query);
	$count = $count[0];

	list($pagertop, $pagerbottom, $limit) = pager(50, $count, 'admincp.php?action=avatars&');
	echo ($pagertop);
	?>
	<CENTER><TABLE class=table_table>
	<TR>
	<TD class=table_head><b>User</b></TD>
	<TD class=table_head><b><center>Avatar</center></b></TD>
	</TR><?

	$query = "SELECT username, id, avatar FROM users WHERE enabled='yes' AND avatar !='' $limit";
	$res = mysql_query($query);

	while($arr = mysql_fetch_array($res)){
			echo("<TR><TD class=table_col1><b><A href=\"account-details.php?id=" . $arr['id'] . "\">" . $arr['username'] . "</b></A></TD><TD class=table_col2>");

			if (!$arr['avatar'])
				echo "<img width=\"80\" src=images/default_avatar.gif></td>";
			else
				echo "<img width=\"80\" src=\"".htmlspecialchars($arr["avatar"])."\"></td>";
	}
	?>
	</TABLE></CENTER>
	<?
	echo ($pagerbottom);
	end_frame();
	stdfoot();
}

if ($action=="freetorrents"){
	stdhead("Free Leech Torrent Management");
	navmenu();

	$search = trim($search);

	if ($search != '' ){
		$whereand = "AND name LIKE " . sqlesc("%$search%") . "";
	}

	
	$res2 = mysql_query("SELECT COUNT(*) FROM torrents WHERE freeleech='1' $whereand");
	$row = mysql_fetch_array($res2);
	$count = $row[0];

	$perpage = 50;

	list($pagertop, $pagerbottom, $limit) = pager($perpage, $count, "admincp.php?action=freetorrents&");

	begin_frame("Free Leech Torrent Management");

	print("<CENTER><form method=get action=?>\n");
	print("<input type=hidden name=action value=torrentmanage>\n");
	print("" . SEARCH . ": <input type=text size=30 name=search>\n");
	print("<input type=submit value='Search'>\n");
	print("</form></CENTER>\n");

	echo $pagertop;
	?>
	<CENTER><table align=center cellpadding="0" cellspacing="0" class="table_table" width="100%" border="1">
	<tr>
	<td class=table_head align=center>Name</td>
	<td class=table_head align=center>Visible</td>
	<td class=table_head align=center>Banned</td>
	<td class=table_head align=center>Seeders</td>
	<td class=table_head align=center>Leechers</td>
	<td class=table_head align=center>Edit?</td>
	</tr>
	<?
	$rqq = "SELECT id, name, seeders, leechers, visible, banned FROM torrents WHERE freeleech='1' $whereand ORDER BY name $limit";
	$resqq = mysql_query($rqq);

	while ($row = mysql_fetch_array($resqq)){
		extract ($row);

		$char1 = 35; //cut name length 
		$smallname = CutName(htmlspecialchars($row["name"]), $char1);

		echo "<tr><td class=table_col1>" . $smallname . "</td><td class=table_col2>$row[visible]</td><td class=table_col1>$row[banned]</td><td class=table_col2>$row[seeders]</td><td class=table_col1>$row[leechers]</td><td class=table_col2><a href=\"torrents-edit.php?returnto=" . urlencode($_SERVER["REQUEST_URI"]) . "&amp;id=" . $row["id"] . "\"><font size=1 face=Verdana>EDIT</a></td></tr>\n";
	}

	echo "</table></CENTER>\n";

	print($pagerbottom);

	end_frame();
	stdfoot();
}

if ($action=="bannedtorrents"){
	stdhead("Banned Torrents");
	navmenu();

		
	$res2 = mysql_query("SELECT COUNT(*) FROM torrents WHERE banned='yes'");
	$row = mysql_fetch_array($res2);
	$count = $row[0];

	$perpage = 50;

	list($pagertop, $pagerbottom, $limit) = pager($perpage, $count, "admincp.php?action=bannedtorrents&");

	begin_frame("Banned Torrent Management");

	print("<CENTER><form method=get action=?>\n");
	print("<input type=hidden name=action value=bannedtorrents>\n");
	print("" . SEARCH . ": <input type=text size=30 name=search>\n");
	print("<input type=submit value='Search'>\n");
	print("</form></CENTER>\n");

	echo $pagertop;
	?>
	<CENTER><table align=center cellpadding="0" cellspacing="0" class="table_table" width="100%" border="1">
	<tr>
	<td class=table_head align=center>Name</td>
	<td class=table_head align=center>Visible</td>
	<td class=table_head align=center>Seeders</td>
	<td class=table_head align=center>Leechers</td>
	<td class=table_head align=center>External?</td>
	<td class=table_head align=center>Edit?</td>
	</tr>
	<?
	$rqq = "SELECT id, name, seeders, leechers, visible, banned, external FROM torrents WHERE banned='yes' ORDER BY name";
	$resqq = mysql_query($rqq);

	while ($row = mysql_fetch_array($resqq)){
		extract ($row);

		$char1 = 35; //cut name length 
		$smallname = CutName(htmlspecialchars($row["name"]), $char1);

		echo "<tr><td class=table_col1>" . $smallname . "</td><td class=table_col2>$row[visible]</td><td class=table_col1>$row[seeders]</td><td class=table_col2>$row[leechers]</td><td class=table_col1>$row[external]</td><td class=table_col2><a href=\"torrents-edit.php?returnto=" . urlencode($_SERVER["REQUEST_URI"]) . "&amp;id=" . $row["id"] . "\"><font size=1 face=Verdana>EDIT</a></td></tr>\n";
	}

	echo "</table></CENTER>\n";

	print($pagerbottom);

	end_frame();
	stdfoot();
}


if ($action=="masspm"){
	stdhead("Mass Private Message");
	navmenu();


	//send pm
	if ($_GET["send"] == '1'){

		$sender_id = ($_POST['sender'] == 'system' ? 0 : $CURUSER['id']);

		$dt = sqlesc(get_date_time());
		$msg = $_POST['msg'];

		if (!$msg)
			show_error_msg("Error","Please Enter Something!",1);

		$updateset = $_POST['clases'];

		$query = mysql_query("SELECT id FROM users WHERE class IN (".implode(",", $updateset).")");
		while($dat=mysql_fetch_assoc($query)){
			mysql_query("INSERT INTO messages (sender, receiver, added, msg) VALUES ($sender_id, $dat[id], '" . get_date_time() . "', " . sqlesc($msg) .")");
		}

		write_log("A Mass PM was sent by ($CURUSER[username])");
		show_error_msg("Complete", "Mass PM Sent",1);
		die;
	}

	begin_frame("Mass Private Message");
	print("<table border=0 cellspacing=0 cellpadding=5 align=center width=90%>\n");
	print("<form method=post action=admincp.php?action=masspm&send=1>\n");
	print("<B>Send to:</B><BR>\n");

	$query = "SELECT group_id, level FROM groups";
	$res = mysql_query($query);

	while ($row = mysql_fetch_array($res)){
		extract ($row);
	
		echo "<input type=checkbox name=clases[] value=$row[group_id]> $row[level]<BR>\n";
	}

	?>
	<BR><b>Message: </b><BR>
	<input type=hidden name=receiver value=<?=$receiver?>>
	<tr>
	<td><textarea name=msg cols=60 rows=10><?=$body?></textarea>
	<br>NOTE: Remember that BB can be used (NO HTML)</td>
	</tr>

	<tr>
	<td><b>Sender: </b>
	<?=$CURUSER['username']?> <input name="sender" type="radio" value="self" checked>
	System <input name="sender" type="radio" value="system"></td>
	</tr>

	<tr>
	<td><input type=submit value="Send" class=btn></td>
	</tr>
	</table></form>
	<?
	end_frame();
	stdfoot();
}

if ($action=="rules" && $do=="view"){
	stdhead("Site Rules Editor");
	navmenu();

	begin_frame("Site Rules Editor");

	$res = mysql_query("SELECT * FROM rules ORDER BY id");

	print("<CENTER><a href=admincp.php?action=rules&do=addsect>Add New Rules Section</a></CENTER><BR>\n");	

	while ($arr=mysql_fetch_assoc($res)){
		begin_frame($arr[title]);
		print("<form method=post action=admincp.php?action=rules&do=edit><table width=95% border=1 class=table_table>");
		print("<tr><td width=100%>");
		print(format_comment($arr["text"]));
		print("</td></tr><tr><td><input type=hidden value=$arr[id] name=id><input type=submit value='Edit'></td></tr></table></form>");
		end_frame();
	}
	end_frame();
	stdfoot();
}

if ($action=="rules" && $do=="edit"){

	if ($_GET["save"]=="1"){
		$id = (int)$_POST["id"];
		$title = sqlesc($_POST["title"]);
		$text = sqlesc($_POST["text"]);
		$public = sqlesc($_POST["public"]);
		$class = sqlesc($_POST["class"]);
		mysql_query("update rules set title=$title, text=$text, public=$public, class=$class where id=$id");
		write_log("Rules have been changed by ($CURUSER[username])");
		show_error_msg("Complete", "Rules edited ok<BR><BR><a href=admincp.php?action=rules&do=view>Back To Rules</a>",1);
		die;
	}


	stdhead("Site Rules Editor");
	navmenu();
	
	begin_frame("Edit Rule Section");
	$id = (int)$_POST["id"];
	$res = @mysql_fetch_array(@mysql_query("select * from rules where id='$id'"));

	print("<form method=\"post\" action=\"admincp.php?action=rules&do=edit&save=1\">");
	print("<table border=\"0\" cellspacing=\"0\" cellpadding=\"10\" align=\"center\">\n");
	print("<tr><td>Section Title:</td><td><input style=\"width: 400px;\" type=\"text\" name=\"title\" value=\"$res[title]\" /></td></tr>\n");
	print("<tr><td style=\"vertical-align: top;\">Rules:</td><td><textarea cols=60 rows=15 name=\"text\">" . stripslashes($res["text"]) . "</textarea><br>NOTE: Remember that BB can be used (NO HTML)</td></tr>\n");

	print("<tr><td colspan=\"2\" align=\"center\"><input type=\"radio\" name='public' value=\"yes\" ".($res["public"]=="yes"?"checked":"").">For everybody<input type=\"radio\" name='public' value=\"no\" ".($res["public"]=="no"?"checked":"").">Members Only (Min User Class: <input type=\"text\" name='class' value=\"$res[class]\" size=1>)</td></tr>\n");
	print("<tr><td colspan=\"2\" align=\"center\"><input type=hidden value=$res[id] name=id><input type=\"submit\" value=\"Save\" style=\"width: 60px;\"></td></tr>\n");
	print("</table>");
	end_frame();
	stdfoot();
}

if ($action=="rules" && $do=="addsect"){

	if ($_GET["save"]=="1"){
		$title = sqlesc($_POST["title"]);
		$text = sqlesc($_POST["text"]);
		$public = sqlesc($_POST["public"]);
		$class = sqlesc($_POST["class"]);
		mysql_query("insert into rules (title, text, public, class) values($title, $text, $public, $class)");
		show_error_msg("Complete", "New Section Added<BR><BR><a href=admincp.php?action=rules&do=view>Back To Rules</a>",1);
		die();
	}
	stdhead("Site Rules Editor");
	navmenu();
	begin_frame("Add New Rules Section");
	print("<form method=\"post\" action=\"admincp.php?action=rules&do=addsect&save=1\">");
	print("<table border=\"0\" cellspacing=\"0\" cellpadding=\"10\" align=\"center\">\n");
	print("<tr><td>Section Title:</td><td><input style=\"width: 400px;\" type=\"text\" name=\"title\"/></td></tr>\n");
	print("<tr><td style=\"vertical-align: top;\">Rules:</td><td><textarea cols=60 rows=15 name=\"text\"></textarea><br>\n");
	print("<br>NOTE: Remember that BB can be used (NO HTML)</td></tr>\n");

	print("<tr><td colspan=\"2\" align=\"center\"><input type=\"radio\" name='public' value=\"yes\" checked>For everybody<input type=\"radio\" name='public' value=\"no\">&nbsp;Members Only - (Min User Class: <input type=\"text\" name='class' value=\"0\" size=1>)</td></tr>\n");
	print("<tr><td colspan=\"2\" align=\"center\"><input type=\"submit\" value=\"Add\" style=\"width: 60px;\"></td></tr>\n");
	print("</table></form>");
	end_frame();
	stdfoot();
}


if ($action=="reports" && $do=="view"){
	stdhead("Reported Items");
	navmenu();

	begin_frame("Reported Items");
/*	$type = $_GET["type"];
	if ($type == "user")
	$where = " WHERE type = 'user'";
	else if ($type == "torrent")
	$where = " WHERE type = 'torrent'";
	else if ($type == "forum")
	$where = " WHERE type = 'forum'";
	else if ($type == "comment")
	$where = " WHERE type = 'comment'";
	else
	$where = "";*/

	$res = mysql_query("SELECT count(id) FROM reports WHERE complete='0'") or die(mysql_error());
	$row = mysql_fetch_array($res);

	$count = $row[0];
	$perpage = 25;
	list($pagertop, $pagerbottom, $limit) = pager($perpage, $count, $_SERVER["PHP_SELF"] . "?type=" . $_GET["type"] . "&" );

	echo "<BR><CENTER><B><a href=#>View Archived Reports</a></B></CENTER><BR>";

	echo $pagertop;

	print("<table border=1 cellspacing=0 cellpadding=1 align=center width=95% class=table_table>\n");
	print("<tr><td class=table_head align=center>By</td><td class=table_head align=center>Reported</td><td class=table_head align=center>Type</td><td class=table_head align=center>Reason</td><td class=table_head align=center>Dealt With</td>");
	print("</tr>");
	$res = mysql_query("SELECT reports.id, reports.dealtwith,reports.dealtby, reports.addedby, reports.votedfor,reports.votedfor_xtra, reports.reason, reports.type, users.username, reports.complete FROM reports INNER JOIN users on reports.addedby = users.id WHERE complete = '0' ORDER BY id desc $limit");

	while ($arr = mysql_fetch_assoc($res))
	{
	if ($arr[dealtwith])
	{
	$res3 = mysql_query("SELECT username FROM users WHERE id=$arr[dealtby]");
	$arr3 = mysql_fetch_assoc($res3);
	$dealtwith = "<font color=green><b>Yes - <a href=account-details.php?id=$arr[dealtby]><b>$arr3[username]</b></a></b></font>";
	}
	else
	$dealtwith = "<font color=red><b>No</b></font>";
	if ($arr[type] == "user")
	{
	$type = "account-details";
	$res2 = mysql_query("SELECT username FROM users WHERE id=$arr[votedfor]");
	$arr2 = mysql_fetch_assoc($res2);
	$name = $arr2[username];
	}
	else if  ($arr[type] == "forum")
	{
	$type = "forums";
	$res2 = mysql_query("SELECT subject FROM forum_topics WHERE id=$arr[votedfor]");
	$arr2 = mysql_fetch_assoc($res2);
	$subject = $arr2[subject];
	}
	else if  ($arr[type] == "comment")
	{
	$type = "comment";
	$res2 = mysql_query("SELECT text FROM comments WHERE id=$arr[votedfor]");
	$arr2 = mysql_fetch_assoc($res2);
	$subject = format_comment($arr2[text]);
	}
	else if ($arr[type] == "torrent")
	{
	$type = "torrents-details";
	$res2 = mysql_query("SELECT name FROM torrents WHERE id=$arr[votedfor]");
	$arr2 = mysql_fetch_assoc($res2);
	$name = $arr2[name];
	if ($name == "")
	 $name = "<b>[Deleted]</b>";
	}

	if ($arr[type] == "forum")
	  { print("<tr><td class=table_col1><a href=account-details.php?id=$arr[addedby]><b>$arr[username]</b></a></td><td align=left class=table_col2><a href=$type.php?action=viewtopic&topicid=$arr[votedfor]&page=p#$arr[votedfor_xtra]><b>$subject</b></a></td><td align=left class=table_col1>$arr[type]</td><td align=left class=table_col2>$arr[reason]</td><td align=left class=table_col1>$dealtwith</td></tr>\n");
	  }
	else {
	print("<tr><td class=table_col1><a href=account-details.php?id=$arr[addedby]><b>$arr[username]</b></a></td><td align=left class=table_col2><a href=$type.php?id=$arr[votedfor]><b>$name</b></a></td><td align=left class=table_col1>$arr[type]</td><td align=left class=table_col2>$arr[reason]</td><td align=left class=table_col1>$dealtwith</td>\n");
	print("</tr>");
	}}

	print("</table>\n");



	echo $pagerbottom;

	end_frame();
	stdfoot();
}

if ($action == "warned") {
	stdhead("Warned Users Management");
	navmenu();

	
	$res2 = mysql_query("SELECT COUNT(*) FROM users WHERE warned='yes'");
	$row = mysql_fetch_array($res2);
	$count = $row[0];

	$perpage = 50;

	list($pagertop, $pagerbottom, $limit) = pager($perpage, $count, "admincp.php?action=warned&");

	begin_frame("Warned Users Management");

	echo $pagertop;
	?>
	<CENTER><table align=center cellpadding="0" cellspacing="0" class="table_table" width="100%" border="1">
	<tr>
	<td class=table_head align=center>Username</td>
	<td class=table_head align=center>Added</td>
	<td class=table_head align=center>Last Visit</td>
	<td class=table_head align=center>Uploaded</td>
	<td class=table_head align=center>Downloaded</td>
	<td class=table_head align=center>Edit?</td>
	</tr>
	<?
	
	$rqq = "SELECT id, username, last_access, added, uploaded, downloaded FROM users WHERE warned='yes' ORDER BY username $limit";
	$resqq = mysql_query($rqq);

	while ($row = mysql_fetch_array($resqq)){
		extract ($row);

		echo "<tr><td class=table_col1><a href=account-details.php?id=$row[id]>$row[username]</a></td><td class=table_col2>".utc_to_tz($row['added'])."</td><td class=table_col1>$row[last_access]</td><td class=table_col2>".mksize($row["uploaded"])."</td><td class=table_col1>".mksize($row["downloaded"])."</td><td class=table_col2><a href=account-details.php?id=$row[id]>EDIT</a></td></tr>\n";
	}

	echo "</table></CENTER>\n";

	print($pagerbottom);

	end_frame();
	stdfoot();
}

#======================================================================#
#    Manual Conf Reg
#======================================================================#
if($action == "confirmreg")
{
stdhead("Manual Registration Confirm");
navmenu();
begin_frame("Info On This List", justify);
?>
<p align="justify">This page shows all users that have not clicked the ACTIVATION link in the signup email, they cannot access the site until they have clicked this link.  You should only manually confirm a user if they request it (via email, irc or other method), where they have lost or not received the email.  All PENDING users will be cleaned from the system every so often.</p>
<?
end_frame();
begin_frame("Manual Registration Confirm", center);
begin_table();
$perpage = 100;
print("<tr><td align=\"center\"  class=alt3 align=left><font size=1 face=Verdana>Username</td><td align=\"center\"  class=alt3><font size=1 face=Verdana>Email Address</td><td align=\"center\"  class=alt3><font size=1 face=Verdana>Date Registered</td><td align=\"center\"  class=alt3 align=left><font size=1 face=Verdana>IP</td><td align=\"center\"  class=alt3><font size=1 face=Verdana>Status</td></tr>\n");

$resww = "SELECT * FROM users WHERE status='pending' ORDER BY username";
$reqww = mysql_query($resww);
while ($row = mysql_fetch_array($reqww))
    {
     extract ($row);
  echo "<tr><td align='center'>$row[username]</td><td align='center'>$row[email]</td><td align='center'>$row[added]</td><td align='center'>$row[ip]</td><td align='center'><a href='admincp.php?action=editreg&id=$row[id]'>$row[status]</a></td></tr>\n";

    }
end_table();
end_frame();
stdfoot();
}

if($action == "save_editreg")
// SAVE THEME EDIT FUNCTION
    {
        mysql_query("UPDATE users SET status='$ed_status' WHERE id=$id");
show_error_msg("Updated", "<br><br><center><b>Updated Completed</b><BR><BR><a href='admincp.php?action=confirmreg'>Click here</a> to go back.</center>");
}

if($action == "editreg" && $id != "")
// EDIT USER REG FORM
{
    $qq = MYSQL_QUERY("SELECT * FROM users WHERE id = $id");
    $ee = MYSQL_FETCH_ARRAY($qq);
    stdhead();
    navmenu();
    begin_frame();
    ?>

    <form action='admincp.php' method='post'>
    <input type='hidden' name='id' value='<?=$id?>'>
    <input type='hidden' name='action' value='save_editreg'>
    Name: <?=$ee[username]?><br />
    Surrent Status: <?=$ee[status]?><br>
    <select name='ed_status'>
        <option value='pending' <? if($status == "pending") echo "selected"; ?>>pending
        <option value='confirmed' <? if($status == "confirmed") echo "selected"; ?>>confirmed
        </select>
    <!--<input type='text' value='<?=$ee[status]?>' size='30' maxlength='30' name='ed_status'><br />-->
    <input type='submit' value='   Save   ' style='background:#eeeeee'>&nbsp;&nbsp;&nbsp;<input type='reset' value='  Reset  ' style='background:#eeeeee'>
    </form>
    <?
        end_frame();
}

#======================================================================#
# Word Censor Filter
#======================================================================#
if($action == "censor") {
stdhead("Censor");
navmenu();
//Output
if ($_POST['submit'] == 'Add Censor'){
$query = "INSERT INTO censor (word, censor) VALUES ('" . $_POST['word'] . "','" . $_POST['censor'] . "');";
             mysql_query($query);
             }
if ($_POST['submit'] == 'Delete Censor'){
  $aquery = "DELETE FROM censor WHERE word = '" . $_POST['censor'] . "' LIMIT 1";
  mysql_query($aquery);
  }

begin_frame("Edit Censored Words", center);  
/*------------------
|HTML form for Word Censor
------------------*/
?>
<div align="center">
<table width='100%' cellspacing='3' cellpadding='3'>
<form id="Add Censor" name="Add Censor" method="POST" action="admincp.php?action=censor">
<tr>
<td bgcolor='#eeeeee'><font face="Verdana" size="1">Word:  <input type="text" name="word" id="word" size="50" maxlength="255" value=""></font></td></tr>
<tr><td bgcolor='#eeeeee'><font face="Verdana" size="1">Censor With:  <input type="text" name="censor" id="censor" size="50" maxlength="255" value=""></font></td></tr>
<tr><td bgcolor='#eeeeee' align='left'>
<font size="1" face="Verdana"><input type="submit" name="submit" value="Add Censor"></font></td>
</tr>
</form>

<form id="Delete Censor" name="Delete Censor" method="POST" action="./admincp.php?action=censor">
<tr>
<td bgcolor='#eeeeee'><font face="Verdana" size="1">Remove Censor For: <select name="censor">
<?
/*-------------
|Get the words currently censored
-------------*/
$select = "SELECT word FROM censor ORDER BY word";
$sres = mysql_query($select);
while ($srow = mysql_fetch_array($sres))
{
        echo "<option>" . $srow[0] . "</option>\n";
        }
echo'</select></font></td></tr><tr><td bgcolor="#eeeeee" align="left">
<font size="1" face="Verdana"><input type="submit" name="submit" value="Delete Censor"></font></td>
</tr></form></table><br>';
end_frame();
stdfoot();
}
// End forum Censored Words


// IP Bans (TorrentialStorm)
if ($action == "ipbans") {
    stdhead("Banned IPs");
    navmenu();

    if ($do == "del") {
        $delids = implode(", ", array_map("intval", $_POST["delids"]));
        $res = mysql_query("SELECT * FROM bans WHERE id IN ($delids)");
        while ($row = mysql_fetch_assoc($res)) {
            mysql_query("DELETE FROM bans WHERE id=$row[id]");
            write_log("IP Ban (".long2ip($row["first"])." - ".long2ip($row["last"]).") was removed by $CURUSER[id] ($CURUSER[username])");
        }
        show_error_msg("Success", "Ban(s) deleted.", 0);
    }

    if ($do == "add") {
        $first = trim($_POST["first"]);
        $last = trim($_POST["last"]);
        $comment = trim($_POST["comment"]);
        if ($first == "" || $last == "" || $comment == "")
            show_error_msg("Error", "Missing form data. Go back and try again", 1);
        $first = ip2long($first);
        $last = ip2long($last);
        if ($first <= 0 || $last <= 0)
            show_error_msg("Error", "Bad IP address.");
        $comment = sqlesc($comment);
        $added = sqlesc(get_date_time());
        mysql_query("INSERT INTO bans (added, addedby, first, last, comment) VALUES($added, $CURUSER[id], $first, $last, $comment)");
        switch (mysql_errno()) {
            case 1062:
                show_error_msg("Error", "Duplicate ban.", 0);
            break;
            case 0:
                show_error_msg("Success", "Ban added.", 0);
            break;
            default:
                show_error_msg("Error", "Database error: ".htmlspecialchars(mysql_error()), 0);
        }
    }

    begin_frame("Banned IPs", "center");
    echo "<p align=\"justify\">This page allows you to prevent individual users or groups of users from accessing your tracker by placing a block on their IP or IP range.<BR>
    If you wish to temporarily disable an account, but still wish a user to be able to view your tracker, you can use the 'Disable Account' option which is found in the user's profile page.</p><BR>";

    $count = get_row_count("bans");
    if ($count == 0)
    print("<b>No Bans Found</b><br />\n");
    else {
        list($pagertop, $pagerbottom, $limit) = pager(50, $count, "admincp.php?action=ipbans&"); // 50 per page
        echo $pagertop;

        echo "<form action='admincp.php?action=ipbans&do=del' method='POST'><table border=1 cellspacing=0 cellpadding=5 align=center class=ttable_headinner>
        <tr>
            <td class=ttable_head>".DATE_ADDED."</td>
            <td class=table_head align=left>First IP</td>
            <td class=ttable_head align=left>Last IP</td>
            <td class=ttable_head align=left>".ADDED_BY."</td>
            <td class=ttable_head align=left>Comment</td>
            <td class=ttable_head>Del?</td>
        </tr>";

        $res = mysql_query("SELECT bans.*, users.username FROM bans LEFT JOIN users ON bans.addedby=users.id ORDER BY added $limit");
        while ($arr = mysql_fetch_assoc($res)) {
            $arr["first"] = long2ip($arr["first"]);
            $arr["last"] = long2ip($arr["last"]);
            echo "<tr>
                <td align=center class=ttable_col1>".date('d/m/Y<\B\R>H:i:s', utc_to_tz_time($arr["added"]))."</td>
                <td align=center class=ttable_col2>$arr[first]</td>
                <td align=center class=ttable_col1>$arr[last]</td>
                <td align=center class=ttable_col2><a href='account-details.php?id=$arr[addedby]'>$arr[username]</a></td>
                <td align=center class=ttable_col1>$arr[comment]</td>
                <td align=center class=ttable_col2><input type='checkbox' name='delids[]' value='$arr[id]'></td>
            </tr>";
        }
        echo "</table><BR><input type='submit' value='Delete Checked'>&nbsp;<input type='button' onclick='this.value=check(form)' value='Check All'></form>";
        echo $pagerbottom;
    }

    echo "<BR><BR>";
    begin_frame("Add Ban", "center");
    print("<table border=1 cellspacing=0 cellpadding=5>\n");
    print("<form method=post action=admincp.php?action=ipbans&do=add>\n");
    print("<tr><td class=rowhead>First IP</td><td><input type=text name=first size=40></td>\n");
    print("<tr><td class=rowhead>Last IP</td><td><input type=text name=last size=40></td>\n");
    print("<tr><td class=rowhead>Comment</td><td><input type=text name=comment size=40></td>\n");
    print("<tr><td colspan=2><input type=submit value='Okay' class=btn></td></tr>\n");
    print("</form>\n</table>\n");
    end_frame();

    end_frame();
    stdfoot();
}
// End IP Bans (TorrentialStorm)

?>
