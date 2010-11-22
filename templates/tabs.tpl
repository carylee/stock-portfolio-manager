<nav id="tabs">
  <ul>
    {if $user}
      <li><a href="?p=overview&id={$user->portfolios[0]->id}">Overview</a></li>
    {else}
      <li><a href="#">Overview</a></li>
    {/if}
    <li><a href="?p=performance">Performance</a></li>
    <li><a href="?p=stock">Stock</a></li>
    <li><a href="?p=trade">Trade</a></li>
    <li><a href="?p=transactions">Transactions</a></li>
  </ul>
</nav>
