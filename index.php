<?php
require('libs/Smarty.class.php');

$page = '';

if(isset($_GET['p'])) {
  $page = $_GET['p'];
}

switch ($page ) {
  case 'stock':
    $smarty = new Smarty;
    $smarty->assign('Email', 'carylee@gmail.com');
    $smarty->assign('portfolios', array( array('href'=>'#','name'=>'Portfolio 1') ) );
    $stock = array('name'=>'AAPL', 'high'=>230, 'low'=>20, 'close'=>42, 'open'=> 53 );
    $smarty->assign('stock', $stock);
    $smarty->display('stock.tpl');
    break;

  default:
    $smarty = new Smarty;
    $smarty->assign('Email', 'carylee@gmail.com');
    $smarty->assign('portfolios', array( array('href'=>'#','name'=>'Portfolio 1') ) );
    $smarty->display('index.tpl');
    break;
}

?>
