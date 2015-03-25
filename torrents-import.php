<?
//
//  TorrentTrader v2.x
//	Mass Torrent Import (based on torrents-upload.php)
//	This file was created: 27/Feb/2008 by TorrentialStorm
//	
//	http://www.torrenttrader.org
//
//
require_once("backend/functions.php");
dbconn();

$dir = "import";

//ini_set("upload_max_filesize",$max_torrent_size);

$files = array();
$dh = opendir("$dir/");
while (false !== ($file=readdir($dh))) {
	if (preg_match("/\.torrent$/i", $file))
		$files[] = $file;
}
closedir($dh);


// check access and rights
if ($CURUSER["edit_torrents"] != "yes")
	show_error_msg("Error", "Access Denied", 1);

$announce_urls = explode(",", strtolower($site_config["announce_list"]));  //generate announce_urls[] from config.php

if ($takeupload == "yes") {
	set_time_limit(0);
	require_once("backend/parse.php");
	stdhead("Upload Complete");
	begin_frame("Upload Complete");
	echo "<center>";

	//check form data
	$catid = (int)$_POST["type"];

	if (!is_valid_id($catid))
		$message = "Please be sure to select a torrent category";
	
	if (empty($message)) {
		$r = mysql_query("SELECT name, parent_cat FROM categories WHERE id=$catid");
		$r = mysql_fetch_row($r);

		echo "<B>Category:</B> ".htmlspecialchars($r[1])." -> ".htmlspecialchars($r[0])."<BR>";
		for ($i=0;$i<count($files);$i++) {
			$fname = $files[$i];

			$descr = "No description given.";

			$langid = (int)$_POST["lang"];
	
			preg_match('/^(.+)\.torrent$/si', $fname, $matches);
			$shortfname = $torrent = $matches[1];

			//parse torrent file
			$torrent_dir = $site_config["torrent_dir"];	

			$TorrentInfo = array();
			$TorrentInfo = ParseTorrent("$dir/$fname");


			$announce = strtolower($TorrentInfo[0]);
			$infohash = $TorrentInfo[1];
			$creationdate = $TorrentInfo[2];
			$internalname = $TorrentInfo[3];
			$torrentsize = $TorrentInfo[4];
			$filecount = $TorrentInfo[5];
			$annlist = $TorrentInfo[6];
			$comment = $TorrentInfo[7];
			
			$message = "<BR><BR><HR><BR><B>$internalname</B><BR><BR>fname: ".htmlspecialchars($fname)."<BR>message: ";

			//check announce url is local or external
			if (!in_array($announce, $announce_urls, 1))
				$external='yes';
			else
				$external='no';

			if (!$site_config["ALLOWEXTERNAL"] && $external == 'yes') {
				$message .= "The .torrent you are trying to upload does not have this trackers announce url!";
				echo $message;
				continue;
			}

			$name = $internalname;
			$name = str_replace(".torrent","",$name);
			$name = str_replace("_", " ", $name);

			//anonymous upload
			$anonyupload = unesc($_POST["anonycheck"]); 
			if ($anonyupload == "yes")
				$anon = "yes";
			else
				$anon = "no";

			$ret = mysql_query("INSERT INTO torrents (filename, owner, name, descr, image1, image2, category, added, info_hash, size, numfiles, save_as, announce, external, nfo, torrentlang, anon) VALUES (".sqlesc($fname).", '".$CURUSER['id']."', ".sqlesc($name).", ".sqlesc($descr).", '".$inames[0]."', '".$inames[1]."', '".$type."', '" . get_date_time() . "', '".$infohash."', '".$torrentsize."', '".$filecount."', ".sqlesc($fname).", '".$announce."', '".$external."', '".$nfo."', '".$langid."','$anon')");

			$id = mysql_insert_id();
	
			if (mysql_errno() == 1062) {
				$message .= "Torrent already uploaded.";
				echo $message;
				continue;
			}

			if($id == 0){
				$message .= "No ID. Server error, please report.";
				echo $message;
				continue;
			}
    
			copy("$dir/$files[$i]", "uploads/$id.torrent");

			//EXTERNAL SCRAPE
			if ($external == "yes") {
				$tracker=str_replace("/announce","/scrape",$announce);	
				$stats 			= torrent_scrape_url($tracker, $infohash);
				$seeders 		= strip_tags($stats['seeds']);
				$leechers 		= strip_tags($stats['peers']);
				$downloaded 	= strip_tags($stats['downloaded']);

				mysql_query("UPDATE torrents SET leechers='".$leechers."', seeders='".$seeders."',times_completed='".$downloaded."',last_action= '".get_date_time()."',visible='yes' WHERE id='".$id."'"); 
			}
			//END SCRAPE

			write_log("Torrent $id ($name) was Uploaded by $CURUSER[username]");

			$message .= "<BR><B>Torrent Uploaded OK</B><BR><a href=torrents-details.php?id=".$id.">View Uploaded Torrent</a><BR><BR>";
			echo $message;
			@unlink("$dir/$fname");
		}
	echo "</center>";
	end_frame();
	stdfoot();
	die;
	}else
		show_error_msg("Upload Failed", $message, 1);

}//takeupload


///////////////////// FORMAT PAGE ////////////////////////

stdhead("Upload");

begin_frame("" . UPLOAD . "");
?>
<form name="upload" enctype="multipart/form-data" action="torrents-import.php" method="post">
<input type="hidden" name="takeupload" value="yes" />
<table border="0" cellspacing="0" cellpadding="6" align="center">
<TR><TD align=right valign=top><B>File List:</B></TD><TD align=left><?
if (!count($files))
	echo "Nothing to show.<BR>Place files to import in $dir/.";
else{
	foreach ($files as $f)
		echo htmlspecialchars($f)."<BR>";
	echo "<BR>Total files: ".count($files);
}?></TD></TR>
<?
$category = "<select name=\"type\">\n<option value=\"0\">" . CHOOSE_ONE . "</option>\n";

$cats = genrelist();
foreach ($cats as $row)
	$category .= "<option value=\"" . $row["id"] . "\">" . htmlspecialchars($row["parent_cat"]) . ": " . htmlspecialchars($row["name"]) . "</option>\n";

$category .= "</select>\n";
print ("<TR><TD align=right>" . TTYPE . ": </td><td align=left>".$category."</td></tr>");


$language = "<select name=\"lang\">\n<option value=\"0\">Unknown/NA</option>\n";

$langs = langlist();
foreach ($langs as $row)
	$language .= "<option value=\"" . $row["id"] . "\">" . htmlspecialchars($row["name"]) . "</option>\n";

$language .= "</select>\n";
print ("<TR><TD align=right>Language: </td><td align=left>".$language."</td></tr>");

if ($site_config['ANONYMOUSUPLOAD']){ ?>
	<TR><TD align=right>Upload Anonymous: </td><td><? printf("<input name=anonycheck value=yes type=radio" . ($anonycheck ? " checked" : "") . ">Yes <input name=anonycheck value=no type=radio" . (!$anonycheck ? " checked" : "") . ">No"); ?> &nbsp;<I>(Your userid will not be associated to this upload)</font>
	</td></tr>

<?}?>
<TR><TD align=center colspan=2><input type="submit" value="<?=UPLOADT?>"><BR>
<I>Click Once!</I></form></TD></TR></TABLE>
<?
end_frame();
stdfoot();
?>