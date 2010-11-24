<?php
require_once('includes/db.php');
require_once('includes/csv_explode.php');

class Stock {
  public function __construct($symbol=FALSE) {
    if($symbol) {
      $this->symbol = $symbol;
    }
    global $ORACLE;
    $this->db = $ORACLE;
  }

  public function init() {
    $this->getStats();
    $this->getQuote();
    $this->getPmv();
    $this->getGains();
    $this->getROI();
  }

  private function getPmv() {
    if( isset($this->shares) && isset($this->close) ) {
      $this->pmv = $this->shares * $this->close;
    }
  }

  private function getGains() {
    if( isset($this->shares) && isset($this->cost_basis) && isset($this->pmv) ) {
	$this->gains = $this->pmv - ($this->cost_basis * $this->shares);
     }
  }

  private function getROI() {
    if( isset($this->gains) && isset($this->cost_basis)) {
	$this->ROI = $this->gains / $this->cost_basis;
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
    list($name, $this->date, $this->low, $this->high, $this->open, $this->close, $this->volume) = csv_explode(',', file_get_contents('http://finance.yahoo.com/d/quotes.csv?s='.$this->symbol.'&f=nd1ghopv'));
    if(!isset($this->name)) $this->name = str_replace('"', '', $name);
  }

  private function getStatsFromCache($opts=array()) {
    // retrieves the pre-calculated statistics for this stock from oracle
    isset($opts['field']) ? $field = $opts['field'] : $field = 'close';
    isset($opts['from']) ? $from = $opts['from'] : $from = NULL;
    isset($opts['to']) ? $from = $opts['from'] : $to = NULL;
    $query = 'SELECT * FROM stocks_stats WHERE symbol=:symbol AND field=:field';
    if(isset($to)) {
      $query .= ' AND from_date=:from';
    } else {
      $query .= ' AND to_date IS NULL';
    }
    if(isset($from)){
      $query .= ' AND to_date=:to';
    } else {
      $query .= ' AND to_date IS NULL';
    }
    $stid = oci_parse($this->db, $query);
    oci_bind_by_name($stid, ':symbol', $this->symbol);
    oci_bind_by_name($stid, ':field', $field);
    if(isset($to))
      oci_bind_by_name($stid, ':to', $to);
    if(isset($from))
      oci_bind_by_name($stid, ':from', $from);
    $r = oci_execute($stid);
    $row = oci_fetch_array($stid, OCI_ASSOC+OCI_RETURN_NULLS);
    if(isset($row['COUNT'])) {
      $ret = array();
      $ret['cnt'] = $row['COUNT'];
      $ret['avg'] = $row['AVERAGE'];
      $ret['std'] = $row['STD_DEV'];
      $ret['min'] = $row['MIN'];
      $ret['max'] = $row['MAX'];
      $ret['cov'] = $row['VOLATILITY'];
      $ret['vol'] = $row['VOLATILITY'];
      $ret['beta'] = $row['BETA'];
      return $ret;
    } else {
      return false;
    }
  }

  public function getStats($opts=array()) {
    $cachedStats = $this->getStatsFromCache($opts);
    //print (string) $cachedStats;
    if($cachedStats) {
      $this->stats = $cachedStats;
    } else {
      $stats = $this->calcStats($opts);
    }
  }

  private function cacheStats($ret, $field, $to=NULL, $from=NULL) {
    $stid = oci_parse($this->db, 'INSERT INTO stocks_stats (symbol, count, average, std_dev, min, max, volatility, beta, field, from_date, to_date)
                                  VALUES (:symbol, :count, :average, :std, :min, :max, :vol, :beta, :field, :fromdate, :todate)');
    oci_bind_by_name($stid, ':symbol', $this->symbol);
    oci_bind_by_name($stid, ':count', $ret['cnt']);
    oci_bind_by_name($stid, ':average', $ret['avg']);
    oci_bind_by_name($stid, ':std', $ret['std']);
    oci_bind_by_name($stid, ':min', $ret['min']);
    oci_bind_by_name($stid, ':max', $ret['max']);
    oci_bind_by_name($stid, ':vol', $ret['cov']);
    oci_bind_by_name($stid, ':beta', $ret['beta']);
    oci_bind_by_name($stid, ':field', $field);
    oci_bind_by_name($stid, ':fromdate', $from);
    oci_bind_by_name($stid, ':todate', $to);
    $r = @oci_execute($stid);
    oci_free_statement($stid);
  }

  private function calcStats($opts=array()) {
    $field = 'close';
    $symbol = mysql_real_escape_string($this->symbol);
    $to = NULL;
    $from = NULL;

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
    $optsbeta = array('field' => $field, 'to' => $to, 'from' => $from);
    $ret['beta'] = $this->getBeta($optsbeta);    
    $this->stats = $ret;
    $this->cacheStats($ret, $field, $to, $from);
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

  public function getBeta($opts=array()) {
    $field = 'close';
    $to = NULL;
    $from = NULL;

    if(isset($opts['field'])) {
      $field = mysql_real_escape_string($opts['field']);
    }
    if(isset($opts['to'])) {
      $to = mysql_real_escape_string($opts['to']);
    }
    if(isset($opts['from'])) {
      $from = mysql_real_escape_string($opts['from']);
    }

    $query = "SELECT (($field - $this->cost_basis)/$this->cost_basis) - AVG($field)) FROM StocksDaily WHERE symbol='$this->symbol'";
    if(isset($to)) {
      $query .= " AND date >= '$to'";
    }
    if(isset($from)) {
      $query .= " AND date <= '$to'";
    }

    $result = mysql_query($query) or die (mysql_error());
    $asset_vals = mysql_fetch_array($result, MYSQL_NUM);

    $query = "SELECT (average - AVG(average)) from averagesDaily WHERE 1=1";
    if(isset($to)) {
      $query .= " AND date >= ':to'";
    }
    if(isset($from)) {
      $query .= " AND date <= ':from'";
    }


    $stid = oci_parse($this->db, $query);
    if(isset($to)) {
	oci_bind_by_name($stid, ':to', $to);
    }
    if(isset($from)) {
    	oci_bind_by_name($stid, ':from', $from);
    }

    $r = oci_execute($stid);
    $market_vals = oci_fetch_array($r, OCI_NUM+ OCI_RETURN_NULLS);
    oci_free_statement($stid);

    $cov = 0;
    $var = 0;
    $count = count($asset_vals);

    for($i = 0; $i < $count; $i++) {
	$cov = ($asset_vals[$i] * $market_vals[$i]) / $count;
	$var = (pow($market_vals[$i], 2)) / $count;
    }

    return $cov/$var;
  }

  public function newCostBasis($shares, $cost) {
    //$total_shares = $this->shares + $shares;
    //$new_percent = $shares/$total_shares;
    //$old_percent = $this->shares / $total_shares;
    return ($this->shares * $this->cost_basis + $shares * $cost) / ($shares + $this->shares);
  }

  public function pastPerformance() {
    $stid = oci_parse($this->db, 'SELECT time,close FROM portfolio_StocksDaily WHERE symbol=:symbol');
    oci_bind_by_name($stid, ':symbol', $this->symbol);
    $r = oci_execute($stid);
    $data = array();
    while( $row = oci_fetch_array($stid, OCI_ASSOC+OCI_RETURN_NULLS) ) {
      $data[$row['TIME']] = $row['CLOSE'] * $this->shares;
    }
    return $data;
  }


}
?>
