<?php
error_reporting(E_ALL);
$symbol = $_GET['s'];
$cmd = "perl /home/cel294/public_html/portfolio/time_series_symbol_project.pl $symbol 30 AR 16";
//$cmd = "./proxyChart.sh $symbol";
// This seems to do something strange, like cache the results of time_series. It 
// often uses old output instead of the output of the given command.

exec($cmd, $output) or die("It doesn't work");

$data = array();
foreach($output as $row) {
  list($_, $old, $new) = explode("\t", $row);
  if($new > 0) {
    $data[] = $new;
    //print $new . ",";
  }
}

function gchart($data) {
  $url = "http://chart.apis.google.com/chart?chs=440x220";
  $url .= "&cht=lc";
  $url .= "&chxt=x,y";
  $url .= "&chxr=0,0," . count($data) . "|1,".min($data).",".max($data);
  $url .= "&chds=".min($data).",".max($data);
  $url .= "&chd=t:" . implode($data, ',');
  //print $url;
  return $url;
}

print gchart($data);
?>
