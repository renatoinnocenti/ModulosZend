<?
function smarty_insert_modulo($params, &$smarty)
{
	$form = array_merge($_POST,$_GET);
    $smarty->LoadModulos($params['modulo']);
	return  $params["load"]($smarty,$params,$form);	 
	}
?>