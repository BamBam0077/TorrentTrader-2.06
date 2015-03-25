<?
if (!$site_config["MEMBERSONLY"] || $CURUSER) {
begin_block("Advance Search");
?>
	<CENTER>
	<form method="get" action="torrents-search.php"><br />
	<input type="text" name="search" size="15" value="<?= stripslashes(htmlspecialchars($searchstr)) ?>">
	<BR><BR>
	<select name="cat">
	<option value="0">(All types)</option>
	<?


	$cats = genrelist();
	$catdropdown = "";
	foreach ($cats as $cat) {
		$catdropdown .= "<option value=\"" . $cat["id"] . "\"";
		if ($cat["id"] == $_GET["cat"])
			$catdropdown .= " selected=\"selected\"";
		$catdropdown .= ">" . htmlspecialchars($cat["parent_cat"]) . ": " . htmlspecialchars($cat["name"]) . "</option>\n";
	}
	?>
	<?= $catdropdown ?>
	</select>
	<BR><BR>
	<select name=incldead>
	<option value="0">Active</option>
	<option value="1">Include Dead</option>
	<option value="2">Only Dead</option>
	</select>
	<?if ($site_config["ALLOWEXTERNAL"]){?>
		<BR><BR>
		<select name=inclexternal>
		<option value="0">Local/External</option>
		<option value="1">Local Only</option>
		<option value="2">External Only</option>
		</select>
		<? } ?>
	<BR><BR>
	<input type="submit" value="<? print("" . SEARCH . "\n"); ?>" />
	</form>
	</CENTER>
	<?
end_block();
}
?>
