<?php
ini_set('display_errors', 1); 
ini_set('log_errors', 1); 
ini_set('error_log', dirname(__FILE__) . '/error_log.txt'); 
error_reporting(E_ALL);

require_once('includes/db.php');
print "Hello?";

global $ORACLE;

$query = "select date, avg(close) FROM StocksDaily WHERE (symbol='T' OR symbol='GE' OR symbol='AAPL' OR symbol='CSCO' OR symbol='K' OR symbol='MSFT' OR symbol='H' OR symbol='PG' OR symbol='COKE' OR symbol='F') GROUP BY date";

//$result = mysql_query($query) or die(mysql_error());
$result = mysql_query($query);
print_r($result);

while ($row = mysql_fetch_array($result, MYSQL_NUM)) {

	$time = $row[0];
	$average = $row[1];

	$stid = oci_parse($ORACLE, 'INSERT INTO averagesDaily (time, average) VALUES (:time, :average)');
	oci_bind_by_name($stid, ':time', $time);
	oci_bind_by_name($stid, ':average', $average);
	$r = oci_execute($stid);
	oci_free_statement($stid);
}

oci_close($ORACLE);
?>

