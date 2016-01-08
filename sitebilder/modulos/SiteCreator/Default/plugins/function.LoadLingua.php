<?
function smarty_function_LoadLingua($params,&$smarty){
 	if(!$params['src'] || $params['src']==""){
 	 	return $smarty->getFinishError('traducao_erro_01');
	  }
	if(!$params['id'] || $params['id']==""){
 	 	return $smarty->getFinishError('traducao_erro_02');
	  }
	if(!$params['send'] || $params['send']==""){
 	 	return $smarty->getFinishError('traducao_erro_04');
	  }
	$arq= new Arquivo();
	$arq->SetOrigem($params['src']);
	$x = $arq->loadPastas();
    $a = '<select name="'.$params['id'].'" id="'.$params['id'].'" onchange="CheckForm(\''.$params['send'].'\')">';
	$a .= '<option name="escolha" value="0" selected="selected">'.$smarty->get_config_vars('trad_select').'</option>';
    foreach($x as $i => $value) {
        $pt = explode('/',$value);
        $modulonome = array_pop($pt);
		$a .= '<option value="'.$modulonome.'" >'.$modulonome.'</option>';
    }
    $a .= '</select>';
    return $a;
  	} 	
?>