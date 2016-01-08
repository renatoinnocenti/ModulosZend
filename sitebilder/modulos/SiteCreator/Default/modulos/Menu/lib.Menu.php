<?
####################################################################
## CLASSE PARA MANIPULAÇÃO DE MENU                                ##
## CLASS.MENU.PHP VERSÃO 1.0  - 05/01/2006                        ##
## CRIADO POR ART-2 => RENATO INNOCENTI                           ##
## EMAIL: r.innocenti@uol.com.br                                  ##
####################################################################
error_reporting(E_ALL & ~ E_NOTICE);
####################################################################
## CRIA UMA INSTANCIA PARA CONTROLE DE LOGIN                      ##
## as entradas são referencias para o mysql                       ##
####################################################################
class Menu extends MYSQL
{ 
    var $tab1  = 'pages';
    var $tab2  = 'pages_dinamics';
    var $page = array();
    function ListMenu($params,&$smarty){
        $tabela = $smarty->cfg['prefix'].$this->tab1;
        $this->menutype = ($params['menutype']=='adm')?"page_madmin":"page_mindex";
        $this->menulink = ($params['menulink']==true)?false:true;
        $this->menutag = ($params['menutag']=='true')?$params['menutag']:false;
        $this->menudirec = ($params['menudirec']==true)?$params['menudirec']:$smarty->get_config_vars('page_main');
        $resultx = $this->SqlSelect("SELECT * FROM $tabela WHERE page_nivel <= '{$smarty->perfil[member_nivel]}' AND {$this->menutype} ='1' AND page_msindex IS NULL AND page_lng = '{$smarty->cfg[language_atual]}' ORDER BY page_modulo,page_msindex DESC,page_msorder,page_name ASC");
        if(mysql_affected_rows()> 0){
            $menu = '<ul>';
            while($item = mysql_fetch_array($resultx,MYSQL_ASSOC)){
                $mname = ($smarty->get_config_vars('page_'.$item[ID_PAGE]))?$smarty->get_config_vars('page_'.$item[ID_PAGE]):$item[page_name];
                $menu .= ($this->menulink == true)?'<li><a href="#" onClick="loadPage(\''.$this->menudirec.'\',\'page='.$item[page_name].'\')">'.$mname.'</a></li>':'<li>'.$mname.'</li>';
                $menu .= $this->readmenu($smarty,$item['ID_PAGE']);            
            }
            $menu .= '</ul>';
        }
        return $menu;
    }
    function readmenu(&$smarty,$idde){        
        $tabela = $smarty->cfg['prefix'].$this->tab1;
        $result = $this->SqlSelect("SELECT * FROM $tabela WHERE page_nivel <= '{$smarty->perfil[member_nivel]}' AND {$this->menutype} ='1' AND page_msindex = '$idde' AND page_lng = '{$smarty->cfg[language_atual]}'ORDER BY page_modulo,page_msindex DESC,page_msorder,page_name ASC");
        if(mysql_affected_rows()> 0){
            while($itema = mysql_fetch_array($result,MYSQL_ASSOC)){              
                if($this->menutype == 'page_madmin' && $this->bloco ==  null){
                    ## 1º giro
                    $this->bloco = $itema[page_modulo];
                    $submenu .= '<ul><li>'.$this->bloco.'</li>';
                }elseif($this->menutype == 'page_madmin' && $this->bloco !=$itema[page_modulo]){
                    $this->bloco = $itema[page_modulo];
                    $submenu .= '<li>'.$this->bloco.'</li>';
                }
                $mname = ($smarty->get_config_vars('page_'.$itema[ID_PAGE]))?$smarty->get_config_vars('page_'.$itema[ID_PAGE]):$itema[page_name];
                $submenu .= '<ul><li><a href="#" onClick="loadPage(\''.$this->menudirec.'\',\'page='.$itema[page_name].'\')">'.$mname.'</a></li>';
                $resultz = $this->SqlSelect("SELECT * FROM $tabela WHERE page_nivel <= '{$smarty->perfil[member_nivel]}' AND {$this->menutype} ='1'  AND page_msindex = '$itema[ID_PAGE]' AND page_lng = '{$smarty->cfg[language_atual]}' ORDER BY page_modulo,page_msindex DESC,page_msorder,page_name ASC");
                if(mysql_affected_rows()> 0){
                    $submenu .= '<ul>';
                    while($itemz = mysql_fetch_array($resultz,MYSQL_ASSOC)){
                        $mname = ($smarty->get_config_vars('page_'.$itemz[ID_PAGE]))?$smarty->get_config_vars('page_'.$itemz[ID_PAGE]):$itemz[page_name];
                        $submenu .= '<li><a href="#" onClick="loadPage(\''.$this->menudirec.'\',\'page='.$itemz[page_name].'\')">'.$mname.'</a></li>';
                        $submenu .= $this->readmenu($smarty,$itemz[ID_PAGE]);
                     }
                     $submenu .= '</ul>';
                }
                $submenu .= '</ul>';
                if($this->menutype == 'page_madmin' && $this->bloco !=$itema[page_modulo]){
                    $submenu .= '</ul>';
                    $this->bloco = NULL;
                }
            }
        }
        return $submenu;
    }
    function ListMenuAdm($params,&$smarty){
        $tabela = $smarty->cfg['prefix'].$this->tab1;
        $this->menudirec = ($params['menudirec']==true)?$params['menudirec']:$smarty->get_config_vars('page_main');
        $resultx = $this->SqlSelect("SELECT * FROM $tabela WHERE page_msindex IS NULL ORDER BY page_msindex DESC,page_msorder,page_name ASC");
        if(mysql_affected_rows()> 0){
            $menu = '<ul>';
            while($item = mysql_fetch_array($resultx,MYSQL_ASSOC)){
                $mname = ($smarty->get_config_vars('page_'.$item[ID_PAGE]))?$smarty->get_config_vars('page_'.$item[ID_PAGE]):$item[page_name];
                $menu .= '<li><a href="#" onClick="loadPage(\''.$this->menudirec.'\',\'page=menuadm&action=edit&idde='.$item[ID_PAGE].'\')">'.$mname.'</a></li>';
                $menu .= $this->readmenuadm($smarty,$item['ID_PAGE']);            
            }
            $menu .= '</ul>';
        }return $menu;
    }
    function readmenuadm(&$smarty,$idde){        
        $tabela = $smarty->cfg['prefix'].$this->tab1;
        $result = $this->SqlSelect("SELECT * FROM $tabela WHERE page_msindex = '$idde' AND (page_madmin <> '0' OR page_mindex <> '0')ORDER BY page_msindex DESC,page_msorder,page_name ASC");
        if(mysql_affected_rows()> 0){
            while($itema = mysql_fetch_array($result,MYSQL_ASSOC)){              
                $mname = ($smarty->get_config_vars('page_'.$itema[v]))?$smarty->get_config_vars('page_'.$itema[ID_PAGE]):$itema[page_name];
                $submenu .= '<ul><li><a href="#" onClick="loadPage(\''.$this->menudirec.'\',\'page=menuadm&action=edit&idde='.$itema[ID_PAGE].'\')">'.$mname.'</a></li>';
                $resultz = $this->SqlSelect("SELECT * FROM $tabela WHERE page_msindex = '$itema[ID_PAGE]' ORDER BY page_msindex DESC,page_msorder,page_name ASC");
                if(mysql_affected_rows()> 0){
                    $submenu .= '<ul>';
                    while($itemz = mysql_fetch_array($resultz,MYSQL_ASSOC)){
                        $mname = ($smarty->get_config_vars('page_'.$itemz[ID_PAGE]))?$smarty->get_config_vars('page_'.$itemz[ID_PAGE]):$itemz[page_name];
                        $submenu .= '<li><a href="#" onClick="loadPage(\''.$this->menudirec.'\',\'page=menuadm&action=edit&idde='.$itemz[ID_PAGE].'\')">'.$mname.'</a></li>';
                        $submenu .= $this->readmenuadm($smarty,$itemz[ID_PAGE]);
                     }
                     $submenu .= '</ul>';
                }
                $submenu .= '</ul>';
            }
        }
        return $submenu;
    }
    function editadmpage(&$smarty,&$form){
        $tabela = $smarty->cfg['prefix'].$this->tab1;
        $tabela2 = $smarty->cfg['prefix'].$this->tab2;
        $idde= $form['idde'];
        if($form['action']=='redit'){
            $form = Check::RegForm($form);
            unset($form['idde']);
            if($form['page_madmin']==false){
                $form['page_madmin'] = '0';
            }
            if($form['page_mindex']==false){
                $form['page_mindex'] = '0';
            }
            if($form['page_msindex']==false){
                $form['page_msindex'] = NULL;
            }
            $sql = $this->SqlUpdate($tabela,$form,"ID_PAGE = '$idde'");
            $result = $this->SqlSelect($sql);
        }
        $result = $this->SqlSelect("SELECT * FROM $tabela WHERE ID_PAGE = '$idde'");
        $page = mysql_fetch_array($result,MYSQL_ASSOC);
        foreach($page as $chave =>$valor){
            $this->$chave = $valor;
        }
        $smarty->assign('nivel_ids', range(0, 6)); 
        $smarty->assign('nivel_names', array($smarty->get_config_vars('nivel_0'),$smarty->get_config_vars('nivel_1'),$smarty->get_config_vars('nivel_2'),$smarty->get_config_vars('nivel_3'),$smarty->get_config_vars('nivel_4'),$smarty->get_config_vars('nivel_5'),$smarty->get_config_vars('nivel_6')));
        $smarty->assign('nivel_id', $this->page_nivel);
        $chk['page_madmin'] = ($this->page_madmin == 1)?'checked="checked"':'0';
        $chk['page_mindex'] = ($this->page_mindex == 1)?'checked="checked"':'0';
        $smarty->assign('act_msindex',$this->page_msindex);
        $smarty->assign('tpl',$this->page_tpl);
        $smarty->assign($chk);             
    }
}
?>