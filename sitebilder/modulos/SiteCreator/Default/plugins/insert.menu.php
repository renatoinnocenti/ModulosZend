<?
function  smarty_insert_menu($params, &$smarty){
    $smarty->LoadModulos('Menu');
    $menu  = new Menu($smarty);
    return $menu->ListMenu($params,$smarty);
}
?>