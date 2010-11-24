<?php
require_once('include/db.php');

global $ORACLE;

$query = "SELECT date, AVG(close) from StocksDaily GROUP BY date";

$result = mysql_query($query) or die(mysql_error());
$row = mysql_fetch_array($result, MYSQL_NUM);

foreach ($row as $ro) {

	$time = $ro[0];
	$average = $ro[1];

	$stid = oci_parse($ORACLE, 'INSERT INTO averagesDaily (time, average) VALUES (:time, :average)';
	oci_bind_by_name($stid, ':time', $time);
	oci_bind_by_name($stid, ':average', $average);
	$r = oci_execute($stid);
	oci_free_statement($stid);
}

oci_close($ORACLE);
?>

