{include 'header.tpl' title="Index" Email=$Email}
{include 'sidebar.tpl' links=$portfolios}
<article>
{include 'tabs.tpl'}
<h1 id="stock-name">{$stock['name']}</h1>
  <p>Present Market Value: {$stock['pmv']}</p>
  <p>High: {$stock['high']}</p>
  <p>Low: {$stock['low']}</p>
  <p>Close: {$stock['close']}</p>
</article>
{include file="footer.tpl"}
