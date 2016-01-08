<?
####################################################################
## CLASSE PARA MANIPULAÇÃO DE PAGINAS E LISTAS                    ##
## LIB.PAGEBILDER.PHP VERSÃO 1.5  - 09/11/2006                    ##
## CRIADO POR RENATO INNOCENTI                                    ##
## EMAIL: r.innocenti@uol.com.br                                  ##
####################################################################
class PageBilder extends FormBilder{
    var $atual;
    var $pages;
    var $uri;   
    ####################################################################
    ## FUNÇÕES ACESSIVEIS APENAS VIA TEMPLATE                         ##
    ####################################################################
    function PageBilder(&$smarty,$form=false){
        $this->MYSQL($smarty);
        $this->uri = $form;
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
    function setTable($params, &$smarty){
        if(!$params["id"] || $params["id"] == ""){
            return $smarty->getFinishError("ERROR_010",array("",$params["id"]));
        }
        if(!$params["NumPage"] || $params["NumPage"] == ""){
            $params["NumPage"] = 10;
        }
        $tabela = '<table id="'.$params["id"].'">';
        if($params["caption"] && $params["caption"] != "")
            $tabela .= '<caption>'.$params["caption"].'</caption>';
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
        $tabela .= '<tr>';
        $ordem = ($this->uri['ord'] == 'DESC')?'ASC':'DESC';
        $this->maxdusplay = ($this->atual * $params["NumPage"]) - $params["NumPage"];
        $this->uri['page'] = $smarty->getVars('page_name');
        foreach($this->caption as $valor){
            $translate = ($smarty->get_config_vars($valor)==true) ? $smarty->get_config_vars($valor) : $valor;
            $ext ='por='.$valor.'&atual='.$this->atual.'&ord='.$ordem.CriaUrlVec($this->uri,array('ord','por','action','idde'));
            $tabela .= '<th scope="col"><a href="#" onclick="loadPage(\''.$smarty->get_config_vars('page_main').'\',\''.$ext.'\')">'.$translate;
            $tabela .= '</a></th>';
        }
        if($params["adm"] && $params["adm"] != ""){
            $adm = explode(',',$params["adm"]);
        }
        if(in_array('view',$adm))
            $tabela .= '<th scope="col"><a href="#">'.$smarty->get_config_vars('table_view').'</a></th>';
        if(in_array('edit',$adm))
            $tabela .= '<th scope="col"><a href="#">'.$smarty->get_config_vars('table_edit').'</a></th>';
        if(in_array('del',$adm))
            $tabela .= '<th scope="col"><a href="#">'.$smarty->get_config_vars('table_del').'</a></th>';
        $tabela .= '</tr>';
        $this->SetLimit($this->maxdusplay,$params["NumPage"]);
        ###### Criar o conteudo ######
        $result = $this->SqlSelect($this->sql);
        $this->numpage = mysql_num_rows($result);
        if(mysql_affected_rows()> 0){
            while($item = mysql_fetch_array($result,MYSQL_ASSOC)){
                $ext2 = 'idde='.$item[$params['indice']].CriaUrlVec($this->uri,array('action','idde'));
                $tabela .= "<tr>";
                foreach($item as $valor){
                    $tabela .= "<td>".trim($valor)."</td>";
                }
                foreach($adm as $valor){
                    $ext3 = '&action='.$valor;
                    $tabela .= '<td>
                                <img class="cursor" src="'.
                                $smarty->get_config_vars('img_dir').
                                $smarty->get_config_vars('img_'.$valor).
                                '" alt="'.$smarty->get_config_vars('table_'.$valor).
                                '" onclick="';
                    if($valor == 'del')
                    $tabela .= 'confirmar(\''.$smarty->get_config_vars('ERROR_012').'\',\''.$smarty->get_config_vars('botao_yes').'\',\''.$smarty->get_config_vars('botao_no').'\');';
                    $tabela .= 'SemiPost(\'Formbusca\',\''.$ext2.$ext3.'\',\''.$smarty->get_config_vars('page_main').'\')"/>
                                </td>';
                }
                $tabela .= "</tr>";
            }
            
        }else{
            $x = count($this->caption) + count($adm);
            $tabela .= "<tr><td colspan=$x>".$smarty->get_config_vars('ERROR_011')."</td></tr>";
        }
        $tabela .= '</table>';
        return $tabela;
    }
    
    ####################################################################
    ## METODO Exibe o total de linhas encontrado                      ##
    ## exibeTotal($params, &$smarty)                                  ##
    ## $params = ARRAY => RECEBE TODOS OS PARAMETROS DA TAG           ##
    ## $smarty = OBJETO => RECEBE O OBJETO DO TEMPLATE                ##
    ####################################################################
    function exibeTotal($params, &$smarty){
     return $this->NumResult;
    }
    ####################################################################
    ## METODO EXIBE A QUANTIDADE MINIMA EXIBIDA                       ##
    ## exibeAtualMin()                                                ##
    ####################################################################
    function exibeAtualMin(){
        return $nmin= (($this->atual * $this->numpage) - $this->numpage)+1;
    }
    ####################################################################
    ## METODO EXIBE A QUANTIDADE MAXIMA EXIBIDA                       ##
    ## exibeAtualMax()                                                ##
    ####################################################################
    function exibeAtualMax(){
        $nmin= (($this->atual * $this->numpage) - $this->numpage)+1;
        return $nmax= ($this->numpage + $nmin)-1;
    }
    ####################################################################
    ## METODO EXIBE A LINHA DE NAVEGAÇÃO DAS PAGINAS                  ##
    ## exibeTotal($params, &$smarty)                                  ##
    ## $params = ARRAY => RECEBE TODOS OS PARAMETROS DA TAG           ##
    ## $smarty = OBJETO => RECEBE O OBJETO DO TEMPLATE                ##
    ##      Opcionais                                                 ##
    ##      @exibe = INT - NUMERO DE LINKS DA NAVEGAÇÃO               ##
    ##      @space = string - SEPARADOR DAS PAGINAS                   ##
    ## RETORNA UMA TAG COM A PAGINAÇÃO                                ##
    ####################################################################
    function ExibeSQLNav($params, &$smarty){
        if(!$params["exibe"] || $params["exibe"] == ""){
            $params["exibe"] = 'all';
        }
        if(!$params["space"] || $params["space"] == ""){
            $params["space"] = " ";
        }
        $cgi =$smarty->cfg["index"].'?page='.$smarty->actualpage['page_name'].CriaUrlVec($this->uri,array('atual'));
        $rew = $this->atual - 1;
        $ff = $this->atual + 1;
        if($params['exibe'] == 'all'){
            for($atual = 1; $atual <= $this->pages; $atual++){
                if($this->atual != $atual)
                    $nav[]=array('link'=>$cgi.'&atual='.$atual,'name'=>$atual);
                else
                    $nav[]=array('link'=>$cgi.'&atual='.$atual,'name'=>$atual,'bold'=>true);
            }   
        }else{
            if($this->atual > 1)
                $nav[]= array('link'=>$cgi.'&atual=1','name'=>$smarty->get_config_vars('nav_rw'));
            if($this->atual != 1)
                $nav[]= array('link'=>$cgi.'&atual='.$rew,'name'=>$smarty->get_config_vars('nav_back'));
            if($this->atual < $params['exibe']){
                $exmax=($this->pages < $params['exibe'])?$this->pages:$params['exibe'];
                $ini=1;
            }else{
                $ini = $this->atual - floor(($params['exibe']/2)) ;
                $exmax = floor(($params['exibe']/2)) + $this->atual;
                $exmax=($exmax > $this->pages)?$this->pages:$exmax;
            }
            for($atual = $ini; $atual <= $exmax;$atual++){
                if($this->atual != $atual)
                    $nav[]=array('link'=>$cgi.'&atual='.$atual,'name'=>$atual);
                else
                    $nav[]=array('link'=>$cgi.'&atual='.$atual,'name'=>$atual,'bold'=>true);
            }
            if($this->atual != $this->pages)
                $nav[]= array('link'=>$cgi.'&atual='.$ff,'name'=>$smarty->get_config_vars('nav_next'));
            if($this->atual < $this->pages)
                $nav[]= array('link'=>$cgi.'&atual='.$this->pages,'name'=>$smarty->get_config_vars('nav_ff'));
            }
            foreach($nav as $v){
                if(isset($v['bold']))
                    $v['name'] = '<strong>'.$v['name'].'</strong>';                
                $navegador .= '<a href="#" onclick="loadPage(\''.$smarty->get_config_vars('page_main').'\',\''.$v['link'].'\')">'.$v['name'].'</a>'.$params["space"];
            }
        return $navegador;
               
    }
    ######################################################################
    ## METODO EXIBE OS REGISTROS DE UM DETERMINADO PARAMETRO            ##
    ## exibeTotal($params,&$smarty)                                     ##
    ## $params = ARRAY => RECEBE TODOS OS PARAMETROS DA TAG             ##
    ## $smarty = OBJETO => RECEBE O OBJETO DO TEMPLATE                  ##
    ##      Parametros                                                  ##
    ##      @select = sql - nome da coluna a ser selecionada            ##
    ##      @tabela = sql - nome da tabela do regidtro                  ##
    ##      @coluna = sql - Coluna indice para o registro               ##
    ##      @idde = sql - valor do registro do registro                 ##
    ##      Opcionais                                                   ##
    ##      @prefix = string - prefixo para tradução dos nomes          ##
    ######################################################################
    function exibeRegistro($params,&$smarty){
     if(!isset($params['select']))
        $params['select'] = "*";
        $result = $this->SqlSelect("SELECT $params[select] FROM $params[tabela] WHERE $params[coluna] = '$params[idde]'");
        if(mysql_affected_rows() > 0){
            if(isset($params['prefix'])){
                $searcharray = array("/^(".$params['prefix'].")(_)([a-zA-Z0-9_]+)$/siU");
                $replacearray = array($params['prefixch'].'\\2\\3');
                $item = mysql_fetch_array($result);
                foreach($item as $chave => $valor){
                    $real = preg_replace($searcharray, $replacearray, $chave);
                    $nitem[$real] = $valor;
                }
                return $nitem;
            }else{
                return mysql_fetch_array($result);
            }
        } 
    }
}
?>