<?php
function SiteCreator_css(&$smarty,$form=false){
    $mysql = new MYSQL($smarty);
    $tabela = $smarty->cfg['prefix'].'bilder_site';
    $mysql->SqlSelect("SELECT ID_SITE FROM $tabela WHERE site_dominio = '$form[site_dominio]'",__FILE__,__LINE__);
    if(mysql_affected_rows() > 0){
        return $smarty->getFinishError('sitecreator_erro_01',array(""," - ".htmlspecialchars($this->form['site_dominio'])));
    }
    switch($form['action']){
        case "selectcss":
            $smarty->assign('ID_CSSCLASS',$form['ID_CSSCLASS']);
            return $smarty->fetch($smarty->modulo_dir."select_css.tpl");  
        break;                            
        default:             
            $form['ID_MEMBER']=$smarty->perfil['ID_MEMBER'];
            $sql = $mysql->SqlInsert($tabela,$form); 
            $mysql->SqlSelect($sql,__FILE__,__LINE__);
            $idde = mysql_insert_id();
            $cam = './sites/'.$idde;
            //$cam = './sites/21';
            $site = new SiteCreator();
            $site->CopyDefultSite($cam);
            $site->createCfgfile($cam,array('ID_SITE'=>$idde));
            $site->CopyCss($form['ID_CSS'],$cam);
            $site->createTemplates($cam,$form['site_pages']);
            unset($_POST);
            unset($_GET);
            return $smarty->getFinish('sitecreator_sucesso_01');
        break;                        
    }       
}
function SiteCreatorAdm(&$smarty,$form=false){ 
    $tabela = $smarty->cfg['prefix'].'bilder_site';
    $smarty->assign($smarty->actualpage);
    $page = new PageBilder($smarty,$form);
    $file = new SiteCreator();
    if($_POST['action'] && !$form['action'])
        $form['action'] = $_POST['action'];
    if($_POST['idde'] && !$form['idde'])
        $form['idde'] = $_POST['idde'];
    switch($form['action']){
        case 'save':
            $request = $page->SqlSelect("SELECT * FROM $tabela WHERE ID_SITE = $form[idde]");
            $itens = mysql_fetch_array($request,MYSQL_ASSOC);
            $file->SaveSiteEditor($smarty,$form);
            if($file->SaveSiteEditor($smarty,$form)){
                return $smarty->getFinishError('sitecreator_erro_03');
            }
            ##################
            ### criar LOG ####
            ##################
        case 'edit':
            $smarty->assign($form);
            $request = $page->SqlSelect("SELECT * FROM $tabela WHERE ID_SITE = '$form[idde]'");
            $itens = mysql_fetch_array($request,MYSQL_ASSOC);
            $file->LoadSiteEditor($smarty,$form);
            return $smarty->fetch($smarty->modulo_dir."sitecreator_adm_editor.tpl");
            break;
        case 'del':
            if(is_numeric($page->uri['idde'])){
                $file->RemoveSite($smarty,$form);
                $sql = $page->SqlDelete($tabela,"ID_SITE ='{$page->uri[idde]}'");
                $page->SqlSelect($sql);## remover grupo
            }
            $cfg = array(   "SetTable"=>$tabela,
                            "SetCaption"=>array("ID_SITE","site_name","site_dominio")
                        );
            if(isset($page->uri['busca']) && $page->uri['busca'] != "")
                $cfg["SetSearch"]=$page->uri['busca'];
            $cfg["SetOrdenar"]=$page->uri['por'];
            $cfg["SetOrder"]=$page->uri["ord"];
            $page->CreateSelectSQL($cfg,$smarty);
            $form['tabela'] = $cfg['SetTable'];
            $form['coluna'] = $cfg['ID_SITE'];
            $smarty->register_object("listagem",$page);
            return $smarty->fetch($smarty->modulo_dir."sitecreator_adm.tpl");
            break;
        case 'view':
        case 'redit':
        default: 
            $cfg = array(   "SetTable"=>$tabela,
                            "SetCaption"=>array("ID_SITE","site_name","site_dominio")
                        );
            if(isset($page->uri['busca']) && $page->uri['busca'] != "")
                $cfg["SetSearch"]=$page->uri['busca'];
            $cfg["SetOrdenar"]=$page->uri['por'];
            $cfg["SetOrder"]=$page->uri["ord"];
            $page->CreateSelectSQL($cfg,$smarty);
            $form['tabela'] = $cfg['SetTable'];
            $form['coluna'] = $cfg['ID_SITE'];
            $smarty->register_object("listagem",$page);
            return $smarty->fetch($smarty->modulo_dir."sitecreator_adm.tpl");
        }
}
?>