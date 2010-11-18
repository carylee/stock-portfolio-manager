<nav id="portfolios">
  <h2>Portfolios</h2>
  <ul>
    {foreach $portfolios as $portfolio}
    <li><a href="{$portfolio['href']}">{$portfolio['name']}</a></li>
    {/foreach}
    <li><a href="#">Edit...</a></li>
  </ul>
</nav>
