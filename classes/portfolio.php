<?php
require_once('includes/db.php');
require_once('includes/json_encode.php');

/*function pr($data) {
  print "<pre>";
  print_r($data);
  print "</pre>";
}*/

Class Portfolio {
  public function __construct() {
    global $ORACLE;
    $this->db = $ORACLE;
  }

  public function init() {
    $this->getStocks();
  }

  public function getById($id) {
    $stid = oci_parse($this->db, 'SELECT * FROM portfolio_portfolios WHERE id=:id');
    oci_bind_by_name($stid, ':id', $id);
    $r = oci_execute($stid);
    $row = oci_fetch_array($stid, OCI_ASSOC+OCI_RETURN_NULLS);
    $this->fromRow($row);
    $this->getStocks();
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

  public function fromRow( $row ) {
    $this->id = $row['ID'];
    $this->name = $row['NAME'];
    $this->owner = $row['OWNER'];
    $this->cash = $row['CASH_BALANCE'];
    //print_r($this);
  }

  public function getStocks() {
    $stid = oci_parse($this->db, 'SELECT * FROM portfolio_stocks WHERE holder=:id order by symbol');
    oci_bind_by_name($stid, ':id', $this->id);
    $r = oci_execute($stid);
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
    $stid = oci_parse($this->db, 'DELETE FROM portfolio_portfolios WHERE id=:id');
    oci_bind_by_name($stid, ':id', $this->id);
    $r = oci_execute($stid);
    oci_free_statement($stid);
  }

  public function create($owner,$name,$description,$initial_deposit) {
    print "Owner is: $owner";
    $stid = oci_parse($this->db, 'INSERT INTO portfolio_portfolios (id, owner, name, description, cash_balance, creation_date) 
      VALUES(portfolio_ids.nextval, :owner, :name, :description, :deposit, :today)');
    oci_bind_by_name($stid, ':owner', $owner);
    oci_bind_by_name($stid, ':name', $name);
    oci_bind_by_name($stid, ':description', $description);
    oci_bind_by_name($stid, ':deposit', $initial_deposit);
    oci_bind_by_name($stid, ':today', time());
    $r = oci_execute($stid);
    //print $r;
    oci_free_statement($stid);
    return $r;
  }

  public function covCorMatrix($opts=NULL) {

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
          $covariance = $covcor['COVAR'];
          $thiscorr = $covcor['CORR'];
        } else {
          #Grab all the mean/std of each stock pair in a join
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
    $stid = oci_parse($this->db, 'INSERT INTO covar_corr (symbol1, symbol2, covar, corr) VALUES (:symbol1, :symbol2, :covar, :corr)');
    oci_bind_by_name($stid, ':symbol1', $symbol1);
    oci_bind_by_name($stid, ':symbol2', $symbol2);
    oci_bind_by_name($stid, ':covar', $cov);
    oci_bind_by_name($stid, ':corr', $corr);
    $r = @oci_execute($stid);
  }

  private function shares($symbol) {
    $shares = 0;
    foreach($this->stocks as $stock) {
      if( $symbol == $stock->symbol ) {
        $shares = $stock->shares;
      }
    }
    return $shares;
  }

  private function stock($symbol) {
    foreach($this->stocks as $stock) {
      if($symbol == $stock->symbol) {
        return $stock;
      }
    }
  }

  public function buyStock( $symbol, $shares, $cost, $date=NULL ) {
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


  public function deposit( $amount ) {
    $this->cashTransaction($amount, 'DEPOSIT');
  }

  public function withdraw( $amount ) {
    $this->cashTransaction($amount, 'DEPOSIT');

  }

  public function pastPerformance() {
    $data = array();
    foreach( $this->stocks as $stock) {
      //print_r($stock->pastPerformance());
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
      $fixeddata[] = array('date'=>$time, 'close'=>$value);
    }
    $json = __json_encode($fixeddata);
    return $json;
  }

  public function getBeta($opts=array()) {
    $field1 = 'close';

    if(isset($opts['field1'])) {
      $field1 = mysql_real_escape_string($opts['field1']);
    }

    if(isset($opts['to'])) {
      $to = mysql_real_escape_string($opts['to']);
    }
    if(isset($opts['from'])) {
      $from = mysql_real_escape_string($opts['from']);
    }
  }
}

?>
