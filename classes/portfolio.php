<?php
require_once('includes/db.php');

Class Portfolio {
  public function __construct() {
    global $ORACLE;
    $this->db = $ORACLE;
  }

  public function getByUser( $email ) {
    $stid = oci_parse($this->db, 'SELECT * FROM portfolio_portfolios WHERE owner=:email');
    oci_bind_by_name($stid, ':email', $email);
    $r = oci_execute($stid);
    $portfolios = array();
    while ($row = oci_fetch_array($stid, OCI_ASSOC+OCI_RETURN_NULLS)) {
      $portfolios[] = $row;
    }
    oci_free_statement($stid);
    return $portfolios;
  }

  public function fromRow( $row ) {
    $this->id = $row['ID'];
    $this->name = $row['NAME'];
    $this->owner = $row['OWNER'];
    $this->cash = $row['CASH_BALANCE'];
    //print_r($row);
  }

}

?>
