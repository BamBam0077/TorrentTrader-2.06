<?
//
//  TorrentTrader v2.x
//	This file was last updated: 06/03/2009 by TorrentialStorm
//	
//	http://www.torrenttrader.org
//
//
require_once("backend/functions.php");
dbconn();

//check permissions
if ($site_config["MEMBERSONLY"]){
	loggedinonly();

	if($CURUSER["view_torrents"]=="no")
		show_error_msg("Error","You do not have permission to view torrents",1);
}

//get http vars
$addparam = "";
$wherea = array();
$wherea[] = "visible = 'yes'";
$thisurl = "torrents.php?";

if ($_GET["cat"]) {
	$wherea[] = "category = " . sqlesc($_GET["cat"]);
	$addparam .= "cat=" . urlencode($_GET["cat"]) . "&amp;";
	$thisurl .= "cat=".urlencode($_GET["cat"])."&";
}

if ($_GET["parent_cat"]) {
	$addparam .= "parent_cat=" . urlencode($_GET["parent_cat"]) . "&amp;";
	$thisurl .= "parent_cat=".urlencode($_GET["parent_cat"])."&";
	$wherea[] = "categories.parent_cat=".sqlesc($_GET["parent_cat"]);
}

$parent_cat = unesc($_GET["parent_cat"]);
$category = (int) $_GET["cat"];

$where = implode(" AND ", $wherea);
$wherecatina = array();
$wherecatin = "";
$res = mysql_query("SELECT id FROM categories");
while($row = mysql_fetch_assoc($res)){
    if ($_GET["c$row[id]"]) {
        $wherecatina[] = $row[id];
        $addparam .= "c$row[id]=1&amp;";
        $thisurl .= "c$row[id]=1&amp;";
    }
    $wherecatin = implode(", ", $wherecatina);
}

if ($wherecatin)
	$where .= ($where ? " AND " : "") . "category IN(" . $wherecatin . ")";

if ($where != "")
	$where = "WHERE $where";

if ($_GET["sort"] || $_GET["order"]) {

	switch ($_GET["sort"]) {
		case 'name': $sort = "torrents.name"; $addparam .= "sort=name&"; break;
		case 'times_completed':	$sort = "torrents.times_completed"; $addparam .= "sort=times_completed&"; break;
		case 'seeders':	$sort = "torrents.seeders"; $addparam .= "sort=seeders&"; break;
		case 'leechers': $sort = "torrents.leechers"; $addparam .= "sort=leechers&"; break;
		case 'comments': $sort = "torrents.comments"; $addparam .= "sort=comments&"; break;
		case 'size': $sort = "torrents.size"; $addparam .= "sort=size&"; break;
		default: $sort = "torrents.id";
	}

	if ($_GET["order"] == "asc" || ($_GET["sort"] != "id" && !$_GET["order"])) {
		$sort .= " ASC";
		$addparam .= "order=asc&";
	} else {
		$sort .= " DESC";
		$addparam .= "order=desc&";
	}

	$orderby = "ORDER BY $sort";

	}else{
		$orderby = "ORDER BY torrents.id DESC";
		$_GET["sort"] = "id";
		$_GET["order"] = "desc";
	}

//Get Total For Pager
$res = mysql_query("SELECT COUNT(*) FROM torrents LEFT JOIN categories ON category = categories.id $where $parent_check") or die(mysql_error());

$row = mysql_fetch_array($res);
$count = $row[0];

//get sql info
if ($count) {
	list($pagertop, $pagerbottom, $limit) = pager(20, $count, "torrents.php?" . $addparam);
	$query = "SELECT torrents.id, torrents.anon, torrents.announce, torrents.category, torrents.leechers, torrents.nfo, torrents.seeders, torrents.name, torrents.times_completed, torrents.size, torrents.added, torrents.comments, torrents.numfiles, torrents.filename, torrents.owner, torrents.external, torrents.freeleech, categories.name AS cat_name, categories.parent_cat AS cat_parent, categories.image AS cat_pic, users.username, users.privacy, IF(torrents.numratings < 2, NULL, ROUND(torrents.ratingsum / torrents.numratings, 1)) AS rating FROM torrents LEFT JOIN categories ON category = categories.id LEFT JOIN users ON torrents.owner = users.id $where $parent_check $orderby $limit";
	$res = mysql_query($query) or die(mysql_error());
}else{
	unset($res);
}


stdhead("" . BROWSE_TORRENTS . "");
begin_frame("" . BROWSE_TORRENTS . "");

// get all parent cats
echo "<CENTER><B>Categories:</B> ";
$catsquery = mysql_query("SELECT distinct parent_cat FROM categories ORDER BY parent_cat")or die(mysql_error());
echo " - <a href=torrents.php>Show All</a>";
while($catsrow = MYSQL_FETCH_ARRAY($catsquery)){
		echo " - <a href=torrents.php?parent_cat=".urlencode($catsrow['parent_cat']).">$catsrow[parent_cat]</a>";
}

?>
<BR><BR>
<form method="get" action="torrents.php">
<table class=bottom align="center">
<tr align='right'>
<?
$i = 0;
$cats = mysql_query("SELECT * FROM categories ORDER BY parent_cat, name");
while ($cat = mysql_fetch_assoc($cats)) {
    $catsperrow = 5;
    print(($i && $i % $catsperrow == 0) ? "</tr><tr align='right'>" : "");
    print("<td style=\"padding-bottom: 2px;padding-left: 2px\"><a class=catlink href=torrents.php?cat={$cat["id"]}>".htmlspecialchars($cat["parent_cat"])." - " . htmlspecialchars($cat["name"]) . "</a><input name=c{$cat["id"]} type=\"checkbox\" " . (in_array($cat["id"], $wherecatina) ? "checked " : "") . "value=1></td>\n");
    $i++;
}
echo "<tr align='center'><td colspan=$catsperrow align='center'><input type='submit' value='Go'></td></tr>";
echo "</tr></table></form>";

//if we are browsing, display all subcats that are in same cat
if ($parent_cat){
	$thisurl .= "parent_cat=".urlencode($parent_cat)."&";
	echo "<BR><BR><b>You are in:</b> <a href=torrents.php?parent_cat=".urlencode($parent_cat).">".htmlspecialchars($parent_cat)."</a><BR><B>Sub Categories:</B> ";
	$subcatsquery = mysql_query("SELECT id, name, parent_cat FROM categories WHERE parent_cat=".sqlesc($parent_cat)." ORDER BY name")or die(mysql_error());
	while($subcatsrow = MYSQL_FETCH_ARRAY($subcatsquery)){
		$name = $subcatsrow['name'];
		echo " - <a href=torrents.php?cat=$subcatsrow[id]>$name</a>";
	}
}

if (is_valid_id($_GET["page"]))
	$thisurl .= "page=$_GET[page]&";

echo "</CENTER><BR><BR>";//some spacing

/*
	echo "<div align=right><form action='torrents-search.php' name='jump' method='GET'>";
	echo "Sort By: <select name='sort' onChange='document.jump.submit();' style=\"font-family: Verdana; font-size: 8pt; border: 1px solid #000000; background-color: #CCCCCC\" size=\"1\">";
    echo "<option selected value='id'>Added</option>";
	echo "<option value='name'>Name</option>";
	echo "<option value='comments'>Comments</option>";
	echo "<option value='size'>Size</option>";
	echo "<option value='times_completed'>Completed</option>";
	echo "<option value='seeders'>Seeders</option>";
	echo "<option value='leechers'>Leechers</option>";
    echo "</select>&nbsp;";
    echo "<select name='order' onChange='document.jump.submit();' style=\"font-family: Verdana; font-size: 8pt; border: 1px solid #000000; background-color: #CCCCCC\" size=\"1\">";
    echo "<option selected value='asc'>Ascend</option>";
	echo "<option value='desc'>Descend</option>";
    echo "</select>";
    echo "</form>";
    echo "</div>";
************ OLD CODE */

// New code (TorrentialStorm)
	echo "<div align=right><form id='sort'>Sort By: <select name='sort' onChange='window.location=\"{$thisurl}sort=\"+this.options[this.selectedIndex].value+\"&order=\"+document.forms[\"sort\"].order.options[document.forms[\"sort\"].order.selectedIndex].value' style=\"font-family: Verdana; font-size: 8pt; border: 1px solid #000000; background-color: #CCCCCC\" size=\"1\">";
	echo "<option value='id'" . ($_GET["sort"] == "id" ? "selected" : "") . ">Added</option>";
	echo "<option value='name'" . ($_GET["sort"] == "name" ? "selected" : "") . ">Name</option>";
	echo "<option value='comments'" . ($_GET["sort"] == "comments" ? "selected" : "") . ">Comments</option>";
	echo "<option value='size'" . ($_GET["sort"] == "size" ? "selected" : "") . ">Size</option>";
	echo "<option value='times_completed'" . ($_GET["sort"] == "times_completed" ? "selected" : "") . ">Completed</option>";
	echo "<option value='seeders'" . ($_GET["sort"] == "seeders" ? "selected" : "") . ">Seeders</option>";
	echo "<option value='leechers'" . ($_GET["sort"] == "leechers" ? "selected" : "") . ">Leechers</option>";
	echo "</select>&nbsp;";
	echo "<select name='order' onChange='window.location=\"{$thisurl}order=\"+this.options[this.selectedIndex].value+\"&sort=\"+document.forms[\"sort\"].sort.options[document.forms[\"sort\"].sort.selectedIndex].value' style=\"font-family: Verdana; font-size: 8pt; border: 1px solid #000000; background-color: #CCCCCC\" size=\"1\">";
	echo "<option selected value='asc'" . ($_GET["order"] == "asc" ? "selected" : "") . ">Ascend</option>";
	echo "<option value='desc'" . ($_GET["order"] == "desc" ? "selected" : "") . ">Descend</option>";
	echo "</select>";
	echo "</form></div>";

// End

if ($count) {
	torrenttable($res);
	print($pagerbottom);
}else {
	show_error_msg("" . NOTHING_FOUND . "", "" . NO_UPLOADS . "",0);
}

if ($CURUSER)
	mysql_query("UPDATE users SET last_browse=".gmtime()." WHERE id=$CURUSER[id]");

end_frame();
stdfoot();
?>