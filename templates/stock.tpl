{include 'header.tpl' title="Index" Email=$Email}
{include 'sidebar.tpl' links=$portfolios}
<article>
{include 'tabs.tpl'}
<h1 id="stock-name">{$stock->name} ({$stock->symbol})</h1>
  <p>Present Market Value: {$stock->close}</p>
  <p>High: {$stock->high}</p>
  <p>Low: {$stock->low}</p>
  <p>Open: {$stock->open}</p>
  <p>Close: {$stock->close}</p>
</article>
{include file="footer.tpl"}
