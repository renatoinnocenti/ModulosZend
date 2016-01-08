<div>{#modulos#}</div>
<form id="modulosform" method="POST">
{SelectFolders id="modulosload" src='modulos' list="dir" onChange="SemiPost(\'modulosform\',\'action=loadform\',\'arquivos\')" SetSelectd=$t SetInicial=#escolha#}
<button onClick="SemiPost(\'modulosform\',\'action=loadform&file=principal\',\'arquivos\');return false;">Arquivo Principal</button>
<div id="arquivos"></div>
<input name="page" type="hidden" value="addTraducao"/>
<input name="retorno" id="retorno" type="hidden" value="arquivos"/>
</form>
