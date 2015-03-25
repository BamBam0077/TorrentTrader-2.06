<?
//
//  TorrentTrader v2.x
//    This file was last updated: 03/October/2007 by TorrentialStorm
//    
//    http://www.torrenttrader.org
//
//
require_once("backend/functions.php");
dbconn();
loggedinonly();

$id = (int)$_GET['id']?$_GET['id']:$_POST['id'];
if (!is_valid_id($id)) show_error_msg("Error", "Invalid ID.", 1);

$row = mysql_fetch_assoc(mysql_query("SELECT `owner` FROM `torrents` WHERE id=$id"));
if($CURUSER["edit_torrents"]=="no" && $CURUSER['id'] != $row['owner'])
    show_error_msg("Error","You do not have permission to edit torrents",1);


function uploadimage($x, $imgname, $tid) {
    global $site_config;

    $maxfilesize = 512000; // 500kb

    $imagesdir = "".$site_config["torrent_dir"]."/images";

    $allowed_types = array(
        "image/gif" => "gif",
        "image/pjpeg" => "jpg",
        "image/jpeg" => "jpg",
        "image/jpg" => "jpg",
        "image/png" => "png"
        // Add more types here if you like
        );

    if (!($_FILES[image.$x]['name'] == "")) {
        if ($imgname != "") {
            $img = "$imagesdir/$imgname";
            $del = unlink($img);
        }

        $y = $x + 1;

        if (!array_key_exists($_FILES[image.$x]['type'], $allowed_types))
            show_error_msg("Error","Invalid file type! Image $y",1);
        
        if (!preg_match('/^(.+)\.(jpg|gif|png)$/si', $_FILES[image.$x]['name']))
            show_error_msg("Invalid image", "This file TYPE is not image!",1);

        if ($_FILES[image.$x]['size'] > $maxfilesize)
            show_error_msg("Error","Invalid file size! Image $y - Must be less than 500kb",1);

        $uploaddir = "$imagesdir/";
  
        $ifile = $_FILES[image.$x]['tmp_name'];
  
        $ifilename = $tid . $x . substr($_FILES[image.$x]['name'], strlen($_FILES[image.$x]['name'])-4, 4);

        $copy = copy($ifile, "".$uploaddir."".$ifilename."");

        if (!$copy)
            show_error_msg("Error","Error occured uploading image! - Image $y",1);

        return $ifilename;
    }
}//end func


//GET DATA FROM DB
$res = mysql_query("SELECT * FROM torrents WHERE id = $id");
$row = mysql_fetch_array($res);
if (!$row){
    show_error_msg("Error", "This Torrent id has gone!",1);
}

$torrent_dir = $site_config["torrent_dir"];    
$nfo_dir = $site_config["nfo_dir"];    

//DELETE TORRENT
if ($action=="deleteit"){
    $torrentid = 0 + $_POST["torrentid"];
    $delreason = sqlesc($_POST["delreason"]);
    $torrentname = $_POST["torrentname"];

    if (!is_valid_id($torrentid))
        show_error_msg("Failed", "Invalid Torrent ID",1);

    if (!$delreason){
        show_error_msg("Error", "Missing form data.",1);
    }

    deletetorrent($torrentid);

    write_log($CURUSER['username']." has deleted torrent: ID:$torrentid - $torrentname - Reason: $delreason");
    if ($CURUSER['id'] != $row['owner']) {
	$delreason = $_POST["delreason"];
	mysql_query("INSERT INTO messages (sender, receiver, added, subject, msg, unread, location) VALUES(0, ".$row['owner'].", '".get_date_time()."', 'Your torrent \'$torrentname\' has been deleted by ".$CURUSER['username']."', ".sqlesc("'$torrentname' was deleted by ".$CURUSER['username']."\n\nReason: $delreason").", 'yes', 'in')");
    }

    show_error_msg("Completed", "$torrentname has been deleted from the database",1);
    die;
}

//DO THE SAVE TO DB HERE
if ($action=="doedit"){
    $updateset = array();

    $nfoaction = $_POST['nfoaction'];
    if ($nfoaction == "update"){
      $nfofile = $_FILES['nfofile'];
      if (!$nfofile) die("No data " . var_dump($_FILES));
      if ($nfofile['size'] > 65535)
        show_error_msg("NFO is too big!", "Max 65,535 bytes.",1);
      $nfofilename = $nfofile['tmp_name'];
      if (@is_uploaded_file($nfofilename) && @filesize($nfofilename) > 0){
            @move_uploaded_file($nfofilename, "$nfo_dir/$id.nfo");
            $updateset[] = "nfo = 'yes'";
        }//success
    }

    $updateset[] = "name = " . sqlesc($name);
    $updateset[] = "descr = " . sqlesc($descr);
    $updateset[] = "category = " . (0 + $type);
    $updateset[] = "torrentlang = " . (0 + $language);

    if ($CURUSER["edit_torrents"] == "yes") {
        if ($_POST["banned"]) {
            $updateset[] = "banned = 'yes'";
            $_POST["visible"] = 0;
        } else {
            $updateset[] = "banned = 'no'";
        }
    }

    $updateset[] = "visible = '" . ($_POST["visible"] ? "yes" : "no") . "'";

    if ($CURUSER["edit_torrents"] == "yes")
        $updateset[] = "freeleech = '".$_POST["freeleech"]."'";

    $updateset[] = "anon = '" . ($_POST["anon"] ? "yes" : "no") . "'";

    //update images
    $img1action = $_POST['img1action'];
    if ($img1action == "update")
        $updateset[] = "image1 = " .sqlesc(uploadimage(0, $row[image1], $id));
    if ($img1action == "delete") {
        if ($row[image1]) {
            $del = unlink("".$site_config["torrent_dir"]."/images/$row[image1]");
            $updateset[] = "image1 = ''";
        }
    }

    $img2action = $_POST['img2action'];
    if ($img2action == "update")
        $updateset[] = "image2 = " .sqlesc(uploadimage(1, $row[image2], $id));
    if ($img2action == "delete") {
        if ($row[image2]) {
            $del = unlink("".$site_config["torrent_dir"]."/images/$row[image2]");
            $updateset[] = "image2 = ''";
        }
    }


    mysql_query("UPDATE torrents SET " . join(",", $updateset) . " WHERE id = $id");

    $returl = "torrents-edit.php?id=$id&edited=1";
    if (isset($_POST["returnto"])){
        $returl .= "&returnto=" . urlencode($_POST["returnto"]);
    }

    write_log("Torrent $id ($name) was edited by $CURUSER[username]");

    header("Location: $returl");
    die();
}//END SAVE TO DB

//UPDATE CATEGORY DROPDOWN
$catdropdown = "<select name=\"type\">\n";
$cats = genrelist();
    foreach ($cats as $catdropdownubrow) {
        $catdropdown .= "<option value=\"" . $catdropdownubrow["id"] . "\"";
        if ($catdropdownubrow["id"] == $row["category"])
            $catdropdown .= " selected=\"selected\"";
        $catdropdown .= ">" . htmlspecialchars($catdropdownubrow["parent_cat"]) . ": " . htmlspecialchars($catdropdownubrow["name"]) . "</option>\n";
    }
$catdropdown .= "</select>\n";
//END CATDROPDOWN

//UPDATE TORRENTLANG DROPDOWN
$langdropdown = "<select name=\"language\"><option value=0>Unknown</option>\n";
$lang = langlist();
foreach ($lang as $lang) {
    $langdropdown .= "<option value=\"" . $lang["id"] . "\"";
    if ($lang["id"] == $row["torrentlang"])
        $langdropdown .= " selected=\"selected\"";
    $langdropdown .= ">" . htmlspecialchars($lang["name"]) . "</option>\n";
}
$langdropdown .= "</select>\n";
//END TORRENTLANG


$char1 = 55;
$shortname = CutName(htmlspecialchars($row["name"]), $char1);

if ($_GET["edited"]){
    show_error_msg("Edited OK","Torrent has been edited OK",1);
}

stdhead("Edit Torrent \"$shortname\"");

begin_frame("Edit Torrent \"$shortname\"", center);

print("<BR><BR><form method=post name=\"bbform\" enctype=multipart/form-data action=\"$PHP_SELF?action=doedit\">\n");
print("<input type=\"hidden\" name=\"id\" value=\"$id\">\n");

if (isset($_GET["returnto"]))
    print("<input type=\"hidden\" name=\"returnto\" value=\"" . htmlspecialchars($_GET["returnto"]) . "\" />\n");

print("<table border=0 cellspacing=4 cellpadding=2 width=95%>\n");

echo "<tr><td align=right width=60><B>".NAME.": </b></TD><TD><input type=\"text\" name=\"name\" value=\"" . htmlspecialchars($row["name"]) . "\" size=\"60\" /></TD></TR>";

echo "<tr><td align=right><B>".IMAGE.": </b></TD><TD><b>".IMAGE." 1:</b>&nbsp&nbsp<input type=radio name=img1action value='keep' checked>".KEEP_IMAGE."&nbsp&nbsp"."<input type=radio name=img1action value='delete'>".DELETE_IMAGE."&nbsp&nbsp"."<input type=radio name=img1action value='update'>".UPDATE_IMAGE."<br><input type=file name=image0 size=60> <br><br> <b>".IMAGE." 2:</b>&nbsp&nbsp<input type=radio name=img2action value='keep' checked>".KEEP_IMAGE."&nbsp&nbsp"."<input type=radio name=img2action value='delete'>".DELETE_IMAGE."&nbsp&nbsp"."<input type=radio name=img2action value='update'>".UPDATE_IMAGE."<BR><input type=file name=image1 size=60></TD></TR>";

echo "<tr><td align=right><B>".NFO.": </b><br></TD><TD><input type=radio name=nfoaction value='keep' checked>".KEEPCURRENT." &nbsp; <input type=radio name=nfoaction value='update'>".UPDATE_IMAGE.":";
if ($row["nfo"] == "yes"){
    echo "&nbsp;&nbsp;<a href=nfo-view.php?id=".$row["id"]." target=_blank>[View Current NFO]</a>";
} else{
    echo "&nbsp;&nbsp;<font color=red>No .NFO Uploaded</font>";
}
echo "<br /><input type=file name=nfofile size=60></TD></TR>";

echo "<tr><td align=right><B>".CATEGORIES.": </b></TD><TD>".$catdropdown."</TD></TR>";

echo "<tr><td align=right><B>".LANG.": </b></TD><TD>".$langdropdown."</TD></TR>";

if ($CURUSER["edit_torrents"] == "yes")
    echo "<tr><td align=right><B>".BANNED.": </b></TD><TD><input type=\"checkbox\" name=\"banned\"" . (($row["banned"] == "yes") ? " checked=\"checked\"" : "" ) . " value=\"1\" /> ".BANNED."?<br>";

echo "<tr><td align=right><B>".VISIBLE.": </b></TD><TD><input type=\"checkbox\" name=\"visible\"" . (($row["visible"] == "yes") ? " checked=\"checked\"" : "" ) . " value=\"1\" /> " . VISIBLEONMAIN . "<br>";

if ($row["external"] != "yes" && $CURUSER["edit_torrents"] == "yes"){
    echo "<tr><td align=right><B>".FREE_LEECH.": </b></TD><TD><input type=\"checkbox\" name=\"freeleech\"" . (($row["freeleech"] == "1") ? " checked=\"checked\"" : "" ) . " value=\"1\" />".FREE_LEECH_MSG."<br>";
}

if ($site_config['ANONYMOUSUPLOAD']) {
	echo "<tr><td align=right><B>Anonymous Upload: </b></TD><TD><input type=\"checkbox\" name=\"anon\"" . (($row["anon"] == "yes") ? " checked=\"checked\"" : "" ) . " value=\"1\" />(Your username will not be associated with this torrent)<br>";
}


print ("<TR><TD align=center colspan=2><B>" . TDESC . ":</B></td></tr></table>");
require_once("backend/bbcode.php");
print ("".textbbcode("bbform","descr","" . htmlspecialchars($row["descr"]) . "")."");

    
print("<BR><CENTER><input type=\"submit\" value='".SUBMIT."' style='height: 25px; width: 110px'> <input type=reset value='".UNDO."' style='height: 25px; width: 105px'></CENTER>\n");
print("</form>\n");
end_frame();

begin_frame("".DELETE_TORRENT."");
        print("<CENTER><form method=post action=torrents-edit.php?action=deleteit&id=$id>\n");
        print("<input type=hidden name='torrentid' value='$id'>\n");
        print("<input type=hidden name='torrentname' value='".htmlspecialchars($row["name"])."'>\n");
        echo "<B>".REASON_FOR_DELETE."</B><input type=text size=30 name=delreason>";
        echo "&nbsp;<input type=submit value='".DELETE_TORRENT."'></form></CENTER>";
end_frame();

stdfoot();

?>
