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

/*$p = new Portfolio;
print_r($p->getByUser('carylee@gmail.com'));*/

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
if( !$user->loggedIn() ) {
  $page = 'login'; // send them to the login page
}
$portfolios = $user->getPortfolios();
///$stocks = $portfolios[1]->getStocks();
//pr($stocks[0]);
//pr($stocks[0]->getStats());

//print_r($portfolios[0]->covCorMatrix( array('AAPL', "MSFT") ));

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
    if(isset($_GET['id']) && $user->ownsPortfolio($_GET['id'])) {
      $user->portfolio($_GET['id'])->delete();
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
    pr($_POST);
    break;

  case 'sell':
    if(isset($_POST['symbol']) && isset($_POST['shares']) && isset($_POST['cost']) && isset($_POST['portfolio'])) {
      $portfolio_id = $_POST['portfolio'];
      $symbol = $_POST['symbol'];
      $shares = $_POST['shares'];
      $cost = $_POST['cost'];
      $date = $_POST['date'];
      if( !$date ) {
        $date = time();
      }
      $portfolio = $user->portfolio($portfolio_id);
      $portfolio->sellStock($symbol, $shares, $cost, $date);
    }
    header('Location: ' . BASEURL . "?p=overview&id=$portfolio_id");
    //pr($_POST);
    break;

  case 'deposit':
    initCashTransaction($_POST, 'DEPOSIT', $user);
    break;

  case 'withdraw':
    initCashTransaction($_POST, 'WITHDRAW', $user);
    break;

}

// See what page the user is trying to access and display it
switch ($page ) {
  case 'stock':
    stockPage($user);
    break;

  case 'overview':
    overviewPage($user);
    break;

  case 'performance':
    performancePage($user);
    break;

  case 'edit-portfolios':
    edit_portfoliosPage($user);
    break;

  case 'login':
    loginPage();
    break;

  case 'trade':
    tradePage($user);
    break;

  case 'transactions':
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

function performancePage($user) {
  $smarty = new Smarty;
  $smarty->assign('user', $user);
  $smarty->display('performance.tpl');
}

function tradePage($user) {
  $smarty = new Smarty;
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
 

oci_close($ORACLE);
?>
