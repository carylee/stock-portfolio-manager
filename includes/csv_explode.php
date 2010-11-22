<?php
// Taken from comments of http://www.php.net/manual/en/function.explode.php
// php 5.3 supports a native form of this
function csv_explode($delim=',', $str, $enclose='"', $preserve=false){ 
  $resArr = array(); 
  $n = 0; 
  $expEncArr = explode($enclose, $str); 
  foreach($expEncArr as $EncItem){ 
    if($n++%2){ 
      array_push($resArr, array_pop($resArr) . ($preserve?$enclose:'') . $EncItem.($preserve?$enclose:'')); 
    }else{ 
      $expDelArr = explode($delim, $EncItem); 
      array_push($resArr, array_pop($resArr) . array_shift($expDelArr)); 
      $resArr = array_merge($resArr, $expDelArr); 
    } 
  } 
  return $resArr; 
}
?>
