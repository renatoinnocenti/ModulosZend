<div>Formulario de registro</div>
<form id="Formregistro" method="POST" enctype="multipart/form-data">
<label for="member_name">{#member_name#}
    {$registro_name}
</label><br />
<label for="member_real">{#member_real#}
    <input name="member_real" type="text" value="{$registro_real}"/>
</label><br />
<label for="member_password">{#member_password#}
    <input name="member_password" type="password" />
</label><br />
<label for="member_email">{#member_email#}
    <input name="member_email" type="text" value="{$registro_email}"/>
</label><br />
<label for="member_registro">{#member_registro#}
    <input name="member_registro" type="text" value="{$registro_registro}"/>
</label><br />
<label for="member_group">{#member_group#}
    {sqldropdown id="member_group" SetTable="guia_membersgroups" SetOrdenar="group_name" SetOrder="ASC" SetValue="group_name" SetLabel="group_name" SetSelectd="$registro_group" SetInicial=#ERROR_013#}
</label><br />
<label for="member_tformat">{#member_tformat#}
    <input name="member_tformat" type="text" value="{$registro_tformat}"/>
</label><br />
<label for="member_toffset">{#member_toffset#}
    <input name="member_toffset" type="text" value="{$registro_toffset}"/>
</label><br />
<label for="member_lng">{#member_lng#}
    <input name="member_lng" type="text" value="{$registro_lng}"/>
</label><br />
<label for="member_squest">{#member_squest#}
    <input name="member_squest" type="text" value="{$registro_squest}"/>
</label><br />
<label for="member_sansw">{#member_sansw#}
    <input name="member_sansw" type="text" value="{$registro_sansw}"/>
</label><br />
<button type="button" onClick="CheckForm('Formregistro')">Enviar</button>

<input name="page" type="hidden" value="{$page_name}"/>
<input name="action" type="hidden" value="redit"/>
<input name="idde" type="hidden" value="{$registro_id}"/>
<input id="retorno" type="hidden" value="centro"/>
<input name="__NOTNULL__" type="hidden" value="member_email"/>
<input name="__NOTTXT__" type="hidden" value="member_password"/>
<input name="__NOTEMAIL__" type="hidden" value="member_email"/>
</form>