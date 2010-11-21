<?php
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

  public function fromRow($row) {
    // Initializes the stock values from a database query row
    $this->symbol = $row['SYMBOL'];
    $this->shares = $row['SHARES'];
    $this->cost_basis = $row['COST_BASIS'];
    $this->holder = $row['HOLDER'];
  }
}
?>
