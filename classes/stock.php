<?php
require_once('includes/db.php');
require_once('includes/csv_explode.php');

class Stock {
  public function __construct($symbol=FALSE) {
    if($symbol) {
      $this->symbol = $symbol;
    }
  }

  public function init() {
    $this->getStats();
    $this->getQuote();
    $this->getPmv();
  }

  private function getPmv() {
    if( isset($this->shares) && isset($this->close) ) {
      $this->pmv = $this->shares * $this->close;
    }
  }


  private function extractQuoteData($quote) {
    // I don't think we're actually using this
    $matches = array();
    if(count($quote) < 8 ) {
      return false;
    }
    preg_match('/[\d\/]+/', $quote[2], $matches['date']);
    preg_match('/[\d.:]+/', $quote[3], $matches['time']); //time 
    preg_match('/[\d.:]+/', $quote[4], $matches['high']); // high
    preg_match('/[\d.:]+/', $quote[5], $matches['low']); // low
    preg_match('/[\d.:]+/', $quote[6], $matches['close']); // close
    preg_match('/[\d.:]+/', $quote[7], $matches['open']); // open
    preg_match('/[\d.:]+/', $quote[8], $matches['volume']); // volume
    $date = @$matches['date'][0];
    $time = @$matches['time'][0];
    $high = @$matches['high'][0];
    $low = @$matches['low'][0];
    $close = @$matches['close'][0];
    $open = @$matches['open'][0];
    if($date && $time && $high && $low ) {
      return array('date'=>$date,'time'=>$time,'high'=>$high,'low'=>$low,'close'=>$close,'open'=>$open);
    }
    return false;
  }


  public function getQuote() {
    list($name, $this->date, $this->low, $this->high, $this->open, $this->close) = csv_explode(',', file_get_contents('http://finance.yahoo.com/d/quotes.csv?s='.$this->symbol.'&f=nd1ghop'));
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
    $ret['cnt'] = $row[0];
    $ret['avg'] = $row[1];
    $ret['std'] = $row[2];
    $ret['min'] = $row[3];
    $ret['max'] = $row[4];
    $ret['cov'] = $ret['std']/$ret['avg'];
    
    $this->stats = $ret;
    return $ret;
  }

  public function fromRow($row) {
    // Initializes the stock values from a database query row
    $this->symbol = $row['SYMBOL'];
    $this->shares = $row['SHARES'];
    $this->cost_basis = $row['COST_BASIS'];
    $this->holder = $row['HOLDER'];
    $this->init();
  }
}
?>
