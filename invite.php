<?php
require_once("backend/functions.php");
dbconn();
loggedinonly;

if (!$site_config["INVITEONLY"] && !$site_config["ENABLEINVITES"]) {
	show_error_msg("Invites are disabled.", "Invites are disabled. Please use the register link.", 1);
}


$users = get_row_count("users", "WHERE enabled = 'yes'");

if ($users >= $site_config["maxusers_invites"]) {
	print("Sorry, The current user account limit (" . number_format($site_config["maxusersinvites"]) . ") has been reached. Inactive accounts are pruned all the time, please check back again later...");
	end_frame();
	exit;
}

if ($CURUSER["invites"] == 0) {
	show_error_msg("You have no invites", "You don't have any invites. Invites are given automatically based on your ratio.", 1);
}

if ($_GET["take"]) {
	if (!validemail($email))
		show_error_msg("Error", "That doesn't look like a valid email.", 1);
		
	//check email isnt banned
	$maildomain = (substr($email, strpos($email, "@") + 1));
	$a = (@mysql_fetch_row(@mysql_query("select count(*) from email_bans where mail_domain='$email'"))) or die(mysql_error());
	if ($a[0] != 0)
		$message = "The e-mail address $email is Banned.";

	$a = (@mysql_fetch_row(@mysql_query("select count(*) from email_bans where mail_domain='$maildomain'"))) or die(mysql_error());
	if ($a[0] != 0)
		$message = "The e-mail address $email is Banned.";

	// check if email addy is already in use
	if (get_row_count("users", "WHERE email='$email'"))
		$message = "The email address '$email' is already in use.";
		
	if ($message)
		show_error_msg("Error", $message, 1);

	$secret = mksecret();
	$username = mksecret(40);
	$ret = mysql_query("INSERT INTO users (username, secret, email, status, invited_by, added) VALUES (".
	implode(",", array_map("sqlesc", array($username, $secret, $email, 'pending', $CURUSER["id"]))) . ",'" . get_date_time() . "')");
	if (!$ret) {
		// If username is somehow taken, keep trying
		while (mysql_errno() == 1062) {
			$username = mksecret(40);
			$ret = mysql_query("INSERT INTO users (username, secret, email, status, invited_by, added) VALUES (".
			implode(",", array_map("sqlesc", array($username, $secret, $email, 'pending', $CURUSER["id"]))) . ",'" . get_date_time() . "')");
		}
		show_error_msg("Error", "Database error. Please report this to an admin.", 1);
	}

	$id = mysql_insert_id();
	$invitees = "$id $CURUSER[invitees]";
	mysql_query("UPDATE users SET invites = invites - 1, invitees='$invitees' WHERE id = $CURUSER[id]");

	$psecret = md5($secret);

	$message = strip_tags($message);

	$body = <<<EOD
You have been invited to $site_config[SITENAME] by $CURUSER[username]. They have specified this address ($email) as your email.
If you do not know this person, please ignore this email. Please do not reply.

Message:
-------------------------------------------------------------------------------
$message
-------------------------------------------------------------------------------

This is a private site and you must agree to the rules before you can enter:

$site_config[SITEURL]/rules.php
$site_config[SITEURL]/faq.php


To confirm your invitation, you have to follow this link:

$site_config[SITEURL]/account-signup.php?invite=$id&secret=$psecret

After you do this, you will be able to use your new account. If you fail to
do this, your account will be deleted within a few days. We urge you to read
the RULES and FAQ before you start using $site_config[SITENAME].
EOD;
	mail($email, "$site_config[SITENAME] user registration confirmation", $body, "From: $site_config[SITENAME] <$SITEEMAIL>");

	header("Refresh: 0; url=account-confirm-ok.php?type=invite&email=" . urlencode($email));
	die;
}

stdhead("Invite");
begin_frame("Invite");
?>

<p>
<form method="post" action="invite.php?take=1">
<table border="0" cellspacing=0 cellpadding="3">
<tr valign=top><td align="right" class="heading"><B>Email Address:</B></td><td align=left><input type="text" size="40" name="email" />
<table width=250 border=0 cellspacing=0 cellpadding=0><tr><td class=embedded><font class=small>Please make sure this is a valid email address, the recipient will receive a confirmation email.</td></tr>
</font></td></tr></table>
<tr><td align="right" class="heading"><B>Message:</B></td><td align=left><textarea name="mess" rows="10" cols="80"></textarea>
</td></tr>
<tr><td colspan="2" align="center"><input type=submit value="Send Invite (PRESS ONLY ONCE)" style='height: 25px'></td></tr>
</table>
</form>
<?
end_frame();
stdfoot();

?>