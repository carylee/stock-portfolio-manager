<?php
require_once('includes/db.php');

Class User {
  public function __construct() {
    // Makes the $ORACLE db handle available
    global $ORACLE;
    $this->db = $ORACLE;
  }

  private function init() {
    // Runs methods which initializes values of the user
    // Only makes sense to be run for authenticated users
    $this->getPortfolios();
  }

  public function logout() {
    // Logout
    // Persistent authentication is based on PHP Sessions (which use cookies)
    // so logging out requires unsetting those session variables
    unset($_SESSION['email']);
    unset($_SESSION['password']);
  }

  public function login($email, $password) {
    // Log the user in if the given email and password are valid
    // Here, password refers to the plaintext password
    $this->email = $email;
    $this->password = $this->securePassword($password);
    $this->authenticate();
    $this->remember();
  }

  private function securePassword($password) {
    // Takes a plaintext password
    // returns a salted hash of the password for use in the database
    $salt = "db9d3a016@$%H1*#098o";
    return str_replace(",", '', hash("sha256", $password . $salt));
  }

  private function authenticate() {
    // Determine wither this user exists in the database
    // returns boolean value
    $authenticated = false;
    if( isset($this->email) && isset($this->password) ) {
      // perform sql query to check if user exists
      $stid = oci_parse($this->db, 'SELECT count(*) FROM portfolio_users WHERE email=:email AND password=:password');
      oci_bind_by_name($stid, ':email', $this->email);
      oci_bind_by_name($stid, ':password', $this->password);
      $r = oci_execute($stid);
      $row = oci_fetch_array($stid, OCI_NUM);
      if( $row[0] ) {
        $authenticated = true;
        $this->init();
      }
    }
    return $authenticated;
  }

  public function getPortfolios() {
    // Get this users portfolios
    // Portfolios are returned and stored as an array in $this->portfolios
    $stid = oci_parse($this->db, 'SELECT * FROM portfolio_portfolios WHERE owner=:email order by id');
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
    // Get a particular portfolio belonging to this user by id
    // returns a portfolio object
    // If the user does not have that portfolio, return false
    foreach( $this->portfolios as $portfolio ) {
      if($portfolio->id == $id) {
        $portfolio->init();
        return $portfolio;
      }
    }
    return FALSE;
  }

  private function remember() {
    // Remembers a user by storing their 
    // email and (salted,hashed) password in the session
    $_SESSION['email'] = $this->email;
    $_SESSION['password'] = $this->password;
  }

  public function loggedIn() {
    // Checks to see if a user is logged in
    // returns a boolean
    if(isset($_SESSION['email']) && isset($_SESSION['password'])) {
      $this->email = $_SESSION['email'];
      $this->password = $_SESSION['password'];
    }
    return $this->authenticate();
  }

  public function register($name, $email, $password) {
    // Add a user to the database
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
    // Create a new portfolio
    $portfolio = new Portfolio;
    $portfolio->create($this->email,$name,$description,$initial_deposit);
  }
}
?>
