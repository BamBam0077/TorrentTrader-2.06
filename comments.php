<?
//
//  TorrentTrader v2.x
//	This file was last updated: 05/December/2007
//	
//	http://www.torrenttrader.org
//
//
require_once("backend/functions.php");
require_once("backend/BDecode.php") ;
require_once("backend/bbcode.php");
dbconn();


$id = (int)$_GET["id"];
$type = $_GET["type"];
$edit = (int)$_GET["edit"];
$delete = (int)$_GET["delete"];

if ($edit == 1 || $delete == 1 || $_GET["takecomment"] == 'yes') loggedinonly();


if (!isset($id) || !$id || ($type != "torrent" && $type != "news"))
	show_error_msg("ERROR","Error",1);

if ($edit=='1'){
	$row = mysql_fetch_assoc(mysql_query("SELECT user FROM comments WHERE id=$id"));

	if(($CURUSER["edit_torrents"]=="no" || $CURUSER["edit_forum"]=="no") && $CURUSER['id'] != $row['user'])
		show_error_msg("ERROR","You cant do this!",1);

		$save = (int)$_GET["save"];

		if($save){
			$text = sqlesc($_POST['text']);

			$query="UPDATE comments SET text=$text WHERE id=$id";
			$result=mysql_query($query);
			write_log($CURUSER['username']." has edited comment: ID:$id");
			show_error_msg("Complete","Comment Edited OK",1);
		}

		stdhead("Edit Comment");

		$res = mysql_query("SELECT * FROM comments WHERE id=$id");
		$arr = mysql_fetch_array($res);

		begin_frame("Edit Comment");
		print("<center><b>Edit comment </b><p>\n");
		print("<form method=\"post\" name=\"comment\" action=\"comments.php?type=torrent&edit=1&save=1&id=$id\">\n");
		print ("".textbbcode("comment","text","" . htmlspecialchars($arr["text"]) . "")."");
		print("<p><input type=\"submit\" class=btn value=\"Submit Changes\" /></p></form></center>\n");
		end_frame();
		stdfoot();
		die();
}

if ($delete=='1'){
	if($CURUSER["delete_torrents"]=="no" || $CURUSER["delete_forum"]=="no")
		show_error_msg("ERROR","You cant do this!",1);

	if ($type == "torrent") {
		$res = mysql_query("SELECT torrent FROM comments WHERE id=$id");
		$row = mysql_fetch_assoc($res);
		if ($row["torrent"] > 0) {
			mysql_query("UPDATE torrents SET comments = comments - 1 WHERE id = $row[torrent]") or die(mysql_error());
		}
	}

	mysql_query("DELETE FROM comments WHERE id = $id");
	write_log($CURUSER['username']." has deleted comment: ID: $id");
	show_error_msg("Complete","Comment deleted OK",1);
}


stdhead("" . COMMENTS . "");


//take comment add
if ($_GET["takecomment"] == 'yes'){
	$commentbody = $_POST['body'];
	
	if (!$commentbody)
		show_error_msg("Error","You did not enter anything!",1);

	if ($type =="torrent"){
		mysql_query("UPDATE torrents SET comments = comments + 1 WHERE id = $id") or die(mysql_error());
	}

	mysql_query("INSERT INTO comments (user, ".$type.", added, text) VALUES (".$CURUSER["id"].", ".$id.", '" .get_date_time(). "', " . sqlesc($body).")") or die(mysql_error());

	if (mysql_affected_rows() == 1)
			show_error_msg("Completed","Your Comment was added successfully.",0);
		else
			show_error_msg("Error","Unable to add comment",0);
}//end insert comment

//NEWS
if ($type =="news"){
	$res = mysql_query("SELECT * FROM news WHERE id = $id");
	$row = mysql_fetch_array($res);

	if (!$row){
		show_error_msg("Error","News id invalid",0);
		stdfoot();
	}

	begin_frame("News");
	echo "".$row['title']."<BR><BR>".format_comment($row['body'])."<BR>";
	end_frame();
	
}

//TORRENT
if ($type =="torrent"){
	$res = mysql_query("SELECT id, name FROM torrents WHERE id = $id");
	$row = mysql_fetch_array($res);

	if (!$row){
		show_error_msg("Error","News id invalid",0);
		stdfoot();
	}

	echo "<CENTER><b>Comments for:</b> <a href=torrents-details.php?id=".$row['id'].">".htmlspecialchars($row['name'])."</a></CENTER><BR>";
	
}

begin_frame("" . COMMENTS . "");
	
	$subres = mysql_query("SELECT COUNT(*) FROM comments WHERE $type = $id") or die(mysql_error());
	$subrow = mysql_fetch_array($subres);
	$commcount = $subrow[0];

	if ($commcount) {
		list($pagertop, $pagerbottom, $limit) = pager(10, $commcount, "comments.php?id=$id&type=$type&");
		$commquery = "SELECT comments.id, text, user, comments.added, avatar, signature, username, title, class, uploaded, downloaded, privacy, donated FROM comments LEFT JOIN users ON comments.user = users.id WHERE $type = $id ORDER BY comments.id $limit";
		$commres = mysql_query($commquery) or die(mysql_error());
	}else{
		unset($commres);
	}

	if ($commcount) {
		print($pagertop);
		commenttable($commres);
		print($pagerbottom);
	}else {
		print("<BR><b><CENTER>" . NOCOMMENTS . "</CENTER></b><BR>\n");
	}

	echo "<CENTER>";
	echo "<form name=\"comment\" method=\"post\" action=\"comments.php?type=$type&id=$id&takecomment=yes\">";
	echo "".textbbcode("comment","body")."<br>";
	echo "<input type=\"submit\" class=btn value=\"Add Comment\" />";
	echo "</form></CENTER>";

	end_frame();

stdfoot();
?>