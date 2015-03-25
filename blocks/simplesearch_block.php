<?
begin_block("".SEARCH."");
?>
	<CENTER>
	<form method="get" action="torrents-search.php"><br />
	<input type="text" name="search" size="15" value="<?= stripslashes(htmlspecialchars($searchstr)) ?>">
	<BR><BR>
	<input type="submit" value="<? print("" . SEARCH . "\n"); ?>" />
	</form>
	</CENTER>
	<?
end_block();
?>
