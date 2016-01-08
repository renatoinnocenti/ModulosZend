<?php
/**
 * Classe para inicialização do site
 * @file: lib.StartSite.php version (0.4) - 07/02/2011
 * Renato Innocenti
 * Email:renato.innocenti@gmail.com
 **/
define ( 'SITEBILDER_CONFIGURATION', 0 );
define ( 'SITEBILDER_ACCESS', 1 );
define ( 'SITEBILDER_CORE', 2 );
define ( 'SITEBILDER_PERFIL', 3 );
define ( 'SITEBILDER_PATH', 4 );
define ( 'SITEBILDER_CACHE', 5 );
define ( 'SITEBILDER_ACTION', 6 );
define ( 'SITEBILDER_AJAX', 7 );
define ( 'SITEBILDER_FULL', 8 );
define ( 'ANONYMOUS_ID_ROLE', 1 );
define ( 'AUTHENTICATED_ID_ROLE', 2 );
define ( 'LANGUAGE_NEGOTIATION_NONE', 0 );
define ( 'LANGUAGE_NEGOTIATION_PATH_DEFAULT', 1 );
define ( 'LANGUAGE_NEGOTIATION_PATH', 2 );
define ( 'LANGUAGE_NEGOTIATION_DOMAIN', 3 );
define ( 'LANGUAGE_LTR', 0 );
define ( 'LANGUAGE_RTL', 1 );

class StartSite extends Smarty {
	
	public static $cfg = array ();
	public static $perfil = array ();
	public $actualpage = array ();
	public static $language = array ();
	/**
	 * inicializa o site
	 */
	public function initialize($phase) {
		static $phases = array (SITEBILDER_CONFIGURATION, SITEBILDER_ACCESS, SITEBILDER_CORE, SITEBILDER_PERFIL, SITEBILDER_PATH, SITEBILDER_CACHE, SITEBILDER_ACTION, SITEBILDER_AJAX, SITEBILDER_FULL ), $phase_index = 0;
		SB_unset_globals ();
		/*
		 * inicia as fazes de configura��o.
		 */
		$GLOBALS ['cfg'] = &$this->cfg;
		$GLOBALS ['language'] = &$this->language;
		$GLOBALS ['perfil'] = &$this->perfil;
		$GLOBALS ['map'] = &$this->map;
		$GLOBALS ['actualpage'] = &$this->actualpage;
		while ( $phase >= $phase_index && isset ( $phases [$phase_index] ) ) {
			$current_phase = $phases [$phase_index];
			unset ( $phases [$phase_index ++] );
			$this->_initialize ( $current_phase );
		}
	}
	/**
	 * Inicia as fazes de configuração do site
	 * 
	 * @param $phase (int)       	
	 */
	private function _initialize($phase) {
		switch ($phase) {
			case SITEBILDER_CONFIGURATION :
				$this->LoadConfig ();
				$this->LoadConfigDb ();
				$this->LoadConfigSmarty ();
				// $this->LoadConfigAJAX();
				$this->ConfigLocation ();
				$this->GetLanguage ();
				break;
			
			case SITEBILDER_ACCESS :
				$this->getDeined ( "host", ip_address () );
				session_set_save_handler('sess_open', 'sess_close', 'sess_read', 'sess_write', 'sess_destroy_sid', 'sess_gc');
				$this->SetSession ();
				session_start ();
				break;
			case SITEBILDER_CORE :
				$this->LoadAllModules('boot');//carrega todos os modulos que tenham uma ação boot.
				
				break;
			case SITEBILDER_PERFIL :
				$this->LoadPerfil ();
				$this->LoadPermission ();
				break;
			case SITEBILDER_PATH :
				$this->LoadPath ();
				break;
			
			case SITEBILDER_ACTION :
				//$this->LoadPages();
				//$this->LoadModulo();
				break;
			
			case SITEBILDER_AJAX :
				
				break;
			
			case SITEBILDER_FULL :
				$this->StartModulos();
				break;
		}
	}
	/**
	 * Método que inicia as configurações internas.
	 * configura $cfg
	 */
	private function LoadConfig() {
		if (isset ( $_SERVER ['HTTP_HOST'] )) {
			$_SERVER ['HTTP_HOST'] = strtolower ( $_SERVER ['HTTP_HOST'] );
			if (! SB_valid_http_host ( $_SERVER ['HTTP_HOST'] )) {
				header ( 'HTTP/1.1 400 Bad Request' );
				exit ();
			}
		} else {
			$_SERVER ['HTTP_HOST'] = '';
		}
		$this->configLoad ( 'mydb.conf' );
		$this->cfg = $this->getConfigVars ();
		$this->clearConfig ();
		//@todo fazer com que o load config carregue arrays
		$this->cfg ['db_prefix'] = array ('default' => $this->cfg ['db_prefix'] );
		SB_base_url ();
	}
	/**
	 * Metodo que carrega as configurações extras do banco de dados
	 */
	private function LoadConfigDb() {
		//@todo fazer com que o load config carregue arrays do mysql com serealize, mas para isso precisa serializar so valores que ja estão no BD.
		$mysql = new MYSQL ( $this->cfg );
		if ($mysql->MysqlSelectDb ( $this->cfg ['db_name'], $mysql->conexao )) {
			$result = $mysql->SqlSelect ( "SELECT * FROM {config}" );
			while ( $row = mysql_fetch_assoc ( $result ) ) {
				$this->cfg [$row ['item']] = $row ['valor'];
			}
		} else {
			/*
			 * @TODO Não Existe banco de dados para instalação;
			 **/
			install_goto ( 'install.php' );
		}
	}
	/**
	 * Configura as diretrizes do SMARTY
	 */
	private function LoadConfigSmarty() {
		$this->plugins_dir [] = $this->cfg ['plugin_dir'];
		$this->allow_php_templates = $this->cfg ['allow_php_templates'];
		$this->force_compile = $this->cfg ['force_compile'];
		$this->caching = $this->cfg ['caching'];
		$this->cache_lifetime = $this->cfg ['cache_lifetime'];
		$this->debugging = $this->cfg ['debugging'];
	}
	private function LoadConfigAjax($param) {
		$param;
	}
	/**
	 * SETAGEM INTERNA CONFIGURA O PAIS DE ACESSO DO USUARIO 
	 */
	private function ConfigLocation() {
		$mysql = new MysqlConsulta ( $this->cfg );
		$this->cfg = array_merge ( $this->cfg, $mysql->getALLfromIP ( $_SERVER ['REMOTE_ADDR'] ) );
	}
	/**
	 * Configura a linguagem padr�o
	 */
	private function GetLanguage() {
		$mysql = new MysqlConsulta ( $this->cfg );
		$this->language ['language_default'] = $mysql->getLanguagefromCOUNTRY ( $this->cfg ['ID_COUNTRY'] ); //--> en,pt-br,ja,it
		$this->language ['language'] = $mysql->ListLanguages ( $this->language ['language_default'] );
		if (isset($this->perfil->user_language)) {
			$this->language ['language_default'] = $this->perfil->user_language;
			$this->language ['language'] = $mysql->ListLanguages ( $this->perfil->user_language );
		}
		if (isset($_GET ['q'])) {
			$query = getLanguageURL ( $_GET ['q'] );
			if ($query [1]) {
				$this->language ['language_default'] = $query [1];
				$this->language ['language'] = $mysql->ListLanguages ( $query [1] );
			}
			$_GET ['q'] = $query [0];
		}
		if ($this->language ['language']->language != language_default ()->language) {
			$this->language ['translate'] = $mysql->getTranslate ( $this->language ['language']->language );
			if (! $this->language ['translate']) {
				$po = loadPO ( $this->language ['language']->language );
				if ($po == false) {
					//@todo mensagem de errooooo
					$message = _t ( "File not found a standard language for this request." );
					setMessage ( $message, 'error' );
				}
				insertTranslation ( $po );
			} else {
				$po = new PO ();
				$po->set_headers ( $this->language ['translate']->headers );
				$po->merge_with ( $this->language ['translate'] );
			
		//@todo = verificar esta requisições de texto, se a função de tradução não irá cumprir o mesmo papel
			}
			$this->language ['translate'] = $po;
		}
	}
	/**
	 * Verifica se o IP esta bloquiado.
	 * @param string $host
	 * @param string $ip
	 */
	private function getDeined($host, $ip) {
		$mysql = new MysqlConsulta ( $this->cfg );
		if ($mysql->is_denied ( $host, $ip )) {
			header ( 'HTTP/1.1 403 Forbidden' );
			echo _t ( "Sorry, @ipadress has been banned", array ("@ipadress" => ip_address () ) );
			exit ();
		}
	}
	/**
	 * Setagem dos cookies e da sess�o
	 */
	private function SetSession() {
		list ( , $session_name ) = explode ( '://', $this->cfg ['base_url'], 2 );
		if (! empty ( $_SERVER ['HTTP_HOST'] )) {
			$cookie_domain = check_plain ( $_SERVER ['HTTP_HOST'] );
			$cookie_domain = ltrim ( $cookie_domain, '.' );
			if (strpos ( $cookie_domain, 'www.' ) === 0) {
				$cookie_domain = substr ( $cookie_domain, 4 );
			}
			$cookie_domain = explode ( ':', $cookie_domain );
			$cookie_domain = '.' . $cookie_domain [0];
		}
		if (ini_get ( 'session.cookie_secure' )) {
			$session_name .= 'SSL';
		}
		if (count ( explode ( '.', $cookie_domain ) ) > 2 && ! is_numeric ( str_replace ( '.', '', $cookie_domain ) )) {
			ini_set ( 'session.cookie_domain', $cookie_domain );
		}
		session_name ( 'SESS' . md5 ( $session_name ) );
		$this->cfg ['session_name'] = $session_name;
		$this->cfg ['cookie_domain'] = $cookie_domain;
	}
	/**
	 * setagem de perfil de usu�rio
	 */
	public function LoadPerfil() {
		if (isset ( $_COOKIE [$this->cfg ['cookie_domain']] )) {
			list ( $username, $password ) = @unserialize ( stripslashes ( $_COOKIE [$this->cfg ['cookie_domain']] ) );
		}
		$username = (isset($username)) ? $username : 0;
		$mysql = new MysqlConsulta ( $this->cfg );
		$this->perfil = $mysql->getPerfil ( $username );
		$this->GetLanguage ();
	}
	private function LoadPath() {
		if (! empty ( $_GET ['q'] )) {
			$_GET ['q'] = get_normalPath ( trim ( $_GET ['q'], '/' ) );
		} else {
			$_GET ['q'] = get_normalPath ( variable_get ( 'site_frontpage', 'node' ) );
		}
	}
	private function LoadPermission() {
		$this->perfil->roles = defultRoles ( $this->perfil->ID_USER );
		$perm = myRoles ( $this->perfil->ID_USER, $this->perfil->roles );
		$this->perfil->roles += $perm;
	}
	
	private function InvokeAllModules($type){
	$hook = '';
		foreach (listModulos(TRUE, TRUE) as $module) {
    	loadFile('module', $module);
		module_invoke($module, $hook);
  	}
	}
function LoadAllModules() {
  foreach (listModulos(TRUE, FALSE) as $module) {
    loadFile('module', $module);
  }
}
	private function StartModulos(){
	$this->LoadAllModules();
  // Let all modules take action before menu system handles the request
  // We do not want this while running update.php.
  if (!defined('MAINTENANCE_MODE') || MAINTENANCE_MODE != 'update') {
    	$this->InvokeAllModules('init');
  	}
	}

	/**
	 * @todo Duvida sobre esta parte n�o esta usando
	 */
	
	public function logMe($msgerro, $tipo = false) {
		$mysql = new MysqlConsulta ( $this->cfg );
		$mysql->insertLog ( $msgerro, $tipo );
		$tipo = ($tipo == FALSE) ? 'ms_error' : 'ms_success';
		$this->assign ( 'MS_ERROR_TYPE', $tipo );
		$this->assign ( 'MS_ERROR', $msgerro );
		return $this->fetch ( $tipo . '.tpl' );
	}
}