<?
function Adm_menu(&$smarty,&$form){
    $menu  = new Menu($smarty);
    $smarty->register_object("menus",$menu);
    $form['action']=($_POST['action'])?$_POST['action']:$form['action'];   
    switch($form['action']){
        case 'edit':
            $menu->editadmpage($smarty,$form);
            return $smarty->fetch($smarty->modulo_dir."menu_adm_edit.tpl");
        Break;
        case 'redit':
            $menu->editadmpage($smarty,$form);
        default:
            return $smarty->fetch($smarty->modulo_dir."menu_adm.tpl");
    }
    
    
}
?>