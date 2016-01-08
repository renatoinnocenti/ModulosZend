<?
function smarty_insert_fncpages($params, &$smarty){
    $cam = $smarty->get_config_vars('patch').$smarty->get_config_vars('modulo_dir').$smarty->actualpage["page_modulo"]."/";
    $uri = array_merge($_POST,$_GET);
    if($smarty->getPages($smarty->actualpage["page_name"])== false){
        $smarty->get_config_vars('ERROR_001');
    }else{
        if($smarty->actualpage["page_modulo"] != "" && $smarty->actualpage["page_modulo"] != NULL){
            $cam = $smarty->get_config_vars('patch').$smarty->get_config_vars('modulo_dir').$smarty->actualpage["page_modulo"].'/';
                if(!is_dir($cam)){
                    $smarty->get_config_vars('ERROR_002').' - '.$smarty->actualpage["page_modulo"].' ('.$smarty->actualpage["page_name"].')';
                }else{
                    $smarty->LoadModulos($smarty->actualpage["page_modulo"],$cam);
                    if($smarty->actualpage["page_fnc"]){
                        $fnc = $smarty->actualpage["page_fnc"];
                        return $fnc($smarty,$uri);
                    }else{
                        return ereg_replace("(\r\n|\n|\r|\t)", "", stripslashes($smarty->fetch($cam.$smarty->actualpage["page_tpl"])));
                    }
                }
        }else{
            return ereg_replace("(\r\n|\n|\r|\t)", "", $smarty->fetch("IndexPage.tpl"));
        }       
    }
}
?>