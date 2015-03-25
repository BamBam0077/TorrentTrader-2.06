</TD><!-- END MAIN CONTENT AREA -->

<? if ($site_config["RIGHTNAV"]){ ?>
<!-- RIGHT COLUMN -->
<TD vAlign="top" width="170">
<?rightblocks();?>
</TD>
<!-- END RIGHT COLUMN -->
<?}?>

</TR>
</TABLE>	

<BR><BR><BR>
<?
//
// *************************************************************************************************************************************
//			PLEASE DO NOT REMOVE THE POWERED BY LINE, SHOW SOME SUPPORT! WE WILL NOT SUPPORT ANYONE WHO HAS THIS LINE EDITED OR REMOVED!
// *************************************************************************************************************************************
print ("<CENTER><BR>Powered by TorrentTrader v".$site_config["ttversion"]."<br>");
$totaltime = array_sum(explode(" ", microtime())) - $GLOBALS['tstart'];
printf("Page generated in %f", $totaltime);
print ("<br><a href=\"http://www.torrenttrader.org\" target=\"_blank\">www.torrenttrader.org</a><BR><a href=rss.php><img src=".$site_config["SITEURL"]."/images/icon_rss.gif border=0></a> <a href=rss.php>RSS Feed</a> - <a href=rss.php?custom=1>Feed Info</a></CENTER>");
//
// *************************************************************************************************************************************
//			PLEASE DO NOT REMOVE THE POWERED BY LINE, SHOW SOME SUPPORT! WE WILL NOT SUPPORT ANYONE WHO HAS THIS LINE EDITED OR REMOVED!
// *************************************************************************************************************************************

?>
<BR><BR>
</BODY>
</HTML>
<?
ob_end_flush();
?>