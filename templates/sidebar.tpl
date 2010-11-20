<nav id="portfolios">
  <h2>Portfolios</h2>
  <ul>
    {if isset($user)}
      {foreach $user->portfolios as $portfolio}
        <li><a href="index.php?p=overview&id={$portfolio->id}">{$portfolio->name}</a></li>
      {/foreach}
      <li><a href="#">Edit...</a></li>
    {/if}
  </ul>
</nav>
