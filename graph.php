<?php
require_once('includes/json_encode.php');
header("Content-type: application/JSON");

$stock = 'AAPL';
if(isset($_GET['stock'])) {
  $stock = $_GET['stock'];
}

$link = mysql_connect('localhost', 'cs339', 'cs339') or die('Could not connect: ' . mysql_error());
mysql_select_db('cs339') or die('Could not select database');

$query = "SELECT date,close FROM StocksDaily WHERE symbol='$stock' order by date desc";

$result = mysql_query($query) or die('Query failed: ' . mysql_error());

$data = array();
while ($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
  $data[] = $line;
}
mysql_free_result($result);
mysql_close($link);
print __json_encode($data);
?>
