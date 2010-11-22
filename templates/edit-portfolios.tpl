{include 'header.tpl' title="Index" Email=$Email}
{include 'sidebar.tpl' links=$portfolios}
{include 'tabs.tpl'}
<article>
<h1>Delete a portfolio</h1>
<ul>
{foreach $user->portfolios as $portfolio}
  <li><a href="index.php?p=edit-portfolios&a=delete-portfolio&id={$portfolio->id}">X</a> {$portfolio->name}</li>
{/foreach}
</ul>
<h1>Create a portfolio</h1>
<form method="POST" action="index.php?p=edit-portfolios">
  <fieldset>
    <label for="porfolio-name">Name</label><input type="text" name="name" id="portfolio-name"/><br/>
    <label for="porfolio-description">Description</label><textarea rows="2" cols="20" type="text" name="description" id="portfolio-description"></textarea><br/>
    <label for="porfolio-deposit">Initial Deposit (in USD)</label><input type="text" name="deposit" id="portfolio-deposit"/><br/>
    <input type="hidden" name="a" value="create-portfolio"/>
    <input type="submit" value="Create"/>
  </fieldset>
</form>
</article>
{include file="footer.tpl"}
