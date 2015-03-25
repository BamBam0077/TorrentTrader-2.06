<?
//
//  TorrentTrader v2.x
//	This file was last updated: 20/July/2007
//	
//	http://www.torrenttrader.org
//
//
require_once("backend/functions.php");
dbconn(true);

stdhead("Home");

//check
if (file_exists("check.php") && $CURUSER["class"] == 7){
	show_error_msg("WARNING", "Check.php still exists, please delete or rename the file as it could pose a security risk<BR><BR><a href=check.php>View Check.php</a> - Use to check your config!<BR><BR>",0);
}

//Site Notice
if ($site_config['SITENOTICEON']){
	begin_frame("" . NOTICE . "");
	echo stripslashes($site_config['SITENOTICE']);
	end_frame();
}

//Site News
if ($site_config['NEWSON']){
	begin_frame("News");
	$res = mysql_query("SELECT * FROM news WHERE ADDDATE(added, INTERVAL 45 DAY) > '".get_date_time()."' ORDER BY added DESC LIMIT 10") or die(mysql_error());
	if (mysql_num_rows($res) > 0){
		print("<table width=100% border=0 cellspacing=0 cellpadding=0><tr><td>\n<ul>");
		$news_flag = 0;

		while($array = mysql_fetch_array($res)){
			$user = mysql_fetch_assoc(mysql_query("SELECT username FROM users WHERE id = $array[userid]")) or die(mysql_error());

			$numcomm = number_format(get_row_count("comments", "WHERE news='".$array['id']."'"));

			if ($news_flag < 2) { //show first 2 items expanded

				print("<BR><a href=\"javascript: klappe_news('a".$array['id']."')\"><img border=\"0\" src=\"".$site_config["SITEURL"]."/images/minus.gif\" id=\"pica".$array['id']."\" alt=\"Show/Hide\">");
				print("&nbsp;<b>". $array['title'] . "</b></a> - <B>Posted:</B> " . date("d-M-y", utc_to_tz_time($array['added'])) . " <B>By:</B> $user[username]");
				
				print("<div id=\"ka".$array['id']."\" style=\"display: block;\"> ".format_comment($array["body"],0)." <BR><BR>Comments (<a href=comments.php?type=news&id=".$array['id'].">".$numcomm."</a>)</div><br> ");

				$news_flag = ($news_flag + 1);

			}else{

				print("<BR><a href=\"javascript: klappe_news('a".$array['id']."')\"><img border=\"0\" src=\"".$site_config["SITEURL"]."/images/plus.gif\" id=\"pica".$array['id']."\" alt=\"Show/Hide\">");
				print("&nbsp;<b>". $array['title'] . "</b></a> - <B>Posted:</B> " . date("d-M-y",utc_to_tz_time($array['added'])) . " <B>By:</B> $user[username]");
				
				print("<div id=\"ka".$array['id']."\" style=\"display: none;\"> ".format_comment($array["body"],0)." <BR><BR>Comments (<a href=comments.php?type=news&id=".$array['id'].">".number_format($numcomm)."</a>)</div><br> ");
			}
		}
		print("</ul></td></tr></table>\n");
	}else{
		echo "<BR><b>No news currently at this time</b>";
	}
	end_frame();
}



if ($site_config['SHOUTBOX']){
	begin_frame("Shoutbox");
	echo '<IFRAME name="shout_frame" src="'.$site_config["SITEURL"].'/shoutbox.php" frameborder="0" marginheight="0" marginwidth="0" width="99%" height="210" width=350 scrolling="no" align="middle"></IFRAME>';
	echo "(Shoutbox will auto-refresh every 5 minutes)<BR>";
	end_frame();
}

// latest torrents
begin_frame("Latest Torrents");

print("<BR><CENTER><a href=torrents.php>".BROWSE_TORRENTS."</a> - <a href=torrents-search.php>".SEARCH_TITLE."</a></CENTER><BR>");

if ($site_config["MEMBERSONLY"] && !$CURUSER) {
	echo "<BR><BR><b><CENTER>You Are Not Logged In<br>Only Members Can View Torrents Please Login or Signup.</CENTER><BR><BR>";
} else {
	$query = "SELECT torrents.id, torrents.anon, torrents.announce, torrents.category, torrents.leechers, torrents.nfo, torrents.seeders, torrents.name, torrents.times_completed, torrents.size, torrents.added, torrents.comments, torrents.numfiles, torrents.filename, torrents.owner, torrents.external, torrents.freeleech, categories.name AS cat_name, categories.image AS cat_pic, categories.parent_cat AS cat_parent, users.username, users.privacy, IF(torrents.numratings < 2, NULL, ROUND(torrents.ratingsum / torrents.numratings, 1)) AS rating FROM torrents LEFT JOIN categories ON category = categories.id LEFT JOIN users ON torrents.owner = users.id WHERE visible = 'yes' AND banned = 'no' ORDER BY id DESC LIMIT 25";
	$res = mysql_query($query) or die(mysql_error());
	if (mysql_num_rows($res)) {
		torrenttable($res);
	}else {
		show_error_msg("" . NOTHING_FOUND . "", "" . NO_UPLOADS . "",0);
	}
	if ($CURUSER)
		mysql_query("UPDATE users SET last_browse=".gmtime()." WHERE id=$CURUSER[id]");

}
end_frame();


if ($site_config['DISCLAIMERON']){
	begin_frame("" . DISCLAIMER . "");
	echo stripslashes($site_config['DISCLAIMERTXT']);
	end_frame();
}


stdfoot();
?>