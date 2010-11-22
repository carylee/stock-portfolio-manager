<?php
require_once('includes/db.php');

Class User {
  public function __construct() {
    $this->authenticated = FALSE;
    global $ORACLE;
    $this->db = $ORACLE;
  }

  private function init() {
    $this->getPortfolios();
  }

  public function logout() {
    unset($_SESSION['email']);
    unset($_SESSION['password']);
    $this->authenticated = FALSE;
  }

  public function login($email, $password) {
    $this->email = str_replace(',', '', $email);
    $this->password = $this->securePassword($password);
    $this->authenticate();
    $this->remember();
  }

  public function securePassword($password) {
    $salt = "db9d3a016@$%H1*#098o";
    return str_replace(",", '', hash("sha256", $password . $salt));
  }

  private function authenticate() {
    $authenticated = FALSE;
    if( isset($this->email) && isset($this->password) ) {
      // perform sql query to check if user exists
      $stid = oci_parse($this->db, 'SELECT count(*) FROM portfolio_users WHERE email=:email AND password=:password');
      oci_bind_by_name($stid, ':email', $this->email);
      oci_bind_by_name($stid, ':password', $this->password);
      $r = oci_execute($stid);
      $row = oci_fetch_array($stid, OCI_NUM);
      if( $row[0] ) {
        $authenticated = TRUE;
        $this->authenticated = TRUE;
        $this->init();
      }
    }
    return $authenticated;
  }

  public function getPortfolios() {
    $stid = oci_parse($this->db, 'SELECT * FROM portfolio_portfolios WHERE owner=:email');
    oci_bind_by_name($stid, ':email', $this->email);
    $r = oci_execute($stid);
    $portfolios = array();
    while ($row = oci_fetch_array($stid, OCI_ASSOC+OCI_RETURN_NULLS)) {
      $portfolio = new Portfolio();
      $portfolio->fromRow($row);
      $portfolios[] = $portfolio;
    }
    oci_free_statement($stid);
    $this->portfolios = $portfolios;
    return $portfolios;
  }

  public function portfolio($id) {
    foreach( $this->portfolios as $portfolio ) {
      if($portfolio->id == $id) {
        return $portfolio;
      }
    }
    return FALSE;
  }

  public function ownsPortfolio($id) {
    return $this->portfolio($id);
  }

  private function remember() {
    $_SESSION['email'] = $this->email;
    $_SESSION['password'] = $this->password;
  }

  public function loggedIn() {
    if(isset($_SESSION['email']) && isset($_SESSION['password'])) {
      $this->email = $_SESSION['email'];
      $this->password = $_SESSION['password'];
    }
    return $this->authenticate();
  }
  public function register($name, $email, $password) {
    $this->email = $email;
    $this->password = $this->securePassword($password);
    $stid = oci_parse($this->db, 'INSERT INTO portfolio_users (name,email,password) VALUES(:name, :email, :password)');
    oci_bind_by_name($stid, ':name', $name);
    oci_bind_by_name($stid, ':email', $this->email);
    oci_bind_by_name($stid, ':password', $this->password);
    $r = oci_execute($stid);
    $this->remember();
    return $r;
  }
  public function createPortfolio($name, $description, $initial_deposit) {
    $portfolio = new Portfolio;
    $portfolio->create($this->email,$name,$description,$initial_deposit);
  }
}
?>
