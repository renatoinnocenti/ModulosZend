<div>
    <form id="formmenu" method="POST">
        <label for="ID_PAGE">{#ID_PAGE#}
            <input  type="text" value="{menus->ID_PAGE}" readonly="readonly" size="3" maxlength="3"/></label>
        <label for="page_name">{#page_name#}
        <input type="text" value="{menus->page_name}" readonly="readonly" size="10" maxlength="10"/></label>
        <label for="page_modulo">{#page_modulo#}
        <input type="text" value="{menus->page_modulo}" readonly="readonly" size="10" maxlength="10"/></label>
        <label for="page_lng">{#page_lng#}
            <input  type="text" value="{menus->page_lng}" readonly="readonly" size="6" maxlength="6"/></label>
        <label for="page_tpl">{#page_tpl#}
        {if $page_mindex}
        {SelectFolders id="page_tpl" src='templates' limit="/interna*.tpl" list="file" SetSelectd=$tpl SetInicial=#escolha# Ignore='index.tpl,header.tpl,feet.tpl'}
        {else}
        <input type="text" value="{menus->page_tpl}" readonly="readonly" size="10" maxlength="10"/>
        {/if}
        </label>
        <label for="page_index">{#page_index#}
            <input  type="text" value="{menus->page_index}" readonly="readonly" size="10"/></label>
        <label for="page_nivel">{#page_nivel#}
        <select id="page_nivel" name="page_nivel">
        {html_options values=$nivel_ids selected=$nivel_id output=$nivel_names}
        </select></label>
        <label for="page_acess">{#page_acess#}
        <textarea id="page_acess" name="page_acess" wrap="OFF">{menus->page_acess}</textarea></label>
        <label for="page_madmin">{#page_madmin#}
            <input id="page_madmin" name="page_madmin" type="checkbox" value="1" {$page_madmin}/>
        </label>
        <label for="page_mindex">{#page_mindex#}
            <input id="page_mindex" name="page_mindex" type="checkbox" value="1" {$page_mindex}/>
        </label>
        <label for="page_msindex">{#page_msindex#}
        {sqldropdown id="page_msindex" SetTable="guia_pages" SetOrdenar="page_name" SetOrder="ASC" SetValue="ID_PAGE" SetLabel="page_name" SetSelectd=$act_msindex SetInicial=#page_error_01# SetTranslation="pages_"}</label>
        <label for="page_msorder">{#page_msorder#}
            <input id="page_msorder" name="page_msorder" type="text" value="{menus->page_msorder}" size="3" maxlength="3"/></label> 
            <button type="button" onClick="CheckForm('formmenu')">{#botao_send#}</button>
        <input name="page" type="hidden" value="{$page_name}"/>
        <input name="action" type="hidden" value="redit"/>
        <input name="idde" type="hidden" value="{menus->ID_PAGE}"/>
        <input id="retorno" type="hidden" value="centro"/>       
    </form>
</div>