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
    /*$smarty = new Smarty;
    $smarty->assign('Email', 'carylee@gmail.com');
    $smarty->assign('portfolios', array( array('href'=>'#','name'=>'Portfolio 1') ) );
    $stock = array('name'=>'AAPL', 'high'=>230, 'low'=>20, 'close'=>42, 'open'=> 53 );
    $smarty->assign('stock', $stock);
    $smarty->display('stock.tpl');*/
    break;

  default:
    $smarty = new Smarty;
    $smarty->assign('Email', 'carylee@gmail.com');
    $smarty->assign('portfolios', array( array('href'=>'#','name'=>'Portfolio 1') ) );
    $smarty->display('index.tpl');
    break;
}

function stockPage() {
  $smarty = new Smarty;
  $symbol = 'AAPL';
  if(isset($_GET['stock'])) {
    $symbol = $_GET['stock'];
  }
  $stock = new Stock($symbol);
  $stock->getQuote();
  $smarty->assign('Email', 'carylee@gmail.com');
  $smarty->assign('portfolios', array( array('href'=>'#','name'=>'Portfolio 1') ) );
  //$stock = array('name'=>'AAPL', 'high'=>230, 'low'=>20, 'close'=>42, 'open'=> 53 );
  $smarty->assign('stock', $stock);
  $smarty->display('stock.tpl');
}

?>
