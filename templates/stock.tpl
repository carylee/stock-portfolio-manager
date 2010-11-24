{include 'header.tpl' title="Index" Email=$Email}
{include 'sidebar.tpl' links=$portfolios}
{include 'tabs.tpl'}
<article>
<script>drawTimeline("{$stock->symbol}");</script>
<h1 id="stock-name">{$stock->name} ({$stock->symbol})</h1>
  <div id='chart_div' style='width: 700px; height: 240px;'></div>
  <p>Present Market Value: {$stock->close}</p>
  <p>High: {$stock->high}</p>
  <p>Low: {$stock->low}</p>
  <p>Open: {$stock->open}</p>
  <p>Close: {$stock->close}</p>
<h2>Future Performance</h2>
<img id="future-performance-chart"></img>
</article>
{include file="footer.tpl"}
