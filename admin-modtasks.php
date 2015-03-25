<?
//
//  TorrentTrader v2.x
//	This file was last updated: 6/Sept/2007
//	
//	http://www.torrenttrader.org
//
//
require_once("backend/functions.php");
dbconn(false);
loggedinonly();


if($CURUSER["edit_users"]!="yes")
	show_error_msg("Access Denied","You do not have permission to edit users",1);

$action = $_POST["action"];

if (!$action)
	show_error_msg("Error","This task is not found",1);

if ($action == 'edituser'){
	$userid = $_POST["userid"];
	$title = $_POST["title"];
	$downloaded = $_POST["downloaded"];
	$uploaded = $_POST["uploaded"];
	$signature = $_POST["signature"];
	$avatar = $_POST["avatar"];
	$ip = $_POST["ip"];
	$class = (int) $_POST["class"];
	$donated = (float) $_POST["donated"];
	$password = $_POST["password"];
	$warned = $_POST["warned"];
	$forumbanned = $_POST["forumbanned"];
	$modcomment = $_POST["modcomment"];
	$enabled = $_POST["enabled"];
	$invites = $_POST["invites"];
	$class = (int)$_POST["class"];

	if (!is_valid_id($userid))
		show_error_msg("Editing Failed", "Invalid UserID",1);

	//change user class
	$res = mysql_query("SELECT class FROM users WHERE id=$userid");
	$arr = mysql_fetch_row($res) or show_error_msg("Error","Mysql error",1);
	$uc = $arr[0];

	// skip if class is same as current
	if ($uc != $class && $class > 0) {
		// You cant demote admins!
	/*	if ((get_user_class() == "5") && ($userid == $CURUSER["id"]))
			show_error_msg("Editing Failed", "You can't demote admins for security reasons.",1);
    
		// User may not demote someone with same or higher class than himself!
		elseif ($uc >= get_user_class())
			show_error_msg("Editing Failed", "You may not demote someone with same or higher class than yourself",1);
    
	// All ok, update db
    else {*/
      @mysql_query("UPDATE users SET class=$class WHERE id=$userid");
      // Notify user
      $prodemoted = ($class > $uc ? "promoted" : "demoted");
      $msg = sqlesc("You have been $prodemoted to '" . get_user_class_name($class) . "' by " . $CURUSER["username"] . ".");
      $added = sqlesc(get_date_time());
      @mysql_query("INSERT INTO messages (sender, receiver, msg, added) VALUES(0, $userid, $msg, $added)");
   // }
	}
	//continue updates


	mysql_query("UPDATE users SET title=".sqlesc($title).", downloaded='$downloaded', uploaded='$uploaded', signature=".sqlesc($signature).", avatar='$avatar', ip='$ip', donated='$donated', forumbanned='$forumbanned', warned='$warned',  modcomment='$modcomment', enabled='$enabled', invites='$invites' WHERE id=$userid");

	write_log($CURUSER['username']." has edited user: $userid details");
  
	if ($_POST['resetpasskey']=='yes'){
		mysql_query("UPDATE users SET passkey='' WHERE id=$userid");
		//write_log($CURUSER['username']." Passkey has been reset for: $userid");
	}

	$chgpasswd = $_POST['chgpasswd']=='yes' ? true : false;
	if ($chgpasswd) {
		$passreq = mysql_query("SELECT password FROM users WHERE id=$userid");
		$passres = mysql_fetch_assoc($passreq);
		if($password != $passres['password']){
			$password = md5($password);
			mysql_query("UPDATE users SET password='$password' WHERE id=$userid");
			write_log($CURUSER['username']." has changed password for user: $userid");
		}
	}
  
  header("Location: account-details.php?id=$userid");
  die;
}

if ($action == 'addwarning'){
	$userid = (int)$_POST["userid"];
	$reason = mysql_real_escape_string($_POST["reason"]);
	$expiry = (int)$_POST["expiry"];
	$type = mysql_real_escape_string($_POST["type"]);

	if (!is_valid_id($userid))
		show_error_msg("Editing Failed", "Invalid UserID",1);

	if (!$reason || !$expiry || !$type){
		show_error_msg("Error", "Missing form data.",1);
	}

	$timenow = get_date_time();

	$expiretime = get_date_time(gmtime() + (86400 * $expiry));

	$ret = mysql_query("INSERT INTO warnings (userid, reason, added, expiry, warnedby, type) VALUES ('$userid','$reason','$timenow','$expiretime','".$CURUSER['id']."','$type')");

	$ret = mysql_query("UPDATE users SET warned='yes' WHERE id='$userid'");

	$msg = sqlesc("You have been warned by " . $CURUSER["username"] . " - Reason: ".$reason." - Expiry: ".$expiretime."");
	$added = sqlesc(get_date_time());
	@mysql_query("INSERT INTO messages (sender, receiver, msg, added) VALUES(0, $userid, $msg, $added)");

	write_log($CURUSER['username']." has added a warning for user: <a href=account-details.php?id=$userid>$userid</a>");
	header("Location: account-details.php?id=$userid");
	die;
}


if ($action == "deleteaccount"){
	if($CURUSER["level"]!="Administrator")//only allow admins to delete users
		show_error_msg("Error", "Only admins can do this",1);

	$userid = (int)$_POST["userid"];
	$username = sqlesc($_POST["username"]);
	$delreason = sqlesc($_POST["delreason"]);

	if (!is_valid_id($userid))
		show_error_msg("Failed", "Invalid UserID",1);

	if (!$delreason){
		show_error_msg("Error", "Missing form data.",1);
	}

	deleteaccount($userid);

	write_log($CURUSER['username']." has deleted account: $username");

	show_error_msg("Completed", "User has been deleted from the database",1);
	die;
}

/*

if ($action == "banuser")
{
  $userid = $_POST["userid"];
  $what = $_POST["what"];
  if (!is_valid_id($userid))
    genbark("Not a vaild Userid");
  $comment = $_POST['comment'];
  if (!$comment)
    genbark("Error:", "Please explain why you are banning this user!");
  $r = mysql_query("SELECT username,ip FROM users WHERE id=$userid") or sqlerr();
  $a = mysql_fetch_assoc($r);
  $username = $a["username"];
  $ip = $a["ip"];
  if ($what == "subnet")
  	$ip = substr($ip, 0, strrpos($ip, ".")) . ".*";
  else
    if ($what == 'ip')
      $extra = " OR ip='" . substr($ip, 0, strrpos($ip, ".")) . ".*'";
    else
      genbark("Heh", "Select what to ban!");
  $r = mysql_query("SELECT * FROM bans WHERE ip='$ip'$extra") or sqlerr();
  if (mysql_num_rows($r) > 0)
    genbark("Error", "IP/subnet is already banned");
  else {
    $dt = get_date_time();
    $comment = sqlesc($comment);
    mysql_query("INSERT INTO bans (userid, first, last, added, addedby, comment) VALUES($userid, '$ip', '$ip', '$dt', $CURUSER[id], $comment)") or sqlerr();
    mysql_query("UPDATE users SET secret='' WHERE id=$userid") or sqlerr();
    $returnto = $_POST["returnto"];
    header("Location: $returnto");
    die;
  }
}

if ($action == "enableaccount")
{
  $userid = $_POST["id"];
  $res = mysql_query("SELECT * FROM users WHERE id='$userid'") or sqlerr();
  if (mysql_num_rows($res) != 1)
    genbark("User $userid not found!");
  $secret = sqlesc(mksecret());
  mysql_query("UPDATE users SET secret=" . $secret . " WHERE id=$userid") or sqlerr();
  header("Location: account-details.php?id=$userid");
  die;
}
*/
?>
