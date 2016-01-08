<div>
    <form id="formmenu" method="POST" onSubmit="ObjUEFV.UpdateEditorFormValue();CheckForm('formmenu');return false;">
        <label for="page_tpl">{#page_tpl#}
        {SelectFolders id="page_tpl" src='templates' limit="/*.tpl" list="file" onChange="SemiPost(\'formmenu\',\'action=edit&file=$filename\',\'editor\')" SetInicial="Escolha" Ignore='index.tpl,header.tpl,headerpop.tpl,popup.tpl'}
        </label>   
        <input name="page" type="hidden" value="{$page_name}"/>
        <input name="action" type="hidden" value="save"/>
        <input id="retorno" type="hidden" value="editor"/>  
        <div id="editor"></div>     
    </form>
    
</div>