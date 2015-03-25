<?
begin_block("" . NAVIGATION . "");
//need to add members only check too
echo "- <a href=index.php>".HOME."</a><BR>";

if ($CURUSER["view_torrents"]=="yes" || !$site_config["MEMBERSONLY"]) echo "- <a href=torrents.php>".BROWSE_TORRENTS."</a><BR>";    
if ($CURUSER["view_torrents"]=="yes" || !$site_config["MEMBERSONLY"]) echo "- <a href=torrents-today.php>".TODAYS_TORRENTS."</a><BR>";    
if ($CURUSER["view_torrents"]=="yes" || !$site_config["MEMBERSONLY"]) echo "- <a href=torrents-search.php>".SEARCH."</a><BR>";    
if ($CURUSER["view_torrents"]=="yes" || !$site_config["MEMBERSONLY"]) echo "- <a href=torrents-needseed.php>".TORRENT_NEED_SEED."</a><BR>";
if ($CURUSER["edit_torrents"]=="yes") echo "- <a href=torrents-import.php>".MASS_TORRENT_IMPORT."</a><BR>";
echo "- <a href=teams-view.php>".TEAMS."</a><BR>";
echo "- <a href=rules.php>".SITE_RULES."</a><BR>";	
echo "- <a href=faq.php>".FAQ."</a><BR>";
end_block();
?>
