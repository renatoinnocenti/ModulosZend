<form id="Formlogin" action="" method="post">
<label for="login_user">{#login_user#}
    <input name="login_user" type="text" />
</label>
<br />
<label for="login_password">{#login_password#}
    <input name="login_password" type="password" /></label>
<br />
<button type="submit">Enviar</button>
<input name="page" type="hidden" value="login"/>
<input id="retorno" type="hidden" value="returnerror"/>
<input name="cookieleng" type="hidden" value="5"/>
<input name="__NOTNULL__" type="hidden" value="login_user;login_password"/>
<input name="__NOTTXT__" type="hidden" value="login_user;login_password"/>
</form>
<div id="returnerror">{$LoginError}</div>