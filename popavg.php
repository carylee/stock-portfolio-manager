<?php
require_once('includes/db.php');

global $ORACLE;

$query = "SELECT date, AVG(close) from StocksDaily GROUP BY date";

$result = mysql_query($query) or die(mysql_error());

while ($row = mysql_fetch_array($result, MYSQL_NUM)) {

	$time = $row[0];
	$average = $row[1];

	$stid = oci_parse($ORACLE, 'INSERT INTO averagesDaily (time, average) VALUES (:time, :average)';
	oci_bind_by_name($stid, ':time', $time);
	oci_bind_by_name($stid, ':average', $average);
	$r = oci_execute($stid);
	oci_free_statement($stid);
}

oci_close($ORACLE);
?>

