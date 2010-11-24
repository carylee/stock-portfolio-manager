{include 'header.tpl' title="Index" Email=$Email}
{include 'sidebar.tpl' links=$portfolios}
{include 'tabs.tpl'}
<article>
<script>portfolioChart("{$portfolio->id}");</script>
<h1>Portfolio: {$portfolio->name}</h1>
<h2>Covariance Matrix</h2>
<table>
  <th>
  {foreach $headings as $symbol}
    <td class='stock-symbol'>{$symbol}</td>
  {/foreach}
  </th>
  {foreach $covar as $symbol1=>$values}
  <tr>
    <td class='stock-symbol'>{$symbol1}</td>
    {foreach $values as $value}
      <td>{$value|number_format:2:".":","}</td>
    {/foreach}
  </tr>
  {/foreach}
</table>
<h2>Correlation Matrix</h2>
<table>
  <th>
  {foreach $headings as $symbol}
    <td class='stock-symbol'>{$symbol}</td>
  {/foreach}
  </th>
  {foreach $corr as $symbol1=>$values}
  <tr>
    <td class='stock-symbol'>{$symbol1}</td>
    {foreach $values as $value}
      <td>{$value|number_format:2:".":","}</td>
    {/foreach}
  </tr>
  {/foreach}
</table>

<h2>Portfolio Value</h2>
<p>Displays your portfolio's value over the past year.</p>
<div id='portfolio_chart' style='width: 900px; height: 240px;'></div>
  
</article>
{include file="footer.tpl"}
