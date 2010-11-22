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
<tr>
  <td>Cash</td>
  <td>${$portfolio->cash|number_format:2:".":","}</td>
</tr>
</table>
<form id="overview-transaction" method="POST">
  <fieldset>
    <select name="a" id="transaction-type">
      <option value="buy">Buy</option>
      <option value="sell">Sell</option>
      <option value="deposit">Deposit</option>
      <option value="withdraw">Withdraw</option>
    </select><br/>
  </fieldset>
  <fieldset class='transaction cash-transaction'>
    <label for="overview-amount">Amount</label><input id='overview-amount' type="text" name="amount" /><br/>
  </fieldset>
  <fieldset class='transaction stock-transaction'>
    <label for="overview-symbol">Symbol</label><input id='overview-symbol' type="text" name="symbol" /><br/>
    <label for="overview-shares">Shares</label><input id='overview-shares' type="text" name="shares" /><br/>
    <label for="overview-date">Date</label><input id='overview-date' type='date' name='date' /><br />
    <label for="overview-cost">Cost</label><input id='overview-cost' type='number' name='cost' />
  </fieldset>
  <input type='submit' value='Go' />
</form>
</article>
{include file="footer.tpl"}
