{include 'header.tpl' title="Index" Email=$Email}
{include 'sidebar.tpl' links=$portfolios}
{include 'tabs.tpl'}
<article>
<h1>Overview</h1>
<table>
<tr>
  <th>Symbol</th>
  <th>Present Market Value</th>
  <th>Volatility</th>
  <th>Correlation</th>
  <th>Open</th>
  <th>High</th>
  <th>Low</th>
</tr>
{foreach $stocks as $stock}
<tr>
  <td><a href="index.php?p=stock&stock={$stock['symbol']}">{$stock['symbol']}</a></td>
  <td>{$stock['pmv']}</td>
  <td>{$stock['volatility']}</td>
  <td>{$stock['correlation']}</td>
  <td>{$stock['open']}</td>
  <td>{$stock['high']}</td>
  <td>{$stock['low']}</td>
</tr>
{/foreach}
</table>
</article>
{include file="footer.tpl"}
