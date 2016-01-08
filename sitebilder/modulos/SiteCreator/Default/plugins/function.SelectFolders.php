<?
function smarty_function_SelectFolders($params,&$smarty){
 	if(!$params['src'] || $params['src']==""){
 	 	return $smarty->getFinishError('traducao_erro_01');
	  }
	if(!$params['id'] || $params['id']==""){
 	 	return $smarty->getFinishError('traducao_erro_02');
	  }
	if(!$params['src'] || $params['src']==""){
	   $params['src'] = $smarty->SelectFolders['src'];
       }
    if(!$params['list'] || $params['list']==""){
	   $params['list'] = 'file';
       }	
	$optionSelect = '<select id="'.$params["id"].'" name="'.$params["id"].'"';
    if(isset($params["size"]) && $params["size"] != ""){
        $optionSelect .= 'size="'.$params["size"].'"';
    }
    if(isset($params["height"]) && $params["height"] != ""){
        $optionSelect .= 'height="'.$params["height"].'"';
    }
    if(isset($params["onChange"]) && $params["onChange"] != ""){
        $optionSelect .= 'onchange="'.$params["onChange"].'"';
    }
    if(isset($params["onFocus"]) && $params["onFocus"] != ""){
        $optionSelect .= 'onfocus="'.$params["onFocus"].'"';
    }
    if(isset($params["onBlur"]) && $params["onBlur"] != ""){
        $optionSelect .= 'onblur="'.$params["onBlur"].'"';
    }
        $optionSelect .= ' >';
    if(isset($params["SetInicial"])){
            $optionSelect .= '<option value="">'.$params["SetInicial"].'</option>'."\r\n";
        }
    $arq= new Arquivo();
    $arq->SetOrigem($params['src']);
    $x = $arq->loadContent($params['list'],$params['limit']);
    
    foreach($x as $i => $value) {
        $pt = explode('/',$value);
        $modulonome = array_pop($pt);
        if(isset($params['Ignore'])){
            $ignore = explode(',',$params['Ignore']);
            if(in_array($modulonome,$ignore))continue;
        }
        if(isset($params['SetSelectd']) && $params['SetSelectd'] == $modulonome)
                $optionSelect .= '<option value="'.$modulonome.'" selected="selected">'.$modulonome.'</option>';
            else
		      $optionSelect .= '<option value="'.$modulonome.'" >'.$modulonome.'</option>';
    }
    $optionSelect .= '</select>';
    return $optionSelect;
  	} 	
?>