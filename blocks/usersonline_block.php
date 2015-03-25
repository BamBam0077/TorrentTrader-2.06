<?
//USERS ONLINE
begin_block("".ONLINE_USERS."");
$file = "".$site_config["cache_dir"]."/cache_usersonlineblock.txt";
$expire = 600; // time in seconds
if (file_exists($file) &&
    filemtime($file) > (time() - $expire)) {
    $usersonlinerecords = unserialize(file_get_contents($file));
}else{ 
	$usersonlinequery = mysql_query("SELECT id, username FROM users WHERE privacy !='strong' AND UNIX_TIMESTAMP('" . get_date_time() . "') - UNIX_TIMESTAMP(users.last_access) < 900") or die(mysql_error());
	
	while ($usersonlinerecord = mysql_fetch_array($usersonlinequery) ) {
        $usersonlinerecords[] = $usersonlinerecord;
    }
    $OUTPUT = serialize($usersonlinerecords);
    $fp = fopen($file,"w");
    fputs($fp, $OUTPUT);
    fclose($fp);
} // end else 
if ($usersonlinerecords == ""){
	echo "No Users Online";
}else{
	foreach ($usersonlinerecords as $id=>$row) { 
		echo "<a href='account-details.php?id=$row[id]'>$row[username]</A>, \n";
	}
}

end_block();
?>
