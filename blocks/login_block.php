<?
if (!$CURUSER)

{

begin_block("" . Login . "");
?>
<table border=0 width=100% cellspacing=0 cellpadding=0>
	<tr><td>
		<form method=post action=account-login.php>
		<div align=center>
		<table border=0 cellpadding=1>
			<tr>
			<td align=center><font face=Verdana size=1><b><? print("" . USER_NAME . "\n"); ?>:</b></font></td>
			</tr><tr>
			<td align=center><input type=text size=12 name=username style="font-family: Verdana; font-size: 8pt; font-weight: bold; border-width: 1px; background-color: #C0C0C0" /></td>
			</tr><tr>
			<td align=center><font face=Verdana size=1><b><? print("" . PASSWORD . "\n"); ?>:</b></font></td>
			</tr><tr>
			<td align=center><input type=password size=12 name=password style="font-family: Verdana; font-size: 8pt; font-weight: bold; border-width: 1px; background-color: #C0C0C0" /></td>
			</tr><tr>
			<td align=center><input type=submit value=Login style="font-family: Verdana; font-size: 8pt; font-weight: bold; border-collapse: collapse; border-width: 1px"></td>
			</tr>
		</table>
		</td>
		</form>
		</tr>
	<tr>
<td align="center">[<a href="account-signup.php"><?echo "" . SIGNUP . "";?></a>]<br>[<a href="account-recover.php"><?echo "" . RECOVER_ACCOUNT . "";?></a>]</td> </tr>
	</table>
<?
end_block();

} else {

begin_block("$CURUSER[username]");

	$avatar = htmlspecialchars($CURUSER["avatar"]);
	if (!$avatar)
		$avatar = "".$site_config["SITEURL"]."/images/default_avatar.gif";

	$userdownloaded = mksize($CURUSER["downloaded"]);
	$useruploaded = mksize($CURUSER["uploaded"]);
	$privacylevel = $CURUSER["privacy"];

	if ($CURUSER["uploaded"] > 0 && $CURUSER["downloaded"] == 0)
		$userratio = "Inf.";
	elseif ($CURUSER["downloaded"] > 0)
		$userratio = number_format($CURUSER["uploaded"] / $CURUSER["downloaded"], 2);
	else
		$userratio = "---";

	print ("<center><img width=80 height=80 src=$avatar></center><br>" . DOWNLOADED . ": $userdownloaded<br>" . UPLOADED . ": $useruploaded<BR>".WORD_CLASS.": $CURUSER[level]<BR>" . ACCOUNT_PRIVACY_LV . ": $privacylevel<BR>". RATIO .": $userratio");

?>


<CENTER><a href="account.php"><? print("" . ACCOUNT . "\n"); ?></a> <br> 
<? if ($CURUSER["control_panel"]=="yes") {print("<a href=admincp.php>" . STAFFCP . "</a>");}?>
</CENTER>
<?
end_block();
}
?>