<?php
  function NewCadastro(&$smarty,$form=false){
    $registro = new Registro();
    $saida = $registro->NewRegistro($smarty,$form);
    return $saida;
  	}  	
function UsuarioAdm(&$smarty,$form=false){
        $tabela = $smarty->cfg['prefix'].'members';
        $smarty->assign($smarty->actualpage);
        $page = new PageBilder($smarty,$form);
        $cfg = array("SetTable"=>$tabela,
                    "SetOrdenar"=>$page->uri['por'],
                    "SetOrder"=>$page->uri["ord"],
                    "SetCaption"=>array("ID_MEMBER","member_name","member_real")
                    );
        if(isset($page->uri['busca']) && $page->uri['busca'] != "")
            $cfg["SetSearch"]=$page->uri['busca'];
        $page->CreateSelectSQL($cfg,$smarty);
        $smarty->register_object("listagem",$page);
        $page->uri['tabela'] = $tabela;
        $page->uri['coluna'] = 'ID_MEMBER';
        $user = $smarty->getVars('ID_MEMBER');
        switch($page->uri['action']){
            case 'view':
                $page->uri['prefix'] = 'member';
                $page->uri['prefixch'] = 'registro';
                $page->uri['select'] = '*, ID_MEMBER as registro_id';
                $item = $page->exibeRegistro($page->uri,$smarty);
                $smarty->assign($item);
                $p = $smarty->fetch($smarty->modulo_dir."usuario_adm_view.tpl");
                $smarty->clear_assign($item);
                return $p;
                break;
            case 'edit':
                $page->uri['prefix'] = 'member';
                $page->uri['prefixch'] = 'registro';
                $page->uri['select'] = '*, ID_MEMBER as registro_id';
                $item = $page->exibeRegistro($page->uri,$smarty);
                $smarty->assign($item);
                $p = $smarty->fetch($smarty->modulo_dir."usuario_adm_edit.tpl");
                $smarty->clear_assign($item);
                $smarty->assign('ID_MEMBER',$user);
                return $p;
                break;
            case 'del':
                if(is_numeric($page->uri['idde'])){
                    $sql = $page->SqlDelete($tabela,"ID_MEMBER ='{$page->uri[idde]}'");
                    $page->SqlSelect($sql);## remover grupo
                }
                return $smarty->fetch($smarty->modulo_dir."usuario_adm.tpl");
                break;
            case 'redit':
                if($page->uri['action']=='redit'){
                    if(!((trim($form['member_password']) == null) || (trim($form['member_password']) == ""))){
                        $form['member_password'] = Registro::md5_hmac($form['member_password'], strtolower($form['member_name']));
                        }else{
                                unset($form['member_password']);
                                }
                $form['member_ip'] = $_SERVER['REMOTE_ADDR'];
                    if(is_numeric($form[idde])){
                        $idde = $form[idde];
                        unset($form[idde]);
                        $sql = $page->SqlUpdate($page->uri['tabela'],$form,"ID_MEMBER = '$idde'");
                        $request = $page->SqlSelect($sql);
                    }
                return $smarty->fetch($smarty->modulo_dir."usuario_adm.tpl");
                }
                break;
            default:
            return $smarty->fetch($smarty->modulo_dir."usuario_adm.tpl");
        }
}
?>