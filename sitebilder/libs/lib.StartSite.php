<?php
####################################################################
## CLASSE PARA MANIPULAÇÃO DE COFIGURAÇÃO
## CLASS.StartSite.PHP VERSÃO 0.1  - 18/01/2010 
## RENATO INNOCENTI                           					  ##
## EMAIL: renato.innocenti@gmail.com                              ##
####################################################################
error_reporting(E_ALL & ~ E_NOTICE);
######################################################################
## CRIA INSTANCIA PARA construção de  site  
## AS ENTRADAS SÃO REFERENTES AO SMARTY 
## Classe: StartSite 
## Arquivo depende de SMARTY.php
######################################################################
define('SITE_CONFIG','./configs/');
define('CONFIG_FILE','config.php');
define('SITEBILDER_CONFIGURATION', 0);
define('SITEBILDER_LANGUAGE', 1);
define('SITEBILDER_ACCESS',2);
define('SITEBILDER_PERFIL', 3);
define('SITEBILDER_SESSION', 4);
define('SITEBILDER_PAGE', 5);
define('SITEBILDER_SAJAX', 6);
define('SITEBILDER_FULL', 7);
define('LANGUAGE_NEGOTIATION_NONE', 0);
define('LANGUAGE_NEGOTIATION_PATH_DEFAULT', 1);
define('LANGUAGE_NEGOTIATION_PATH', 2);
define('LANGUAGE_NEGOTIATION_DOMAIN', 3);

class StartSite extends Smarty{
	public static $cfg = array();
	public static $perfil = array();
	public $actualpage = array();
	public static $language = array();
	public $map = array();
	public static $debug = array();
	
	################################################################################
	## CRIA INSTANCIA PARA INICIALIZAR O SITE SEGUINDO UMA SEQUENCIA DE EXECUÇÃO
	##  
	## Classe: inicializador($phase); 
	## $phase - fase de conclusão do site;
	######################################################################	
	public function inicializador($phase){
		static $phases = array(SITEBILDER_CONFIGURATION, SITEBILDER_LANGUAGE, SITEBILDER_ACCESS, SITEBILDER_PERFIL, SITEBILDER_SESSION, SITEBILDER_PAGE, SITEBILDER_SAJAX, SITEBILDER_FULL), $phase_index = 0;
		$this->unset_globals();
		timer_start('index.php');
		/* inicia as fazes de configuração. */
		while ($phase >= $phase_index && isset($phases[$phase_index])) {
			$current_phase = $phases[$phase_index];
			unset($phases[$phase_index++]);
			$this->sitebilder_inicializador($current_phase);
		}
		$this->debug['index.php'] = timer_stop('index.php');
	}
	################################################################################
	## INICIA AS FASES DE INICIALIZAÇÃO PARA QUE NÃO VOLTE AO INICIO (?)
	##  
	## Classe: sitebilder_inicializador($phase); 
	## $phase - FASE ATUAL
	######################################################################
	private function sitebilder_inicializador($phase){
		switch ($phase) {
			case SITEBILDER_CONFIGURATION:
				timer_start('config');
				$this->LoadConfig();
				$this->LoadConfigDb();
				$this->LoadConfigSmarty();
				$this->ConfigLocation();
			break;

		case SITEBILDER_LANGUAGE:
			$this->ListLanguages();
			$this->GetLanguage();
			$this->LoadLanguage();
			break;

		case SITEBILDER_ACCESS:
			if ($this->is_denied('host', ip_address())) {
				header('HTTP/1.1 403 Forbidden');
				print 'Sorry, '. check_plain(ip_address()) .' has been banned.';
				exit();
			}
			break;
		case SITEBILDER_PERFIL:
			$this->LoadPerfil();
			break;
		case SITEBILDER_SESSION:
			session_start();
			$this->LoadMap();
			break;

		case SITEBILDER_PAGE:
			$this->LoadPages();
			$this->LoadModulo();
			break;

		case SITEBILDER_SAJAX:

			break;

		case SITEBILDER_FULL:

		  break;
		}
	}
	
	##################################################################
    ## METODO INICIALIZADOR INICIA AS CONFIGURAÇÕES NORMAIS MAIS AS
    ## CONFIGURAÇÕES INTERNAS 
    ## LoadConfig()
	## define a global $cfg;
    ##################################################################
	private function LoadConfig(){
	// /* verifica se o host é válido */
		if (isset($_SERVER['HTTP_HOST'])) {
			$_SERVER['HTTP_HOST'] = strtolower($_SERVER['HTTP_HOST']);
			if (!sitebilder_valid_http_host($_SERVER['HTTP_HOST'])) {
				header('HTTP/1.1 400 Bad Request');
				exit;
			}
		}else {
			$_SERVER['HTTP_HOST'] = '';
		}
		$this->configLoad('mydb.conf',"Database");
		$this->cfg = $this->getConfigVars();
		$this->clearConfig();
		$this->configLoad('my.conf');
		$this->cfg += $this->getConfigVars();		
		if ($cookie_domain) {
			$session_name = $cookie_domain;
		}else {
			list( , $session_name) = explode('://', $base_url, 2);
			if (!empty($_SERVER['HTTP_HOST'])) {
				$cookie_domain = check_plain($_SERVER['HTTP_HOST']);
				$cookie_domain = ltrim($cookie_domain, '.');
				if (strpos($cookie_domain, 'www.') === 0) {
					$cookie_domain = substr($cookie_domain, 4);
				}
				$cookie_domain = explode(':', $cookie_domain);
				$cookie_domain = '.'. $cookie_domain[0];
			}
		}
		if (ini_get('session.cookie_secure')) {
			$session_name .= 'SSL';
		}
		if (count(explode('.', $cookie_domain)) > 2 && !is_numeric(str_replace('.', '', $cookie_domain))) {
			ini_set('session.cookie_domain', $cookie_domain);
		}
		session_name('SESS'. md5($session_name));
		$this->cfg['session_name'] = $session_name;
		$this->cfg['cookie_domain'] = $cookie_domain;
		$GLOBALS['cfg'] = &$this->cfg;
		$GLOBALS['translation'] = &$this->language['translation'];
		$GLOBALS['perfil'] = &$this->perfil;
		$GLOBALS['map'] = &$this->map;
		$GLOBALS['actualpage'] = &$this->actualpage;		
	}
	##################################################################
    ## Metodo que carrega as configurações extras do banco de dados
    ## CONFIGURAÇÕES INTERNAS 
    ## LoadConfigDb()
    ##################################################################
	
	private function LoadConfigDb(){
		$mysql = new MYSQL($this);
		$params = array(
			'SetDb' => $this->cfg['db_prefix'].'config',
			'SetTable'=> '*'
		);
		$sql = $mysql->CreateSelectSQL($params,$this);
		$result = $mysql->SqlSelect($sql,__FILE__,__LINE__);
		while($row= mysql_fetch_assoc($result)){
			$this->cfg[$row['item']] = $row['valor'];		
		}
	}
	##################################################################
    ## Metodo que carrega as configurações extras do banco de dados
	## configuraa as variaveis internas do SMARTY
    ## CONFIGURAÇÕES INTERNAS 
    ## LoadConfigSmarty()
    ##################################################################
	private function LoadConfigSmarty(){
		$this->plugins_dir[] = $this->cfg['plugin_dir'];
		$this->allow_php_templates= $this->cfg['allow_php_templates'];
		$this->force_compile = $this->cfg['force_compile'];
		$this->caching = $this->cfg['caching'];
		$this->cache_lifetime = $this->cfg['cache_lifetime'];
		$this->debugging = $this->cfg['debugging'];
	}
	
	######################################################################
    ## SETAGEM INTERNA CONFIGURA O PAIS DE ACESSO DO USUARIO 
    ## ConfigLocation()
    ## RETORNA OS DADOS DE CONFIGURAÇÕES PARA O PAIS DE ORIGEM 
    ######################################################################
	private function ConfigLocation(){
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
        $country = mysql_fetch_array($result,MYSQL_ASSOC);
        $this->cfg += $country;
		return $country;
	}
	######################################################################
    ## SETAGEM INTERNA CONFIGURA as linguas disponiveis
    ## LoadLanguages()
    ## Configura $cfg['language']; com as linguas disponiveis no BD
    ######################################################################	
	private function ListLanguages(){
		// Primeiro carregar as linguas disponiveis em banco de dados
		$mysql = new MYSQL($this);
		$params = array(
			'SetDb' => $this->cfg['db_prefix'].'languages',
			'SetTable'=> '*',
			'SetOrdenar' => array("weight"=> "ASC", "name"=> "ASC")
		);
		$sql = $mysql->CreateSelectSQL($params,$this);
		$result = $mysql->SqlSelect($sql,__FILE__,__LINE__);
		while ($row = mysql_fetch_object($result)) {
			$this->cfg['language'][$row->language] = $row;
		}
	}
	##################################################################
    ## Metodo que carrega a linguagem a ser considerada atual
    ## CONFIGURAÇÕES INTERNAS 
    ## GetLanguage()
	## Set - language['language_default']
    ##################################################################
	private function GetLanguage(){
		//negociar a tradução o GET sempre tem prioridade.
		if($this->perfil->MEMBER_ID > 0 && isset($languages[$this->perfil->language])){
			$this->cfg['language_default'] = $this->cfg['language'][$this->perfil->language];
		}elseif(isset($this->cfg['language'][$this->cfg['country_lng']])){
			$this->cfg['language_default'] = $this->cfg['language'][$this->cfg['country_lng']];
		}
		if(isset($_GET['q'])){
			$args = explode('/', $_GET['q']);
			if(array_key_exists($args[0], _locale_get_predefined_list())){
				$prefix = array_shift($args);
				if($this->cfg['language'][$prefix]){
					$this->cfg['language_default'] = $this->cfg['language'][$prefix];
				}else{
					$this->cfg['language_default'] = language_default();
				}
				$_GET['q'] = implode('/', $args);
			}
		}
		if(!$this->language['language_default']){
			$this->cfg['language_default'] = language_default();
		}
	}
	##################################################################
    ## Metodo que verifica se o IP esta banido
    ## CONFIGURAÇÕES INTERNAS 
    ## is_denied($type, $mask)
	## $type = string - tipo de banimento (host, email)
	## $mask = string - valor a verificar
    ##################################################################
	private function is_denied($type, $mask){
		$mysql = new MYSQL($this);
		$sql = "SELECT 1 FROM {$this->cfg[db_prefix]}access WHERE type = '%s' AND LOWER('%s') LIKE LOWER(mask) AND status = %d";
		return $mysql->dbResult($mysql->SqlSelect($mysql->db_query_range($sql, $type, $mask, 0, 0, 1),__FILE__,__LINE__))  && !$mysql->db_result($mysql->SqlSelect($mysql->db_query_range($sql, $type, $mask, 1, 0, 1),__FILE__,__LINE__));
	}
	######################################################################
    ## SETAGEM INTERNA CONFIGURA OS DADOS DE USUARIO EM PERFIL 
    ## LoadPerfil($ID_MEMBER) 
    ## $ID_MEMBER = int => NUMERO DE ID DO REGISTRO
    ## RETORNA O OBJETO COM O PERFIL DO USUARIO ATUAL 
    ######################################################################
	public function LoadPerfil($ID_MEMBER=false){
        if($ID_MEMBER == false){
            if(isset($_COOKIE[$this->cfg['cookie']])){
                list($username, $password) = @unserialize(stripslashes($_COOKIE[$this->cfg['cookie']]));
			     $username = ($username != '') ? $username : 'Guest';
            }else{
                $perfil = $this->LoadPerfilGuest();
            }
        }else{
            ## chamando registro
            $username = $ID_MEMBER;
        }
        $mysql = new MYSQL($this);
		$result = $mysql->SqlSelect("SELECT * FROM {$this->cfg[db_prefix]}members WHERE ID_MEMBER = '{$username}'",__FILE__,__LINE__);
		if(mysql_affected_rows()>0){
			$perfil = mysql_fetch_object($result,MYSQL_ASSOC);
        }else{
            $perfil = $this->LoadPerfilGuest();
		}
		if($ID_MEMBER == false){
			$this->perfil = $perfil;
		}else{
		return $perfil;
		}
			
    }
	######################################################################
    ## SETAGEM INTERNA CONFIGURA OS DADOS DE USUARIO CONVIDADO
    ## LoadPerfilGuest()  
    ## RETORNA O OBJETO COM O PERFIL DO USUARIO ATUAL 
    ######################################################################
    private function LoadPerfilGuest(){
        return (object) array('ID_MEMBER'=>"0",'member_name'=>"Guest", 'member_real'=>"Guest", 'member_password'=>"", 'member_ip'=>ip_address());
    }
	######################################################################
    ## SETAGEM INTERNA CONFIGURA A TRADUÇÃO PRINCIPAL
    ## LoadLanguage() 
    ## CRIA O OBJETO $po COM A LINGUAGEM DE TRADUÇÃO.
    ######################################################################
	public function LoadLanguage($group = 'default'){ 
		$mysql = new MYSQL($this);
		$langto = $this->cfg['language_default'];
		$sql = "SELECT s.lid, s.source, s.textgroup, s.location, t.translation, t.plid,
					(SELECT t.translation FROM {$this->cfg[db_prefix]}locales_target t WHERE s.lid = t.plid) AS tplural,
					(SELECT s.source FROM  {$this->cfg[db_prefix]}locales_source s Where s.lid = 
					(SELECT t.lid FROM {$this->cfg[db_prefix]}locales_target t WHERE t.translation = tplural)) AS plural
				FROM {$this->cfg[db_prefix]}locales_source s
				LEFT JOIN  {$this->cfg[db_prefix]}locales_target  t ON s.lid = t.lid
				AND t.language = '{$langto->language}'
				WHERE s.textgroup = '{$group}' AND t.plural = false";
		$result = $mysql->SqlSelect($sql,__FILE__,__LINE__);
		if(mysql_affected_rows()> 0 ){
			while($linha= mysql_fetch_assoc($result)){
				$args= poFormater($linha);
				$this->language['translation'][$linha['source']] = new Translation_Entry($args);
			}
		}else{
			if($po_file = $this->LoadPo($langto->language, $group)){
				//carrega uma linguagem para o banco de dados caso não exista nenhuma
				$args = po_db($po_file,$group);
				$result = $mysql->SqlSelect($mysql->SqlInsert($args),__FILE__,__LINE__);
				$this->language['translation'] = $po_file;
			}
		}		
	} 
	
	######################################################################
    ## SETAGEM INTERNA CONFIGURA AS PAGINAS QUE O USUARIO TEM PERMISSÃO
    ## LoadPages() 
    ## RETORNA O OBJETO COM AS PAGINAS DE ACESSO
    ######################################################################
    public function LoadMap(){
		$perfil = $this->perfil;
		if($this->cfg['manutencao'] == true && true == LoadRules($perfil->ID_MEMBER,'Administrator',$this)){
 			$_GET['q'] = "manutencao";
 		}
		if($this->cfg['forcelogin'] == true && true == LoadRules($perfil->ID_MEMBER,'ONLY_GUEST',$this)){
 			$_GET['q'] = "login";
 		}
		$this->map = ReadUrl();
	}
	######################################################################
    ## SETAGEM INTERNA CONFIGURA AS PAGINAS QUE O USUARIO TEM PERMISSÃO
    ## LoadPages() 
    ## RETORNA O OBJETO COM AS PAGINAS DE ACESSO
    ######################################################################
    public function LoadPages(){
		$mysql = new MYSQL($this);
		$result = $mysql->SqlSelect("SELECT * FROM {$this->cfg[db_prefix]}pages",__FILE__,__LINE__);
		while($linha = mysql_fetch_object($result)){
			$page[$linha->page_name] = $linha;	
		}
		if(!$page[$this->map['node']]){
			print "ERROOO A PAGINA QUE ESTA SENDO ACESSADA NÂO EXISTE";
		}else{
			$this->actualpage = $page[$this->map['node']];
		}
	}
	
	######################################################################
    ## Carrega um arquivo de linguagem.po
    ## LoadPo() 
	## Depende das libs Translation.php
	## $lang = String - codigo da linguagem a ser carregada
	## $nome do arquivo (ex: 'principal.pt_br.po)
    ## RETORNA OS DADOS DE CONFIGURAÇÕES PARA O PAIS DE ORIGEM
    ######################################################################	
	public function LoadPo($lang,$modulo='default'){
		$PO = new PO();
		$modulo = $this->cfg['po_dir'].$modulo.'.'.$lang.'.po';
		$modulo2 = $this->cfg['modulo_dir'].'/'.$modulo.'/'.$modulo.'.'.$lang.'.po';
		if(file_exists($modulo))
			$PO->import_from_file($modulo);
		elseif(file_exists($modulo2))
			$PO->import_from_file($modulo);
		else
			return false;
		return $PO;	
	}

	######################################################################
    ## Registro de log
    ## logMe($ID_MEMBER,$min='15') 
    ## CRIA UM REGISTRO COM O LOGIN E TEMPO DE LOGIN DE UM USUÁRIO
	## $ID_MEMBER = NUMERO DE ID DE REGISTRO DO MEMBRO A SER LOGADO.
	## $min = TEMPO MAXIMO DE LOGIN SEM MOVIMENTAÇÃO EM MINUTOS
    ######################################################################
	public function logMe($ID_MEMBER,$min='15'){
		$mysql = new MYSQL($this);
		$tabela = $this->cfg['db_prefix'].'log_online';
		$logme['identity'] = $ID_MEMBER;
		$perfil['member_lastlogin'] = $logme['logTime'] = 'NOW()';
		$perfil['member_lestonline'] = $logme['logoutTime'] = "NOW() + INTERVAL ".$min ." MINUTE";
		$logme['url'] = ($this->actualpage['page_name'])?$this->actualpage['page_name']:"index";
		$logme['ip'] = $_SERVER['REMOTE_ADDR'];
		$request = $mysql->SqlSelect("Select identity From {$tabela} where ip = \"{$logme[ip]}\" and identity = \"{$ID_MEMBER}\"",__FILE__,__LINE__);
		mysql_affected_rows();
		if(mysql_affected_rows()>0){
			$sql = $mysql->SqlUpdate($tabela,$logme,"identity = {$ID_MEMBER}");
		}else{
			$sql = $mysql->SqlInsert($tabela,$logme);
		}
		$request = $mysql->SqlSelect($sql,__FILE__,__LINE__);
		$tabela = $this->cfg['db_prefix'].'log_online';
		$sql = $mysql->SqlUpdate($tabela,$logme,"identity =".$ID_MEMBER);
		$request = $mysql->SqlSelect($sql,__FILE__,__LINE__);
		$sql = $mysql->SqlDelete($tabela,"logoutTime < NOW()");
		$request = $mysql->SqlSelect($sql,__FILE__,__LINE__);
		if($ID_MEMBER > 0){
			$tabela = $this->cfg['db_prefix'].'members';
			$sql = $mysql->SqlUpdate($tabela,$perfil,"ID_MEMBER =".$ID_MEMBER);
			$request = $mysql->SqlSelect($sql,__FILE__,__LINE__);
		}		
	}
	######################################################################
    ## SETAGEM INTERNA CARREGA OS MODULOS NECESSÁRIOS PARA A PAGINA  
    ## LoadModulos($modulo)  
    ## $modulo => string -> nome do modulo a ser carregado 
    ##                      (mesmo da pasta) 
    ## RETORNA O OBJETO COM AS PAGINAS DE ACESSO  
    ######################################################################
 	function LoadModulo($modulo = NULL){
      	$page = $this->actualpage;
		$modulo = (isset($modulo))?$modulo:$page->page_modulo;
		if(!empty($modulo)){
			$p_modulo = Arquivo::ChangePath(realpath('./'.$this->cfg['modulo_dir'] .$modulo));
			if(!is_dir($p_modulo)){
				Print "ERROOOO";
			}
			foreach(glob($p_modulo."/*.php") as $fn){
				require_once($fn);
		    }
			foreach(glob($p_modulo."/*.tpl") as $fn){
				$this->actualpage = (object)array('tpl'=>array(basename($fn,'.tpl')=>$fn));
		    }
			print_r($this->actualpage);
			//carregar a linguagem
			$this->LoadLanguage($modulo);
		}
    }
	function getError($msgerro, $tipo=false){
		$valores['err_who'] = ($this->perfil['ID_MEMBER']>0)?$this->perfil['ID_MEMBER']:$this->perfil['member_name'];
		$valores['err_where'] = (!$this->actualpage)?serialize(array('page_name'=>'index')):serialize($this->actualpage);
		$valores['err_type'] = ($tipo==false)?FALSE:TRUE;
		$valores['err_ip'] = $_SERVER['REMOTE_ADDR'];
		$valores['err_how'] = (is_array($msgerro))?implode("</br>", $msgerro):$msgerro;
		$mysql = new MYSQL($this);		
		$tabela = $this->cfg['db_prefix'].'logError';
		$sql = $mysql->SqlInsert($tabela,$valores);
		//$request = $mysql->SqlSelect($sql,__FILE__,__LINE__);
		$tipo = ($tipo==false)?'ms_error':'ms_success';
		$this->assign('MS_ERROR_TYPE',$tipo);
		$this->assign('MS_ERROR',$this->language->translate($msgerro));
		return $this->fetch($tipo.'.tpl');	
	}

	function unset_globals() {
	  if (ini_get('register_globals')) {
		$allowed = array('_ENV' => 1, '_GET' => 1, '_POST' => 1, '_COOKIE' => 1, '_FILES' => 1, '_SERVER' => 1, '_REQUEST' => 1, 'GLOBALS' => 1);
		foreach ($GLOBALS as $key => $value) {
		  if (!isset($allowed[$key])) {
			unset($GLOBALS[$key]);
		  }
		}
	  }
	}
}
?>