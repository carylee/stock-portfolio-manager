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

<<<<<<< HEAD:classes/stock.php
  public function getStats($args) {
    
    $field = 'close';

    if(isset($args['field']) {
	$field = $args['field']; 
	}
    if(isset($args['to']) {
	$to = $args['to'];
	}
    if(isset($args['from'] {
	$from = $args['from'];
	}



    $field = mysql_real_escape_string($field);
    $symbol = mysql_real_escape_string($symbol);
    if(defined($to)) $to = mysql_real_escape_string($to);
    if(defined($from)) $from = mysql_real_escape_string($from);

    $query = "SELECT count('$field'), avg('$field'), std('$field'), min('$field'), min('$field'), max('$field') from StocksDaily where symbol='$symbol'";
    if(defined($to)) {
	$query .= " and date >= '$to'";
	}
    if(defined($from)) {
	$query .= " and date <= '$to'";
	}

    $result = mysql_query($query) or die (mysql_error());
    $row = mysql_fetch_array($result);

    $ret = array();
    $ret['cnt'] = $row['count('$field')'];
    $ret['avg'] = $row['avg('$field')'];
    $ret['std'] = $row['std('$field')'];
    $ret['min'] = $row['min('$field')'];
    $ret['max'] = $row['max('$field')'];
    $ret['cov'] = $ret['std']/$ret['avg'];
    
    return $ret;

=======
  public function fromRow($row) {
    // Initializes the stock values from a database query row
    $this->symbol = $row['SYMBOL'];
    $this->shares = $row['SHARES'];
    $this->cost_basis = $row['COST_BASIS'];
    $this->holder = $row['HOLDER'];
  }
}
>>>>>>> 9f909cc645b18d3437fda9c11a513a784a986b84:classes/stock.php
?>
