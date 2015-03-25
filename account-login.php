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

unset($returnto);
if (!empty($_GET["returnto"])) {
	$returnto = $_GET["returnto"];
	if (!$_GET["nowarn"]) {
		$message = "Sorry this page is only for members.";
	}
}

if (mkglobal("username:password")) {
	$password = md5($password);

	if (!empty($username) && !empty($password)) {
		$res = mysql_query("SELECT id, password, secret, status, enabled FROM users WHERE username = " . sqlesc($username) . "");
		$row = mysql_fetch_array($res);
	

		if (!$row)
			$message = "Username Incorrect";
		elseif ($row["status"] == "pending")
			$message = "Your account is currently pending, please check your email";
		elseif ($row["password"] != $password)
			$message = "Password Incorrect";
		elseif ($row["enabled"] == "no")
			$message = "This account has been disabled by an administrator.";
	} else
		$message = "Don't leave any required field blank.";

	if (!$message){
		logincookie($row["id"], $row["password"], $row["secret"]);
		if (!empty($_POST["returnto"])) {
			header("Refresh: 0; url=" . $_POST["returnto"]);
			die();
		}
		else {
			header("Refresh: 0; url=index.php");
			die();
		}
	}else{
		show_error_msg("Access Denied", $message,1);
	}
}

logoutcookie();

stdhead("Login");

begin_frame("" . LOGIN . "");

?>

<form method="post" action="account-login.php">
	<div align="center">
	<table border="0" cellpadding=5>
		<tr><td><B><?echo "" . USERNAME . "";?>:</B></td><td align=left><input type="text" size=40 name="username" /></td></tr>
		<tr><td><B><?echo "" . PASSWORD . "";?>:</B></td><td align=left><input type="password" size=40 name="password" /></td></tr>
		<tr><td colspan="2" align="center"><input type="submit" value="<?echo "" . LOGIN . "";?>" class=btn><BR><BR><i><?echo "" . COOKIES . "";?></i></td></tr>
	</table>
	</div>
<?

if (isset($returnto))
	print("<input type=\"hidden\" name=\"returnto\" value=\"" . htmlspecialchars($returnto) . "\" />\n");

?>

</form>
<p align="center"><a href="account-signup.php"><?echo "" . REGISTERNEW . "";?></a> | <a href="account-recover.php"><?echo "" . RECOVER_ACCOUNT . "";?></a></p>

<?
end_frame();
stdfoot();
?>