<?
if (!$site_config["MEMBERSONLY"] || $CURUSER) {
begin_block("Latest Posters");
	$news = mysql_query("SELECT id, name, added, image1, image2 FROM torrents WHERE banned = 'no' AND visible='yes'");

	if (mysql_num_rows($news) > 0) {

		print("<table align=center cellpadding=0 cellspacing=0 width=100% border=0>");

		while ($row2 = mysql_fetch_array($news, MYSQL_NUM)) {
			$tor = $row2['0'];
			$altname = $row2['1'];
			$date_time=get_date_time(time()-(3600*48)); // the 24 is the hours you want listed change by whatever you want
			$orderby = "ORDER BY torrents.id DESC"; //Order

			$limit = "LIMIT 5"; //Limit

			$where = "WHERE banned = 'no' AND visible='yes' AND torrents.id='$tor'";

			$res = mysql_query("SELECT torrents.id, torrents.image1, torrents.image2, torrents.added, categories.name AS cat_name FROM torrents LEFT JOIN categories ON torrents.category = categories.id $where AND torrents.added >='$date_time' $orderby $limit");
			$row = mysql_fetch_array($res);
			$cat = $row['cat_name'];

			$img1 = "<a href='$site_config[SITEURL]/torrents-details.php?id=$row[id]'><img border='0' src='uploads/images/$row[image1]' alt=\"$altname / $cat\" width=100></a>";

			if ($row["image1"] != ""){
				print("<tr><td align=center>". $img1 ."<BR></td></tr>");
			}
		}

		print("</table>");

	}
end_block();
}
?>