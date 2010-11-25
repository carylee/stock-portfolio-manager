<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>CS339 Handins</title>
<style>
img {
  width: 320px;
  height: 240px;
}
div.image {
  width: 340px;
  float: left;
  display: inline;
}
h2 {
  clear:both;
}
</style>
</head>
<body>
<h1>Portfolio Handins</h1>
<p>Cary Lee and David McGough</p>
<a href="http://339.cs.northwestern.edu/~cel294/portfolio/index.php">Live portfolio</a>
<h2>Application Design</h2>
<div class="image">
  <p>Edit Portfolios Page</p>
  <a href="edit_portfolios.jpg"><img src="thumbs/edit_portfolios.jpg"></img></a>
</div>
<div class="image">
  <p>Performance (now details)</p>
  <a href="performance.jpg"><img src="thumbs/performance.jpg"></img></a>
</div>
<div class="image">
  <p>Transactions</p>
  <a href="transactions.jpg"><img src="thumbs/transactions.jpg"></img></a>
</div>
<div class="image">
  <p>Overview</p>
  <a href="overview.jpg"><img src="thumbs/overview.jpg"></img></a>
</div>
<div class="image">
  <p>Trade</p>
  <a href="trade.jpg"><img src="thumbs/trade.jpg"></img></a>
</div>
<div class="image">
  <p>Stock</p>
  <a href="stock.jpg"><img src="thumbs/stock.jpg"></img></a>
</div>
<h2>ER Diagram</h2>
<div class="image">
  <p>First draft</p>
  <a href="er.jpg"><img src="thumbs/er.jpg"></img></a>
</div>
<div class="image">
  <p>Second draft</p>
  <a href="er2.jpg"><img src="thumbs/er2.jpg"></img></a>
</div>
<h2>Relational<h2>
<h2>SQL DDL</h2>
  <pre>
<?php print file_get_contents('../portfolio.sql'); ?>
  </pre>
<h2>SQL DML/DQL</h2>
<pre>
<?php print file_get_contents('dqldml.txt'); ?>
</pre>


</body>
</html>
