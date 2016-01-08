<?php
/**
 * classe para manipulação de banco de dados
 * @file: _MYSQL.php version (1.0) - 08/02/2011 - 18:52:45
 * Renato Innocenti
 * Email:renato.innocenti@gmail.com
 **/

/*
 * estabelece metodos de manipulação da banco de dados mysql
 */
define ( 'DB_QUERY_REGEXP', '/(%d|%s|%%|%f|%b|%n)/' );
class MYSQL {
	public static $conexao;
	public static $sql = array ();
	public $prefix;
	
	/*
	 * SELECT [STRAIGHT_JOIN]
	 * [SQL_SMALL_RESULT] [SQL_BIG_RESULT] [SQL_BUFFER_RESULT]
	 * [SQL_CACHE | SQL_NO_CACHE] [SQL_CALC_FOUND_ROWS] [HIGH_PRIORITY]
	 * [DISTINCT | DISTINCTROW | ALL]
	 * express�o_select,...
	 * [INTO {OUTFILE | DUMPFILE} 'nome_arquivo' op��es_exporta��o]
	 * [FROM tabelas_ref
	 * [WHERE defini��o_where]
	 * [GROUP BY {inteiro_sem_sinal | nome_col | formula} [ASC | DESC], ...
	 * [WITH ROLLUP]]
	 * [HAVING where_definition]
	 * [ORDER BY {inteiro_sem_sinal | nome_coluna | formula} [ASC | DESC], ...]
	 * [LIMIT [offset,] row_count | row_count OFFSET offset]
	 * [PROCEDURE nome_procedimento(lista_argumentos)]
	 * [FOR UPDATE | LOCK IN SHARE MODE]]
	 */
	
	/**
	 * inicia uma conecção ao banco de dados
	 * @param array $arg - deve conter as referencias [db_server],['db_user'],['db_passwd'],['db_name']
	 */
	function __construct($arg = array()) {
		$this->conexao = $this->MysqlPconnect ( $arg ['db_server'], $arg ['db_user'], $arg ['db_passwd'] );
		if (! $this->conexao)
			die ( 'Not connected : ' . mysql_error () );
		$this->MysqlSelectDb ( $arg ['db_name'], $this->conexao );
		$this->prefix = $arg ['db_prefix'];
	
	}
	/**
	 * SELECIONA O BANCO DE DADOS A SER UTILIZADO MYSQL
	 * @param string $db_name - nome da tabela selecionada
	 * @param resource $conexao - link de conec��o
	 * @return boolean
	 */
	public function MysqlSelectDb($db_name, &$conexao = FALSE) {
		return mysql_select_db ( $db_name, $conexao );
	}
	
	/**
	 * FAZ UMA CHAMADA AO MYSQL REFERENTE A SELECT
	 * @param string $sql comando sql select
	 * @param string $file __FILE__
	 * @param int $line __LINE__
	 * @param string $class __CLASS__
	 * @param string $method __METHOD__
	 * @param string $fnc __FUNCTION__
	 * @return Ambigous <boolean, resource>
	 */
	public function SqlSelect($sql) {
		$a = func_get_args ();
		array_shift ( $a );
		$a = (! $a) ? FALSE : $a;
		if (is_array ( $sql )) {
			foreach ( $sql as $query ) {
				$this->sql [] = $query;
				$out [] = $this->_SqlSelect ( $query, $a );
			}
			return $out;
		} else {
			$this->sql [] = $sql;
			return $this->_SqlSelect ( $sql, $a );
		}
	}
	
	/**
	 * INSERE VALORES DENTRO DE UMA TABELA
	 * @param string/array $tabelas nome das tabelas a serem consultadas para array usar a constru��o
	 * [nome_da_tabela][x(int)][campo]=>valor
	 * @param array/bolean $valores valores para inserir na tabela [campo]=>valor
	 * @return array vetor com o SQL para inserir em uma tabela
	 */
	public function SqlInsert($tabelas, $valores = false) {
		if ($valores == false) {
			foreach ( $tabelas as $tabela => $nvalores ) {
				$sql [] = $this->_SqlInsert ( $tabela, $nvalores );
			}
		} else {
			$valores = array_map ( array ('MYSQL', 'f_checkValue' ), $valores );
			$sql [0] = $this->_SqlInsert ( $tabelas, $valores );
		}
		return $this->sql = $sql;
	}
	
	/**
	 * ATUALIZA UMA TABELA DE ACORDO COM OS VALORES DENTRO DE UM ARRAY
	 * @param string/array $tabelas
	 * @param array $valores [campo]=>valor
	 * @param string/bolean $where condi��o comparativa "coluna = 'valor'"
	 * @return string SQL construido do update
	 */
	public function SqlUpdate($tabelas, $valores, $where = false) {
		$tabela = (is_array ( $tabelas )) ? implode ( ",", $tabelas ) : $tabelas;
		$sql = "UPDATE " . $tabela . " SET ";
		if (is_array ( $valores )) {
			$valores = array_map ( array ('MYSQL', 'f_checkValue' ), $valores );
			foreach ( $valores as $chave => $valor ) {
				$x [] = $chave . " = " . $valor;
			}
			$sql .= implode ( ",", $x );
		} else {
			$sql .= $valores;
		}
		if ($where != false)
			$sql .= " WHERE " . $where;
		return $sql;
	}
	
	/**
	 * REMOVE UMA CONSULTA APARTIR DE UM COMPARA��O SQL UNICA 
	 * @param string/array $tabela
	 * @param string/bolean $where condi��o comparativa "coluna = 'valor'"
	 * @return string SQL para deletar
	 */
	public function SqlDelete($tabela, $where) {
		if (is_array ( $tabela )) {
			$tabelas = implode ( ",", $tabela );
		} else {
			$tabelas = $tabela;
		}
		return "DELETE FROM " . $tabelas . "WHERE " . $where;
	}
	
	/**
	 * Auxilia numa pesquisa por numeros dinamicos
	 * @param string <sql> $query
	 * @param integer <optional> limit, count
	 * @return string
	 */
	public function SqlSelectRange($query) {
		$args = func_get_args ();
		$count = array_pop ( $args );
		$from = array_pop ( $args );
		array_shift ( $args );
		if (isset ( $args [0] ) and is_array ( $args [0] )) { // 'All arguments in one array' syntax
			$args = $args [0];
		}
		self::db_query_callback ( $args, TRUE );
		$query = preg_replace_callback ( DB_QUERY_REGEXP, 'MYSQL::db_query_callback', $query );
		$query .= ' LIMIT ' . ( int ) $from . ', ' . ( int ) $count;
		return $query;
	}
	
	/*
	FUN��ES DE NIVEL PRIVADO, N�O ACESSIVEL FORA DO OBJETO, APENAS PARA APOIO A CLASSE
*/
	
	/**
	 * Cria uma conec��o percistente ao MYSQL
	 * @param string $db_server - Nome do Host do banco de dados
	 * @param string $db_user - Nome do usu�rio de acesso ao banco de dados
	 * @param string $db_passwd - Nome do usu�rio de acesso ao banco de dados
	 * @return resource
	 */
	private function MysqlPconnect($db_server, $db_user, $db_passwd) {
		return mysql_pconnect ( $db_server, $db_user, $db_passwd );
	}
	
	/**
	 * FUN��O DE APOIO AO SqlSelect
	 * @param string $sql comando sql select
	 * @param string $file __FILE__
	 * @param int $line __LINE__
	 * @param string $class __CLASS__
	 * @param string $method __METHOD__
	 * @param string $fnc __FUNCTION__
	 * @return resource
	 */
	private function _SqlSelect($sql, $args = FALSE) {
		$query = $this->db_prefix_tables ( $sql );
		if (isset ( $args [0] ) and is_array ( $args [0] )) { // 'All arguments in one array' syntax
			$args = $args [0];
		}
		$this->db_query_callback ( $args, TRUE );
		$query = preg_replace_callback ( DB_QUERY_REGEXP, array("MYSQL","db_query_callback"), $query );
		$hdl = mysql_query ( $query, $this->conexao );
		if ($hdl == false) {
			print $this->error_db ( $query );
			exit ();
		} else {
			return $hdl;
		}
	}
	
	/**
	 * FUN��O DE APOIO AO SqlInsert
	 * @param string $tabela - tabela a ser inserida
	 * @param array/bolean $valores - valores para inserir na tabela [campo]=>valor
	 * @return string
	 */
	private function _SqlInsert($tabela, $valores) {
		$sql = "INSERT INTO " . $tabela;
		foreach ( $valores as $chave => $valor ) {
			$tab [] = '`' . $chave . '`';
			if (is_array ( $valor )) {
				$valor = array_map ( array ('MYSQL', 'f_checkValue' ), $valor );
				$tab = array_keys ( $valor );
				$valor = " (" . implode ( ", ", $valor ) . ")";
				$k = 1;
			}
			$val [] = $valor;
		}
		$chaves = " (" . implode ( ", ", $tab ) . ") ";
		$valors = (! $k) ? " (" . implode ( ", ", $val ) . ")" : implode ( ", ", $val );
		$sql .= $chaves . "VALUES" . $valors;
		$sql = $this->db_query ( $sql );
		return $sql;
	}
	
	/**
	 * RETORNA UM ERRO DE SELECT JUNTO COM SUA SINTAX
	 * @param string $sql comando sql select
	 * @param string $file __FILE__
	 * @param int $line __LINE__
	 * @param string $class __CLASS__
	 * @param string $method __METHOD__
	 * @param string $fnc __FUNCTION__
	 * @return string - um <p> de erro
	 */
	private function error_db($sql) {
		$erro = "<p>";
		$erro .= "SQL:" . $sql . "<br/>" . mysql_error ();
		$erro .= "</p>";
		return $erro;
	}
	/*
	FUNÇÕES DE NIVEL ST�TICO, ACESSIVEL PARA O OBJETO E TAMBÉM FORA DE INSTANCIAS (::)
*/
	/**
	 * RETORNA O NOME DAS COLUNAS DE UMA TABELA
	 * @param string $db_name - nome do banco de dados a ser verificado
	 * @param string $tabela - tabela a ser verificada
	 * @param bolean $pre - retornar o nome da tabela na frente
	 * @return array - um aray com os nomes das tabelas
	 */
	static function ListaCampos($db_name, $tabela, $pre = false) {
		$fields = @mysql_list_fields ( $db_name, $tabela );
		$columns = @mysql_num_fields ( $fields );
		for($i = 0; $i < $columns; $i ++) {
			$tab [] = ($pre != false) ? mysql_field_name ( $fields, $i ) : $tabela . '.' . mysql_field_name ( $fields, $i );
		}
		return $tab;
	}
	
	/**
	 * RETORNA O NOME DAS COLUNAS DE UMA TABELA
	 * @param string $tabela - nome da tabela
	 * @return boolean
	 */
	static function CheckTable($tabela) {
		if (mysql_num_rows ( mysql_query ( "SHOW TABLES LIKE `" . $tabela . "`" ) ) == 1)
			return true;
		else
			return false;
	}
	
	/**
	 * Func��o exclusiva para array_map para filtrar dados de entrada
	 * @param mix $value
	 * @return Ambiguos <string, integer>
	 */
	private static function f_checkValue($value) {
		if (is_bool ( $value )) {
			$value = ($value == false) ? "FALSE" : "TRUE";
		} elseif (is_string ( $value ) && $value == 'NOW()') {
			$value = "NOW()";
		} elseif (($value == '' || is_null ( $value )) && ! is_int ( $value )) {
			$value = "NULL";
		} elseif (is_string ( $value )) {
			$value = "'" . $value . "'";
		}
		return $value;
	}
	
	/**
	 * Retorna a primeira coluna do resultado em geral o ID
	 * @param resorce $result
	 * @return Ambigous <mix>|boolean
	 */
	static function dbResult($result) {
		if ($result && mysql_num_rows ( $result ) > 0) {
			$array = mysql_fetch_row ( $result );
			return $array [0];
		}
		return FALSE;
	}
	
	/**
	 * Função callback para tratamento da senten�a dinamica
	 * @param array $match - valores a serem trocados
	 * @param boolean $init - true seta os valores, false seta os coringas
	 * @return void|Ambigous <number, string>|string|number
	 */
	static function db_query_callback($match, $init = FALSE) {
		static $args = NULL;
		if ($init) {
			$args = $match;
			return;
		}
		switch ($match [1]) {
			case '%d' : // We must use type casting to int to convert FALSE/NULL/(TRUE?)
				$value = array_shift ( $args );
				// Do we need special bigint handling?
				if ($value > PHP_INT_MAX) {
					$precision = ini_get ( 'precision' );
					@ini_set ( 'precision', 16 );
					$value = sprintf ( '%.0f', $value );
					@ini_set ( 'precision', $precision );
				} else {
					$value = ( int ) $value;
				}
				// We don't need db_escape_string as numbers are db-safe.
				return $value;
			case '%s' :
				return self::db_escape_string ( array_shift ( $args ) );
			case '%n' :
				// Numeric values have arbitrary precision, so can't be treated as float.
				// is_numeric() allows hex values (0xFF), but they are not valid.
				$value = trim ( array_shift ( $args ) );
				return is_numeric ( $value ) && ! preg_match ( '/x/i', $value ) ? $value : '0';
			case '%%' :
				return '%';
			case '%f' :
				return ( float ) array_shift ( $args );
			case '%b' : // binary data
				return self::db_encode_blob ( array_shift ( $args ) );
		}
	}
	
	/**
	 * escapa os caracteres de uma string
	 * @param string $text
	 * @return string
	 */
	private static function db_escape_string($text) {
		return mysql_real_escape_string ( $text );
	}
	/**
	 * Escapa caracteres para tipo blob
	 * @param blob $data
	 * @return string
	 */
	private static function db_encode_blob($data) {
		return "'" . mysql_real_escape_string ( $data ) . "'";
	}
	private function db_prefix_tables($sql) {
		global $cfg;
		
		if (is_array ( $cfg ['db_prefix'] )) {
			if (array_key_exists ( 'default', $cfg ['db_prefix'] )) {
				$tmp = $cfg ['db_prefix'];
				unset ( $tmp ['default'] );
				foreach ( $tmp as $key => $val ) {
					$sql = strtr ( $sql, array ('{' . $key . '}' => $val . $key ) );
				}
				return strtr ( $sql, array ('{' => $cfg ['db_prefix'] ['default'], '}' => '' ) );
			} else {
				foreach ( $cfg ['db_prefix'] as $key => $val ) {
					$sql = strtr ( $sql, array ('{' . $key . '}' => $val . $key ) );
				}
				return strtr ( $sql, array ('{' => '', '}' => '' ) );
			}
		} else {
			return strtr ( $sql, array ('{' => $cfg ['db_prefix'], '}' => '' ) );
		}
	}
}

