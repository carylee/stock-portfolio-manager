<?php
require_once('includes/db.php');

Class User {
  public function __construct() {
    $this->authenticated = FALSE;
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
      global $ORACLE;
      $stid = oci_parse($ORACLE, 'SELECT count(*) FROM portfolio_users WHERE email=:email AND password=:password');
      oci_bind_by_name($stid, ':email', $this->email);
      oci_bind_by_name($stid, ':password', $this->password);
      $r = oci_execute($stid);
      $row = oci_fetch_array($stid, OCI_NUM);
      if( $row[0] ) {
        $authenticated = TRUE;
        $this->authenticated = TRUE;
      }
    }
    return $authenticated;
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
}
?>
