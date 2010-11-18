<?php
class Stock {
  public function __construct($symbol) {
    $this->symbol = $symbol;
  }

  public function getQuote() {
    list($name, $this->date, $this->low, $this->high, $this->open, $this->close) = explode(',', file_get_contents('http://finance.yahoo.com/d/quotes.csv?s='.$this->symbol.'&f=nd1ghop'));
    if(!isset($this->name)) $this->name = $name;
  }
}
?>
