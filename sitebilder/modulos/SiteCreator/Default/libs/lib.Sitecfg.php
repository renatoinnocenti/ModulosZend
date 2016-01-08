<?
####################################################################
## CLASSE PARA MANIPULAÇÃO DE COFIGURAÇÃO                         ##
## CLASS.SiteAtr.PHP VERSÃO 1.0  - 08/08/2005                     ##
## CRIADO POR ART-2 => RENATO INNOCENTI                           ##
## EMAIL: r.innocenti@uol.com.br                                  ##
####################################################################
error_reporting(E_ALL & ~ E_NOTICE);
######################################################################
## CRIA INSTANCIA PARA construção de  site                          ##
## AS ENTRADAS SÃO REFERENTES AO SMAR|TY                            ##
## Classe: Sitecfg                                                  ##
## Arquivo depende de SMARTY.php                                    ##
######################################################################
class Sitecfg extends Smarty{
    var $cfg = array();
	var $perfil = array();
	var $page = array();
	##################################################################
    ## METODO INICIALIZADOR INICIA AS CONFIGURAÇÕES NORMAIS MAIS AS ##
    ## CONFIGURAÇÕES INTERNAS                                       ##
    ## Sitecfg()                                                    ##
    ##################################################################
	function Sitecfg(){
		$this->LoadConfigs();
  		$mysql = new MYSQL($this);
		$result = $mysql->SqlSelect("Select * From {$this->cfg[db_prefix]}config",__FILE__,__LINE__);
		while($ref = mysql_fetch_array($result,MYSQL_ASSOC)){
            $this->cfg[$ref['item']] = $ref['valor'];
		}
	}
	######################################################################
    ## SETAGEM INTERNA CONFIGURA AS PRINCIPAIS VARIAVEIS DE             ##
    ## ENDEREÇAMENTO DE SERVIDOR E DE ACESSO BANCO DE DADOS             ##
    ## LoadConfigs($refbd,$refpt);                                      ##
    ## $refbd = string = Nome do arquivo de config de banco de daods    ##
    ## $refpt = string = Nome do arquivo de config de endereçamento     ##
    ######################################################################
	function LoadConfigs($refbd='bd',$refpt='patch'){
        $this->config_load($refbd.".conf",$refbd);
        $this->config_load($refpt.".conf",refpt);
		$this->template_dir = $this->get_config_vars('template_dir');
		$this->compile_dir = $this->get_config_vars('compile_dir');
		$this->config_dir = $this->get_config_vars('config_dir');
		$this->cache_dir = $this->get_config_vars('cache_dir');
		$this->sites_dir = $this->get_config_vars('sites_dir');
		$this->cfg['db_server'] = $this->get_config_vars('db_server');
		$this->cfg['db_user'] = $this->get_config_vars('db_user');
		$this->cfg['db_passwd'] = $this->get_config_vars('db_passwd');
		$this->cfg['db_name'] = $this->get_config_vars('db_name');
		$this->cfg['db_prefix'] = $this->get_config_vars('db_prefix');
		//$this->plugins_dir[] = './libs/';
		$this->clear_config($refbd);
	}
	######################################################################
    ## SETAGEM INTERNA CONFIGURA O PAIS DE ACESSO DO USUARIO            ##
    ## ConfigLocation($date)                                            ##
    ## $date = DATA - DATA NO FORMATO BR (dd/mm/aaaa)                   ##
    ## RETORNA OS DADOS DE CONFIGURAÇÕES PARA O PAIS DE ORIGEM          ##
    ######################################################################
	function ConfigLocation(){
		$mysql = new MYSQL($this);
		$result = $mysql->SqlSelect("Select *
									From {$this->cfg[db_prefix]}iptocountry
									WHERE ip_from <= inet_aton('$_SERVER[REMOTE_ADDR]')
									AND ip_to >= inet_aton('$_SERVER[REMOTE_ADDR]')",__FILE__,__LINE__);
		if(mysql_affected_rows()>0){
			$country = mysql_fetch_array($result,MYSQL_ASSOC);
		}else{
			$country = array(    'ip_from' =>$_SERVER[REMOTE_ADDR],
    			                 'ip_to' =>$_SERVER[REMOTE_ADDR],
    			                 'country_code2' => 'BR',
    			                 'country_code3' => 'BRA',
    			                 'country_name' => 'BRAZIL');
		}
		$result = $mysql->SqlSelect("Select *
									From {$this->cfg[db_prefix]}cfg_pais
									WHERE country_code2 = '{$country[country_code2]}'",__FILE__,__LINE__);
        return $country = mysql_fetch_array($result,MYSQL_ASSOC);
        $this->cfg += $country;
	}
	######################################################################
    ## SETAGEM INTERNA CONFIGURA O PAIS DE ACESSO DO USUARIO            ##
    ## ConfigLocation($date)                                            ##
    ## $date = DATA - DATA NO FORMATO BR (dd/mm/aaaa)                   ##
    ## RETORNA OS DADOS DE CONFIGURAÇÕES PARA O PAIS DE ORIGEM          ##
    ######################################################################
	function Confglanguage(){ 
        if(!isset($_SESSION['language']) && !isset($_GET["lng"])){
            ## se Não tem nada configurado
            $_SESSION['language'] = ($this->get_config_vars('languagefile') == true)?$this->get_config_vars('languagefile'):($this->cfg['country_lng'] == true)?$this->cfg['country_lng']:"pt-br";
        }elseif(isset($_GET["lng"])){
            ##se get for ativado
            $_SESSION['language'] = $_GET["lng"];
        }
        if(@is_file($this->config_dir.$_SESSION['language'].".conf")){
            $this->clear_config("language");
 			$this->config_load($_SESSION['language'].".conf","language");
 		}else{
            $this->clear_config("language");
 			$this->config_load('pt-br'.".conf","language");               
        }       
 		$this->cfg['language_atual']= $_SESSION['language'];
 	}
    ######################################################################
    ## SETAGEM INTERNA CONFIGURA OS DADOS DE USUARIO EM PERFIL          ##
    ## LoadPerfil($ID_MEMBER)                                           ##
    ## $ID_MEMBER = int => nUMERO DE id DO REGISTRO                     ##
    ## RETORNA O OBJETO COM O PERFIL DO USUARIO ATUAL                   ##
    ######################################################################
	function LoadPerfil($ID_MEMBER=false){
        if($ID_MEMBER == false){
            if(isset($_COOKIE[$this->cfg['cookie']])){
                list($username, $password) = @unserialize(stripslashes($_COOKIE[$this->cfg['cookie']]));
			     $username = ($username != '') ? $username : 'Guest';
            }else{
                $this->perfil = $this->LoadPerfilGuest();
            }
        }else{
            ## chamando registro
            $username = $ID_MEMBER;
        }
        $mysql = new MYSQL($this);
		$result = $mysql->SqlSelect("SELECT * FROM {$this->cfg[prefix]}members WHERE ID_MEMBER = '$username'",__FILE__,__LINE__);
		if(mysql_affected_rows()>0){
			$this->perfil = mysql_fetch_array($result,MYSQL_ASSOC);
        }else{
            $this->perfil = $this->LoadPerfilGuest();
		}
		return $this->perfil;    
    }
    ######################################################################
    ## SETAGEM INTERNA CONFIGURA OS DADOS DE USUARIO CONVIDADO          ##
    ## LoadPerfilGuest()                                                ##
    ## RETORNA O OBJETO COM O PERFIL DO USUARIO ATUAL                   ##
    ######################################################################
    function LoadPerfilGuest(){
        $perfil['ID_MEMBER'] = "-1";
        $perfil['member_name'] = "Guest";
        $perfil['member_real'] = "Guest";
        $perfil['member_password'] = "";
        $perfil['member_ip'] = $_SERVER['REMOTE_ADDR'];
        $perfil['member_nivel'] = 0;
        return $perfil;
    }
    ######################################################################
    ## SETAGEM INTERNA CONFIGURA AS PAGINAS QUE O USUARIO TEM PERMISSÃO ##
    ## LoadPages()                                                      ##
    ## RETORNA O OBJETO COM AS PAGINAS DE ACESSO                        ##
    ######################################################################
    function LoadPages(){
 		$mysql = new MYSQL($this);
 		$nivel = $this->getVars('member_nivel');
 		$group = explode(";",$this->getVars('member_group'));
 		foreach($group as $valor){
 			if($valor == "")continue;
 			$ex .="OR page_acess like '$valor' ";
 		}
 		$result = $mysql->SqlSelect("SELECT * FROM {$this->cfg[prefix]}pages WHERE page_nivel <= '$nivel' $ex ");
 		while($page = mysql_fetch_array($result,MYSQL_ASSOC)){
 			$this->page[$page['page_name']] =  $page;
 		}
 	}
 	######################################################################
    ## SETAGEM INTERNA CONFIGURA A PAGINA ATUAL A SER CARREGADA         ##
    ## SetPages()                                                       ##
    ######################################################################
 	function SetPages(){
        if(isset($_POST['page'])){
            $page = $_POST['page'];
        }elseif(isset($_GET['page'])){
            $page = $_GET['page'];
        }elseif(!isset($page)){
            $page = "index";
        }
        if(($this->cfg['manutencao'] == true) && ($this->perfil['member_group']!= 'Administrador')){
 			$page = "manutencao";
 		}
 	    if(($this->cfg['forcelogin'] == '1')&&($this->perfil['ID_MEMBER'] == 'Guest')){
            $page = "login";
        }
        if($this->getPages($page)== false){
             return $this->getFinishError('ERROR_001');
        }else{
            $this->actualpage =  $this->getPages($page);
 			$this->assign($this->getPages($page));
        }
 	}
 	######################################################################
    ## SETAGEM INTERNA CARREGA OS MODULOS NECESSÁRIOS PARA A PAGINA     ##
    ## LoadModulos($modulo)                                             ##
    ## $modulo => string -> nome do modulo a ser carregado              ##
    ##                      (mesmo da pasta)                            ##
    ## RETORNA O OBJETO COM AS PAGINAS DE ACESSO                        ##
    ######################################################################
 	function LoadModulos($modulo = false){
      	$modulo = ($modulo != false)?$modulo:$this->actualpage["page_modulo"];
        if($modulo == true){
            $this->modulo_dir = $this->get_config_vars('patch').$this->get_config_vars('modulo_dir').$modulo.'/';
            if(!is_dir($this->modulo_dir)){
                return $this->getFinishError('ERROR_002',array('',' - '.$modulo.' ('.$this->actualpage["page_name"].')'));
            }
            foreach(glob($this->modulo_dir."*.php") as $fn){
      	     require_once($fn);
		    }
		    if(is_file($this->modulo_dir.$this->cfg['language_atual'].'.'.$modulo.'.conf'))
				$this->config_load($this->modulo_dir.$this->cfg['language_atual'].'.'.$modulo.'.conf',"modulo");
        }
    }
    ######################################################################
    ## EXECUTA A FUNÇÃO DA PAGINA E CARREGA OS TEMPLATES                ##
    ## LoadFunction()                                                   ##
    ## RETORNA O a EXIBIÇÃO DA PAGINA DENTRO DO SISTEMA                 ##
    ######################################################################
    function LoadFunction(){
        $uri = array_merge($_POST,$_GET);
        if($this->actualpage["page_fnc"] != '' || $this->actualpage["page_fnc"] != NULL){
            $fnc = $this->actualpage["page_fnc"];
            $out = $fnc($this,$uri);
            if($out == true)
                return $out;
        }elseif($this->actualpage["page_tpl"] != '' || $this->actualpage["page_tpl"] != NULL){
            return $this->fetch($this->modulo_dir.$this->actualpage["page_tpl"]);
        }else{
            return $this->getFinishError('ERROR_015', array("",'('.$this->actualpage["page_name"].')'));
        }
    }
    ######################################################################
    ## RETORNA OS ERROS INTERNOS ACUMULADOS DE ARRAY                    ##
    ## getError($error,$tag,$class)                                     ##
    ## $error => string -> NOME DO ARRAY DE ERRO                        ##
    ## $tag => string -> NOME DA TAG A SER EXIBIDA O ERRO               ##
    ## $class => string -> CLASSE CSS PARA A EXIBIÇÃO                   ##
    ######################################################################
    function getError($error,$tag='p',$class='error'){
        foreach($error as $tipo => $campo){
            foreach($campo as $Nomedocampo){
                $Nomedocampo = trim($Nomedocampo);
                $campo = trim($campo);
                $Nomecampo = ($this->get_config_vars($Nomedocampo))?$this->get_config_vars($Nomedocampo):$Nomedocampo;
                $campos .= (isset($campos))?', '.$Nomecampo:$Nomecampo;
            }
            $erro .= '<'.$tag.' class="'.$class.'">'.$this->get_config_vars($tipo)."(".$campos.")".'</'.$tag.'>';
            $campos = Null;
        }
        return $erro;
    }
    ######################################################################
    ## RETORNA OS SUCESSO INTERNOS FINALIZANDO A EXECUÇÃO               ##
    ## getFinish($sucesso,$complemento=,$tag,$class)                    ##
    ## $error => string -> NOME DO ARRAY DE sucesso                     ##
    ## $complemento => array -> Complemento de texto [0]inicio [1]final ##
    ## $tag => string -> NOME DA TAG A SER EXIBIDA O ERRO               ##
    ## $class => string -> CLASSE CSS PARA A EXIBIÇÃO                   ##
    ######################################################################
    function getFinish($sucesso,$complemento=array("",""),$tag='p',$class='sucess'){
        $sucesso = '<'.$tag.' class="'.$class.'">'.$complemento[0].$this->get_config_vars($sucesso).$complemento[1].'</'.$tag.'>';
        $out = ereg_replace("\'", "&#039;", $sucesso);
        return ereg_replace("(\r\n|\n|\r|\t)", "", $out);
    }
    ######################################################################
    ## RETORNA OS ERROS INTERNOS FINALIZANDO A EXECUÇÃO                 ##
    ## getFinish($sucesso,$complemento=,$tag,$class)                    ##
    ## $error => string -> NOME DO ARRAY DE erro                        ##
    ## $complemento => array -> Complemento de texto [0]inicio [1]final ##
    ## $tag => string -> NOME DA TAG A SER EXIBIDA O ERRO               ##
    ## $class => string -> CLASSE CSS PARA A EXIBIÇÃO                   ##
    ######################################################################
    function getFinishError($error,$complemento=array("",""),$tag='p',$class='error'){
        $error = '<'.$tag.' class="'.$class.'">'.$complemento[0].$this->get_config_vars($error).$complemento[1].'</'.$tag.'>';
        $out = ereg_replace("\'", "&#039;", $error);
        return ereg_replace("(\r\n|\n|\r|\t)", "", $out);
    }
    ######################################################################
    ## RETORNA um item da configuração personalizada                    ##
    ## getConfg()                                                       ##
    ######################################################################	
    function getConfg(){
		return $this->cfg;
	}
	######################################################################
    ## RETORNA um item da configuração de template                      ##
    ## getVars($name)                                                   ##
    ## $name = string - nome da chave                                   ##
    ######################################################################
	function getVars($name){
		return $this->_tpl_vars[$name];
		}
	######################################################################
    ## RETORNA um item da configuração de paginas                       ##
    ## getVars($name)                                                   ##
    ## $name = string - nome da chave                                   ##
    ######################################################################
	function getPages($name){
		if(isset($this->page[$name]))
            return $this->page[$name];
		else
            return false;
	}
}
?>