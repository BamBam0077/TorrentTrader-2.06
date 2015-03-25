<?
if (($site_config["INVITEONLY"] || $site_config["ENABLEINVITES"]) && $CURUSER) {
	$invites = $CURUSER["invites"];
	begin_block("" . INVITES . "");
	?>
	<table border="0" width="100%" cellspacing="0" cellpadding="0">
	<tr><td align="center"><? print("" . YOUHAVE . "\n"); ?> <?=$invites?> <? print("" . INVITES . "\n"); ?><br></td></tr>
	<?if ($invites > 0 ){?>
	<tr><td align="center"><a href=invite.php><? print("" . SENDANINVITE . "\n"); ?></a><br></td></tr>
	<?}?>
	</table>
	<?
	end_block();
}
?>