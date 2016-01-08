<?
function smarty_function_sqlradiobox($params, &$smarty){
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
                    $vec[] = array( 'value'=>$item[$params["SetValue"]],
                                    'label' =>$item[$params["SetLabel"]],
                                    'selected'=>($params["SetSelectd"] == $item[$params["SetValue"]])? 'checked' : ($params["SetSelectd"] == $item[$params["SetLabel"]])? 'checked':null
                                    );                   
            }
        }else{
            return $smarty->getFinishError("ERROR_011");   
        }
        ##### Traduzir conteudos #####
        if(isset($params["SetTranslation"])){
            foreach($vec as $valor){
                $val = $params["SetTranslation"].$valor['value'];
                $valor['label'] = ($smarty->get_config_vars($val))? $smarty->get_config_vars($val):$val;
                $novo[] = $valor;
                }
        }else{
            $novo = $vec;
        }
        usort($novo, "cmp");
        reset($novo);
        ########################
        $optionSelect = '';
        foreach($novo as $itemcat){
                $optionSelect .= '<label for="'.$params["id"]."_".$itemcat['value'].'">';
                if($params["SetDisplay"] == "img"){
                    $optionSelect .= "<img src=\"$itemcat[label]\" ";
                    if(isset($params["SetDisplayheight"]))
                        $optionSelect .= 'height="'.$params["SetDisplayheight"].'" ';
                    if(isset($params["SetDisplaywidth"]))
                        $optionSelect .= 'width="'.$params["SetDisplaywidth"].'" ';
                    $optionSelect .= "/><br />";
                }else{
                    $optionSelect .= $itemcat['label'];
                }
                $check = ($itemcat['selected'] == null)?null:" checked=\"$itemcat[selected]\"";
                $optionSelect .= '<input type="radio" id="'.$params["id"]."_".$itemcat['value'].'" name="'.$params["id"].'" value="'.$itemcat['value'].'" ';
                if($params["onSelect"]){
                   $optionSelect .='onselect="'.$params["onSelect"].'" '; 
                }
                if($params["onFocus"]){
                   $optionSelect .='onfocus="'.$params["onFocus"].'" '; 
                }
                if($params["onBlur"]){
                   $optionSelect .='onblur="'.$params["onBlur"].'" '; 
                }
                if($params["onClick"]){
                   $optionSelect .='onclick="'.$params["onClick"].'" '; 
                }
                $optionSelect .= $check.' />';
                $optionSelect .= '</label>';
                }
    return  $optionSelect;       
    }   
?>