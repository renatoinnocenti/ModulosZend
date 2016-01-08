<h2><img src="templates/images/gifs/muffin_32.gif" alt="satelite" />{#etapa_01#}</h2>
<p>{#texto_etapa_01#}</p>
<form id="Formsite" action="" method="post">
<label for="ID_CLIENTE">{#ID_CLIENTE#}<br />
       {sqldropdown id="ID_CLIENTE" SetTable="guia_members" SetOrdenar="member_real" SetOrder="DESC" SetValue="ID_MEMBER" SetLabel="member_real" SetSelectd="$ID_CLIENTE" SetInicial=#ERROR_013#}
</label>
<label for="site_name">{#site_name#}<br />
    <input name="site_name" type="text" />
	<font class="exemplo">{#exemplo_01#}</font>
</label>
<label for="site_dominio">{#site_dominio#}<br />
    <input name="site_dominio" type="text" />
	<font class="exemplo">{#exemplo_02#}</font>
</label>
<label for="site_pages">{#site_pages#}<br />
    <input name="site_pages" type="text" value="5"/>
	<font class="exemplo">{#exemplo_03#}</font>
</label>
<label for="ID_CSSCLASS">{#ID_CSSCLASS#}<br />
{sqldropdown id="ID_CSSCLASS" SetTable="guia_css_class" SetOrdenar="cssclass_nome" SetOrder="DESC" SetValue="ID_CSSCLASS" SetLabel="cssclass_nome" SetSelectd="$ID_CSSCLASS" onChange="SemiPost(\'Formsite\',\'action=selectcss\','part1')" SetInicial=#ERROR_013#}
</label>
<div id="part1"></div>
<br />
<button type="button" onClick="CheckForm('Formsite','/')">Enviar</button>
<input name="page" type="hidden" value="sitecss"/>
<input id="retorno" type="hidden" value="returnerror"/>
<input name="__NOTNULL__" type="hidden" value="site_dominio;site_name;ID_CSSCLASS;ID_CSS"/>
<input name="__NOTURL__" type="hidden" value="site_dominio"/>
<input name="__NOTNUM__" type="hidden" value="site_pages"/>
</form>
<div id="returnerror">{$LoginError}</div>