{include 'header.tpl' title="Index"}
{include 'sidebar.tpl'}
{include 'tabs.tpl'}
<article>
<h1>Log in</h1>
<p>You must be logged in to manage your portfolio. Log in or register below.</p>
<form class="login" method="POST">
<fieldset>
  <h2>Log in</h2>
  <label for="email">Email</label><input type="email" name="email" id="email" /><br/>
  <label for="password">Password</label><input type="password" name="password" id="password" /><br/>
  <input type="hidden" name="a" value="login" />
  <input type="submit" name="submit" value="Log in" />
</fieldset>
</form>
<form class="login" method="POST">
<fieldset>
  <h2>Register</h2>
  <label for="name">Name</label><input type="text" name="name" id="name" /><br/>
  <label for="email">Email</label><input type="email" name="email" id="email" /><br/>
  <label for="password">Password</label><input type="password" name="password" id="password" /><br/>
  <input type="hidden" name="a" value="register" />
  <input type="submit" name="submit" value="Register" />
</fieldset>
</form>
</article>
{include file="footer.tpl"}
