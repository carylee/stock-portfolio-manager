<?php
require_once('includes/json_encode.php');
require_once('includes/db.php');
header("Content-type: application/JSON");

if(isset($_GET['symbol'])) {
  $stock = $_GET['symbol'];
} else {
  die('Stock not provided');
}

$query = "SELECT date,close FROM StocksDaily WHERE symbol='$stock' order by date desc";

$result = mysql_query($query) or die('Query failed: ' . mysql_error());

$stid = oci_parse($ORACLE, 'SELECT time,close FROM portfolio_StocksDaily WHERE symbol=:symbol order by time desc');
oci_bind_by_name($stid, ':symbol', $stock);
$r = oci_execute($stid);

$data = array();
while ($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
  $data[] = $line;
}
while ($r && $row = oci_fetch_array($stid, OCI_ASSOC+OCI_RETURN_NULLS)) {
  $item = array('date'=>$row['TIME'], 'close'=>$row['CLOSE']);
  $data[] = $item;
}
oci_free_statement($stid);
  
mysql_free_result($result);
print __json_encode($data);
oci_close($ORACLE);
?>
