<?php
	function getstats() {
		require 'db.php';
		$con = mysql_connect($dbinfo['host'], $dbinfo['user'], $dbinfo['pass']) or die(mysql_error());
		$db = mysql_select_db($dbinfo['db'], $con) or die(mysql_error());

		$date = date("d F, Y"); //"01 Janurary, 2013"
		$finddate  = "SELECT * FROM `stats` WHERE `date` = '".$date."'";
		$search = mysql_query($finddate, $con);

		$trow  = "SELECT * FROM `stats` WHERE `id` = '1'";
		$tsearch = mysql_query($trow, $con);
		$tassoc = mysql_fetch_assoc($tsearch);

		$stats;

		if (mysql_num_rows($search) == 0) {
			$stats['today']['views'] = 0;
			$stats['today']['calls'] = 0;
			$stats['today']['downloads'] = 0;
			$stats['today']['uploads'] = 0;
		}else {
			$assoc = mysql_fetch_assoc($search);
			$stats['today']['views'] = $assoc['views'];
			$stats['today']['calls'] = $assoc['calls'];
			$stats['today']['downloads'] = $assoc['downloads'];
			$stats['today']['uploads'] = $assoc['uploads'];
		}

		$stats['total']['views'] = $tassoc['views'];
		$stats['total']['calls'] = $tassoc['calls'];
		$stats['total']['downloads'] = $tassoc['downloads'];
		$stats['total']['uploads'] = $tassoc['uploads'];

		echo json_encode($stats);
	}
	getstats();
?>