<?php
ini_set('display_errors', 1); 
ini_set('log_errors', 1); 
ini_set('error_log', dirname(__FILE__) . '/error_log.txt'); 
error_reporting(E_ALL);
require('libs/Smarty.class.php');
require_once('classes/stock.php');

$page = '';

if(isset($_GET['p'])) {
  $page = $_GET['p'];
}


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
    $smarty->assign('Email', 'carylee@gmail.com');
    $smarty->assign('portfolios', array( array('href'=>'#','name'=>'Portfolio 1') ) );
    $smarty->display('index.tpl');
    break;
}

function stockPage() {
  $portfolios = array( array('id'=>1,'name'=>'Portfolio 1') );
  $email = "carylee@gmail.com";
  $smarty = new Smarty;
  $symbol = 'AAPL';
  if(isset($_GET['stock'])) {
    $symbol = $_GET['stock'];
  }
  $stock = new Stock($symbol);
  $stock->getQuote();
  $smarty->assign('Email', $email);
  $smarty->assign('portfolios', $portfolios);
  //$stock = array('name'=>'AAPL', 'high'=>230, 'low'=>20, 'close'=>42, 'open'=> 53 );
  $smarty->assign('stock', $stock);
  $smarty->display('stock.tpl');
}

function overviewPage() {
  $portfolios = array( array('id'=>1,'name'=>'Portfolio 1') );
  $email = "carylee@gmail.com";
  $smarty = new Smarty;
  $F = array('symbol'=>'F','pmv'=>16.51,'volatility'=>'0.53','correlation'=>'42.2','open'=>16.75,'high'=>16.90,'low'=>16.52);
  $LUV = array('symbol'=>'LUV','pmv'=>18.01,'volatility'=>'3.51','correlation'=>'41.2','open'=>1.75,'high'=>1.90,'low'=>19.28);
  $SBUX = array('symbol'=>'SBUX','pmv'=>42.51,'volatility'=>'0.33','correlation'=>'32.1','open'=>49.75,'high'=>61.90,'low'=>14.02);

  $smarty->assign('Email', $email);
  $smarty->assign('portfolios', $portfolios );
  $smarty->assign('stocks', array($F,$LUV,$SBUX));
  $smarty->display('overview.tpl');
}

function performancePage() {
  $portfolios = array( array('id'=>1,'name'=>'Portfolio 1') );
  $email = "carylee@gmail.com";
  $smarty = new Smarty;
  $smarty->assign('Email', $email);
  $smarty->assign('portfolios', $portfolios );
  $smarty->display('performance.tpl');
}

function tradePage() {
  $portfolios = array( array('id'=>1,'name'=>'Portfolio 1') );
  $email = "carylee@gmail.com";
  $smarty = new Smarty;
  $smarty->assign('Email', $email);
  $smarty->assign('portfolios', $portfolios );
  $smarty->display('trade.tpl');
}

function transactionsPage() {
  $portfolios = array( array('id'=>1,'name'=>'Portfolio 1') );
  $email = "carylee@gmail.com";
  $smarty = new Smarty;
  $smarty->assign('Email', $email);
  $smarty->assign('portfolios', $portfolios );
  $smarty->display('transactions.tpl');
}

function loginPage() {
  //$portfolios = array( array('id'=>1,'name'=>'Portfolio 1') );
  $smarty = new Smarty;
  //$smarty->assign('portfolios', $portfolios );
  $smarty->display('login.tpl');
}

?>
