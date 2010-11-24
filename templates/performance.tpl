{include 'header.tpl' title="Index" Email=$Email}
{include 'sidebar.tpl' links=$portfolios}
{include 'tabs.tpl'}
<article>
<script>drawTimeline("{$stock->symbol}");</script>
<div id='chart_div' style='width: 700px; height: 240px;'></div>
</article>
{include file="footer.tpl"}
