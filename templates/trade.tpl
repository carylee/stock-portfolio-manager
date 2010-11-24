{include 'header.tpl' title="Index" Email=$Email}
{include 'sidebar.tpl' links=$portfolios}
{include 'tabs.tpl'}
<article>
<h1>Compute the Shannon-Ratchet Trade Strategy</h1>
<form id="trade-strategy-form" method="GET" action="index.php">
  <fieldset>
    <label for="trade-stock-symbol">Symbol</label><input type="text" id="trade-stock-symbol" name="symbol"/>
    <label for="trade-cash">Initial Cash</label><input type="number" id="trade-cash" value="{$portfolio->cash}" name="cash"/>
    <label for="trade-cost">Trade Cost</label><input type="number" id="trade-cost" name="cost"/>
    <input type="hidden" name="a" value="trade-search"/>
    <input type="hidden" name="p" value="trade"/>
    <input type="submit" value="Go"/>
  </fieldset>
</form>
<table class='trade-strategy'>
{if count($tradedata)}
<tr>
  <td>Invested</td>
  <td>{$tradedata[0]}</td>
</tr>
<tr>
  <td>Days</td>
  <td>{$tradedata[1]}</td>
</tr>
<tr>
  <td>Total</td>
  <td>{$tradedata[2]}</td>
</tr>
<tr>
  <td>Total after trade costs</td>
  <td>{$tradedata[3]}</td>
</tr>
</table>
{/if}
</article>
{include file="footer.tpl"}
