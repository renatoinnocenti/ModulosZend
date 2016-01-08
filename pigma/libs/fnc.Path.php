<?php
/**
 * codifica uma URL - função de apoio.
 * @param stringe $text
 * @return url decodificada
 */
function SB_urlencode($text) {
	if (variable_get ( 'clean_url', '0' )) {
		return str_replace ( array ('%2F', '%26', '%23', '//' ), array ('/', '%2526', '%2523', '/%252F' ), rawurlencode ( $text ) );
	} else {
		return str_replace ( '%2F', '/', rawurlencode ( $text ) );
	}
}
/**
 * Decodifica caracteres de um texto.
 * @param string $text - texto a ser decodificado
 * @param array $exclude - textos a serem excluidos da decodificação
 * @return mixed
 */
function decode_entities($text, $exclude = array()) {
	static $html_entities = null;
	if (! isset ( $html_entities )) {
		include_once './libs/unicode.entities.inc';
	}
	
	// Flip the exclude list so that we can do quick lookups later.
	$exclude = array_flip ( $exclude );
	
	// Use a regexp to select all entities in one pass, to avoid decoding 
	// double-escaped entities twice. The PREG_REPLACE_EVAL modifier 'e' is
	// being used to allow for a callback (see 
	// http://php.net/manual/en/reference.pcre.pattern.modifiers).
	return preg_replace ( '/&(#x?)?([A-Za-z0-9]+);/e', '_decode_entities("$1", "$2", "$0", $html_entities, $exclude)', $text );
}
/**
 * Apoio para a funcção decode_entities
 */
function _decode_entities($prefix, $codepoint, $original, &$html_entities, &$exclude) {
	// Named entity
	if (! $prefix) {
		// A named entity not in the exclude list.
		if (isset ( $html_entities [$original] ) && ! isset ( $exclude [$html_entities [$original]] )) {
			return $html_entities [$original];
		} else {
			return $original;
		}
	}
	// Hexadecimal numerical entity
	if ($prefix == '#x') {
		$codepoint = base_convert ( $codepoint, 16, 10 );
	} // Decimal numerical entity (strip leading zeros to avoid PHP octal notation)
else {
		$codepoint = preg_replace ( '/^0+/', '', $codepoint );
	}
	// Encode codepoint as UTF-8 bytes
	if ($codepoint < 0x80) {
		$str = chr ( $codepoint );
	} else if ($codepoint < 0x800) {
		$str = chr ( 0xC0 | ($codepoint >> 6) ) . chr ( 0x80 | ($codepoint & 0x3F) );
	} else if ($codepoint < 0x10000) {
		$str = chr ( 0xE0 | ($codepoint >> 12) ) . chr ( 0x80 | (($codepoint >> 6) & 0x3F) ) . chr ( 0x80 | ($codepoint & 0x3F) );
	} else if ($codepoint < 0x200000) {
		$str = chr ( 0xF0 | ($codepoint >> 18) ) . chr ( 0x80 | (($codepoint >> 12) & 0x3F) ) . chr ( 0x80 | (($codepoint >> 6) & 0x3F) ) . chr ( 0x80 | ($codepoint & 0x3F) );
	}
	// Check for excluded characters
	if (isset ( $exclude [$str] )) {
		return $original;
	} else {
		return $str;
	}
}
/**
 * Processes an HTML attribute value and ensures it does not contain an URL
 * with a disallowed protocol (e.g. javascript:)
 *
 * @param $string
 * The string with the attribute value.
 * @param $decode
 * Whether to decode entities in the $string. Set to FALSE if the $string
 * is in plain text, TRUE otherwise. Defaults to TRUE.
 * @return
 * Cleaned up and HTML-escaped version of $string.
 */
function filter_xss_bad_protocol($string, $decode = TRUE) {
	static $allowed_protocols = null;
	if (! isset ( $allowed_protocols )) {
		$allowed_protocols = array_flip ( variable_get ( 'filter_allowed_protocols', array ('http', 'https', 'ftp', 'news', 'nntp', 'tel', 'telnet', 'mailto', 'irc', 'ssh', 'sftp', 'webcal', 'rtsp' ) ) );
	}
	
	// Get the plain text representation of the attribute value (i.e. its meaning).
	if ($decode) {
		$string = decode_entities ( $string );
	}
	
	// Iteratively remove any invalid protocol found.
	

	do {
		$before = $string;
		$colonpos = strpos ( $string, ':' );
		if ($colonpos > 0) {
			// We found a colon, possibly a protocol. Verify.
			$protocol = substr ( $string, 0, $colonpos );
			// If a colon is preceded by a slash, question mark or hash, it cannot
			// possibly be part of the URL scheme. This must be a relative URL,
			// which inherits the (safe) protocol of the base document.
			if (preg_match ( '![/?#]!', $protocol )) {
				break;
			}
			// Per RFC2616, section 3.2.3 (URI Comparison) scheme comparison must be case-insensitive
			// Check if this is a disallowed protocol.
			if (! isset ( $allowed_protocols [strtolower ( $protocol )] )) {
				$string = substr ( $string, $colonpos + 1 );
			}
		}
	} while ( $before != $string );
	return check_plain ( $string );
}
/**
 * formata os atributos para uma tag
 * @param array $attributes - com os atributos a serem inseridos
 * @return string
 */
function get_attributes($attributes = array()) {
	if (is_array ( $attributes )) {
		$t = '';
		foreach ( $attributes as $key => $value ) {
			$t .= " $key=" . '"' . check_plain ( $value ) . '"';
		}
		return $t;
	}
}
/**
 * Prepara uma URL para ser usada em atributos HTML. Strips harmful protocols.
 */
function check_url($uri) {
	return filter_xss_bad_protocol ( $uri, FALSE );
}

/**
 * Dado um apelido, retornar a sua URL de sistema, se houver. 
 * Dado um sistema um retorno URL do seu alias, se tal pessoa existe. 
 * Caso contr�rio, retorna FALSE.
 *
 * @param $action
 * um dos seguintes valores:
 * - wipe: apaga um cache de apelidos.
 * - alias: retorna um apelido para dar ao URL do sistema de caminhos (se existir).
 * - source: retorna a URL do sistema para um apelido (se existir)
 * @param $path
 * O caminho para investigar o sistema de apelidos ou correspondentes URLs.
 * @param $path_language
 * Opcional codigo de linguagem para procurar um caminho nele. O padr�o � a linguagem
 * da p�gina.
 * se nenhum caminho for definido pela lingagem ent�o ser� buscado um caminho se a linguagem.
 *
 * @return
 * ou um caminho de sistema, um caminho do apelido, ou FALSE se nenhum caminho for encontrado.
 */
function lookup_path($action, $path = '', $path_language = '') {
	global $language, $cfg;
	// $map é um array com a chave da linguagem, contendo arrays com os apelidos dos caminhos 
	static $map = array (), $no_src = array (), $count = NULL;
	
	$path_language = $path_language ? $path_language : $language ['language']->language;
	$mysql = new MYSQL ( $cfg );
	
	// Use $count to avoid looking up paths in subsequent calls if there simply are no aliases
	if (! isset ( $count )) {
		$sql = "SELECT COUNT(ID_PATH) FROM {url_alias}";
		$count = $mysql->dbResult ( $mysql->SqlSelect ( $sql ) );
	}
	
	if ($action == 'wipe') {
		$map = array ();
		$no_src = array ();
		$count = NULL;
	} elseif ($count > 0 && $path != '') {
		if ($action == 'alias') {
			if (isset ( $map [$path_language] [$path] )) {
				return $map [$path_language] [$path];
			}
			// Obtenha o resultado mais adequado caindo para tr�s com alias sem linguagem
			$sql = "SELECT dst FROM {url_alias} WHERE src = '{$path}' AND language IN('{$path_language}', '') ORDER BY language DESC, ID_PATH DESC";
			$alias = $mysql->dbResult ( $mysql->SqlSelect ( $sql, __FILE__, __LINE__, __CLASS__, __METHOD__, __FUNCTION__ ) );
			$map [$path_language] [$path] = $alias;
			return $alias;
		} // Check $no_src para este $path no caso de ja ter determinado que este 
		elseif ($action == 'source' && ! isset ( $no_src [$path_language] [$path] )) {
			// procura no valor de  $path sem cachear $map
			$src = FALSE;
			if (! isset ( $map [$path_language] ) || ! ($src = array_search ( $path, $map [$path_language] ))) {
				// Obtenha o resultado mais adequado caindo para tr�s com alias sem linguagem
				$sql = "SELECT src FROM {url_alias} WHERE dst = '{$path}' AND language IN('{$path_language}', '') ORDER BY language DESC, ID_PATH DESC";
				$src = "";
				if ($src = $mysql->dbResult ( $mysql->SqlSelect ( $sql, __FILE__, __LINE__, __CLASS__, __METHOD__, __FUNCTION__ ) )) {
					$map [$path_language] [$src] = $path;
				} else {
					// We can't record anything into $map because we do not have a valid
					// index and there is no need because we have not learned anything
					// about any Drupal path. Thus cache to $no_src.
					$no_src [$path_language] [$path] = TRUE;
				}
			}
			return $src;
		}
	}
	
	return FALSE;
}
/**
 * Fornece um caminho interno do site, retorna um atalho definido pela administração.
 *
 * @param $path
 * Um caminho interno.
 * @param $path_language
 * codigo de lingua opcional para checar o caminho nele.
 *
 * @return
 * Um atalho do caminho se encontrado ou o caminho original em caso contrário.
 */
function get_path_alias($path, $path_language = '') {
	$result = $path;
	$alias = lookup_path ( 'alias', $path, $path_language );
	if (isset($alias)) {
		$result = $alias;
	}
	return $result;
}
/**
 * Dado um alias caminho, retornar o caminho interno que ele representa.
 *
 * @param $path
 * A atalho de caminho.
 * @param $path_language
 * Um código de lingua de opção para procurar o caminho dentro
 *
 * @return
 * O caminho interno representado pelo apelido, ou o apelido original, 
 * se nenhum caminho interno foi encontrado.
 */
function get_normalPath($path, $path_language = '') {
	$result = $path;
	$src = lookup_path ( 'source', $path, $path_language );
	if (isset($src)) {
		$result = $src;
	}
	/*
   * @todo verificar a necessidade
   * if (function_exists('custom_url_rewrite_inbound')) {
    // Modules may alter the inbound request path by reference.
    custom_url_rewrite_inbound($result, $path, $path_language);
  }*/
	return $result;
}
/**
 * Verifica se a pagina atual � a pagina frontal.
 *
 * @return
 * Boolean value: TRUE se a pagina atal � a pagina frontal; FALSE para todas as outras.
 */

function is_frontPage() {
	static $is_front_page = null;
	
	if (! isset ( $is_front_page )) {
		// As drupal_init_path updates $_GET['q'] with the 'site_frontpage' path,
		// we can check it against the 'site_frontpage' variable.
		$is_front_page = ($_GET ['q'] == get_normalPath ( variable_get ( 'site_frontpage', 'node' ) ));
	}
	
	return $is_front_page;
}
/**
 * Gera uma URL interna ou externa.
 *
 * Quando criar links nos modulos, considere usar _l() pode ser melhor alternativa que url()
 *
 * @param $path
 * O caminho interno ou Externo que será lincado, como por exemplo "node/34" ou
 * "http://example.com/foo". Notas:
 * - Se você fornecer uma URL completa, será considerada uma URL externa.
 * - Se você fornecer apenas o camiho (ex. "node/34"), sera considerado
 * um link interno. neste caso, pode ser uma URL de sistema e será 
 * subistittuida por seu atalho, se existir. Argumentos de query adicionais
 * devem ser declarados em $options['query'], não incluidos na URL.
 * - Se for fornecido um caminho interno e $options['alias'] estiver definido como TRUE, 
 * este será assumido como o atalho correto para o caminho, e o atalho não será checado.
 * - A string especial '<front>' gera um link para a pagina principal do site.
 * - Se sua URL externa possuir uma query (ex. http://example.com/foo?a=b),
 * então voc~e pode decodificar as chaves e os valores por conta propria e inclui-la no $path,
 * ou usar em $options['query'] e deixar a função decodificar sua URL.
 * 
 * @param $options
 * An associative array of additional options, with the following elements:
 * - 'query': A URL-encoded query string to append to the link, or an array of
 * query key/value-pairs without any URL-encoding.
 * - 'fragment': A fragment identifier (named anchor) to append to the URL.
 * Do not include the leading '#' character.
 * - 'absolute' (default FALSE): Whether to force the output to be an absolute
 * link (beginning with http:). Useful for links that will be displayed
 * outside the site, such as in an RSS feed.
 * - 'alias' (default FALSE): Whether the given path is a URL alias already.
 * - 'external': Whether the given path is an external URL.
 * - 'language': An optional language object. Used to build the URL to link
 * to and look up the proper alias for the link.
 * - 'base_url': Only used internally, to modify the base URL when a language
 * dependent URL requires so.
 * - 'prefix': Only used internally, to modify the path when a language
 * dependent URL requires so.
 *
 * @return
 * A string containing a URL to the given path.
 */
function url($path = NULL, $options = array()) {
	// Merge in defaults.
	$options += array ('fragment' => '', 'query' => '', 'absolute' => FALSE, 'alias' => FALSE, 'prefix' => '' );
	if (! isset ( $options ['external'] )) {
		// Return an external link if $path contains an allowed absolute URL.
		// Only call the slow filter_xss_bad_protocol if $path contains a ':' before
		// any / ? or #.
		$colonpos = strpos ( $path, ':' );
		$options ['external'] = ($colonpos !== FALSE && ! preg_match ( '![/?#]!', substr ( $path, 0, $colonpos ) ) && filter_xss_bad_protocol ( $path, FALSE ) == check_plain ( $path ));
	}
	
	// May need language dependent rewriting if language.inc is present.
	if (function_exists ( 'language_url_rewrite' )) {
		language_url_rewrite ( $path, $options );
	}
	if ($options ['fragment']) {
		$options ['fragment'] = '#' . $options ['fragment'];
	}
	if (is_array ( $options ['query'] )) {
		$options ['query'] = query_string_encode ( $options ['query'] );
	}
	
	if ($options ['external']) {
		// Split off the fragment.
		if (strpos ( $path, '#' ) !== FALSE) {
			list ( $path, $old_fragment ) = explode ( '#', $path, 2 );
			if (isset ( $old_fragment ) && ! $options ['fragment']) {
				$options ['fragment'] = '#' . $old_fragment;
			}
		}
		// Append the query.
		if ($options ['query']) {
			$path .= (strpos ( $path, '?' ) !== FALSE ? '&' : '?') . $options ['query'];
		}
		// Reassemble.
		return $path . $options ['fragment'];
	}
	
	global $cfg;
	static $script = null;
	
	if (! isset ( $script )) {
		// On some web servers, such as IIS, we can't omit "index.php". So, we
		// generate "index.php?q=foo" instead of "?q=foo" on anything that is not
		// Apache.
		$script = (strpos ( $_SERVER ['SERVER_SOFTWARE'], 'Apache' ) === FALSE) ? 'index.php' : '';
	}
	
	if (! isset ( $options ['base_url'] )) {
		// The base_url might be rewritten from the language rewrite in domain mode.
		$options ['base_url'] = $cfg ['base_url'];
	}
	
	// Preserve the original path before aliasing.
	$original_path = $path;
	
	// The special path '<front>' links to the default front page.
	if ($path == '<front>') {
		$path = '';
	} elseif (! empty ( $path ) && ! $options ['alias']) {
		$path = get_path_alias ( $path, isset ( $options ['language'] ) ? $options ['language']->language : '' );
	}
	
	/*
   * @todo verificar necessidade
   * if (function_exists('custom_url_rewrite_outbound')) {
    // Modules may alter outbound links by reference.
    custom_url_rewrite_outbound($path, $options, $original_path);
  }*/
	
	$base = $options ['absolute'] ? $options ['base_url'] . '/' : $cfg ['base_path'];
	$prefix = empty ( $path ) ? rtrim ( $options ['prefix'], '/' ) : $options ['prefix'];
	$path = SB_urlencode ( $prefix . $path );
	
	if (variable_get ( 'clean_url', '0' )) {
		// With Clean URLs.
		if ($options ['query']) {
			return $base . $path . '?' . $options ['query'] . $options ['fragment'];
		} else {
			return $base . $path . $options ['fragment'];
		}
	} else {
		// Without Clean URLs.
		$variables = array ();
		if (! empty ( $path )) {
			$variables [] = 'q=' . $path;
		}
		if (! empty ( $options ['query'] )) {
			$variables [] = $options ['query'];
		}
		$query = join ( '&', $variables );
		if (isset($query)) {
			return $base . $script . '?' . $query . $options ['fragment'];
		} else {
			return $base . $options ['fragment'];
		}
	}
}
