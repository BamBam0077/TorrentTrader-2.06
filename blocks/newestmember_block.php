<?
//USERS ONLINE
begin_block("Newest Members");
$file = "".$site_config["cache_dir"]."/cache_newestmemberblock.txt";
$expire = 600; // time in seconds
if (file_exists($file) &&
    filemtime($file) > (time() - $expire)) {
    $newestmemberrecords = unserialize(file_get_contents($file));
}else{ 
	$newestmemberquery = mysql_query("SELECT id, username FROM users WHERE status='confirmed' ORDER BY id DESC LIMIT 5") or die(mysql_error());
	
	while ($newestmemberrecord = mysql_fetch_array($newestmemberquery) ) {
        $newestmemberrecords[] = $newestmemberrecord;
    }
    $OUTPUT = serialize($newestmemberrecords);
    $fp = fopen($file,"w");
    fputs($fp, $OUTPUT);
    fclose($fp);
} // end else 
if ($newestmemberrecords == ""){
	echo "No new members";
}else{
	foreach ($newestmemberrecords as $id=>$row) { 
		echo "<CENTER><a href='account-details.php?id=$row[id]'>$row[username]</A></CENTER>\n";
	}
}

end_block();
?>