<?
//
//  TorrentTrader v2.x
//	This file was last updated: 21/June/2007
//	
//	http://www.torrenttrader.org
//
//
// Confirm account OK!
require_once("backend/functions.php");
dbconn();

if (!mkglobal("type"))
	die();

if ($type =="noconf"){//email conf is disabled?
	stdhead("" . ACCOUNT_ALREADY_CONFIRMED . "");
    begin_frame("Please now login");
	print("Please now login, there is no confirmation email to activate your account");
	end_frame();
	stdfoot();
	die();
	//welcome pm is not sent yet with this option
}

if ($type == "signup" && mkglobal("email")) {
	stdhead("" . ACCOUNT_USER_SIGNUP ."");
        begin_frame("" . ACCOUNT_SIGNUP_SUCCESS . "");
		if (!$site_config["ACONFIRM"]) {
			print("" . ACCOUNT_CONFIRM_SENT_TO_ADDY . " (" . htmlspecialchars($email) . "). " . ACCOUNT_CONFIRM_SENT_TO_ADDY_REST . " <br/ >");
		} else {
			print("" . ACCOUNT_CONFIRM_SENT_TO_ADDY . " (" . htmlspecialchars($email) . "). An admin needs to approve your account before you can use it <br/ >");
		}
	end_frame();
}
elseif ($type == "confirmed") {
	stdhead("" . ACCOUNT_ALREADY_CONFIRMED . "");
        begin_frame("" . ACCOUNT_ALREADY_CONFIRMED . "");
	print("" . ACCOUNT_ALREADY_CONFIRMED . "\n");
	end_frame();
}

//invite code
elseif ($type == "invite" && mkglobal("email")) {
stdhead("User invite");
     Begin_frame();
		Print("<CENTER>Invite successful!</CENTER><br><BR>A confirmation email has been sent to the address you specified (" . htmlspecialchars($email) . "). They need to read and respond to this email before they can use their account. If they don't do this, the new account will be deleted automatically after a few days.");
	End_frame();
stdfoot();
}//end invite code

elseif ($type == "confirm") {
	if (isset($CURUSER)) {
		stdhead("" . ACCOUNT_SIGNUP_CONFIRMATION . "");
		begin_frame("" . ACCOUNT_SUCCESS_CONFIRMED . "");
		print("" . ACCOUNT_ACTIVATED . " <a href=". $site_config["SITEURL"] ."/index.php>" . ACCOUNT_ACTIVATED_REST . "\n");
		print("" . ACCOUNT_BEFOR_USING . "" . $site_config["SITENAME"] . " " . ACCOUNT_BEFOR_USING_REST ."\n");
		end_frame();
	}
	else {
		stdhead("" . ACCOUNT_SIGNUP_CONFIRMATION . "");
		begin_frame("" . ACCOUNT_SUCCESS_CONFIRMED . "");
		print("" . ACCOUNT_ACTIVATED . "");
		end_frame();
	}
        //send welcome pm
    if ($site_config["WELCOMEPMON"])
    {
        $added = sqlesc(get_date_time());
        mysql_query("INSERT INTO messages (poster, sender, receiver, msg, added) VALUES('0', '0', '$id', '$WELCOMEPMMSG', '$added')");
    }//end welcome pm
}
else
	die();

stdfoot();
?>