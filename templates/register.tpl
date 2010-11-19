{include 'header.tpl' title="Index" Email=$Email}
{include 'sidebar.tpl'}
{include 'tabs.tpl'}
<article>
<form id="login" method="POST">
<fieldset>
  <label for="email">Email</label><input type="email" name="email" id="email" />
  <label for="password">Password</label><input type="password" name="password" id="password" />
  <input type="submit" value="Log in" />
</fieldset>
</form>
</article>
{include file="footer.tpl"}
