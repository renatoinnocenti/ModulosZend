<?php
/**
 * Biblioteca de consultas mysql para as tabelas country
 * @todo biblioteca temporaria deve se tornar libs dentro de modulos
 * @file: lib.Consultas.php - version (1.0) - 09/02/201123:41:12
 * Renato Innocenti (pre24)
 * Email:renato.innocenti@gmail.com
 **/

class MysqlConsulta extends MYSQL {
	/**
	 * Retorna toda a configuração de country
	 * @param string <ip> $addr
	 * @return multitype:number string |multitype:
	 */
	function getALLfromIP($addr) {
		$ipnum = sprintf ( "%u", ip2long ( ip_address ( $addr ) ) );
		$sql = "SELECT * FROM {country_ip} NATURAL JOIN {country} WHERE ${ipnum} BETWEEN start AND end";
		$result = $this->SqlSelect ( $sql );
		if (mysql_numrows ( $result ) < 1){
			if($addr == "127.0.0.1")
				return array ('ID_COUNTRY' => 64, 'country_code' => "BR", 'country_name' => "Brazil" );
			return array ('ID_COUNTRY' => 30, 'country_code' => "US", 'country_name' => "United States" );
		}else{
			return mysql_fetch_assoc ( $result );
		}
	}
	/**
	 * Retorna o codigo do pais
	 * @param string <ip> $addr
	 * @return Ambigous <boolean, multitype:array>|boolean
	 */
	function getCCfromIP($addr) {
		$data = $this->getALLfromIP ( $addr );
		if ($data)
			return $data ['country_code'];
		return false;
	}
	/**
	 * Retorna o pais baseado em ip
	 * @param string <ip> $addr
	 * @return Ambigous <boolean, multitype: array/string>|boolean
	 */
	function getCOUNTRYfromIP($addr) {
		$data = $this->getALLfromIP ( $addr );
		if ($data)
			return $data ['country_name'];
		return false;
	}
	/**
	 * Retorna um codigo de pais baseado em um url
	 * @param string <url> $name
	 * @return Ambigous <boolean, multitype: array/string>
	 */
	function getCCfromNAME($name) {
		$addr = $this->gethostbyname ( $name );
		return $this->getCCfromIP ( $addr );
	}
	/**
	 * retorna um pais baseado em um url
	 * @param string <url> $name
	 * @return Ambigous <Ambigous, boolean, multitype: array/string>
	 */
	function getCOUNTRYfromNAME($name) {
		$addr = $this->gethostbyname ( $name );
		return $this->getCOUNTRYfromIP ( $addr );
	}
	/**
	 * verifica se a linguagem esta habilitada, se não retorna padrão (ingles)
	 * @param string $lang - Codigo de linguagem
	 * @return Ambiguous <multitype: array/object, boolean>
	 */
	function ListLanguages($lang) {
		$result = $this->SqlSelect ( "SELECT * From {languages} WHERE language = '{$lang}' AND enabled = '1' ORDER BY weight, name ASC" );
		if (mysql_numrows ( $result ) < 1)
			return language_default ();
		$row = NULL;
		while ( $row = mysql_fetch_object ( $result ) )
			$languages = $row;
		return $languages;
	}
	/**
	 * Retorna a linguagem de acordo com o id do pais
	 * @param integer <id> $param
	 * @return Ambigous <string, boolean>
	 */
	function getLanguagefromCOUNTRY($param) {
		$sql = "SELECT language FROM {languages} NATURAL JOIN {country_language} where ID_COUNTRY = {$param}";
		return self::dbResult ( $this->SqlSelect ( $sql ) );
	}
	/**
	 * Verifica se o Ip esta banido
	 * @param string $type - tipo de banimento
	 * @param string $mask - valor a ser consultado
	 * @return boolean
	 */
	function is_denied($type, $mask) {
		$sql = "SELECT 1 FROM {access} WHERE type = '%s' AND LOWER('%s') LIKE LOWER(mask) AND status = %d";
		return self::dbResult ( $this->SqlSelect ( $this->SqlSelectRange ( $sql, $type, $mask, 0, 0, 1 ) ) ) && ! self::dbResult ( $this->SqlSelect ( $this->SqlSelectRange ( $sql, $type, $mask, 1, 0, 1 ) ) );
	}
	/**
	 * Retorna o perfil de um usário especifico
	 * @param integer <id> $id_user - id do usuário
	 * @return StdClass|Ambiguous
	 */
	function getPerfil($id_user) {
		if ($id_user <= 0 || ! is_numeric ( $id_user ))
			return setGuest ();
		$result = $this->SqlSelect ( "SELECT * FROM {users} WHERE ID_USER = '$id_user'" );
		$user = mysql_fetch_object ( $result );
		if ($this->is_denied ( 'email', $user->user_email ) || $this->is_denied ( 'ip', $user->user_ip )) {
			$message = _t ( "Your profile is locked, its access rules were limited guest." );
			$message .= _t ( 'For more information contact an <a href="!url">administrator</a>.', array ('!url' => url ( "user/1", array ('absolute' => TRUE ) ) ) );
			setMessage ( $message, 'warning' );
			return setGuest ();
		} else {
			return $user;
		}
	}
	/**
	 * retorna um vetor com a tradução
	 * @param string $language - lingua referente da tradução
	 * @param string $group - Nome do grupo/modulo que a tradução pertence
	 * @return Translation_Entry
	 */
	function getTranslate($language, $group = 'default') {
		$sql = "SELECT s.*, t.*,
					(SELECT t.translation FROM {locales_target} t WHERE s.lid = t.plid) AS tplural,
					(SELECT s.source FROM  {locales_source} s Where s.lid = 
					(SELECT t.lid FROM {locales_target} t WHERE t.translation = tplural)) AS plural
				FROM {locales_source} s
				LEFT JOIN  {locales_target}  t ON s.lid = t.lid
				AND t.language = '{$language}'
				WHERE s.textgroup = '{$group}' AND t.plural = false";
		$result = $this->SqlSelect ( $sql );
		if (mysql_affected_rows () > 0) {
			while ( $linha = mysql_fetch_assoc ( $result ) ) {
				$args = poFormater ( $linha );
				$headers = hpoFormater ( $linha );
				$out [$linha ['source']] = new Translation_Entry ( $args );
			}
			return ( object ) array ('entries' => $out, "headers" => $headers, "_nplurals" => $language ['language']->plurals, "_gettext_select_plural_form" => "�lambda_1103" );
		}
	}
	function loadRoles($username) {
		$username = (! $username || ! is_numeric ( $username )) ? - 1 : $username;
		if ($username < 0) {
			$sql = "SELECT r.ID_ROLE, r.name FROM {role} WHERE 
		NATURAL JOIN {users_roles} ur WHERE ur.ID_USER = {$username}";
			$request = $this->SqlSelect ( $sql );
			$result = null;
			$role = mysql_fetch_assoc ( $result );
			return $rule [$role ['ID_ROLE']] = $role ['name'];
		}
		return array (ANONYMOUS_ID_ROLE => 'anonymous user' );
	}
	/*	
 * @todo verificar se vai usar e revisar
 **/
	
	function insertLog($msgerro, $tipo = FALSE) {
		global $perfil, $cfg, $actualpage;
		$valores ['err_who'] = $perfil ['ID_USER'];
		//$valores['err_where'] = (!$actualpage)?serialize(getIndex()):serialize($this->actualpage);
		$valores ['err_type'] = ($tipo == FALSE) ? FALSE : TRUE;
		$valores ['err_ip'] = ip_address ();
		$valores ['err_how'] = (is_array ( $msgerro )) ? implode ( "</br>", $msgerro ) : $msgerro;
		$tabela = '{logError}';
		$sql = $this->SqlInsert ( $tabela, $valores );
		return $request = $this->SqlSelect ( $sql );
	}
}