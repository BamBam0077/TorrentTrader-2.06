<?
//
//  TorrentTrader v2.x
//	This file was last updated: 21/June/2007
//	
//	http://www.torrenttrader.org
//
//
require_once("backend/functions.php");
dbconn();

// Disable checks if we're signing up with an invite
if (!is_valid_id($_REQUEST["invite"]) || strlen($_REQUEST["secret"]) != 32) {
	//invite only check
	if ($site_config["INVITEONLY"]) {
		show_error_msg("Invite only", "<br><br><center>Sorry this site has disabled user registration, the only way to register is via an invite from an existing member.<br><br></center>",1);
	}

	//get max members, and check how many users there is
	$numsitemembers = get_row_count("users");
	if ($numsitemembers >= $site_config["maxsiteusers"])
		show_error_msg("Sorry...", "The site is full!<br>The limit of ".number_format($site_config["maxsiteusers"])." users have been reached.<br>HOWEVER, user accounts expire all the time so please check back again later!<BR><BR>There is currently ".number_format($numsitemembers)." members",1);
} else {
	$res = mysql_query("SELECT id FROM users WHERE id = $_REQUEST[invite] AND MD5(secret) = ".sqlesc($_REQUEST["secret"])."");
	$invite_row = mysql_fetch_array($res);
	if (!$invite_row) {
		show_error_msg("Error", "No invite found with those details. Unconfirmed accounts/invites expire after ".($site_config['signup_timeout']/86400)." days.", 1);
	}
}

if ($takesignup == "1") {

$message == "";

function validusername($username) {
		$allowedchars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
		for ($i = 0; $i < strlen($username); ++$i)
			if (strpos($allowedchars, $username[$i]) === false)
			return false;
		return true;
}

	//Setup Error Messages
  if (empty($wantpassword) || (empty($email) && !$invite_row) || empty($wantusername))
	$message = "Don't leave any required field blank.";
  elseif (strlen($wantusername) > 15)
	$message = "Sorry, username is too long (max is 15 chars)";
  elseif ($wantpassword != $passagain)
	$message = "The passwords didn't match! Must've typoed. Try again.";
  elseif (strlen($wantpassword) < 6)
	$message = "Sorry, password is too short (min is 6 chars)";
  elseif (strlen($wantpassword) > 40)
	$message = "Sorry, password is too long (max is 40 chars)";
  elseif ($wantpassword == $wantusername)
 	$message = "Sorry, password cannot be same as user name.";
  elseif (!validusername($wantusername))
	$message = "Invalid username.";
  elseif (!$invite_row && !validemail($email))
		$message = "That doesn't look like a valid email address.";

	if ($message == "") {
		// Certain checks must be skipped for invites
		if (!$invite_row) {
			//check email isnt banned
			$maildomain = (substr($email, strpos($email, "@") + 1));
			$a = (@mysql_fetch_row(@mysql_query("select count(*) from email_bans where mail_domain='$email'"))) or die(mysql_error());
			if ($a[0] != 0)
				$message = "The e-mail address $email is Banned.";

			$a = (@mysql_fetch_row(@mysql_query("select count(*) from email_bans where mail_domain='$maildomain'"))) or die(mysql_error());
			if ($a[0] != 0)
				$message = "The e-mail address $email is Banned.";
	  
		  // check if email addy is already in use
		  $a = (@mysql_fetch_row(@mysql_query("select count(*) from users where email='$email'"))) or die(mysql_error());
		  if ($a[0] != 0)
			$message = "The e-mail address $email is already in use.";
		}

	   //check username isnt in use
	  $a = (@mysql_fetch_row(@mysql_query("select count(*) from users where username='$wantusername'"))) or die(mysql_error());
	  if ($a[0] != 0)
		$message = "The username $wantusername is already in use."; 

	  $secret = mksecret(); //generate secret field

	  $wantpassword = md5($wantpassword);//md5 hash the password
	}
	
	if ($message != "")
		show_error_msg("Signup Failed", $message, 1);

  if ($message == "") {
		if ($invite_row) {
			mysql_query("UPDATE users SET username=".sqlesc($wantusername).", password=".sqlesc($wantpassword).", secret=".sqlesc($secret).", status='confirmed', added='".get_date_time()."' WHERE id=$invite_row[id]");
			header("Refresh: 0; url=account-confirm-ok.php?type=confirm");
			die;
		}

	if ($site_config["CONFIRMEMAIL"]) { //req confirm email true/false
		$status = "pending";
	}else{
		$status = "confirmed";
	}

	//make first member admin
	if ($numsitemembers == '0')
		$signupclass = '7';
	else
		$signupclass = '1';

   $ret = mysql_query("INSERT INTO users (username, password, secret, email, status, added, age, country, gender, client, stylesheet, language, class) VALUES (" .
	  implode(",", array_map("sqlesc", array($wantusername, $wantpassword, $secret, $email, $status, get_date_time(), $age, $country, $gender, $client, $site_config["default_theme"], $site_config["default_language"], $signupclass))).")");

    $id = mysql_insert_id();

    $psecret = md5($secret);
    $thishost = $_SERVER["HTTP_HOST"];
    $thisdomain = preg_replace('/^www\./is', "", $thishost);

	//ADMIN CONFIRM
	if ($site_config["ACONFIRM"]) {
		$body = "Your account at ".$site_config['SITENAME']." has been created.\n\nYou will have to wait for the approval of an admin before you can use your new account.\n\n".$site_config['SITENAME']." Admin";
	}else{//NO ADMIN CONFIRM, BUT EMAIL CONFIRM
		$body = "Your account at ".$site_config['SITENAME']." has been : APPROVED\n\nTo confirm your user registration, you have to follow this link:\n\n	".$site_config['SITEURL']."/account-confirm.php?id=$id&secret=$psecret\n\nAfter you do this, you will be able to use your new account.\n\n	If you fail to do this, your account will be deleted within a few days.\n\n".$site_config['SITENAME']." Admin";
	}

	if ($site_config["CONFIRMEMAIL"]){ //email confirmation is on
		ini_set("sendmail_from", "");
		mail($email, "Your ".$site_config['SITENAME']." User Account", $body, "From: ".$site_config['SITENAME']." <".$site_config['SITEEMAIL'].">");
		header("Refresh: 0; url=account-confirm-ok.php?type=signup&email=" . urlencode($email));
	}else{ //email confirmation is off
		header("Refresh: 0; url=account-confirm-ok.php?type=noconf");
	}

	//send pm to new user
	if ($site_config["WELCOMEPMON"]){
		$dt = sqlesc(get_date_time());
		$msg = sqlesc($site_config["WELCOMEPMMSG"]);
		mysql_query("INSERT INTO messages (sender, receiver, added, msg, poster) VALUES(0, $id, $dt, $msg, 0)");
	}

    die;
  }

}//end takesignup



stdhead("Signup");
begin_frame("Signup");
?>
<? echo "" . COOKIES . "";?>
<p>
<form method="post" action="account-signup.php?takesignup=1">
	<?php if ($invite_row) { ?>
	<input type="hidden" name="invite" value="<?php echo $_GET[invite]; ?>" />
	<input type="hidden" name="secret" value="<?php echo $_GET[secret]; ?>" />
	<?php } ?>
	<table cellSpacing="0" cellPadding="2" border="0" >
			<tr>
				<td>Username: <font class="small"><font color="#FF0000">*</font></td>
				<td><input type="text" size="40" name="wantusername" /></td>
			</tr>
			<tr>
				<td>Password: <font class="small"><font color="#FF0000">*</font></td>
				<td><input type="password" size="40" name="wantpassword" /></td>
			</tr>
			<tr>
				<td>Confirm: <font class="small"><font color="#FF0000">*</font></td>
				<td><input type="password" size="40" name="passagain" /></td>
			</tr>
			<?php if (!$invite_row) {?>
			<tr>
				<td>Email: <font class="small"><font color="#FF0000">*</font></td>
				<td><input type="text" size="40" name="email"/></td>
			</tr>
			<?php } ?>
			<tr>
				<td>Age:</td>
				<td><input type="text" size="40" name="age" maxlength="3" /></td>
			</tr>
			<tr>
				<td>Country:</td>
				<td>
					<select name="country" size="1">
						<?php
						$countries = "<option value=\"0\">---- None selected ----</option>\n";
						$ct_r = mysql_query("SELECT id,name,domain from countries ORDER BY name") or die;
						while ($ct_a = mysql_fetch_array($ct_r)) {
						  $countries .= "\t\t\t\t\t\t<option value=\"$ct_a[id]\"";
						  if ($dom == $ct_a["domain"])
						    $countries .= " SELECTED";
						  $countries .= ">$ct_a[name]</option>\n";
						}
						?>
						<?=$countries ?>
					</select>
				</td>
			</tr>
			<tr>
				<td>Gender:</td>
				<td>
					<input type="radio" name="gender" value="Male">Male
					&nbsp;&nbsp;
					<input type="radio" name="gender" value="Female">Female
				</td>
			</tr>
			<tr>
				<td>Preferred BitTorrent Client:</td>
				<td><input type="text" size="40" name="client"  maxlength="20" /></td>
			</tr>
			<tr>
				<td align="middle" colSpan="2">
                <input type="submit" value="Sign Up" />
              </td>
			</tr>
	</table>
</form>
<?
end_frame();
stdfoot();
?>
