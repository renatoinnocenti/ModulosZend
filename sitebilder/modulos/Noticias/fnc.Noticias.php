<?php
function Newnews(&$smarty,&$form){
    $news = new Noticias($smarty,$form);
    if(isset($form['page'])){
        if(is_array($_FILES)){
            ## se há arquivos enviados.
            foreach($_FILES as $nome => $file){
                $arquivo = new Arquivo();
                $arquivo->SetOrigem($file['tmp_name']);
                $arquivo->SetDestino(NOTICIAS_DIR_IMG);
                $arquivo->SetPropriedades($file);
                if($arquivo->error)
                    return $smarty->getFinishError($arquivo->error);
                if($arquivo->SetMimetype($arquivo->prop['filename'],array('jpg','jepg','gif','png'),$file['type']) == true)
                    return $smarty->getFinishError('ERROR_017');
                if($arquivo->UploadFile() == true)
                    return $smarty->getFinishError('ERROR_021');
                $thb[$nome] = $arquivo->Thumbnail(NOTICIAS_THB_SIZE);
                $arquivo->FileLogs($nome);    
                $form[$nome] = $arquivo->filelog[$nome]['filename'];
                $form[$nome.'_thb'] = $thb[$nome]['thb_name'];
            }
        }
        return $news->NewNoticias($smarty,$form);             
    }   
}
function Admnews(&$smarty,&$form){
    $tabela = $smarty->cfg['prefix'].'news';
    $smarty->assign($smarty->actualpage);
    $news= new Noticias($smarty,$form);
    $cfg = array(
        "SetTable"=>$tabela,
        "SetOrdenar"=>$news->uri['por'],
        "SetOrder"=>$news->uri["ord"],
        "SetCaption"=>array("ID_NEWS","news_titulo","news_date")
        );
    if(isset($news->uri['busca']) && $news->uri['busca'] != "")
        $cfg["SetSearch"]=$news->uri['busca'];
    $news->CreateSelectSQL($cfg,$smarty);
    $smarty->register_object("listagem",$news);
    $news->uri['tabela'] = $tabela;
    $news->uri['coluna'] = 'ID_NEWS';
    $user = $smarty->getVars('ID_MEMBER');
    switch($news->uri['action']){
        case 'edit':
            $news->uri['select'] = '*, ID_NEWS as news_id';
            $item = $news->exibeRegistro($news->uri,$smarty);
            $item['news_img_thb'] = $news->SetThbPatch($item['news_img_thb']);
            $smarty->assign($item);
            $p = $smarty->fetch($smarty->modulo_dir."noticias_adm_edit.tpl");
            $smarty->clear_assign($item);
            $smarty->assign('ID_MEMBER',$user);
            return $p;
        break;
        case 'del':
            $news->DelNews($smarty, $news->uri['idde']);
            return $smarty->fetch($smarty->modulo_dir."noticias_adm.tpl");
        break;
        case 'redit':
            $error = $news->EditNews($smarty,$form['idde'],$form);
                if($error == false){
                    return $smarty->fetch($smarty->modulo_dir."noticias_adm.tpl");    
                }else{
                    return $error;
                }
        break;
        default:
        if(isset($form['page'])){
            return $smarty->fetch($smarty->modulo_dir."noticias_adm.tpl");
        }else{return false;}
    }
}
function noticias_view(&$smarty,&$form){ 
    $tabela = $smarty->cfg['prefix'].'news';
    $smarty->assign($smarty->actualpage);
    $news= new Noticias($smarty,$form);
    $cfg = array(
        "SetTable"=>$tabela,
        "SetOrdenar"=>$news->uri['por'],
        "SetOrder"=>$news->uri["ord"],
        "SetCaption"=>"*"
        );
    $news->CreateSelectSQL($cfg,$smarty);
    $smarty->register_object("noticias",$news);
    $news->uri['tabela'] = $tabela;
    $news->uri['coluna'] = 'ID_NEWS';
    return $smarty->fetch($smarty->modulo_dir."noticias_in.tpl"); 
}
function PageNews(&$smarty,&$form){
    $tabela = $smarty->cfg['prefix'].'news';
    $smarty->assign($smarty->actualpage);
    $news= new Noticias($smarty,$form);
    $cfg = array(
        "SetTable"=>$tabela,
        "SetOrdenar"=>$news->uri['por'],
        "SetOrder"=>$news->uri["ord"],
        "SetCaption"=>"*"
        );
    $news->CreateSelectSQL($cfg,$smarty);
    $smarty->register_object("noticias",$news);
    $news->uri['tabela'] = $tabela;
    $news->uri['coluna'] = 'ID_NEWS';
    return $smarty->fetch("noticias_page.tpl");    
}
?>