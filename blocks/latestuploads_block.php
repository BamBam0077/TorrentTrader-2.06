<?
if (!$site_config["MEMBERSONLY"] || $CURUSER) {
begin_block("Latest Torrents");

$expire = 1800; // time in seconds
if (($latestuploadsrecords = $GLOBALS["TTCache"]->get("latestuploadsblock", $expire)) === false) {
    $latestuploadsquery = mysql_query("SELECT id, name FROM torrents WHERE banned='no' ORDER BY id DESC LIMIT 5")or die(mysql_error());
    $latestuploadsrecords = array();
    while ($latestuploadsrecord = mysql_fetch_array($latestuploadsquery))
        $latestuploadsrecords[] = $latestuploadsrecord;
    $GLOBALS["TTCache"]->set("latestuploadsblock", $latestuploadsrecords, $expire);
}

if ($latestuploadsrecords) {
    foreach ($latestuploadsrecords as $id=>$row) { 
                $char1 = 18; //cut length 
                $smallname = htmlspecialchars(CutName($row["name"], $char1));
                echo "<a href='torrents-details.php?id=$row[id]'>$smallname</A><BR>\n";
        }    
}else{
    print("<CENTER>None uploaded yet</CENTER>\n");
}
end_block();
}
?>