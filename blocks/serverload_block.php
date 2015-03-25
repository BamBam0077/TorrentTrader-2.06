<?
//SERVER LOAD BLOCK
begin_block("".SYSLOAD."");

if (strtoupper(substr(PHP_OS, 0, 3)) == "WIN") {
	if (!class_exists("COM"))
		echo "COM support not available.";
	else {
		function mkprettytime2($s){
			foreach (array("60:sec","60:min","24:hour","1:day") as $x) {
				$y = explode(":", $x);
				if ($y[0] > 1) {
					$v = $s % $y[0];
					$s = floor($s / $y[0]);
				} else
					$v = $s;
				$t[$y[1]] = $v;
			}

			if ($t['week'] > 1 || $t['week'] == 0) $wk = " weeks";
			else $wk = " week";
			if ($t['day'] > 1 || $t['day'] == 0) $day = " days";
			else $day = " day";
			if ($t['hour'] > 1 || $t['hour'] == 0) $hr = " hrs";
			else $hr = " hr";
			if ($t['min'] > 1 || $t['min'] == 0) $min = " mins";
			else $min = " min";
			if ($t['sec'] > 1 || $t['sec'] == 0) $sec = " secs";
			else $sec = " sec";

			if ($t["month"])
				return "{$t['month']}$mth {$t['week']}$wk {$t['day']}$day ".sprintf("%d$hr %02d$min %02d$sec", $t["hour"], $t["min"], $t["sec"], $f["month"]);
			if ($t["week"])
				return "{$t['week']}$wk {$t['day']}$day ".sprintf("%d$hr %02d$min %02d$sec", $t["hour"], $t["min"], $t["sec"], $f["month"]);
			if ($t["day"])
				return "{$t['day']}$day ".sprintf("%d$hr %02d$min %02d$sec", $t["hour"], $t["min"], $t["sec"]);
			if ($t["hour"])
				return sprintf("%d$hr %02d$min %02d$sec", $t["hour"], $t["min"], $t["sec"]);
			if ($t["min"])
				return sprintf("%d$min %02d$sec", $t["min"], $t["sec"]);
			return $t["sec"].$sec;
		}

		if (version_compare(PHP_VERSION, '5.0.0', '<'))
			require("backend/serverload4.php");
		else
			require("backend/serverload5.php");
	}
} else {
	// Users and load information
	$reguptime = trim(exec("uptime"));
	if ($reguptime) {
		if (preg_match("/, *(\d) (users?), .*: (.*), (.*), (.*)/", $reguptime, $uptime)) {
			$users[0] = $uptime[1];
			$users[1] = $uptime[2];
			$loadnow = $uptime[3];
			$load15 = $uptime[4];
			$load30 = $uptime[5];
		  }
	} else {
		$users[0] = "NA";
		$users[1] = "--";
		$loadnow = "NA";
		$load15 = "--";
		$load30 = "--";
	}

	//echo("<b>Current Users:</b> $users[0]<br>
	echo("<b>Current Load:</b> $loadnow<br><b>Load 5 mins ago:</b> $load15<br><b>Load 15 mins ago:</b> $load30<br><hr>");

	// Operating system
	$fp = @fopen("/proc/version", "r");
	if ($fp) {
		$temp = fgets($fp);
		fclose($fp);

		if (preg_match("/version (.*?) /", $temp, $osarray)) {
			$kernel = $osarray[1];
			preg_match("/[0-9]{5,} (\((.* *)\)\))/", $temp, $osarray);
			$flavour = $osarray[2];
			$operatingsystem = $flavour." (".PHP_OS." ".$kernel.")";
			if (preg_match("/SMP/", $buf)) {
				$operatingsystem .= " (SMP)";
			}
		} else {
			$result = "(N/A)";
		}
	} else {
		$result = "(N/A)";
	}

	echo("<b>".OPERATING_SYSTEM.":</b><br>$operatingsystem");
}
end_block();
?>
