<form id="Formbusca" method="POST" onsubmit="CheckForm('Formbusca')">
    <input id="busca" name="busca" type="text" />
    <button type="button" onclick="CheckForm('Formbusca')">{#busca#}</button>
    <input name="page" type="hidden" value="{$page_name}"/>
    <input id="retorno" type="hidden" value="centro"/>
</form>
{listagem->setTable id="tabela1" caption="Lista de sites" indice="ID_SITE" adm="edit,del" NumPage="5"}
<b>total:</b><i>{listagem->exibeTotal}</i><br />
<b>minimo:</b><i>{listagem->exibeAtualMin}</i><br/>

<b>maximo:</b><i>{listagem->exibeAtualMax}</i>
<div>{listagem->ExibeSQLNav}</div>