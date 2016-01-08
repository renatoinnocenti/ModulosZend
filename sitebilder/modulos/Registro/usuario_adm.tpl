<h2><img src="templates/images/gifs/barista_32.gif" alt="usuario" />{#titulo_usuario#}</h2>

<p>{#texto_usuario#}<br /><br />
<form id="Formbusca" method="POST" onsubmit="CheckForm('Formbusca')">
    <input id="busca" name="busca" type="text" /><br /><br />
    <button type="button" onclick="CheckForm('Formbusca')">{#busca#}</button>
    <input name="page" type="hidden" value="{$page_name}"/>
    <input id="retorno" type="hidden" value="centro"/>
</form></p>



{listagem->setTable id="tabela1" caption=#titulo_tabela01# indice="ID_MEMBER" adm="view,edit,del" NumPage="8" ClassLinha="class1,class2,class3"}
<b>{#nav_total#}</b><i>{listagem->exibeTotal}</i><br />
<b>{#nav_min#}</b><i>{listagem->exibeAtualMin}</i><br/>

<b>{#nav_max#}</b><i>{listagem->exibeAtualMax}</i>
<div id="paginas"><b>{#nav_paginas#}</b>{listagem->ExibeSQLNav}</div>