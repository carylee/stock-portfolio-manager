<?php
ini_set('display_errors', 1); 
ini_set('log_errors', 1); 
ini_set('error_log', dirname(__FILE__) . '/error_log.txt'); 
error_reporting(E_ALL);

include('includes/db.php');

if(isset($_GET['s'])) {
  $symbol = $_GET['s'];
} else {
  die('No symbol provided');
}

function getHistoricalData($symbol) {
  $url = "http://www.google.com/finance/historical?q=$symbol&output=csv";
  $f = fopen($url,'r');
  $headrow = fgetcsv($f);
  $data = array();
  while( $row = fgetcsv($f) ) {
    $quote = array();
    foreach( $row as $index=>$item ) {
      $quote[$headrow[$index]] = $item;
    }
    $data[] = $quote;
  }
  fclose($f);
  return $data;
}
function formatTime($date) {
  return strtotime($date);
}

function addRecord($symbol, $data) {
  global $ORACLE;
  $time = formatTime($data['Date']);
  $stid = oci_parse($ORACLE, 'INSERT INTO portfolio_StocksDaily (symbol,time,open,close,high,low,volume) 
                              VALUES (:symbol,:time,:open,:close,:high,:low,:volume)');
  oci_bind_by_name($stid, ':symbol', $symbol);
  oci_bind_by_name($stid, ':time', $time);
  oci_bind_by_name($stid, ':open', $data['Open']);
  oci_bind_by_name($stid, ':close', $data['Close']);
  oci_bind_by_name($stid, ':high', $data['High']);
  oci_bind_by_name($stid, ':low', $data['Low']);
  oci_bind_by_name($stid, ':volume', $data['Volume']);
  $r = oci_execute($stid);
  oci_free_statement($stid);
  return $r;
}

function addData($symbol, $data) {
  foreach( $data as $record) {
    $r = addRecord($symbol, $record);
    if($r) {
      //print "Added data for $symbol on date " . $record['Date'] . "\n";
    } else {
      //print "Failed to add data for record on date " . $record['Date'] . "\n";
    }
  }
}


$data = getHistoricalData($symbol);
addData($symbol, $data);
print "<pre>";
print_r($data);
print "</pre>";
?>
