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
require_once('classes/portfolio.php');

// Function for debugging:
function pr($data){
  echo "<pre>";
  print_r($data);
  echo "</pre>";
}

$page = 'overview'; // the page the user is trying to access
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
if( !$user->loggedIn() && !$page=='portfolio-json' && !$page=='getcost' ) {
  $page = 'login'; // send them to the login page
}
$portfolios = $user->getPortfolios();

// See what action the user is trying to perform and respond accordingly
switch ($action) {
  case 'login': // The user is logging in (submitting a login form)
    if(isset($_POST['email']) && isset($_POST['password'])) {
      $user->login( $_POST['email'], $_POST['password'] );
    }
    $url = BASEURL . "?p=overview";
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
    $url = BASEURL . "?p=edit-portfolios";
    header("Location: " . $url);
    break;

  case 'delete-portfolio':
    if(isset($_GET['id']) && $portfolio = $user->portfolio($_GET['id'])) {
      $portfolio->delete();
    }
    break;

  case 'create-portfolio':
    if(isset($_POST['name']) && isset($_POST['description'])) {
      $deposit = 0;
      if(isset($_POST['deposit'])) {
        $deposit = $_POST['deposit'];
      }
      $user->createPortfolio($_POST['name'],$_POST['description'], $deposit);
    }
    $url = BASEURL . "?p=edit-portfolios";
    header("Location: $url");
    break;

  case 'buy':
    if(isset($_POST['symbol']) && isset($_POST['shares']) && isset($_POST['cost']) && isset($_POST['portfolio'])) {
      $portfolio_id = $_POST['portfolio'];
      $symbol = $_POST['symbol'];
      $shares = $_POST['shares'];
      $cost = $_POST['cost'];
      $date = $_POST['date'];
      if( $date ) {
        strtotime($date);
      } else {
        $date = time();
      }
      $portfolio = $user->portfolio($portfolio_id);
      $portfolio->buyStock($symbol, $shares, $cost, $date);
    }
    header('Location: ' . BASEURL . "?p=overview&id=$portfolio_id");
    break;

  case 'sell':
    if(isset($_POST['symbol']) && isset($_POST['shares']) && isset($_POST['cost']) && isset($_POST['portfolio'])) {
      $portfolio_id = $_POST['portfolio'];
      $symbol = $_POST['symbol'];
      $symbol = preg_replace('/[^A-Za-z]/','',$symbol);
      $shares = $_POST['shares'];
      $cost = $_POST['cost'];
      $date = $_POST['date'];
      if( $date ) {
        strtotime($date);
      } else {
        $date = time();
      }
      $portfolio = $user->portfolio($portfolio_id);
      $portfolio->sellStock($symbol, $shares, $cost, $date);
    }
    header('Location: ' . BASEURL . "?p=overview&id=$portfolio_id");
    break;

  case 'deposit':
    initCashTransaction($_POST, 'DEPOSIT', $user);
    break;

  case 'withdraw':
    initCashTransaction($_POST, 'WITHDRAW', $user);
    break;

  case 'trade-search':
    /*if(isset($_POST['symbol']) && isset($_POST['cash']) && isset($_POST['cost'])) {
      tradeSearch($_POST['symbol'], $_POST['cash'], $_POST['cost'], $user);
    }*/
    break;

}

// See what page the user is trying to access and display it
switch ($page ) {
  case 'stock':
    checkLogin($user);
    stockPage($user);
    break;

  case 'overview':
    checkLogin($user);
    overviewPage($user);
    break;

  case 'performance':
    checkLogin($user);
    performancePage($user);
    break;

  case 'edit-portfolios':
    checkLogin($user);
    edit_portfoliosPage($user);
    break;

  case 'portfolio-json':
    if(isset($_GET['id'])) {
      $portfolio = $user->portfolio($_GET['id']);
      printPortfolioJSON($user, $portfolio);
    }
    break;

  case 'login':
    loginPage();
    break;

  case 'trade':
    checkLogin($user);
    $tradeData = array();
    if(isset($_GET['a']) && isset($_GET['symbol']) && isset($_GET['cash']) && isset($_GET['cost'])) {
      $tradeData = tradeSearch($_GET['symbol'], $_GET['cash'], $_GET['cost'], $user);
    }
    tradePage($user, $tradeData);
    break;

  case 'getcost':
    if(isset($_GET['s'])) {
      $symbol = $_GET['s'];
      $stock = new Stock($symbol);
      $stock->getQuote();
      if($stock->close != 'N/A')
        print $stock->close;
    }
    break;

  case 'transactions':
    checkLogin($user);
    transactionsPage($user);
    break;

  default:
    $smarty = new Smarty;
    $smarty->assign('user', $user);
    $smarty->assign('portfolios', array( array('href'=>'#','name'=>'Portfolio 1') ) );
    $smarty->display('index.tpl');
    break;
}

function stockPage($user) {
  $smarty = new Smarty;
  $symbol = 'AAPL';
  if(isset($_GET['stock'])) {
    $symbol = $_GET['stock'];
    $symbol = preg_replace('/[^A-Za-z]/', '', $symbol);
  }
  $stock = new Stock($symbol);
  $stock->getQuote();
  $smarty->assign('user', $user);
  $smarty->assign('stock', $stock);
  $smarty->display('stock.tpl');
}

function overviewPage($user) {
  if(isset($_GET['id'])) {
    $id = $_GET['id'];
  } else {
    $id = $user->portfolios[0]->id;
  }
  $smarty = new Smarty;
  $portfolio = $user->portfolio($id);
  $smarty->assign('user', $user);
  $smarty->assign('portfolio', $portfolio);
  $smarty->assign('stocks', $portfolio->stocks);
  $smarty->display('overview.tpl');
}

function checkLogin($user) {
  if(!$user->email) {
    header('Location: ' . BASEURL . "?p=login");
  }
}

function performancePage($user) {
  checkLogin($user);
  if(isset($_GET['id'])) {
    $id = $_GET['id'];
  } else {
    $id = $user->portfolios[0]->id;
  }
  $portfolio = $user->portfolio($id);
  $matrix = $portfolio->covCorMatrix();
  $smarty = new Smarty;
  $headings = array_keys($matrix['covar']);
  $smarty->assign('user', $user);
  $smarty->assign('headings', $headings);
  $smarty->assign('corr', $matrix['corrc']);
  $smarty->assign('covar', $matrix['covar']);
  $smarty->assign('portfolio', $portfolio);
  $smarty->display('performance.tpl');
}

function tradePage($user, $tradeData) {
  $smarty = new Smarty;
  if(isset($_GET['id'])) {
    $id = $_GET['id'];
    $portfolio = $user->portfolio($id);
  } else {
    $portfolio = $user->portfolios[0];
  }

  $smarty->assign('tradedata', $tradeData);
  $smarty->assign('portfolio', $portfolio);
  $smarty->assign('user', $user);
  $smarty->display('trade.tpl');
}

function transactionsPage($user) {
  $smarty = new Smarty;
  $smarty->assign('user', $user);
  $smarty->display('transactions.tpl');
}

function loginPage() {
  $smarty = new Smarty;
  $smarty->display('login.tpl');
}

function edit_portfoliosPage($user) {
  $smarty = new Smarty;
  $smarty->assign('user', $user);
  $smarty->display('edit-portfolios.tpl');
}

function initCashTransaction($postvars, $type, $user) {
  if(isset($postvars['amount']) && isset($postvars['portfolio'])) {
    $id = $postvars['portfolio'];
    $amount = $postvars['amount'];
    $portfolio = $user->portfolio($id);
    if($portfolio) {
      $portfolio->cashTransaction( $amount, $type );
      header('Location: ' . BASEURL . "?p=overview&id=$id");
    }
  }
}

function printPortfolioJSON() {
  $id = $_GET['id'];
  $portfolio = new Portfolio;
  $portfolio->getById($id);
  $data = $portfolio->pastPerformance();

  header("Content-type: application/JSON");
  print $data;
}

function tradeSearch($symbol, $cash, $cost, $user) {
  $portfolio = $user->portfolios[0];
  $out = $portfolio->shannonRatchet($symbol, $cash, $cost);
  return $out;
}
 
oci_close($ORACLE);
?>
