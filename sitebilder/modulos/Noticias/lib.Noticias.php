<?php
####################################################################
## CLASSE PARA MANIPULAÇÃO DE NOTICIAS                            ##
## CLASS.NOTICIAS.PHP VERSÃO 1.0  - 02/06/2005                    ##
## CRIADO POR ART-2 => RENATO INNOCENTI                           ##
## EMAIL: r.innocenti@uol.com.br                                  ##
####################################################################
error_reporting(E_ALL & ~ E_NOTICE);
####################################################################
## IMPORTA AS VARIAVEIS NECESSÁRIAS                               ##
## FUNÇÃO: bilderXML($cfg,$page)                                  ##
## $cfg = array - contem as varias configurações do site          ##
## $page = string - contem a informação da pagina ativa           ##
####################################################################
define('NOTICIAS_DIR_IMG','./templates/Image/news');
define('NOTICIAS_THB_SIZE',120);
class Noticias extends PageBilder{
    function NewNoticias(&$smarty,$form){
        $tabela = $smarty->cfg['prefix'].'news';
        $form = Check::RegForm($form);
        $form['news_date'] = date("Y-m-d H:i:s");
        $form['ID_MEMBER'] = $smarty->perfil['ID_MEMBER'];
        $tabela = $smarty->cfg['prefix'].'news';
        $sql = $this->SqlInsert($tabela,$form);
        $request = $this->SqlSelect($sql,__FILE__,__LINE__);
        return $smarty->getFinish("news_sucesso_01");        
    }
    function DelNews(&$smarty, $idde){
        $tabela = $smarty->cfg['prefix'].'news';
        if(is_numeric($idde)){
            $result = $this->SqlSelect("SELECT news_img,news_img_thb FROM $tabela WHERE ID_NEWS = '$idde'");
            $item = mysql_fetch_array($result,MYSQL_ASSOC);
            unlink(realpath(NOTICIAS_DIR_IMG.'/'.$item['news_img']));
            unlink(realpath(NOTICIAS_DIR_IMG.THUMBNAIL_DIR_IMG.'/'.$item['news_img_thb']));
            $sql = $this->SqlDelete($tabela,"ID_NEWS ='$idde'");
            $this->SqlSelect($sql);## remover grupo
        }
    }
    function EditNews(&$smarty,$idde,$form){
        unset($form[idde]);
        $tabela = $smarty->cfg['prefix'].'news';
        $form = Check::RegForm($form);
        if(is_numeric($idde)){       
            if(is_array($_FILES)){
                foreach($_FILES as $nome => $file){
                    if($file['tmp_name'] == '')continue;
                    $result = $this->SqlSelect("SELECT $nome,{$nome}_thb FROM $tabela WHERE ID_NEWS = '$idde'");
                    $item = mysql_fetch_array($result,MYSQL_ASSOC);
                    unlink(realpath(NOTICIAS_DIR_IMG.'/'.$item[$nome]));
                    unlink(realpath(NOTICIAS_DIR_IMG.THUMBNAIL_DIR_IMG.'/'.$item[$nome.'_thb']));
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
            $sql = $this->SqlUpdate($tabela,$form,"ID_NEWS = '$idde'");
            $request = $this->SqlSelect($sql);
            if($request != 1)
                return $request;
        }
    }
    function SetThbPatch($item){
        return NOTICIAS_DIR_IMG.THUMBNAIL_DIR_IMG.'/'.$item;
    }
    function SetImgPatch($item){
        return NOTICIAS_DIR_IMG.'/'.$item;
    }
    ####################################################################
    ## METODO FAZ a MONTAGEM DA TABELA APARTIR DAS CONFIGURAÇÕES      ##
    ## setTable($params, &$smarty)                                    ##
    ## $params = ARRAY => RECEBE TODOS OS PARAMETROS DA TAG           ##
    ## $smarty = OBJETO => RECEBE O OBJETO DO TEMPLATE                ##
    ##      @id = strinh - id da tabela a ser criada                  ##
    ##      Opcionais                                                 ##
    ##      @caption = string - TITULO DA TABELA                      ##
    ##      @adm = string - COLUNAS DE ADMINISTRAÇÃO                  ##
    ####################################################################
    function setviewpage($params, &$smarty){
        if(!$params["NumPage"] || $params["NumPage"] == ""){
            $params["NumPage"] = 1;
        }
        $this->atual = (!empty($this->uri['atual']))?$this->uri['atual']:1;    
        $result = $this->SqlSelect($this->sql);
        $this->NumResult = mysql_num_rows($result);
        if($this->NumResult > $params["NumPage"]){
            $npages = $this->NumResult / $params["NumPage"];
            $npages = ceil($npages);
            $this->pages = $npages;
        }else{
            $this->pages = 1;
        }
        $ordem = ($this->uri['ord'] == 'DESC')?'ASC':'DESC';
        $this->maxdusplay = ($this->atual * $params["NumPage"]) - $params["NumPage"];
        $this->uri['page'] = $smarty->getVars('page_name');
        $this->SetLimit($this->maxdusplay,$params["NumPage"]);
        ###### Criar o conteudo ######
        $result = $this->SqlSelect($this->sql);
        $this->numpage = mysql_num_rows($result);
        if($this->numpage > 0){
            while($item = mysql_fetch_array($result,MYSQL_ASSOC)){
                if(isset($params['prefix'])){
                    $searcharray = array("/^(".$params['prefix'].")(_)([a-zA-Z0-9_]+)$/siU");
                    $replacearray = array($params['prefixch'].'\\2\\3');
                    foreach($item as $chave => $valor){
                        $real = preg_replace($searcharray, $replacearray, $chave);
                        $item[$real] = $valor;
                    }    
                }
                $item['news_img_thb'] = $this->SetThbPatch($item['news_img_thb']);
                $item['news_img'] = $this->SetImgPatch($item['news_img_thb']);
                $smarty->assign($item);
                $p .= $smarty->fetch("noticias.tpl");
            }
            $smarty->clear_assign($item);
        return $p;
        }
    }   
}
?>