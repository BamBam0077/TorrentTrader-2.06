<?php
// Invite update function (Author: TorrentialStorm)
function autoinvites($interval, $minlimit, $maxlimit, $minratio, $invites, $maxinvites) {
	$time = gmtime() - ($interval*86400);
	$minlimit = $minlimit*1024*1024*1024;
	$maxlimit = $maxlimit*1024*1024*1024;
	$res = mysql_query("SELECT id, username, class, invites FROM users WHERE enabled = 'yes' AND downloaded >= $minlimit AND downloaded < $maxlimit AND uploaded / downloaded >= $minratio AND warned = 'no' AND enabled='yes' AND UNIX_TIMESTAMP(invitedate) <= $time") or die;
	if (mysql_num_rows($res) > 0) {
		while ($arr = mysql_fetch_assoc($res)) {
			$maxninvites = $maxinvites[$arr['class']];
			if ($arr['invites'] >= $maxninvites)
				continue;
			if (($maxninvites-$arr['invites']) < $invites)
				$invites = $maxninvites - $arr['invites'];

			mysql_query("UPDATE users SET invites = invites+$invites, invitedate = NOW() WHERE id=$arr[id]");
			write_log("Gave $invites invites to '$arr[username]' - Class: ".get_user_class_name($arr['class'])."");
		}
	}
}


function do_cleanup() {
global $site_config;

//LOCAL TORRENTS - GET PEERS DATA AND UPDATE BROWSE STATS
$torrents = array();
$res = mysql_query("SELECT torrent, seeder, COUNT(*) AS c FROM peers GROUP BY torrent, seeder");
	while ($row = mysql_fetch_assoc($res)) {
		if ($row["seeder"] == "yes")
			$key = "seeders";
		else
			$key = "leechers";
		$torrents[$row["torrent"]][$key] = $row["c"];
	}

	$fields = explode(":", "comments:leechers:seeders");
	$res = mysql_query("SELECT id, seeders, leechers FROM torrents WHERE external !='yes'");
	while ($row = mysql_fetch_assoc($res)) {
		$id = $row["id"];
		$torr = $torrents[$id];
		foreach ($fields as $field) {
			if (!isset($torr[$field]))
				$torr[$field] = 0;
		}
		$update = array();
		foreach ($fields as $field) {
			if ($torr[$field] != $row[$field])
				$update[] = "$field = " . $torr[$field];
		}
		if (count($update))
			mysql_query("UPDATE torrents SET " . implode(",", $update) . " WHERE id = $id AND external !='yes'");
	}


//LOCAL TORRENTS - MAKE NON-ACTIVE/OLD TORRENTS INVISIBLE
$deadtime = gmtime() - $site_config["max_dead_torrent_time"];
mysql_query("UPDATE torrents SET visible='no' WHERE visible='yes' AND last_action < FROM_UNIXTIME($deadtime) AND external !='yes'");


//DELETE OLD NON-ACTIVE PEERS
$res = mysql_query("DELETE FROM peers WHERE ".gmtime()."-UNIX_TIMESTAMP(last_action) >= 10800");

//DELETE PENDING USER ACCOUNTS OVER TIMOUT AGE
$deadtime = gmtime() - $site_config["signup_timeout"];
mysql_query("DELETE FROM users WHERE status = 'pending' AND added < FROM_UNIXTIME($deadtime)");

//LEECHWARN USERS WITH LOW RATIO

if ($site_config["ratiowarn_enable"]){
    $minratio = $site_config["ratiowarn_minratio"];
    $downloaded = $site_config["ratiowarn_mingigs"]*1024*1024*1024;
    $length = $site_config["ratiowarn_daystowarn"];

    //ADD WARNING
    $res = mysql_query("SELECT id,username FROM users WHERE class = 1 AND id NOT IN (SELECT userid FROM warnings WHERE type = 'Poor Ratio' AND active = 'yes') AND enabled='yes' AND uploaded / downloaded < $minratio AND downloaded >= $downloaded");

    if (mysql_num_rows($res) > 0){
        $timenow = get_date_time();
        $reason = "You have been warned because of having low ratio. You need to get a ".$minratio." before next ".$length." days or your account may be banned.";

        $expiretime = gmdate("Y-m-d H:i:s", gmtime() + (86400 * $length));

        while ($arr = mysql_fetch_assoc($res)){
            mysql_query("INSERT INTO warnings (userid, reason, added, expiry, warnedby, type) VALUES ('".$arr["id"]."','".$reason."','".$timenow."','".$expiretime."','0','Poor Ratio')");
            mysql_query("UPDATE users SET warned='yes' WHERE id='".$arr["id"]."'");
            mysql_query("INSERT INTO messages (sender, receiver, added, msg, poster) VALUES ('0', '".$arr["id"]."', '".$timenow."', '".$reason."', '0')");
            write_log("Auto Leech warning has been <B>added</B> for: <a href=account-details.php?id=".$arr["id"].">".$arr["username"]."</a>");
        }
    }

    //REMOVE WARNING
    $res1 = mysql_query("SELECT users.id, users.username FROM users INNER JOIN warnings ON users.id=warnings.userid WHERE type='Poor Ratio' AND warned = 'yes'  AND enabled='yes' AND uploaded / downloaded >= $minratio AND downloaded >= $downloaded");
    if (mysql_num_rows($res1) > 0){
        $timenow = get_date_time();
        $reason = "Your warning of low ratio has been removed. We highly recommend you to keep a your ratio up to not be warned again.\n";

        while ($arr1 = mysql_fetch_assoc($res1)){
            write_log("Auto Leech warning has been removed for: <a href=account-details.php?id=".$arr1["id"].">".$arr1["username"]."</a>");
                
            mysql_query("UPDATE users SET warned = 'no' WHERE id = '".$arr1["id"]."'");
            mysql_query("UPDATE warnings SET expiry = '$timenow', active = 'no' WHERE userid = $arr1[id]");
            mysql_query("INSERT INTO messages (sender, receiver, added, msg, poster) VALUES ('0', '".$arr1["id"]."', '".$timenow."', '".$reason."', '0')");
        }
    }

    //BAN WARNED USERS
    $res = mysql_query("SELECT users.id, users.username FROM users INNER JOIN warnings ON users.id=warnings.userid WHERE type='Poor Ratio' AND active = 'yes' AND class = 1 AND enabled='yes' AND warned = 'yes' AND uploaded / downloaded < $minratio AND downloaded >= $downloaded");

    if (mysql_num_rows($res) > 0){
        $timenow = get_date_time();
        $expires = (86400 * $length);

        while ($arr = mysql_fetch_assoc($res)){
            $r = mysql_query("SELECT id, UNIX_TIMESTAMP(expiry) as expiry FROM warnings WHERE userid=$arr[id]");
            $row = mysql_fetch_assoc($r);

            if (gmtime() - $row["expiry"] >= 0) {
                mysql_query("UPDATE users SET enabled='no', warned='no' WHERE id='".$arr["id"]."'");
                write_log("User <a href=account-details.php?id=".$arr["id"].">".$arr["username"]."</a> has been banned (Auto Leech warning).");
            }
        }
    }
    
}//check if warning system is on
// REMOVE WARNINGS
$res = mysql_query("SELECT users.id, users.username, warnings.expiry, warnings.type FROM users INNER JOIN warnings ON users.id=warnings.userid WHERE type != 'Poor Ratio' AND warned = 'yes'  AND enabled='yes' AND warnings.active = 'yes' AND warnings.expiry < '".get_date_time()."'");
while ($arr = mysql_fetch_assoc($res)) {
    mysql_query("UPDATE users SET warned = 'no' WHERE id = $arr[id]");
    mysql_query("UPDATE warnings SET active = 'no' WHERE userid = $arr[id] AND expiry < '".get_date_time()."'");
    write_log("Removed warning for $arr[username]. Expiry: $arr[expiry]. Type: $arr[type]");
}
// WARN USERS THAT STILL HAVE ACTIVE WARNINGS
mysql_query("UPDATE users SET warned = 'yes' WHERE warned = 'no' AND id IN (SELECT userid FROM warnings WHERE active = 'yes')");
//END//

	// START INVITES UPDATE
	// SET INVITE AMOUNTS ACCORDING TO RATIO/GIGS ETC
	// autoinvites(interval to give invites (days), min downloaded GB, max downloaded GB, min ratio, invites to give, max invites allowed (array))
	// $maxinvites[CLASS ID] = max # of invites;
	$maxinvites[1] = 5;   // User
	$maxinvites[2] = 10;  // Power User
	$maxinvites[3] = 20;  // VIP
	$maxinvites[4] = 25;  // Uploader
	$maxinvites[5] = 100; // Moderator
	$maxinvites[6] = 100; // Super Moderator
	$maxinvites[7] = 400; // Administrator

	// Give 1 invite every 21 days to users with > 1GB downloaded AND < 4GB downloaded AND ratio > 0.50
	autoinvites(21, 1, 4, 0.50, 1, $maxinvites);
	autoinvites(14, 1, 4, 0.90, 2, $maxinvites);
	autoinvites(14, 4, 7, 0.95, 2, $maxinvites);

	$maxinvites[1] = 7; // User
	autoinvites(14, 7, 10, 1.00, 3, $maxinvites);

	$maxinvites[1] = 10; // User
	autoinvites(14, 10, 100000, 1.05, 4, $maxinvites);
	//END INVITES

//OPTIMISE TABLES
mysql_query("OPTIMIZE TABLE `peers` , `torrents` , `tasks` , `guests` , `users` , `messages`; ");
	
}//end func

?>