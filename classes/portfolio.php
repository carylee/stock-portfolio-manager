<?php
require_once('includes/db.php');
require_once('includes/json_encode.php');

Class Portfolio {
  public function __construct() {
    /*
     * Consructor
     * Makes the oracle connection established in db.php available
     */
    global $ORACLE;
    $this->db = $ORACLE;
  }

  public function init() {
    /*
     * Runs methods which initialize values of the object
     */
    $this->getStocks();
    $this->getTotalValue();
  }

  public function getById($id) {
    // Performs a query and initializes the object given a portfolio id
    $stid = oci_parse($this->db, 'SELECT * FROM portfolio_portfolios WHERE id=:id');
    oci_bind_by_name($stid, ':id', $id);
    $r = oci_execute($stid);
    $row = oci_fetch_array($stid, OCI_ASSOC+OCI_RETURN_NULLS);
    $this->fromRow($row);
    $this->init();
  }
    

  public function getByUser( $email ) {
    $stid = oci_parse($this->db, 'SELECT * FROM portfolio_portfolios WHERE owner=:email');
    oci_bind_by_name($stid, ':email', $email);
    $r = oci_execute($stid);
    $portfolios = array();
    while ($row = oci_fetch_array($stid, OCI_ASSOC+OCI_RETURN_NULLS)) {
      $portfolios[$row['ID']] = $row;
    }
    oci_free_statement($stid);
    return $portfolios;
  }

  public function fromRow($row) {
    // Initializes member properties from a database query
    // $row is an associative array returned from  oci_fetch_array()
    $this->id = $row['ID'];
    $this->name = $row['NAME'];
    $this->owner = $row['OWNER'];
    $this->cash = $row['CASH_BALANCE'];
  }

  public function getStocks() {
    // Runs a query to fetch all the stocks held by this portfolio
    // These stocks are instantiated as stock objects and stored
    // in the array $this->stocks
    $stid = oci_parse($this->db, 'SELECT * FROM portfolio_stocks WHERE holder=:id order by symbol');
    oci_bind_by_name($stid, ':id', $this->id);
    oci_execute($stid);
    $stocks = array();
    while ($row = oci_fetch_array($stid, OCI_ASSOC+OCI_RETURN_NULLS)) {
      $stock = new Stock;
      $stock->fromRow($row);
      $stocks[] = $stock;
    }
    oci_free_statement($stid);
    $this->stocks = $stocks;
    return $this->stocks;
  }

  public function delete() {
    //  Remove this portfolio from the database
    //  Returns a boolean of the success/failure of the query
    $stid = oci_parse($this->db, 'DELETE FROM portfolio_portfolios WHERE id=:id');
    oci_bind_by_name($stid, ':id', $this->id);
    $r = oci_execute($stid);
    oci_free_statement($stid);
    return $r;
  }

  public function create($owner,$name,$description,$initial_deposit) {
    /*
     * Add a new portfolio to the database
     * arguments:
     * owner (user's email address)
     * name (the name of the new portfolio)
     * description
     * initial_deposit
     * Returns a boolean of the success/failure of the query
     */
    $stid = oci_parse($this->db, 'INSERT INTO portfolio_portfolios (id, owner, name, description, cash_balance, creation_date) 
      VALUES(portfolio_ids.nextval, :owner, :name, :description, :deposit, :today)');
    oci_bind_by_name($stid, ':owner', $owner);
    oci_bind_by_name($stid, ':name', $name);
    oci_bind_by_name($stid, ':description', $description);
    oci_bind_by_name($stid, ':deposit', $initial_deposit);
    oci_bind_by_name($stid, ':today', time());
    $r = oci_execute($stid);
    oci_free_statement($stid);
    return $r;
  }

  public function covCorMatrix($opts=NULL) {
    /*
     * Calculate the covariance & correlation matricies
     * for this portfolio
     *
     * returns an associative array
     * $return['covar'] is Covariance
     * $return['corrc'] is Correlation
     *
     * optionally takes named arguments
     * $opts['field1'] - the first field to be used
     * $opts['field2'] - the second field to be used
     * $opts['from'] - start date of window (in unix time)
     * $opts['to'] - end date of window (in unix time)
     *
     * This function is basically translated from the provided perl script
     */

    if(!isset($this->stocks)) {
      $this->getStocks();
    }
    $symbols = $this->stocks;
    $field1 = 'close';
    $field2 = 'close';

    if(isset($opts['field1'])) {
      $field1 = $opts['field1'];
    }
    if(isset($opts['field2'])) {
      $field2 = $opts['field2'];
    }
    if(isset($opts['to'])) {
      $to = $opts['to'];
    }
    if(isset($opts['from'])) {
      $from = $opts['from'];
    }

    $covar = array();
    $corrc = array();
    foreach ($symbols as $outersym) {
      $sym1 = $outersym->symbol;
      $covar[$sym1] = array();
      $corrc[$sym1] = array();
      foreach ($symbols as $innersym) {
        $sym2 = $innersym->symbol;
        if($covcor = $this->getCovCorr($sym1,$sym2)) {
          // Check to see if this pair has cached values
          // If so, use those. If not, calculate them.
          $covariance = $covcor['COVAR'];
          $thiscorr = $covcor['CORR'];
        } else {
          //Grab all the mean/std of each stock pair in a join
          $query = "select count(*), avg(l.$field1), std(l.$field2), avg(r.$field2), std(r.$field2) from StocksDaily l join StocksDaily r on l.date=r.date where l.symbol='$sym1' and r.symbol='$sym2'";

          if(isset($to)) {
            $query .= " and date >= '$to'";
          }
      
          if(isset($from)) {
            $query .= " and date <= '$to'";
          }
   
          $result = mysql_query($query) or die (mysql_error());
          $row = mysql_fetch_array($result);

          list($count, $meanf1, $stdf1, $meanf2, $stdf2) = $row;
          mysql_free_result($result);


          if($count < 30) {
            $covar[$sym1][$sym2] = 'NODATA';
            $corrc[$sym1][$sym2] = 'NODATA';
          } else {
            $query = "select avg((l.$field1 - $meanf1)*(r.$field2 - $meanf2)) from StocksDaily l join StocksDaily r on l.date=r.date where l.symbol='$sym1' and r.symbol='$sym2'";
            if(isset($to)) {
              $query .= " and date >= '$to'";
            }
            if(isset($from)) {
              $query .= " and date <= '$to'";
            }

            $result = mysql_query($query) or die (mysql_error());
            $row = mysql_fetch_array($result, MYSQL_NUM);
            mysql_free_result($result);

            $covariance = $row[0];
            $thiscorr = $covariance / ($stdf1 * $stdf2);

            // Cache this stock pair and these values
            $this->cacheCovCorr($sym1,$sym2,$covariance,$thiscorr);
          }
        }
      $covar[$sym1][$sym2] = $covariance;
      $corrc[$sym1][$sym2] = $thiscorr;
      }
    }
    return array('covar'=>$covar, 'corrc'=>$corrc);
  }

  private function getCovCorr($symbol1, $symbol2) {
    /*
     * Looks up a pair of symbols in the covar_corr cache table
     * to see if their covariance and correlation have already been calculated.
     * If they have, return an object of the form array('COVAR'=>value,'CORR'=>value)
     * otherwise, return false (cache miss)
     */
    $stid = oci_parse($this->db, 'SELECT covar, corr FROM covar_corr WHERE symbol1=:symbol1 AND symbol2=:symbol2');
    oci_bind_by_name($stid, ':symbol1', $symbol1);
    oci_bind_by_name($stid, ':symbol2', $symbol2);
    $r = oci_execute($stid);
    $row = oci_fetch_array($stid, OCI_ASSOC+OCI_RETURN_NULLS);
    oci_free_statement($stid);
    if( count($row) > 0 ) {
      return $row;
    } else {
      return false;
    }
  }

  private function cacheCovCorr($symbol1, $symbol2, $cov, $corr) {
    /*
     * Cache a pair of stocks' calculated covariance and correlation
     * values are cached to the table covar_corr
     *
     * currently, caching does not support any of the options for covCorMatrix (field, from, to)
     * TODO: Add support for $opts
     */
    $stid = oci_parse($this->db, 'INSERT INTO covar_corr (symbol1, symbol2, covar, corr) VALUES (:symbol1, :symbol2, :covar, :corr)');
    oci_bind_by_name($stid, ':symbol1', $symbol1);
    oci_bind_by_name($stid, ':symbol2', $symbol2);
    oci_bind_by_name($stid, ':covar', $cov);
    oci_bind_by_name($stid, ':corr', $corr);
    $r = @oci_execute($stid);
  }

  private function shares($symbol) {
    // Checks the number of shares of a particular stock in this portfolio
    // given that stock's symbol.
    // If the stock is not in the portfolio, shares($symbol) = 0
    $shares = 0;
    foreach($this->stocks as $stock) {
      if( $symbol == $stock->symbol ) {
        $shares = $stock->shares;
      }
    }
    return $shares;
  }

  public function getStocksValue() {
    // Returns the current value of the stocks held by this portfolio
    // based on the quantity of each stock and each stock's last
    // close price.
    //
    // returns this value and sets $this->stocksValue
    $value = 0;
    foreach($this->stocks as $stock) {
      $value += $stock->close*$stock->shares;
    }
    $this->stocksValue = $value;
    return $value;
  }

  public function getTotalValue() {
    // Returns the total value of the portfolio
    // value of stocks + cash
    // Also sets $this->total
    $value = $this->getStocksValue();
    $value += $this->cash;
    $this->total = $value;
    return $value;
  }


  private function stock($symbol) {
    // Returns a stock object of a stock held by this portfolio
    // given its symbol
    foreach($this->stocks as $stock) {
      if($symbol == $stock->symbol) {
        return $stock;
      }
    }
  }

  public function buyStock( $symbol, $shares, $cost, $date=NULL ) {
    /* Adds $shares shares of stock $symbol to the portfolio
     * only if this portfolio has enough cash (at least $shares * $cost )
     * Decrement cash accordingly
     *
     * Date is currently unused.
     * TODO: Implement use of date
     */
    if(!$date) $date = time();
    $shares_before = $this->shares($symbol);
    $amount = $shares*$cost;
    $stid1 = oci_parse($this->db, 'UPDATE portfolio_portfolios SET cash_balance=(cash_balance - :amount) WHERE id=:id');
    oci_bind_by_name($stid1, ':amount', $amount);
    oci_bind_by_name($stid1, ':id', $this->id);
    $r1 = oci_execute($stid1, OCI_DEFAULT);
    if( $shares_before > 0 ) {
      $stock = $this->stock($symbol);
      $cost_basis = $stock->newCostBasis($shares, $cost);
      $stid2 = oci_parse($this->db, 'UPDATE portfolio_stocks SET shares=(shares + :shares), cost_basis=:cost_basis WHERE holder=:holder AND symbol=:symbol');
    } else {
      $stid2 = oci_parse($this->db, 'INSERT INTO portfolio_stocks (symbol, shares, cost_basis, holder) VALUES (:symbol, :shares, :cost_basis, :holder)');
      $cost_basis = $cost;
    }
    oci_bind_by_name($stid2, ':cost_basis', $cost_basis);
    oci_bind_by_name($stid2, ':symbol', $symbol);
    oci_bind_by_name($stid2, ':shares', $shares);
    oci_bind_by_name($stid2, ':holder', $this->id);
    $r2 = oci_execute($stid2, OCI_DEFAULT);
    if($r1 && $r2) {
      oci_commit($this->db);
    } else {
      oci_rollback($this->db);
    }
  }

  public function sellStock( $symbol, $shares, $cost, $date=NULL ) {
    // Sell $shares of stock $symbol at $cost (only if the portfolio has enough stock)
    // Increment cash accordingly
    //
    // $date is currently unused (defaults to time())
    // TODO: implement $date
    if(!$date) $date = time();
    $shares_remaining = $this->shares($symbol) - $shares;
    if($shares_remaining >= 0) {
      $stid1 = oci_parse($this->db, 'UPDATE portfolio_portfolios SET cash_balance=(cash_balance + :amount) WHERE id=:id');
      $amount = $shares*$cost;
      oci_bind_by_name($stid1, ':amount', $amount);
      oci_bind_by_name($stid1, ':id', $this->id);
      $r1 = oci_execute($stid1, OCI_DEFAULT);
      if( $shares_remaining > 0 ) {
        $stid2 = oci_parse($this->db, 'UPDATE portfolio_stocks SET shares=(shares - :shares) WHERE holder=:id AND symbol=:symbol');
        oci_bind_by_name($stid2, ':shares', $shares);
        oci_bind_by_name($stid2, ':id', $this->id);
        oci_bind_by_name($stid2, ':symbol', $symbol);
        $r2 = oci_execute($stid2, OCI_DEFAULT);
      } else {
        $stid2 = oci_parse($this->db, 'DELETE FROM portfolio_stocks WHERE holder=:id AND symbol=:symbol');
        oci_bind_by_name($stid2, ':shares', $shares);
        oci_bind_by_name($stid2, ':id', $this->id);
        oci_bind_by_name($stid2, ':symbol', $symbol);
        $r2 = oci_execute($stid2, OCI_DEFAULT);
      }
      if($r1 && $r2) {
        oci_commit($this->db);
      } else {
        oci_rollback($this->db);
      }
    }

  }

  public function cashTransaction($amount, $type) {
    // executes a withdrawl or deposit, changing the cash in the portfolio
    // $type expects 'DEPOSIT' or 'WITHDRAW'
    switch($type) {
      case 'DEPOSIT':
        $stid = oci_parse($this->db, 'UPDATE portfolio_portfolios SET cash_balance=(cash_balance + :amount) WHERE id=:id');
        break;
      case 'WITHDRAW':
        $stid = oci_parse($this->db, 'UPDATE portfolio_portfolios SET cash_balance=(cash_balance - :amount) WHERE id=:id');
        break;
    }
    oci_bind_by_name($stid, ':amount', $amount);
    oci_bind_by_name($stid, ':id', $this->id);
    $r = oci_execute($stid);
    oci_free_statement($stid);
    return $r;
  }

  public function deposit($amount) {
    // Wrapper function for cashTransaction(deposit)
    $this->cashTransaction($amount, 'DEPOSIT');
  }

  public function withdraw($amount) {
    // Wrapper function for cashTransaction(withdraw)
    $this->cashTransaction($amount, 'DEPOSIT');
  }

  public function pastPerformance() {
    /*
     * Calculate the cumulative past performance of the stocks in this portfolio
     * based on their historical close dates
     *
     * Returns a json object of form [{'date':unixtime,'close':value}]
     *
     * NOTE: this naiively only considers current holdings.
     * It retrospectively calculates what the value of current portfolio would have been
     * over the most recent year of historical data.
     *
     * it does NOT take into consideration changes in the portfolio over time
     */
    $data = array();
    foreach( $this->stocks as $stock) {
      $perf = $stock->pastPerformance();
        foreach( $perf as $time=>$value) {
          if(!isset($data[$time])) {
            $data[$time] = 0;
          }
          $data[$time] += $value;
        }
    }
    $fixeddata = array();
    foreach( $data as $time=>$value ) {
      // Restructure array for jsonencode
      // This could have been designed better
      // Todo: refactor
      $fixeddata[] = array('date'=>$time, 'close'=>$value);
    }
    $json = __json_encode($fixeddata);
    return $json;
  }

  public function getBeta($opts=array()) {
    // Calculate the beta for this portfolio

    if(!isset($this->stocks)) {
      $this->getStocks();
    }

    $stocks = $this->stocks;
    $runningBeta = 0;
    $totalshares = 0;

    foreach ($stocks as $s) {
      if(!isset($s->stats)) {
        $s->init();
      }
      $runningBeta += ($s->stats['BETA']) * ($s->shares);
      $totalshares += $s->shares;
    }

    $pBeta = $runningBeta/$totalshares;
    $this->beta = $pBeta;

    return $pBeta;
  }

  public function getGains() {
    if(!isset($this->stocks)) {
      $this->getStocks();
    }

    $pGains = 0;

    foreach ($stocks as $s) {
      if(!isset($s->stats)) {
        $s->init();
      }
      $pGains += $s->gains;
    }

    $this->gains = $pGains;
    return $pGains;
  }


  public function getROI() {
    // Calculate the ROI of this portfolio
    if(!isset($this->total)) {
      $this->getTotalValue();
    }
    if(!isset($this->gains)) {
      $this->getGains();
    }
    if(!isset($this->stocks)) {
      $this->getStocks();
    }

    $pCostBasis = 0;

    foreach ($stocks as $s) {
	    if(!isset($s->stats)) {
		    $s->init();
      }
      $pCostBasis += ($s->cost_basis)*($s->shares);
    }

    $ROI = $this->gains/$pCostBasis;

    $this->ROI = $ROI;
    return $ROI;
  }

  public function shannonRatchet($symbol, $cash, $cost) {
    /* 
     * Runs the shannon_ratchet.pl trading strategy and parses the result
     *
     * Returns a numbered array of just the values from the result
     */
    exec("/home/cel294/public_html/portfolio/shannon_ratchet.pl $symbol $cash $cost", $output);
    $data = array();
    foreach( $output as $value ) {
      preg_match('/\t+(.*)/',$value, $matches);
      $data[] = $matches[1];
    }
    return $data;
  }

}

?>
