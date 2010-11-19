<?php
// Start the session
session_start();

// Print PHP errors
ini_set('display_errors', 1); 
ini_set('log_errors', 1); 
ini_set('error_log', dirname(__FILE__) . '/error_log.txt'); 
error_reporting(E_ALL);

// Use the Smarty templating engine (http://www.smarty.net/)
require('libs/Smarty.class.php');

// Include the classes used
require_once('classes/stock.php');
require_once('classes/user.php');

$page = ''; // the page the user is trying to access
$action = ''; // any action the user is trying to perform

// Fetch the users GET and POST requests
if(isset($_POST['a'])) {
  $action = $_POST['a'];
}
if(isset($_GET['a'])) {
  $action = $_GET['a'];
}

if(isset($_GET['p'])) {
  $page = $_GET['p'];
}

// Instantiate a user object
$user = new User();
if( !$user->loggedIn() ) {
  $page = 'login'; // send them to the login page
}

// See what action the user is trying to perform and respond accordingly
switch ($action) {
  case 'login': // The user is logging in (submitting a login form)
    if(isset($_POST['email']) && isset($_POST['password'])) {
      $user->login( $_POST['email'], $_POST['password'] );
    }
    $url = "http://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']."?p=overview";
    header("Location: " . $url);
    break;
  case 'logout': // The user is logging out
    $user->logout();
    $page = 'login';
    break;
  case 'register': // submitting a registration form
    if(isset($_POST['name']) && isset($_POST['email']) && isset($_POST['password'])) {
      $user->register($_POST['name'], $_POST['email'], $_POST['password']);
    }
    $page = 'overview';
    break;
}

// See what page the user is trying to access and display it
switch ($page ) {
  case 'stock':
    stockPage();
    break;

  case 'overview':
    overviewPage();
    break;

  case 'performance':
    performancePage();
    break;

  case 'login':
    loginPage();
    break;

  case 'trade':
    tradePage();
    break;

  case 'transactions':
    transactionsPage();
    break;

  default:
    $smarty = new Smarty;
    global $user;
    $smarty->assign('user', $user);
    $smarty->assign('portfolios', array( array('href'=>'#','name'=>'Portfolio 1') ) );
    $smarty->display('index.tpl');
    break;
}

function stockPage() {
  $portfolios = array( array('id'=>1,'name'=>'Portfolio 1') );
  global $user;
  $smarty = new Smarty;
  $symbol = 'AAPL';
  if(isset($_GET['stock'])) {
    $symbol = $_GET['stock'];
  }
  $stock = new Stock($symbol);
  $stock->getQuote();
  $smarty->assign('user', $user);
  //$smarty->assign('Email', $user->email);
  $smarty->assign('portfolios', $portfolios);
  //$stock = array('name'=>'AAPL', 'high'=>230, 'low'=>20, 'close'=>42, 'open'=> 53 );
  $smarty->assign('stock', $stock);
  $smarty->display('stock.tpl');
}

function overviewPage() {
  $portfolios = array( array('id'=>1,'name'=>'Portfolio 1') );
  global $user;
  $smarty = new Smarty;
  $F = array('symbol'=>'F','pmv'=>16.51,'volatility'=>'0.53','correlation'=>'42.2','open'=>16.75,'high'=>16.90,'low'=>16.52);
  $LUV = array('symbol'=>'LUV','pmv'=>18.01,'volatility'=>'3.51','correlation'=>'41.2','open'=>1.75,'high'=>1.90,'low'=>19.28);
  $SBUX = array('symbol'=>'SBUX','pmv'=>42.51,'volatility'=>'0.33','correlation'=>'32.1','open'=>49.75,'high'=>61.90,'low'=>14.02);

  $smarty->assign('user', $user);
  $smarty->assign('portfolios', $portfolios );
  $smarty->assign('stocks', array($F,$LUV,$SBUX));
  $smarty->display('overview.tpl');
}

function performancePage() {
  $portfolios = array( array('id'=>1,'name'=>'Portfolio 1') );
  $smarty = new Smarty;
  global $user;
  $smarty->assign('user', $user);
  $smarty->assign('portfolios', $portfolios );
  $smarty->display('performance.tpl');
}

function tradePage() {
  $portfolios = array( array('id'=>1,'name'=>'Portfolio 1') );
  $smarty = new Smarty;
  global $user;
  $smarty->assign('user', $user);
  $smarty->assign('portfolios', $portfolios );
  $smarty->display('trade.tpl');
}

function transactionsPage() {
  $portfolios = array( array('id'=>1,'name'=>'Portfolio 1') );
  global $user;
  $smarty = new Smarty;
  $smarty->assign('user', $user);
  $smarty->assign('portfolios', $portfolios );
  $smarty->display('transactions.tpl');
}

function loginPage() {
  $smarty = new Smarty;
  $smarty->display('login.tpl');
}

oci_close($ORACLE);
?>
