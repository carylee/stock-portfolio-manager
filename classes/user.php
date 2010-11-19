<?php

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

  private function securePassword($password) {
    $salt = "db9d3a016@$%H1*#098o";
    return = str_replace(",", '', hash("sha256", $password . $salt));
  }

  private function authenticate() {
    if( $this->email && $this->password ) {
      // perform sql query to check if user exists
      $this->authenticated = TRUE;
      return rand(0,1);
    }
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
