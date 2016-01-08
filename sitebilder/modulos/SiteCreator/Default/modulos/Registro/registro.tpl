<h2><img src="templates/images/gifs/cafe-table_32.gif" alt="registro" />{#titulo_registro#}</h2>
<form id="Formregistro" method="POST" enctype="multipart/form-data">
<label for="member_name">{#member_name#}<br />
    <input name="member_name" type="text" />
</label><br />
<label for="member_real">{#member_real#}<br />
    <input name="member_real" type="text" />
</label><br />
<label for="member_password">{#member_password#}<br />
    <input name="member_password" type="password" />
</label><br />
<label for="member_password2">{#member_password2#}<br />
    <input name="member_password2" type="password" />
</label><br />
<label for="member_email">{#member_email#}<br />
    <input name="member_email" type="text" />
</label><br />
<button type="button" onClick="CheckForm('Formregistro')">Enviar</button>

<input name="page" type="hidden" value="addcadastro"/>
<input id="retorno" type="hidden" value="returnerror"/>
<input name="__NOTNULL__" type="hidden" value="member_name;member_password;member_email"/>
<input name="__NOTTXT__" type="hidden" value="member_name;member_password"/>
<input name="__NOTEMAIL__" type="hidden" value="member_email"/>
</form>
<div id="returnerror">{$LoginError}</div>