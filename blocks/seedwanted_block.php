<?
if (!$site_config["MEMBERSONLY"] || $CURUSER) {
begin_block("Seeders Wanted");
//uncomment the following line to exclude external torrents

$file = "".$site_config["cache_dir"]."/cache_needseedsblock.txt";
$expire = 600; // time in seconds
if (file_exists($file) &&
    filemtime($file) > (time() - $expire)) {
    $seederswantedrecords = unserialize(file_get_contents($file));
}else{ 

	$seederswantedquery = mysql_query("SELECT id,name,seeders,leechers FROM torrents WHERE seeders ='0' and external !='yes' ORDER BY leechers DESC LIMIT 5")or die(mysql_error());

	while ($seederswantedrecord = mysql_fetch_array($seederswantedquery) ) {
        $seederswantedrecords[] = $seederswantedrecord;
    }
    $OUTPUT = serialize($seederswantedrecords);
    $fp = fopen($file,"w");
    fputs($fp, $OUTPUT);
    fclose($fp);
} // end else 

if (!$seederswantedrecords){
	echo "<BR>Currently there is no torrents needing seeders<BR>";
}else{
	foreach ($seederswantedrecords as $id=>$row) { 
				$char1 = 18; //cut length 
				$smallname = CutName(htmlspecialchars($row["name"]), $char1);
				echo "<a href='torrents-details.php?id=$row[id]'>$smallname</A><BR> - Leechers Waitng: [".$row["leechers"]."]<BR>\n";
	}
}
end_block();
}
?>