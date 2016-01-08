<?php
function PaginasAdm(&$smarty,&$form){
    $page  = new Pagina($form);
    $page->AdmTemplates($smarty,$form);
    if($_POST['action'] && !$form['action']){
        $form['action'] = $_POST['action'];
    }
    switch($form['action']){
        case 'save':
            $page->AdmTemplatesSave($smarty,$form);
        case 'edit':
            $smarty->assign($form);
            return $smarty->fetch($smarty->modulo_dir."paginas_editor.tpl");
            break;
        default:
            return $smarty->fetch($smarty->modulo_dir."paginas_adm.tpl"); 
    }
    
}
?>