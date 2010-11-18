<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <link rel="stylesheet" href="style.css" />
  <link href='http://fonts.googleapis.com/css?family=Vollkorn&subset=latin' rel='stylesheet' type='text/css'>
  <title>{$Title}</title>
  <script type='text/javascript' src='https://www.google.com/jsapi'></script>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.4.4/jquery.min.js"></script>
  <script src="scripts.js"></script>
</head>
<body>
<header>
<img id="logo" src="http://dummyimage.com/120x70/000/fff&text=Portfolio"></img>
<form id="search" action="index.php" method="GET">
  <input value="Symbol" name="stock"></input>
  <input type="hidden" name="p" value="stock" />
  <input type="Submit" value="Get quote"></input>
</form>
<div id="account">
<a href="index.php?p=account">{$Email}</a> | <a href="#">Log out</a>
</div>
</header>

