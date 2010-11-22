{include 'header.tpl' title="Index" Email=$Email}
{include 'sidebar.tpl' links=$portfolios}
{include 'tabs.tpl'}
<article>
<h1>{$portfolio->name}</h1>
<table class='stock-data'>
<tr>
  <th>Symbol</th>
  <th>Shares</th>
  <th>Market Value</th>
  <th>Volatility</th>
  <th>Open</th>
  <th>Close</th>
  <th>High</th>
  <th>Low</th>
  <th>Beta (Market)</th>
  <th>Beta (Portfolio)</th>
</tr>
{foreach $stocks as $stock}
<tr>
  <td><a href="index.php?p=stock&stock={$stock->symbol}">{$stock->symbol}</a></td>
  <td>{$stock->shares}</td>
  <td>${$stock->pmv|number_format:2:".":","}</td>
  <td>{$stock->stats['cov']|string_format:"%.2f"}</td>
  <td>{$stock->open}</td>
  <td>{$stock->close}</td>
  <td>{$stock->high}</td>
  <td>{$stock->low}</td>
</tr>
{/foreach}
<tr>
  <td>Cash</td>
  <td>${$portfolio->cash|number_format:2:".":","}</td>
</tr>
</table>
<form id="overview-transaction" method="POST" >
  <fieldset>
    <select name="a" id="transaction-type">
      <option value="buy">Buy</option>
      <option value="sell">Sell</option>
      <option value="deposit">Deposit</option>
      <option value="withdraw">Withdraw</option>
    </select><br/>
  </fieldset>
  <fieldset class='transaction cash-transaction'>
    <label for="overview-amount">Amount</label><input id='overview-amount' type="text" name="amount" autocomplete="off"/><br/>
  </fieldset>
  <fieldset class='transaction stock-transaction'>
    <label for="overview-symbol">Symbol</label><input id='overview-symbol' type="text" name="symbol" /><br/>
    <label for="overview-shares">Shares</label><input id='overview-shares' type="text" name="shares" autocomplete="off"/><br/>
    <label for="overview-date">Date</label><input id='overview-date' type='date' name='date' autocomplete="off"/><br />
    <label for="overview-cost">Cost</label><input id='overview-cost' type='number' name='cost' autocomplete="off"/>
  </fieldset>
  <input type='hidden' value='{$portfolio->id}' name='portfolio' />
  <input type='submit' value='Go' />
</form>
</article>
{include file="footer.tpl"}
