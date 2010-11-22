<?php
require_once('includes/db.php');

class Stock {
  public function __construct($symbol=FALSE) {
    if($symbol) {
      $this->symbol = $symbol;
    }
  }

  public function getQuote() {
    list($name, $this->date, $this->low, $this->high, $this->open, $this->close) = explode(',', file_get_contents('http://finance.yahoo.com/d/quotes.csv?s='.$this->symbol.'&f=nd1ghop'));
    if(!isset($this->name)) $this->name = str_replace('"', '', $name);
  }


  public function getStats($opts=array()) {
    
    $field = 'close';
    $symbol = mysql_real_escape_string($this->symbol);

    if(isset($opts['field'])) {
      $field = mysql_real_escape_string($opts['field']); 
    }
    if(isset($opts['to'])) {
      $to = mysql_real_escape_string($opts['to']);
    }
    if(isset($opts['from'])) {
      $from = mysql_real_escape_string($opts['from']);
    }

    $query = "SELECT COUNT($field), AVG($field), STD($field), MIN($field), MAX($field) FROM StocksDaily WHERE symbol='$this->symbol'";
    if(isset($to)) {
      $query .= " AND date >= '$to'";
    }
    if(isset($from)) {
      $query .= " AND date <= '$to'";
    }

    $result = mysql_query($query) or die (mysql_error());
    $row = mysql_fetch_array($result);

    $ret = array();
    /*$ret['cnt'] = $row["count($field)"];
    $ret['avg'] = $row["avg($field)"];
    $ret['std'] = $row["std($field)"];
    $ret['min'] = $row["min($field)"];
    $ret['max'] = $row["max($field)"];*/
    $ret['cnt'] = $row[0];
    $ret['avg'] = $row[1];
    $ret['std'] = $row[2];
    $ret['min'] = $row[3];
    $ret['max'] = $row[4];
    $ret['cov'] = $ret['std']/$ret['avg'];
    
    return $ret;
  }

  public function fromRow($row) {
    // Initializes the stock values from a database query row
    $this->symbol = $row['SYMBOL'];
    $this->shares = $row['SHARES'];
    $this->cost_basis = $row['COST_BASIS'];
    $this->holder = $row['HOLDER'];
  }
}
?>
