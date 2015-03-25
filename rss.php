<?php 
//
//  TorrentTrader v2.x
//	This file was last updated: 20/July/2007
//	
//	http://www.torrenttrader.org
//
//
//  Vars $cat = category id, $num = number to extract
//
//
// Validated at: http://feedvalidator.org
//
require_once("backend/functions.php");
dbconn(false); 

if ($custom){
	stdhead("Custom RSS XML Feed");
	begin_frame("Custom RSS XML Feed");

	$rqt = "SELECT id, name, parent_cat FROM categories ORDER BY sort_index, id";
	$resqt = mysql_query($rqt);
	$resqn = mysql_query($rqt);
	 
	?>
	What is RSS? Take a look at the <a href="http://wikipedia.org/wiki/RSS_%28file_format%29">Wiki</a> to <a href="http://wikipedia.org/wiki/RSS_%28file_format%29">learn more</a>.<br><br>

	<table border=1 cellpadding=0 cellspacing=0 width=95% class=table_table>
	<tr>
	<td class=table_head>Link To</td><td class=table_head>Category</td>
	</tr>
	<tr>
	<td class=table_col1><a href="<? echo "$site_config[SITEURL]/rss.php";?>"><? echo "$site_config[SITEURL]/rss.php";?></a></td><td class=table_col2 align=left>&nbsp;<b>All</b></td>
	</tr>
	<?
	while ($row = mysql_fetch_array($resqn))	{
		extract ($row);
		echo "<tr><td class=table_col1><a href=\"$site_config[SITEURL]/rss.php?cat=$id\">$site_config[SITEURL]/rss.php?cat=$id</td><td class=table_col2 align=left>&nbsp;<b>$parent_cat > $name</b></td></tr>\n";
	}
	?>
	</table>
	<br><br>
	<div align=left>
	Quick information regarding our RSS:
	<ul>
	<li>Our RSS feeds are properly validated by true RSS 2.0 XML Parsing Standards. Visit FeedValidator.org to validate.</li><BR>
	<li>Our feeds can display those items specifically by category ID. These too are limited to 15 results as default.<BR>EG: <a href=rss.php?cat=1>rss.php?cat=1</a> will show last 15 torrents from category id 1</li><BR>
	<li>Our feeds display only the latest 15 uploaded Torrents as default.</li><BR>
	<li>Our feed can be modified to give more results, up to a maximum of 50 items<BR>EG: <a href=rss.php?cat=1&num=50>rss.php?cat=1&num=50</a> will show last 50 torrents from category id 1</li>
	</ul>
	</div>
	<?
	end_frame();
	stdfoot();
	die();
}

$cat = (int)$_GET["cat"];
$num = (int)$_GET["num"];

// by category ? 
if (!$cat){ 
    $catvar ="WHERE visible='yes'";  //just show last visible
}else{
    $catvar ="WHERE categories.id ='$cat' AND visible='yes'"; 
}

if (!$num){
	$limit ="LIMIT 15";  //just show last 15 visible
}else{
	if ($num < 50){ //if num is less than 50 
		$limit ="LIMIT $num"; 
	}else{
		$limit ="LIMIT 50"; 
	}
}


// start the RSS feed output
header("Content-Type: application/xml"); 
echo("<?xml version=\"1.0\" encoding=\"iso-8859-1\"?>");
echo("<rss version=\"2.0\"><channel><generator>" . $site_config["SITENAME"] . " RSS 2.0</generator><language>en</language>" . 
"<title>" . $site_config["SITENAME"] . "</title><description>" . $site_config["SITENAME"] . " RSS Feed". ucfirst($cat)." Display</description><link>" . $site_config["SITEURL"] . "</link><copyright>Copyright " . $site_config["SITENAME"] . "</copyright><pubDate>".date("r")."</pubDate>"); 

$res = mysql_query("SELECT torrents.id, torrents.name, torrents.size, torrents.category, torrents.added, torrents.leechers, torrents.seeders, categories.name AS cat_name FROM torrents LEFT JOIN categories ON category = categories.id $catvar ORDER BY added DESC $limit");

while ($row = mysql_fetch_row($res)){ 
	list($id,$name,$size,$category,$added,$leechers,$seeders,$catname) = $row; 

	$link = "".$site_config["SITEURL"]."/torrents-details.php?id=$id&amp;hit=1"; 

	$cres = mysql_query("SELECT name, parent_cat FROM categories WHERE id = '$category'"); 
	$b = mysql_fetch_assoc($cres); 

    $pubdate = date("r", sql_timestamp_to_unix_timestamp($row["added"]));


	echo("<item><title>" . htmlspecialchars($name) . "</title><guid>" . $link . "</guid><link>" . $link . "</link><pubDate>" . $pubdate . "</pubDate>	<category> " . $b["parent_cat"] . ": " . $b["name"] . "</category><description>Category: " . $b["parent_cat"] . ": " . $b["name"] . "  Size: " . mksize($size) . " Added: " . $added . " Seeders: " . $seeders . " Leechers: " . $leechers . "</description></item>"); 
} 


echo("</channel></rss>"); 
 
