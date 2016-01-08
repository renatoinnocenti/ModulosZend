
<h2><img src="templates/images/gifs/cafe-table_32.gif" alt="Adicionar Noticias" />{#add_news#}</h2>
<form id="Formnoticias" method="POST" onSubmit="ObjUEFV.UpdateEditorFormValue();CheckForm('Formnoticias');return false;" enctype="multipart/form-data">
<label for="news_titulo">{#news_titulo#}<br />
    <input name="news_titulo" type="text" />
</label><br />
<label for="news_inner">{#news_inner#}<br />
    {insert name="editor" InstanceName="news_inner" ToolbarSet="Noticia" Width="400" Height="250"}
</label><br />
<label for="news_img">{#news_img#}<br />
    <input name="news_img" id="news_img" type="file" />
</label><br />
<button type="submit">Enviar</button>
<input name="page" type="hidden" value="addnews"/>
<input id="retorno" type="hidden" value="returnerror"/>
<input name="__NOTNULL__" type="hidden" value="news_titulo;news_inner"/>
<input name="__NOTTXT__" type="hidden" value="news_titulo"/>
</form>
<div id="returnerror">{$LoginError}</div>