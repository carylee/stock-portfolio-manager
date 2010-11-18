<?php
require('libs/Smarty.class.php');

$smarty = new Smarty;

$smarty->assign("Email", "carylee@gmail.com");

$smarty->display('index.tpl');
?>
