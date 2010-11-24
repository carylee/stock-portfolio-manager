<?php
ini_set('display_errors', 1); 
ini_set('log_errors', 1); 
ini_set('error_log', dirname(__FILE__) . '/error_log.txt'); 
error_reporting(E_ALL);
require_once('../includes/db.php');
require_once('../includes/csv_explode.php');

global $ORACLE;
$symbol = $_GET['s'];

function getQuote($symbol) {
  $data = csv_explode(',', file_get_contents('http://finance.yahoo.com/d/quotes.csv?s='.$symbol.'&f=nd1ghopv'));
  list($name, $date, $low, $high, $open, $close, $volume) = $data;
  $date = (int) strtotime($date);
  $volume = (int) $volume;
  $high = (real) $high;
  $low = (real) $low;
  $open = (real) $open;
  $close = (real) $close;
  global $ORACLE;
  //$stid = oci_parse($ORACLE, 'SELECT * FROM portfolio_stocksDaily WHERE symbol=:symbol AND time=:time AND low=:low AND high=:high AND open=:open AND close=:close AND volume=:volume');
  //$query = "SELECT * FROM portfolio_stocksDaily WHERE symbol='$symbol' AND time='$time' AND low='$low' AND high='$high' AND open='$open' AND close='$close' AND volume='$volume'");
  $stid = oci_parse($ORACLE, 'INSERT INTO portfolio_stocksDaily (symbol,time,high,low,open,close,volume) VALUES (:symbol,:time,:high,:low,:open,:close,:volume)');
  oci_bind_by_name($stid, ':symbol', $symbol);
  oci_bind_by_name($stid, ':time', $date);
  oci_bind_by_name($stid, ':high', $high);
  oci_bind_by_name($stid, ':low', $low);
  oci_bind_by_name($stid, ':open', $open);
  oci_bind_by_name($stid, ':close', $close);
  oci_bind_by_name($stid, ':volume', $volume);
  $r = oci_execute($stid);
  //$row = oci_fetch_array($stid, OCI_ASSOC+OCI_RETURN_NULLS);
  //print_r(oci_error());
  //print $row;
  //print_r($row);
  if($r) { 
    print "Success!";
  } else {
    print "Failure.";
  }
  oci_free_statement($stid);
}


getQuote($symbol);
oci_close($ORACLE);
?>
