<div id="deditor">
<form id="Formpage" action="" method="post" onSubmit="ObjUEFV.UpdateEditorFormValue();CheckForm('Formpage');return false;">
{SelectFolders id="page_tpl" src=$pagelist onChange="SemiPost(\'Formpage\',\'action=edit&idde=$idde\',\'deditor\')" Ignore="header.tpl,index.tpl" SetSelectd=$nameTemplate SetInicial=#sitecreator_select#}
{insert name="editores" InstanceName=$page_tpl}
<input name="page" type="hidden" value="{$page_name}"/>
<input name="action" type="hidden" value="save"/>
<input name="idde" type="hidden" value="{$idde}"/>
<input id="retorno" type="hidden" value="centro"/>  
</form>
</div>