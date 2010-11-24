<?php
$symbol = $_GET['s'];

$cmd = "perl time_series_symbol_project.pl " . $symbol . " 30 AR 16";

//print "Command: " . $cmd;
exec($cmd, $output);
//$raw = `$cmd`;
//$output = explode("\n", $raw);
print "<pre>";
print_r($output);
print "</pre>";

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
unset($output);
?>
