<?
//
//  TorrentTrader v2.x
//	This file was last updated: 09/October/2007 by TorrentialStorm
//	
//	http://www.torrenttrader.org
//
//
require_once("backend/functions.php");
dbconn();

//ini_set("upload_max_filesize",$max_torrent_size);


// check access and rights
if ($site_config["MEMBERSONLY"]){
	loggedinonly();

	if($CURUSER["can_upload"]=="no")
		show_error_msg("Error","You do not have permission to upload",1);
	if ($site_config["UPLOADERSONLY"] && $CURUSER["class"] < 4)
		show_error_msg("Error", "Only uploaders can upload.",1);
}

$announce_urls = explode(",", strtolower($site_config["announce_list"]));  //generate announce_urls[] from config.php

if($takeupload == "yes") {
	require_once("backend/parse.php");

	//check form data
	foreach(explode(":","type:name") as $v) {
		if (!isset($_POST[$v]))
			$message = "Missing form data";
	}

	if (!isset($_FILES["torrent"]))
	$message = "Missing form data";

	$f = $_FILES["torrent"];
	$fname = unesc($f["name"]);

	if (empty($fname))
		$message = "Empty filename!";

	if ($_FILES['nfo']['size'] != 0) {
		$nfofile = $_FILES['nfo'];

		if ($nfofile['name'] == '')
			$message = "No NFO!";
			
		if (!preg_match('/^(.+)\.nfo$/si', $nfofile['name'], $fmatches))
			$message = "Invalid filename (not a .NFO).";

		if ($nfofile['size'] == 0)
			$message = "0-byte NFO";

		if ($nfofile['size'] > 65535)
			$message = "NFO is too big! Max 65,535 bytes.";

		$nfofilename = $nfofile['tmp_name'];

		if (@!is_uploaded_file($nfofilename))
			$message = "NFO upload failed";
			$nfo = 'yes';
	}

	$descr = unesc($_POST["descr"]);

	if (!$descr)
		$descr = "No description given.";

	$langid = (0 + $_POST["lang"]);
	
	/*if (!is_valid_id($langid))
		$message = "Please be sure to select a torrent language";*/

	$catid = (0 + $_POST["type"]);

	if (!is_valid_id($catid))
		$message = "Please be sure to select a torrent category";

	if (!validfilename($fname))
		$message = "Invalid filename!";

	if (!preg_match('/^(.+)\.torrent$/si', $fname, $matches))
		$message = "Invalid filename (not a .torrent).";

		$shortfname = $torrent = $matches[1];

	if (!empty($_POST["name"]))
		$torrent = unesc($_POST["name"]);

		$tmpname = $f["tmp_name"];

	if (!is_uploaded_file($tmpname))
		$message = "The file was uploaded, but wasn't found on the temp directoy.";
	//end check form data

	if (!$message) {
	//parse torrent file
	$torrent_dir = $site_config["torrent_dir"];	
	$nfo_dir = $site_config["nfo_dir"];	

	//if(!copy($f, "$torrent_dir/$fname"))
	if(!move_uploaded_file($tmpname, "$torrent_dir/$fname"))
		show_error_msg("Error:","Error: File Could not be copied $tmpname - $torrent_dir - $fname",1);

    $TorrentInfo = array();
    $TorrentInfo = ParseTorrent("$torrent_dir/$fname");


    $announce = strtolower($TorrentInfo[0]);
	$infohash = $TorrentInfo[1];
	$creationdate = $TorrentInfo[2];
	$internalname = $TorrentInfo[3];
	$torrentsize = $TorrentInfo[4];
	$filecount = $TorrentInfo[5];
	$annlist = $TorrentInfo[6];
	$comment = $TorrentInfo[7];

/*
//for debug...
	print ("<BR><BR>announce: ".$announce."");
	print ("<BR><BR>infohash: ".$infohash."");
	print ("<BR><BR>creationdate: ".$creationdate."");
	print ("<BR><BR>internalname: ".$internalname."");
	print ("<BR><BR>torrentsize: ".$torrentsize."");
	print ("<BR><BR>filecount: ".$filecount."");
	print ("<BR><BR>annlist: ".$annlist."");
	print ("<BR><BR>comment: ".$comment."");
*/
	
	//check announce url is local or external
	if (!in_array($announce, $announce_urls, 1)){
		$external='yes';
    }else{
		$external='no';
	}

	//if externals is turned off
	if (!$site_config["ALLOWEXTERNAL"] && $external == 'yes')
		$message = "The .torrent you are trying to upload does not have this trackers announce url!";
	}
	if ($message) {
		@unlink("$torrent_dir/$fname");
		@unlink($tmpname);
		@unlink("$nfo_dir/$nfofilename");
		show_error_msg("Upload Failed", $message,1);
	}

	//release name check and adjust
	if ($name ==""){
		$name = $internalname;
	}
	$name = str_replace(".torrent","",$name);
	$name = str_replace("_", " ", $name);

	//upload images
	$maxfilesize = 512000; // 500kb

	$allowed_types = array(
		"image/gif" => "gif",
		"image/pjpeg" => "jpg",
		"image/jpeg" => "jpg",
		"image/jpg" => "jpg",
		"image/png" => "png"
	);

	for ($x=0; $x < 2; $x++) {
		if (!($_FILES[image.$x]['name'] == "")) {
			$y = $x + 1;

			if (!array_key_exists($_FILES[image.$x]['type'], $allowed_types))
				show_error_msg("Error","Invalid file type! Image $y",1);
			
			if (!preg_match('/^(.+)\.(jpg|gif|png)$/si', $_FILES[image.$x]['name']))
				show_error_msg("Invalid image", "This file TYPE is not image!",1);

			if ($_FILES[image.$x]['size'] > $maxfilesize)
				show_error_msg("Error","Invalid file size! Image $y - Must be less than 500kb",1);

			$uploaddir = "".$site_config["torrent_dir"]."/images/";
   
			$ifile = $_FILES[image.$x]['tmp_name'];
   
			$ret = mysql_query("SHOW TABLE STATUS LIKE 'torrents'");
			$row = mysql_fetch_array($ret);
			$next_id = $row['Auto_increment'];

			$ifilename = $next_id . $x . substr($_FILES[image.$x]['name'], strlen($_FILES[image.$x]['name'])-4, 4);

			$copy = copy($ifile, $uploaddir.$ifilename);

			if (!$copy)
				show_error_msg("Error","Error occured uploading image! - Image $y",1);

			$inames[] = $ifilename;

		}

	}
	//end upload images

	//anonymous upload
	$anonyupload = unesc($_POST["anonycheck"]); 
	if ($anonyupload == "yes") {
		$anon = "yes";
	}else{
		$anon = "no";
	}

	$ret = mysql_query("INSERT INTO torrents (filename, owner, name, descr, image1, image2, category, added, info_hash, size, numfiles, save_as, announce, external, nfo, torrentlang, anon) VALUES (".sqlesc($fname).", '".$CURUSER['id']."', ".sqlesc($name).", ".sqlesc($descr).", '".$inames[0]."', '".$inames[1]."', '".$type."', '" . get_date_time() . "', '".$infohash."', '".$torrentsize."', '".$filecount."', ".sqlesc($fname).", '".$announce."', '".$external."', '".$nfo."', '".$langid."','$anon')");

	$id = mysql_insert_id();
	
	if (mysql_errno() == 1062)
		show_error_msg("Upload Failed", "Torrent already uploaded.", 1);

	//Update the members uploaded torrent count
	/*if ($ret){
		mysql_query("UPDATE users SET torrents = torrents + 1 WHERE id = $userid");*/
        
	if($id == 0){
		unlink("$torrent_dir/$fname");
		$message = "No ID. Server error, please report.";
		show_error_msg("Upload Failed", $message,1);
	}
    
    rename("$torrent_dir/$fname", "$torrent_dir/$id.torrent"); 

	if ($nfo == 'yes') { 
            move_uploaded_file($nfofilename, "$nfo_dir/$id.nfo"); 
    } 

	//EXTERNAL SCRAPE
	if ($external=='yes'){
		$tracker=str_replace("/announce","/scrape",$announce);	
		$stats 			= torrent_scrape_url($tracker, $infohash);
		$seeders 		= strip_tags($stats['seeds']);
		$leechers 		= strip_tags($stats['peers']);
		$downloaded 	= strip_tags($stats['downloaded']);

		mysql_query("UPDATE torrents SET leechers='".$leechers."', seeders='".$seeders."',times_completed='".$downloaded."',last_action= '".get_date_time()."',visible='yes' WHERE id='".$id."'"); 
	}
	//END SCRAPE

	write_log("Torrent $id ($name) was Uploaded by $CURUSER[username]");

	//insert email notif, irc, req notif, etc here
	
	//Uploaded ok message (update later)
	if ($external=='no')
		$message = "Torrent Uploaded OK:<BR><BR>".$name." was uploaded.<BR><BR>  Please remember to re-download so that your passkey is added and you can seed this torrent<BR><BR><a href=download.php?id=".$id.">Download Now</a><BR><a href=torrents-details.php?id=".$id.">View Uploaded Torrent</a><BR><BR>";
	else
		$message = "Torrent Uploadeded OK:<BR><BR>".$name." was uploaded.<BR><BR><a href=torrents-details.php?id=".$id.">View Uploaded Torrent</a><BR><BR>";
	show_error_msg("Upload Complete", $message,1);

	die();
}//takeupload


///////////////////// FORMAT PAGE ////////////////////////

stdhead("Upload");

begin_frame("" . UPLOAD_RULES . "");
	echo "<b>".stripslashes($site_config["UPLOADRULES"])."</b>";
	echo "<BR>";
end_frame();

begin_frame("" . UPLOAD . "");
?>
<form name="upload" enctype="multipart/form-data" action="torrents-upload.php" method="post">
<input type="hidden" name="takeupload" value="yes" />
<table border="0" cellspacing="0" cellpadding="6" align="center">
<?
print ("<TR><TD align=right valign=top>" . ANNOUNCE . ": </td><td align=left>");

while (list($key,$value) = each($announce_urls)) {
	echo "<B>$value</B><br>";
}

if ($site_config["ALLOWEXTERNAL"]){
	echo "<BR><B>This site accepts ALL external torrents also!</B>";
}
print ("</td></tr>");

print ("<TR><TD align=right>" . TORRENT_FILE . ": </td><td align=left> <input type=file name=torrent size=50 value=" . $_FILES['torrent']['name'] . ">\n</td></tr>");

print ("<TR><TD align=right>" . NFO . ": </td><td align=left> <input type=file name=nfo size=50 value=" . $_FILES['nfo']['name'] . "><br />\n</td></tr>");

print ("<TR><TD align=right>" . TNAME . ": </td><td align=left><input type=text name=name size=60 value=" . $_POST['name'] . "><BR>This will be taken from .torrent if left empty\n</td></tr>");

print ("<TR><TD align=right>".IMAGE."</b>: </td><td align=left>Max File Size: 500kb<br>Accepted Formats: .gif, .jpg, .png<br><b>".IMAGE." 1:</b>&nbsp&nbsp<input type=file name=image0 size=50><br><b>".IMAGE." 2:</b>&nbsp&nbsp<input type=file name=image1 size=50>\n</td></tr>");

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
print ("<TR><TD align=right>".LANG.": </td><td align=left>".$language."</td></tr>");

if ($site_config['ANONYMOUSUPLOAD'] && $site_config["MEMBERSONLY"] ){ ?>
	<TR><TD align=right>Upload Anonymous: </td><td><? printf("<input name=anonycheck value=yes type=radio" . ($anonycheck ? " checked" : "") . ">Yes <input name=anonycheck value=no type=radio" . (!$anonycheck ? " checked" : "") . ">No"); ?> &nbsp;<I>(Your userid will not be associated to this upload)</font>
	</td></tr>
	<?
}

print ("<TR><TD align=center colspan=2>" . TDESC . "</td></tr></table>");

require_once("backend/bbcode.php");
print ("".textbbcode("upload","descr","$descr")."");
?>

<BR><BR><CENTER><input type="submit" value="<? print("" . UPLOADT . "\n"); ?>"><BR>
<I>Click Once! - Uploading an image may take longer</I>
</CENTER>
</form>

<?
end_frame();
stdfoot();
?>
