<?php
####################################################################
## CLASSE PARA MANIPULAÇÃO DE TRADUÇÕES                         
## CLASS.TRANSLATE.PHP VERSÃO 1.0  - 18/01/2011                       
## RENATO INNOCENTI                           					  ##
## EMAIL: renato.innocenti@gmail.com                              ##
####################################################################
error_reporting(E_ALL & ~ E_NOTICE);
####################################################################
## CRIA INSTANCIA PARA TRADUÇÕES DE ARQUIVOS .PO  
## AS ENTRADAS SÃO REFERENTES AO ARQUIVO DE TRADUÇÃO FORMATO .PO 
## Classe: Translation_Entry 
####################################################################
/* PO Object
(
    [entries] => Array
        (
            [enable] => Translation_Entry Object
                (
                    [is_plural] => 
                    [context] => 
                    [singular] => enable
                    [plural] => 
                    [translations] => Array
                        (
                            [0] => ativar
                        )

                    [translator_comments] => 
                    [extracted_comments] => 
                    [references] => Array
                        (
                        )

                    [flags] => Array
                        (
                        )

                )

            [Delete] => Translation_Entry Object
                (
                    [is_plural] => 
                    [context] => 
                    [singular] => Delete
                    [plural] => 
                    [translations] => Array
                        (
                            [0] => Apagar
                        )

                    [translator_comments] => 
                    [extracted_comments] => 
                    [references] => Array
                        (
                        )

                    [flags] => Array
                        (
                        )

                )

            [List] => Translation_Entry Object
                (
                    [is_plural] => 
                    [context] => 
                    [singular] => List
                    [plural] => 
                    [translations] => Array
                        (
                            [0] => Listar
                        )

                    [translator_comments] => 
                    [extracted_comments] => 
                    [references] => Array
                        (
                        )

                    [flags] => Array
                        (
                        )

                )

            [Please] => Translation_Entry Object
                (
                    [is_plural] => 
                    [context] => 
                    [singular] => Please
                    [plural] => 
                    [translations] => Array
                        (
                        )

                    [translator_comments] => 
                    [extracted_comments] => 
                    [references] => Array
                        (
                        )

                    [flags] => Array
                        (
                        )

                )

            [Cancel] => Translation_Entry Object
                (
                    [is_plural] => 
                    [context] => 
                    [singular] => Cancel
                    [plural] => 
                    [translations] => Array
                        (
                            [0] => Cancelar
                        )

                    [translator_comments] => 
                    [extracted_comments] => 
                    [references] => Array
                        (
                        )

                    [flags] => Array
                        (
                        )

                )

 */
class Translation_Entry{
	var $is_plural = false;
	var $context = null;
	var $singular = null;
	var $plural = null;
	var $translations = array();
	var $translator_comments = '';
	var $extracted_comments = '';
	var $references = array();
	var $flags = array();
	/**
	 * @param array $args associative array, support following keys:
	 * 	- singular (string) -- the string to translate, if omitted and empty entry will be created
	 * 	- plural (string) -- the plural form of the string, setting this will set {@link $is_plural} to true
	 * 	- translations (array) -- translations of the string and possibly -- its plural forms
	 * 	- context (string) -- a string differentiating two equal strings used in different contexts
	 * 	- translator_comments (string) -- comments left by translators
	 * 	- extracted_comments (string) -- comments left by developers
	 * 	- references (array) -- places in the code this strings is used, in relative_to_root_path/file.php:linenum form
	 * 	- flags (array) -- flags like php-format
	 */
	function Translation_Entry($args=array()) {
		// if no singular -- empty object
		if (!isset($args['singular'])) {
			return;
		}
		// get member variable values from args hash
		foreach ($args as $varname => $value) {
			$this->$varname = $value;
		}
		if (isset($args['plural'])) $this->is_plural = true;
		if (!is_array($this->translations)) $this->translations = array();
		if (!is_array($this->references)) $this->references = array();
		if (!is_array($this->flags)) $this->flags = array();
	}

	/**
	 * Generates a unique key for this entry
	 *
	 * @return string|bool the key or false if the entry is empty
	 */
	function key() {
		if (is_null($this->singular)) return false;
		// prepend context and EOT, like in MO files
		return is_null($this->context)? $this->singular : $this->singular;
	}
}
class Translations{
	var $entries = array();
	var $headers = array();

	/**
	 * Add entry to the PO structure
	 *
	 * @param object &$entry
	 * @return bool true on success, false if the entry doesn't have a key
	 */
	function add_entry($entry) {
		if (is_array($entry)) {
			$entry = new Translation_Entry($entry);
		}
		$key = $entry->key();
		if (false === $key) return false;
		$this->entries[$key] = &$entry;
		return true;
	}

	/**
	 * Sets $header PO header to $value
	 *
	 * If the header already exists, it will be overwritten
	 *
	 * TODO: this should be out of this class, it is gettext specific
	 *
	 * @param string $header header name, without trailing :
	 * @param string $value header value, without trailing \n
	 */
	function set_header($header, $value) {
		$this->headers[$header] = $value;
	}

	function set_headers(&$headers) {
		foreach($headers as $header => $value) {
			$this->set_header($header, $value);
		}
	}

	function get_header($header) {
		return isset($this->headers[$header])? $this->headers[$header] : false;
	}

	function translate_entry(&$entry) {
		$key = $entry->key();
		return isset($this->entries[$key])? $this->entries[$key] : false;
	}

	function translate($singular, $context=null) {
		$entry = new Translation_Entry(array('singular' => $singular, 'context' => $context));
		$translated = $this->translate_entry($entry);
		return ($translated && !empty($translated->translations))? $translated->translations[0] : $singular;
	}

	/**
	 * Given the number of items, returns the 0-based index of the plural form to use
	 *
	 * Here, in the base Translations class, the commong logic for English is implmented:
	 * 	0 if there is one element, 1 otherwise
	 *
	 * This function should be overrided by the sub-classes. For example MO/PO can derive the logic
	 * from their headers.
	 *
	 * @param integer $count number of items
	 */
	function select_plural_form($count) {
		return 1 == $count? 0 : 1;
	}

	function get_plural_forms_count() {
		return 2;
	}

	function translate_plural($singular, $plural, $count, $context = null) {
		$entry = new Translation_Entry(array('singular' => $singular, 'plural' => $plural, 'context' => $context));
		$translated = $this->translate_entry($entry);
		$index = $this->select_plural_form($count);
		$total_plural_forms = $this->get_plural_forms_count();
		if ($translated && 0 <= $index && $index < $total_plural_forms &&
				is_array($translated->translations) &&
				isset($translated->translations[$index]))
			return $translated->translations[$index];
		else
			return 1 == $count? $singular : $plural;
	}

	/**
	 * Merge $other in the current object.
	 *
	 * @param Object &$other Another Translation object, whose translations will be merged in this one
	 * @return void
	 **/
	function merge_with(&$other) {
		foreach( $other->entries as $entry ) {
			$this->entries[$entry->key()] = $entry;
		}
	}
}

class Gettext_Translations extends Translations {
	/**
	 * The gettext implmentation of select_plural_form.
	 *
	 * It lives in this class, because there are more than one descendand, which will use it and
	 * they can't share it effectively.
	 *
	 */
	function gettext_select_plural_form($count) {
		if (!isset($this->_gettext_select_plural_form) || is_null($this->_gettext_select_plural_form)) {
			list( $nplurals, $expression ) = $this->nplurals_and_expression_from_header($this->get_header('Plural-Forms'));
			$this->_nplurals = $nplurals;
			$this->_gettext_select_plural_form = $this->make_plural_form_function($nplurals, $expression);
		}
		return call_user_func($this->_gettext_select_plural_form, $count);
	}

	function nplurals_and_expression_from_header($header) {
		if (preg_match('/^\s*nplurals\s*=\s*(\d+)\s*;\s+plural\s*=\s*(.+)$/', $header, $matches)) {
			$nplurals = (int)$matches[1];
			$expression = trim($this->parenthesize_plural_exression($matches[2]));
			return array($nplurals, $expression);
		} else {
			return array(2, 'n != 1');
		}
	}

	/**
	 * Makes a function, which will return the right translation index, according to the
	 * plural forms header
	 */
	function make_plural_form_function($nplurals, $expression) {
		$expression = str_replace('n', '$n', $expression);
		$func_body = "
			\$index = (int)($expression);
			return (\$index < $nplurals)? \$index : $nplurals - 1;";
		return create_function('$n', $func_body);
	}

	/**
	 * Adds parantheses to the inner parts of ternary operators in
	 * plural expressions, because PHP evaluates ternary oerators from left to right
	 *
	 * @param string $expression the expression without parentheses
	 * @return string the expression with parentheses added
	 */
	function parenthesize_plural_exression($expression) {
		$expression .= ';';
		$res = '';
		$depth = 0;
		for ($i = 0; $i < strlen($expression); ++$i) {
			$char = $expression[$i];
			switch ($char) {
				case '?':
					$res .= ' ? (';
					$depth++;
					break;
				case ':':
					$res .= ') : (';
					break;
				case ';':
					$res .= str_repeat(')', $depth) . ';';
					$depth= 0;
					break;
				default:
					$res .= $char;
			}
		}
		return rtrim($res, ';');
	}

	function make_headers($translation) {
		$headers = array();
		// sometimes \ns are used instead of real new lines
		$translation = str_replace('\n', "\n", $translation);
		$lines = explode("\n", $translation);
		foreach($lines as $line) {
			$parts = explode(':', $line, 2);
			if (!isset($parts[1])) continue;
			$headers[trim($parts[0])] = trim($parts[1]);
		}
		return $headers;
	}

	function set_header($header, $value) {
		parent::set_header($header, $value);
		if ('Plural-Forms' == $header) {
			list( $nplurals, $expression ) = $this->nplurals_and_expression_from_header($this->get_header('Plural-Forms'));
			$this->_nplurals = $nplurals;
			$this->_gettext_select_plural_form = $this->make_plural_form_function($nplurals, $expression);
		}
	}
}

class PO extends Gettext_Translations {


	/**
	 * Exports headers to a PO entry
	 *
	 * @return string msgid/msgstr PO entry for this PO file headers, doesn't contain newline at the end
	 */
	function export_headers() {
		$header_string = '';
		foreach($this->headers as $header => $value) {
			$header_string.= "$header: $value\n";
		}
		$poified = PO::poify($header_string);
		return rtrim("msgid \"\"\nmsgstr $poified");
	}

	/**
	 * Exports all entries to PO format
	 *
	 * @return string sequence of mgsgid/msgstr PO strings, doesn't containt newline at the end
	 */
	function export_entries() {
		//TODO sorting
		return implode("\n\n", array_map(array('PO', 'export_entry'), $this->entries));
	}
	function export_entries_to_array() {
		//TODO sorting
		

	}

	/**
	 * Exports the whole PO file as a string
	 *
	 * @param bool $include_headers whether to include the headers in the export
	 * @return string ready for inclusion in PO file string for headers and all the enrtries
	 */
	function export($include_headers = true) {
		$res = '';
		if ($include_headers) {
			$res .= $this->export_headers();
			$res .= "\n\n";
		}
		$res .= $this->export_entries();
		return $res;
	}

	/**
	 * Same as {@link export}, but writes the result to a file
	 *
	 * @param string $filename where to write the PO string
	 * @param bool $include_headers whether to include tje headers in the export
	 * @return bool true on success, false on error
	 */
	function export_to_file($filename, $include_headers = true) {
		$fh = fopen($filename, 'w');
		if (false === $fh) return false;
		$export = $this->export($include_headers);
		$res = fwrite($fh, $export);
		if (false === $res) return false;
		return fclose($fh);
	}

	/**
	 * Formats a string in PO-style
	 *
	 * @static
	 * @param string $string the string to format
	 * @return string the poified string
	 */
	function poify($string) {
		$quote = '"';
		$slash = '\\';
		$newline = "\n";

		$replaces = array(
			"$slash" 	=> "$slash$slash",
			"$quote"	=> "$slash$quote",
			"\t" 		=> '\t',
		);

		$string = str_replace(array_keys($replaces), array_values($replaces), $string);

		$po = $quote.implode("${slash}n$quote$newline$quote", explode($newline, $string)).$quote;
		// add empty string on first line for readbility
		if (false !== strpos($string, $newline) &&
				(substr_count($string, $newline) > 1 || !($newline === substr($string, -strlen($newline))))) {
			$po = "$quote$quote$newline$po";
		}
		// remove empty strings
		$po = str_replace("$newline$quote$quote", '', $po);
		return $po;
	}

	/**
	 * Gives back the original string from a PO-formatted string
	 *
	 * @static
	 * @param string $string PO-formatted string
	 * @return string enascaped string
	 */
	function unpoify($string) {
		$escapes = array('t' => "\t", 'n' => "\n", '\\' => '\\');
		$lines = array_map('trim', explode("\n", $string));
		$lines = array_map(array('PO', 'trim_quotes'), $lines);
		$unpoified = '';
		$previous_is_backslash = false;
		foreach($lines as $line) {
			preg_match_all('/./u', $line, $chars);
			$chars = $chars[0];
			foreach($chars as $char) {
				if (!$previous_is_backslash) {
					if ('\\' == $char)
						$previous_is_backslash = true;
					else
						$unpoified .= $char;
				} else {
					$previous_is_backslash = false;
					$unpoified .= isset($escapes[$char])? $escapes[$char] : $char;
				}
			}
		}
		return $unpoified;
	}

	/**
	 * Inserts $with in the beginning of every new line of $string and
	 * returns the modified string
	 *
	 * @static
	 * @param string $string prepend lines in this string
	 * @param string $with prepend lines with this string
	 */
	function prepend_each_line($string, $with) {
		$php_with = var_export($with, true);
		$lines = explode("\n", $string);
		// do not prepend the string on the last empty line, artefact by explode
		if ("\n" == substr($string, -1)) unset($lines[count($lines) - 1]);
		$res = implode("\n", array_map(create_function('$x', "return $php_with.\$x;"), $lines));
		// give back the empty line, we ignored above
		if ("\n" == substr($string, -1)) $res .= "\n";
		return $res;
	}

	/**
	 * Prepare a text as a comment -- wraps the lines and prepends #
	 * and a special character to each line
	 *
	 * @access private
	 * @param string $text the comment text
	 * @param string $char character to denote a special PO comment,
	 * 	like :, default is a space
	 */
	function comment_block($text, $char=' ') {
		$text = wordwrap($text, PO_MAX_LINE_LEN - 3);
		return PO::prepend_each_line($text, "#$char ");
	}

	/**
	 * Builds a string from the entry for inclusion in PO file
	 *
	 * @static
	 * @param object &$entry the entry to convert to po string
	 * @return string|bool PO-style formatted string for the entry or
	 * 	false if the entry is empty
	 */
	function export_entry(&$entry) {
		if (is_null($entry->singular)) return false;
		$po = array();
		if (!empty($entry->translator_comments)) $po[] = PO::comment_block($entry->translator_comments);
		if (!empty($entry->extracted_comments)) $po[] = PO::comment_block($entry->extracted_comments, '.');
		if (!empty($entry->references)) $po[] = PO::comment_block(implode(' ', $entry->references), ':');
		if (!empty($entry->flags)) $po[] = PO::comment_block(implode(", ", $entry->flags), ',');
		if (!is_null($entry->context)) $po[] = 'msgctxt '.PO::poify($entry->context);
		$po[] = 'msgid '.PO::poify($entry->singular);
		if (!$entry->is_plural) {
			$translation = empty($entry->translations)? '' : $entry->translations[0];
			$po[] = 'msgstr '.PO::poify($translation);
		} else {
			$po[] = 'msgid_plural '.PO::poify($entry->plural);
			$translations = empty($entry->translations)? array('', '') : $entry->translations;
			foreach($translations as $i => $translation) {
				$po[] = "msgstr[$i] ".PO::poify($translation);
			}
		}
		return implode("\n", $po);
	}

	function import_from_file($filename) {
		$f = fopen($filename, 'r');
		if (!$f) return false;
		$lineno = 0;
		while (true) {
			$res = $this->read_entry($f, $lineno);
			if (!$res) break;
			if ($res['entry']->singular == '') {
				$this->set_headers($this->make_headers($res['entry']->translations[0]));
			} else {
				$this->add_entry($res['entry']);
			}
		}
		PO::read_line($f, 'clear');
		return $res !== false;
	}

	function read_entry($f, $lineno = 0) {
		$entry = new Translation_Entry();
		// where were we in the last step
		// can be: comment, msgctxt, msgid, msgid_plural, msgstr, msgstr_plural
		$context = '';
		$msgstr_index = 0;
		$is_final = create_function('$context', 'return $context == "msgstr" || $context == "msgstr_plural";');
		while (true) {
			$lineno++;
			$line = PO::read_line($f);
			if (!$line)  {
				if (feof($f)) {
					if ($is_final($context))
						break;
					elseif (!$context) // we haven't read a line and eof came
						return null;
					else
						return false;
				} else {
					return false;
				}
			}
			if ($line == "\n") continue;
			$line = trim($line);
			if (preg_match('/^#/', $line, $m)) {
				// the comment is the start of a new entry
				if ($is_final($context)) {
					PO::read_line($f, 'put-back');
					$lineno--;
					break;
				}
				// comments have to be at the beginning
				if ($context && $context != 'comment') {
					return false;
				}
				// add comment
				$this->add_comment_to_entry($entry, $line);
			} elseif (preg_match('/^msgctxt\s+(".*")/', $line, $m)) {
				if ($is_final($context)) {
					PO::read_line($f, 'put-back');
					$lineno--;
					break;
				}
				if ($context && $context != 'comment') {
					return false;
				}
				$context = 'msgctxt';
				$entry->context .= PO::unpoify($m[1]);
			} elseif (preg_match('/^msgid\s+(".*")/', $line, $m)) {
				if ($is_final($context)) {
					PO::read_line($f, 'put-back');
					$lineno--;
					break;
				}
				if ($context && $context != 'msgctxt' && $context != 'comment') {
					return false;
				}
				$context = 'msgid';
				$entry->singular .= PO::unpoify($m[1]);
			} elseif (preg_match('/^msgid_plural\s+(".*")/', $line, $m)) {
				if ($context != 'msgid') {
					return false;
				}
				$context = 'msgid_plural';
				$entry->is_plural = true;
				$entry->plural .= PO::unpoify($m[1]);
			} elseif (preg_match('/^msgstr\s+(".*")/', $line, $m)) {
				if ($context != 'msgid') {
					return false;
				}
				$context = 'msgstr';
				$entry->translations = array(PO::unpoify($m[1]));
			} elseif (preg_match('/^msgstr\[(\d+)\]\s+(".*")/', $line, $m)) {
				if ($context != 'msgid_plural' && $context != 'msgstr_plural') {
					return false;
				}
				$context = 'msgstr_plural';
				$msgstr_index = $m[1];
				$entry->translations[$m[1]] = PO::unpoify($m[2]);
			} elseif (preg_match('/^".*"$/', $line)) {
				$unpoified = PO::unpoify($line);
				switch ($context) {
					case 'msgid':
						$entry->singular .= $unpoified; break;
					case 'msgctxt':
						$entry->context .= $unpoified; break;
					case 'msgid_plural':
						$entry->plural .= $unpoified; break;
					case 'msgstr':
						$entry->translations[0] .= $unpoified; break;
					case 'msgstr_plural':
						$entry->translations[$msgstr_index] .= $unpoified; break;
					default:
						return false;
				}
			} else {
				return false;
			}
		}
		if (array() == array_filter($entry->translations, create_function('$t', 'return $t || "0" === $t;'))) {
			$entry->translations = array();
		}
		return array('entry' => $entry, 'lineno' => $lineno);
	}

	function read_line($f, $action = 'read') {
		static $last_line = '';
		static $use_last_line = false;
		if ('clear' == $action) {
			$last_line = '';
			return true;
		}
		if ('put-back' == $action) {
			$use_last_line = true;
			return true;
		}
		$line = $use_last_line? $last_line : fgets($f);
		$last_line = $line;
		$use_last_line = false;
		return $line;
	}

	function add_comment_to_entry(&$entry, $po_comment_line) {
		$first_two = substr($po_comment_line, 0, 2);
		$comment = trim(substr($po_comment_line, 2));
		if ('#:' == $first_two) {
			$entry->references = array_merge($entry->references, preg_split('/\s+/', $comment));
		} elseif ('#.' == $first_two) {
			$entry->extracted_comments = trim($entry->extracted_comments . "\n" . $comment);
		} elseif ('#,' == $first_two) {
			$entry->flags = array_merge($entry->flags, preg_split('/,\s*/', $comment));
		} else {
			$entry->translator_comments = trim($entry->translator_comments . "\n" . $comment);
		}
	}

	function trim_quotes($s) {
		if ( substr($s, 0, 1) == '"') $s = substr($s, 1);
		if ( substr($s, -1, 1) == '"') $s = substr($s, 0, -1);
		return $s;
	}
}
