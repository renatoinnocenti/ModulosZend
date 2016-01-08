<?php
/**
 * Formata uma consulta SQL em um vetor para arquivos Po
 * @param array $linha
 * @return Ambigous <unknown, multitype:unknown multitype: multitype:unknown  >
 */
function poFormater($linha) {
	$arg = array ('singular' => $linha ['source'], 'plural' => $linha ['plural'], 'references' => explode ( ';;', $linha ['location'] ) );
	if ($linha ['translation'] && ($linha ['translation'] != NULL && $linha ['translation'] != ''))
		$arg ['translations'] = array ($linha ['translation'] );
	if ($linha ['plural'] != NULL)
		$arg ['translations'] [] = $linha ['tplural'];
	return $arg;
}
/**
 * Formata uma consulta SQL em um vetor para header PO
 * @param resource $linha
 * @param bolean $reset - reinicia a data
 * @return multitype:NULL string unknown 
 */
function hpoFormater($linha, $reset = false) {
	static $udate = NULL;
	global $language;
	if ($reset == true)
		$udate = NULL;
	if (! $udate) {
		$udate = $linha ['ultima_alter'];
	
	}
	$udate = ($udate > $linha ['ultima_alter']) ? $udate : $linha ['ultima_alter'];
	$team = _locale_get_predefined_list ();
	$arg = array ('Project-Id-Version' => $linha ['version'], 'POT-Creation-Date' => $linha ['pot'], 'PO-Revision-Date' => $udate, 'Last-Translator' => $linha ['ultimo_tradutor'], 'Language' => $linha ['language'], 'Language-Team' => $team [$linha ['language']] [0], 'MIME-Version' => '1.0', 'Content-Type' => 'text/plain; charset=utf-8', 'Content-Transfer-Encoding' => $language ['language']->encoding . 'bit', 'Plural-Forms' => "nplurals=" . $language ['language']->plurals . "; plural=" . $language ['language']->formula . ";" );
	return $arg;
}
/**
 * formata um array para inser��o no banco de dados
 * @param objeto $entry - objeto PO
 * @param string $group - nome do grupo de tradu��o
 * @param integer $x - marca��o de inicio de indice
 * @return Ambigous <multitype:NULL number boolean unknown string , multitype:NULL boolean unknown string number >
 */
function po_db(&$entry, $group = 'default', $x = 1) {
	global $cfg;
	foreach ( $entry->entries as $key ) {
		$linha = get_object_vars ( $key );
		$arg [$cfg ['db_prefix'] . 'locales_source'] [$x] = array ('lid' => $x, 'location' => implode ( ';;', $linha ['references'] ), 'textgroup' => $group, 'source' => $linha ['singular'], 'version' => $entry->headers ['Project-Id-Version'], 'pot' => $entry->headers ['POT-Creation-Date'], 'ultimo_tradutor' => $entry->headers ['Last-Translator'] );
		$arg [$cfg ['db_prefix'] . 'locales_target'] [$x] = array ('translation' => (is_null ( $linha ['translations'] [0] )) ? '' : $linha ['translations'] [0], 'language' => strtolower ( $entry->headers ['Language'] ), 'lid' => $x, 'plid' => 0, 'plural' => false );
		if ($linha [is_plural] == true) {
			$y = $x + 1;
			$arg [$cfg ['db_prefix'] . 'locales_source'] [$y] = array ('lid' => $y, 'location' => implode ( ';;', $linha ['references'] ), 'textgroup' => $group, 'source' => $linha ['plural'], 'version' => $entry->headers ['Project-Id-Version'], 'pot' => $entry->headers ['POT-Creation-Date'], 'ultimo_tradutor' => $entry->headers ['Last-Translator'] );
			
			$arg [$cfg ['db_prefix'] . 'locales_target'] [$y] = array ('translation' => $linha ['translations'] [1], 'language' => strtolower ( $entry->headers ['Language'] ), 'lid' => $y, 'plid' => $x, 'plural' => true );
			$x ++;
		}
		$x ++;
	}
	return $arg;
}
/**
 * Carrega um arquivo PO
 * @param string $language - codigo da linguagem
 * @param string $group - grupo que pertence a tradu��o
 * @param string <URL> $path - caminho para encontrar o arquivo
 * @return object
 */
function loadPO($language, $group = 'default', $path = false) {
	global $cfg;
	$PO = new PO ();
	$path = ($path == false) ? './' . $cfg ['po_dir'] : $path . '/';
	$path .= $group . '.' . $language . ".po";
	if (file_exists ( $path )) {
		$PO->import_from_file ( realpath ( $path ) );
		return $PO;
	}
	return false;
}
/**
 * executa um insert no banco de dados de um objeto po
 * @param unknown_type $entry
 * @param unknown_type $group
 * @return Ambigous <Ambigous, resource>
 * @todo atualizar para que o insert também faça um update
 */
function insertTranslation(&$entry, $group = 'default') {
	global $cfg;
	$mysql = new MYSQL ( $cfg );
	$args = po_db ( $entry, $group );
	return $mysql->SqlSelect ( $mysql->SqlInsert ( $args ), __FILE__, __LINE__ );
}
/**
 *  Rescreve URL com base no prefixo da lingagem. paraetros são os mesmos dafunção url()
 * @param string $path - URL
 * @param array $options - OPÇões da função URL
 */
function language_url_rewrite(&$path, &$options) {
	global $language;
	
	// Only modify relative (insite) URLs.
	if (empty ( $options ['external'] )) {
		
		// Language can be passed as an option, or we go for current language.
		if (! isset ( $options ['language'] )) {
			$options ['language'] = $language ['language'];
		}
		
		switch (variable_get ( 'language_negotiation', LANGUAGE_NEGOTIATION_NONE )) {
			case LANGUAGE_NEGOTIATION_NONE :
				// No language dependent path allowed in this mode.
				unset ( $options ['language'] );
				break;
			
			case LANGUAGE_NEGOTIATION_DOMAIN :
				if ($options ['language']->domain) {
					// Ask for an absolute URL with our modified base_url.
					$options ['absolute'] = TRUE;
					$options ['base_url'] = $options ['language']->domain;
				}
				break;
			
			case LANGUAGE_NEGOTIATION_PATH_DEFAULT :
				$default = language_default ();
				if ($options ['language']->language == $default->language) {
					break;
				}
			// Intentionally no break here.
			

			case LANGUAGE_NEGOTIATION_PATH :
				if (! empty ( $options ['language']->prefix )) {
					$options ['prefix'] = $options ['language']->prefix . '/';
				}
				break;
		}
	}
}