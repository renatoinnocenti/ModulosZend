<?
function smarty_function_sqldropdown($params, &$smarty){
        if(!$params["id"] || $params["id"] == ""){
            return $smarty->getFinishError("ERROR_008",array("",'(id)'));
        }
        if(!$params["SetLabel"] || $params["SetLabel"] == ""){
            return $smarty->getFinishError("ERROR_008",array("",'(SetLabel)'));
        }
        if(!$params["SetValue"] || $params["SetValue"] == ""){
            return $smarty->getFinishError("ERROR_008",array("",'(SetValue)'));         
         }
        $params["SetCaption"] = array_merge($params["SetValue"], $params["SetLabel"]);
        $page = new PageBilder($smarty);
        $sql = $page->CreateSelectSQL($params,$smarty);
        $result = $page->SqlSelect($sql);
        if(mysql_affected_rows()> 0){
            while($item = mysql_fetch_array($result,MYSQL_ASSOC)){
                if(isset($params["SetGroup"])){
                    $vec = $item[$params["SetGroup"]]; 
                    $vec[] = array( 'value'=>$item[$params["SetValue"]],
                                    'label' =>$item[$params["SetLabel"]],
                                    'selected'=>($params["SetSelectd"] == $item[$params["SetValue"]])? 'selected' : null
                                    );
                }else{
                    $vec[] = array( 'value'=>$item[$params["SetValue"]],
                                    'label' =>$item[$params["SetLabel"]],
                                    'selected'=>($params["SetSelectd"] == $item[$params["SetValue"]])? 'selected' : ($params["SetSelectd"] == $item[$params["SetLabel"]])? 'selected':null
                                    );                   
                }
            }
        }else{
            $vec[] = array( 'value'=>'0',
                            'label' =>$smarty->get_config_vars('ERROR_010')
                            );    
        }
        ##### Traduzir conteudos #####
        if(isset($params["SetTranslation"])){
            foreach($vec as $valor){
                $val = $params["SetTranslation"].strtr($valor['value'], "-", "_");
                $valor['label'] = ($smarty->get_config_vars($val))? $smarty->get_config_vars($val):$valor['label'];
                $novo[] = $valor;
                }
        }else{
            $novo = $vec;
        }
        usort($novo, "cmp");
        reset($novo);
        ########################
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
            $optionSelect .= '<option value="0">'.$params["SetInicial"].'</option>'."\r\n";
        }
        if(isset($params["SetGroup"])){
            ##asdasd
        }else{
            foreach($novo as $itemcat){
                $check = ($itemcat['selected'] == null)?null:" selected=\"$itemcat[selected]\"";
                $optionSelect .= '<option value="'.$itemcat['value'].'"'.$check.'>'.$itemcat['label'].'</option>';
                }
        }
    $optionSelect .= '</select>';
    return  $optionSelect;       
    }   
?>