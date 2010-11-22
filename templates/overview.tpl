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
  <td><a href="index.php?p=stock&stock={$stock->symbol}">{$stock->symbol}</a></td>
</tr>
{/foreach}
</table>
</article>
{include file="footer.tpl"}
