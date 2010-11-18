<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<link rel="stylesheet" href="style.css" />
<title>{$Title}</title>
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

