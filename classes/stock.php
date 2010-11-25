<?php
require_once('includes/db.php');
require_once('includes/csv_explode.php');

class Stock {
  public function __construct($symbol=FALSE) {
    /*
     * Stock objects can optionally be constructed by
     * passing their symbol to the constructor:
     * new Stock("SBUX");
     */
    if($symbol) {
      $this->symbol = $symbol;
    }
    // Make the db handle available
    global $ORACLE;
    $this->db = $ORACLE;
  }

  public function init() {
    // Runs methods which initialize member properties for this object
    $this->getStats();
    $this->getQuote();
    $this->getPmv();
    $this->getGains();
    $this->getROI();
    $this->getBeta();
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

  public function getQuote() {
    // Get a stock quote from Yahoo! Finance
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
      // 'cov' is depricated
      $ret['cov'] = $row['VOLATILITY'];
      $ret['vol'] = $row['VOLATILITY'];
      $ret['beta'] = $row['BETA'];
      return $ret;
    } else {
      return false;
    }
  }

  public function getStats($opts=array()) {
    // Gets the statistics for this stock
    // handles cache hits/misses
    $cachedStats = $this->getStatsFromCache($opts);
    if($cachedStats) {
      $this->stats = $cachedStats;
    } else {
      $stats = $this->calcStats($opts);
    }
  }

  private function cacheStats($ret, $field, $to=NULL, $from=NULL) {
    // Cache the stock statistics into the database stock_stats
    // to save time on calculation
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
    $r = @oci_execute($stid); // ignore error in the case of duplication
    oci_free_statement($stid);
  }

  private function calcStats($opts=array()) {
    // Calculate the statistics for this stock
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

    error_log($query);
    //$result = mysql_query($query) or die (mysql_error());
    $result = mysql_query($query);
    $row = mysql_fetch_array($result);

    $ret = array();
    $ret['cnt'] = $row[0];
    $ret['avg'] = $row[1];
    $ret['std'] = $row[2];
    $ret['min'] = $row[3];
    $ret['max'] = $row[4];
    $ret['cov'] = $ret['std']/$ret['avg'];
    $optsbeta = array('field' => $field, 'to' => $to, 'from' => $from);
    $ret['beta'] = $this->beta;    
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
    // Gets the Beta coefficient for this stock
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

    $query = "SELECT ((($field - $this->cost_basis)/$this->cost_basis) - AVG($field)) FROM StocksDaily WHERE symbol='$this->symbol'";
    if(isset($to)) {
      $query .= " AND date >= '$to'";
    }
    if(isset($from)) {
      $query .= " AND date <= '$to'";
    }

    $result = mysql_query($query) or die (mysql_error());
    $asset_vals = mysql_fetch_array($result, MYSQL_NUM);

    $query = "SELECT (average - (SELECT AVG(average) from averagesDaily)) from averagesDaily WHERE 1=1";
    if(isset($to)) {
      $query .= " AND date >= ':to'";
    }
    if(isset($from)) {
      $query .= " AND date <= ':from'";
    }

    $stid = oci_parse($this->db, $query);
    error_log($query);
    if(isset($to)) {
      oci_bind_by_name($stid, ':to', $to);
    }
    if(isset($from)) {
    	oci_bind_by_name($stid, ':from', $from);
    }

    $r = oci_execute($stid);
    $market_vals = oci_fetch_array($stid, OCI_NUM+ OCI_RETURN_NULLS);
    oci_free_statement($stid);

    $cov = 0;
    $var = 0;
    $count = count($asset_vals);

    for($i = 0; $i < $count; $i++) {
	$cov = ($asset_vals[$i] * $market_vals[$i]) / $count;
	$var = (pow($market_vals[$i], 2)) / $count;
    }
    $this->beta = $cov/$var;
    return $this->beta;
  }

  public function newCostBasis($shares, $cost) {
    // Returns what the newCostBasis for this stock would be
    // if $shares more shares were purchased at $cost
    return ($this->shares * $this->cost_basis + $shares * $cost) / ($shares + $this->shares);
  }

  public function pastPerformance() {
    // Returns the past performance of this stock.
    // This is done naiively using the current number of shares and the daily close price
    // over the past recent data in the database.
    //
    // This does NOT take into consideration changes in the share quantity, but
    // only uses the number of current shares
    //
    // Returns a array of form $data[unixtime] = value
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
