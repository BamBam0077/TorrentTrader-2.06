<?
//
//  TorrentTrader v2.x
//	This file was last updated: 25/June/2007
//	
//	http://www.torrenttrader.org
//
//
error_reporting(0);

require_once("backend/functions.php");
dbconn();
loggedinonly();

// check access and rights
if($CURUSER["edit_torrents"]=="no")
	show_error_msg("Error","You do not have permission to Edit nfo's",1);

$nfolocation = "".$site_config["nfo_dir"]."/$id.nfo";


if($do == "save_nfo"){
    $nfo = fopen("$nfolocation", "w");
    $nfoupdated = fwrite($nfo,$nfocontents);
    fclose($nfo);
    if($nfoupdated){
        show_error_msg("Success", "NFO Updated OK", 1);
        write_log("NFO of $type $id was edited by $CURUSER[username]");
    }
}

if($do == "del_nfo"){      
    $queryCheck = mysql_query("SELECT nfo FROM torrents WHERE nfo='yes' AND id='$id' LIMIT 1");
    $resultCheck = mysql_num_rows($queryCheck);
    if ($resultCheck == 0)
        $message = "There is no NFO available to delete for ID $id."; 

    if(!$message){
        @unlink($nfolocation);
        @mysql_query("UPDATE torrents SET nfo='no' WHERE id='$id' LIMIT 1");
        show_error_msg("Success", "NFO Deleted OK", 1);
        write_log("NFO $id was deleted by $CURUSER[username] ($reason)");
    }
}

if(!$do){

	$id = (int)$_GET["id"];

	if (!$id)
		show_error_msg("ID not found", "You can't edit, if you don't tell me what you want!",1);

	$filegetcontents = file_get_contents($nfolocation);
	$nfo = htmlspecialchars($filegetcontents);

	if (!$nfo){
		show_error_msg("Error", "No NFO!",1);
	}

    stdhead("NFO Editor");  
    begin_frame("NFO Editor");
    echo "<br><CENTER><form action='$PHP_SELF' method='post'>\n";
	echo "<input type='hidden' name='id' value='$id'>\n";
	echo "<input type='hidden' name='do' value='save_nfo'>\n";
	echo "<textarea name='nfocontents' cols='80' rows='20' style='border:1px black solid;background:#eeeeee;font-family:verdana,arial; font-size: 12px; color:#000000;'>\n";
	echo "".stripslashes($nfo)."";
	echo "</textarea>\n<p>\n";
	echo "<input style='background:#eeeeee' type='submit' value='   Save   '>\n";
	echo "<input style='background:#eeeeee' type='reset' value='  Reset   '>\n";
	echo "</form></CENTER>\n";
    end_frame();
    
    begin_frame("Delete NFO");
	echo "<CENTER><form action='$PHP_SELF' method='post'>\n";
	echo "<input type='hidden' name='id' value='$id'>\n";
    echo "<input type='hidden' name='do' value='del_nfo'>\n";
  	echo "Reason for deletion: <input type=text size=40 name=reason> <input type=submit value='Delete it!' style='height: 25px'>\n";
	echo "</form></CENTER>\n";
    end_frame();
	
}

stdfoot();
?>