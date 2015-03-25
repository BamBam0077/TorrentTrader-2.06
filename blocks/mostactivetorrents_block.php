<?
if (!$site_config["MEMBERSONLY"] || $CURUSER) {
begin_block("Most Active");
//uncomment the following line to exclude external torrents

//$where = "WHERE external !='yes'"  

$file = "".$site_config["cache_dir"]."/cache_mostactivetorrentsblock.txt";
$expire = 600; // time in seconds
if (file_exists($file) &&
    filemtime($file) > (time() - $expire)) {
    $mostactiverecords = unserialize(file_get_contents($file));
}else{ 

	$mostactivequery = mysql_query("SELECT id,name,seeders,leechers FROM torrents $where ORDER BY seeders + leechers DESC, seeders DESC, added ASC LIMIT 5")or die(mysql_error());

	while ($mostactiverecord = mysql_fetch_array($mostactivequery) ) {
        $mostactiverecords[] = $mostactiverecord;
    }
    $OUTPUT = serialize($mostactiverecords);
    $fp = fopen($file,"w");
    fputs($fp, $OUTPUT);
    fclose($fp);
} // end else 


if ($mostactiverecords){
	foreach ($mostactiverecords as $id=>$row) { 
				$char1 = 18; //cut length 
				$smallname = CutName(htmlspecialchars($row["name"]), $char1);
				echo "<a href='torrents-details.php?id=$row[id]'>$smallname</A><BR> - [S: ".$row["seeders"]."] [L: ".$row["leechers"]."]<BR>\n";
		}

}else{
	print("<CENTER>None uploaded yet</CENTER>\n");
}
end_block();
}
?>