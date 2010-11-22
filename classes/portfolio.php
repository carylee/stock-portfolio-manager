<?php
require_once('includes/db.php');

Class Portfolio {
  public function __construct() {
    global $ORACLE;
    $this->db = $ORACLE;
  }

  public function init() {
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
    $this->getStocks();
  }

  public function getStocks() {
    $stid = oci_parse($this->db, 'SELECT * FROM portfolio_stocks WHERE holder=:id');
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

  public function covCorMatrix($symbols, ) {
	return -1;
  }
    

}

?>
