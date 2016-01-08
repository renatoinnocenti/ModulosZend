<?php
/**
 * funções de nivel global
 * @file: fnc.Globals.php version (0.1) - 07/02/2011
 * Renato Innocenti
 * Email:renato.innocenti@gmail.com
 **/

/**
 * DESABILITA VARIAVEIS GLOBAIS NÃO PERMITIDAS
 */

/**
 * Desabilita globais indesejaveis
 */
function SB_unset_globals() {
	if (ini_get ( 'register_globals' )) {
		$allowed = array ('_ENV' => 1, '_GET' => 1, '_POST' => 1, '_COOKIE' => 1, '_FILES' => 1, '_SERVER' => 1, '_REQUEST' => 1, 'GLOBALS' => 1 );
		foreach ( $GLOBALS as $key => $value ) {
			if (! isset ( $allowed [$key] )) {
				unset ( $GLOBALS [$key] );
			}
		}
	}
}
/**
 * Define a URL de instalação
 */
function SB_base_url() {
	global $cfg;
	if (isset ( $cfg ['base_url'] )) {
		// Parse fixed base URL from settings.php.
		$parts = parse_url ( $cfg ['base_url'] );
		if (! isset ( $parts ['path'] )) {
			$parts ['path'] = '';
		}
		$cfg ['base_path'] = $parts ['path'] . '/';
		// Build $base_root (everything until first slash after "scheme://").
		$cfg ['base_root'] = substr ( $cfg ['base_url'], 0, strlen ( $cfg ['base_url'] ) - strlen ( $parts ['path'] ) );
	} else {
		// Create base URL
		$cfg ['base_root'] = (isset ( $_SERVER ['HTTPS'] ) && $_SERVER ['HTTPS'] == 'on') ? 'https' : 'http';
		
		$cfg ['base_url'] = $cfg ['base_root'] .= '://' . $_SERVER ['HTTP_HOST'];
		
		// $_SERVER['SCRIPT_NAME'] can, in contrast to $_SERVER['PHP_SELF'], not
		// be modified by a visitor.
		$dir = trim ( dirname ( $_SERVER ['SCRIPT_NAME'] ), '\,/' );
		if (isset($dir)) {
			$cfg ['base_path'] = "/$dir";
			$cfg ['base_url'] .= $cfg ['base_path'];
			$cfg ['base_path'] .= '/';
		} else {
			$cfg ['base_path'] = '/';
		}
	}
}
/**
 * Verifica se é um host valido
 * @param url $host
 * @return integer number
 */
function SB_valid_http_host($host) {
	return preg_match ( '/^\[?(?:[a-z0-9-:\]_]+\.?)+$/', $host );
}
/**
 * Verifica a versão
 * @param string $text
 * @return string
 */
function check_plain($text) {
	static $php525 = NULL;
	if (! isset ( $php525 )) {
		$php525 = version_compare ( PHP_VERSION, '5.2.5', '>=' );
	}
	// @todo remove this when support for either IE6 or PHP < 5.2.5 is dropped.
	

	if ($php525) {
		return htmlspecialchars ( $text, ENT_QUOTES, 'UTF-8' );
	}
	return (preg_match ( '/^./us', $text ) == 1) ? htmlspecialchars ( $text, ENT_QUOTES, 'UTF-8' ) : '';
}
/**
 * Define a linguagem padr�o a utilizar
 * @param string $property - string com o odigo da lingua a ser carregado
 * @return object - com a linguagem padr�o
 */
function language_default($property = NULL) {
	global $cfg;
	$language = variable_get ( 'language_default', ( object ) array ('language' => 'en', 'name' => 'English', 'native' => 'English', 'direction' => 0, 'enabled' => 1, 'plurals' => 0, 'formula' => '', 'domain' => '', 'prefix' => '', 'weight' => 0, 'javascript' => '' ) );
	return $property ? $language->$property : $language;
}
/**
 * cria um array com relação lingua=> pais
 * @return multitype:multitype:string  
 */
function _locale_get_predefined_list() {
	return array ("aa" => array ("Afar" ), "ab" => array ("Abkhazian", "аҧсуа бызшәа" ), "ae" => array ("Avestan" ), "af" => array ("Afrikaans" ), "ak" => array ("Akan" ), "am" => array ("Amharic", "አማርኛ" ), "ar" => array ("Arabic", /* Left-to-right marker "‭" */ "العربية", LANGUAGE_RTL ), "as" => array ("Assamese" ), "av" => array ("Avar" ), "ay" => array ("Aymara" ), "az" => array ("Azerbaijani", "azərbaycan" ), "ba" => array ("Bashkir" ), "be" => array ("Belarusian", "Беларуская" ), "bg" => array ("Bulgarian", "Български" ), "bh" => array ("Bihari" ), "bi" => array ("Bislama" ), "bm" => array ("Bambara", "Bamanankan" ), "bn" => array ("Bengali" ), "bo" => array ("Tibetan" ), "br" => array ("Breton" ), "bs" => array ("Bosnian", "Bosanski" ), "ca" => array ("Catalan", "Català" ), "ce" => array ("Chechen" ), "ch" => array ("Chamorro" ), "co" => array ("Corsican" ), "cr" => array ("Cree" ), "cs" => array ("Czech", "Čeština" ), "cu" => array ("Old Slavonic" ), "cv" => array ("Chuvash" ), "cy" => array ("Welsh", "Cymraeg" ), "da" => array ("Danish", "Dansk" ), "de" => array ("German", "Deutsch" ), "dv" => array ("Maldivian" ), "dz" => array ("Bhutani" ), "ee" => array ("Ewe", "Ɛʋɛ" ), "el" => array ("Greek", "Ελληνικά" ), "en" => array ("English" ), "eo" => array ("Esperanto" ), "es" => array ("Spanish", "Español" ), "et" => array ("Estonian", "Eesti" ), "eu" => array ("Basque", "Euskera" ), "fa" => array ("Persian", /* Left-to-right marker "‭" */ "فارسی", LANGUAGE_RTL ), "ff" => array ("Fulah", "Fulfulde" ), "fi" => array ("Finnish", "Suomi" ), "fj" => array ("Fiji" ), "fo" => array ("Faeroese" ), "fr" => array ("French", "Français" ), "fy" => array ("Frisian", "Frysk" ), "ga" => array ("Irish", "Gaeilge" ), "gd" => array ("Scots Gaelic" ), "gl" => array ("Galician", "Galego" ), "gn" => array ("Guarani" ), "gu" => array ("Gujarati" ), "gv" => array ("Manx" ), "ha" => array ("Hausa" ), "he" => array ("Hebrew", /* Left-to-right marker "‭" */ "עברית", LANGUAGE_RTL ), "hi" => array ("Hindi", "हिन्दी" ), "ho" => array ("Hiri Motu" ), "hr" => array ("Croatian", "Hrvatski" ), "hu" => array ("Hungarian", "Magyar" ), "hy" => array ("Armenian", "Հայերեն" ), "hz" => array ("Herero" ), "ia" => array ("Interlingua" ), "id" => array ("Indonesian", "Bahasa Indonesia" ), "ie" => array ("Interlingue" ), "ig" => array ("Igbo" ), "ik" => array ("Inupiak" ), "is" => array ("Icelandic", "Íslenska" ), "it" => array ("Italian", "Italiano" ), "iu" => array ("Inuktitut" ), "ja" => array ("Japanese", "日本語" ), "jv" => array ("Javanese" ), "ka" => array ("Georgian" ), "kg" => array ("Kongo" ), "ki" => array ("Kikuyu" ), "kj" => array ("Kwanyama" ), "kk" => array ("Kazakh", "Қазақ" ), "kl" => array ("Greenlandic" ), "km" => array ("Cambodian" ), "kn" => array ("Kannada", "ಕನ್ನಡ" ), "ko" => array ("Korean", "한국어" ), "kr" => array ("Kanuri" ), "ks" => array ("Kashmiri" ), "ku" => array ("Kurdish", "Kurdî" ), "kv" => array ("Komi" ), "kw" => array ("Cornish" ), "ky" => array ("Kirghiz", "Кыргыз" ), "la" => array ("Latin", "Latina" ), "lb" => array ("Luxembourgish" ), "lg" => array ("Luganda" ), "ln" => array ("Lingala" ), "lo" => array ("Laothian" ), "lt" => array ("Lithuanian", "Lietuvių" ), "lv" => array ("Latvian", "Latviešu" ), "mg" => array ("Malagasy" ), "mh" => array ("Marshallese" ), "mi" => array ("Maori" ), "mk" => array ("Macedonian", "Македонски" ), "ml" => array ("Malayalam", "മലയാളം" ), "mn" => array ("Mongolian" ), "mo" => array ("Moldavian" ), "mr" => array ("Marathi" ), "ms" => array ("Malay", "Bahasa Melayu" ), "mt" => array ("Maltese", "Malti" ), "my" => array ("Burmese" ), "na" => array ("Nauru" ), "nd" => array ("North Ndebele" ), "ne" => array ("Nepali" ), "ng" => array ("Ndonga" ), "nl" => array ("Dutch", "Nederlands" ), "nb" => array ("Norwegian Bokmål", "Bokmål" ), "nn" => array ("Norwegian Nynorsk", "Nynorsk" ), "nr" => array ("South Ndebele" ), "nv" => array ("Navajo" ), "ny" => array ("Chichewa" ), "oc" => array ("Occitan" ), "om" => array ("Oromo" ), "or" => array ("Oriya" ), "os" => array ("Ossetian" ), "pa" => array ("Punjabi" ), "pi" => array ("Pali" ), "pl" => array ("Polish", "Polski" ), "ps" => array ("Pashto", /* Left-to-right marker "‭" */ "پښتو", LANGUAGE_RTL ), "pt-pt" => array ("Portuguese, Portugal", "Português" ), "pt-br" => array ("Portuguese, Brazil", "Português" ), "qu" => array ("Quechua" ), "rm" => array ("Rhaeto-Romance" ), "rn" => array ("Kirundi" ), "ro" => array ("Romanian", "Română" ), "ru" => array ("Russian", "Русский" ), "rw" => array ("Kinyarwanda" ), "sa" => array ("Sanskrit" ), "sc" => array ("Sardinian" ), "sd" => array ("Sindhi" ), "se" => array ("Northern Sami" ), "sg" => array ("Sango" ), "sh" => array ("Serbo-Croatian" ), "si" => array ("Sinhala", "සිංහල" ), "sk" => array ("Slovak", "Slovenčina" ), "sl" => array ("Slovenian", "Slovenščina" ), "sm" => array ("Samoan" ), "sn" => array ("Shona" ), "so" => array ("Somali" ), "sq" => array ("Albanian", "Shqip" ), "sr" => array ("Serbian", "Српски" ), "ss" => array ("Siswati" ), "st" => array ("Sesotho" ), "su" => array ("Sudanese" ), "sv" => array ("Swedish", "Svenska" ), "sw" => array ("Swahili", "Kiswahili" ), "ta" => array ("Tamil", "தமிழ்" ), "te" => array ("Telugu", "తెలుగు" ), "tg" => array ("Tajik" ), "th" => array ("Thai", "ภาษาไทย" ), "ti" => array ("Tigrinya" ), "tk" => array ("Turkmen" ), "tl" => array ("Tagalog" ), "tn" => array ("Setswana" ), "to" => array ("Tonga" ), "tr" => array ("Turkish", "Türkçe" ), "ts" => array ("Tsonga" ), "tt" => array ("Tatar", "Tatarça" ), "tw" => array ("Twi" ), "ty" => array ("Tahitian" ), "ug" => array ("Uighur" ), "uk" => array ("Ukrainian", "Українська" ), "ur" => array ("Urdu", /* Left-to-right marker "‭" */ "اردو", LANGUAGE_RTL ), "uz" => array ("Uzbek", "o'zbek" ), "ve" => array ("Venda" ), "vi" => array ("Vietnamese", "Tiếng Việt" ), "wo" => array ("Wolof" ), "xh" => array ("Xhosa", "isiXhosa" ), "yi" => array ("Yiddish" ), "yo" => array ("Yoruba", "Yorùbá" ), "za" => array ("Zhuang" ), "zh-hans" => array ("Chinese, Simplified", "简体中文" ), "zh-hant" => array ("Chinese, Traditional", "繁體中文" ), "zu" => array ("Zulu", "isiZulu" ) );

}
/**
 * retorna um valor da global cfg se não existir retorna um padrão.
 * @param string $name
 * @param multitype $default
 * @return multitype
 */
function variable_get($name, $default) {
	global $cfg;
	return $cfg [$name] = isset ( $cfg [$name] ) ? $cfg [$name] : $default;
}
/**
 * Sets a persistent variable.
 *
 * Case-sensitivity of the variable_* functions depends on the database
 * collation used. To avoid problems, always use lower case for persistent
 * variable names.
 *
 * @param $name
 *   The name of the variable to set.
 * @param $value
 *   The value to set. This can be any PHP data type; these functions take care
 *   of serialization as necessary.
 *
 * @see variable_del(), variable_get()
 */
function variable_set($name, $value) {
  global $cfg;

  $mysql = new MYSQL($cfg);
  $serialized_value = serialize($value);
  $mysql->SqlSelect("UPDATE {variable} SET value = '%s' WHERE name = '%s'", $serialized_value, $name);
  if (!mysql_affected_rows()) {
    @$mysql->SqlSelect("INSERT INTO {variable} (name, value) VALUES ('%s', '%s')", $name, $serialized_value);
  }
  $cfg[$name] = $value;
}
function SB_unpack($obj, $field = 'data') {
  if ($obj->$field && $data = unserialize($obj->$field)) {
    foreach ($data as $key => $value) {
      if (!empty($key) && !isset($obj->$key)) {
        $obj->$key = $value;
      }
    }
  }
  return $obj;
}
/**
 * Enter description here ...
 * @return Ambigous <NULL, unknown, mixed>
 */
function ip_address() {
	static $ip_address = NULL;
	
	if (! isset ( $ip_address )) {
		$ip_address = $_SERVER ['REMOTE_ADDR'];
		if (variable_get ( 'reverse_proxy', 0 ) && array_key_exists ( 'HTTP_X_FORWARDED_FOR', $_SERVER )) {
			// If an array of known reverse proxy IPs is provided, then trust
			// the XFF header if request really comes from one of them.
			$reverse_proxy_addresses = variable_get ( 'reverse_proxy_addresses', array () );
			if (! empty ( $reverse_proxy_addresses ) && in_array ( $ip_address, $reverse_proxy_addresses, TRUE )) {
				// If there are several arguments, we need to check the most
				// recently added one, i.e. the last one.
				$ip_address_parts = explode ( ',', $_SERVER ['HTTP_X_FORWARDED_FOR'] );
				$ip_address = array_pop ( $ip_address_parts );
			}
		}
	}
	
	return $ip_address;
}
/**
 * Redireciona para a página de instalação
 * @param string <url> $path
 */
function install_goto($path) {
	global $cfg;
	header ( 'Location: ' . SITE_ROOT . '/' . $path );
	header ( 'Cache-Control: no-cache' ); // Not a permanent redirect.
	exit ();
}
/**
 * Retorna um perfil sem registro
 * @return StdClass
 */
function setGuest($session="") {
	$user = new stdClass ();
	$user->ID_USER = 0;
	$user->user_name = "Guest";
	$user->username = 0;
	$user->real_name = "Guest";
	$user->session = $session;
	$user->user_ip = ip_address ();
	$user->roles = array ();
	$user->roles [ANONYMOUS_ID_ROLE] = 'anonymous user';
	return $user;
}
/**
 * Extrai o prefixo e refaz a query
 * @param array $query - Comum $_GET[q]
 * @return string array [novo array,prefixo]
 */
function getLanguageURL($query) {
	$args = explode ( '/', $query );
	$a = array_intersect ( $args, array_keys ( _locale_get_predefined_list () ) );
	if ($a) {
		$prefix = $args [key ( $a )];
		unset ( $args [key ( $a )] );
	}
	$out [0] = implode ( '/', $args );
	$out [1] = $prefix;
	return $out;
}
/**
 * Translate strings to the page language or a given language.
 *
 * Human-readable text that will be displayed somewhere within a page should
 * be run through the t() function.
 *
 * Examples:
 * @code
 * if (!$info || !$info['extension']) {
 * form_set_error('picture_upload', t('The uploaded file was not an image.'));
 * }
 *
 * $form['submit'] = array(
 * '#type' => 'submit',
 * '#value' => t('Log in'),
 * );
 * @endcode
 *
 * Any text within t() can be extracted by translators and changed into
 * the equivalent text in their native language.
 *
 * Special variables called "placeholders" are used to signal dynamic
 * information in a string which should not be translated. Placeholders
 * can also be used for text that may change from time to time (such as
 * link paths) to be changed without requiring updates to translations.
 *
 * For example:
 * @code
 * $output = t('There are currently %members and %visitors online.', array(
 * '%members' => format_plural($total_users, '1 user', '@count users'),
 * '%visitors' => format_plural($guests->count, '1 guest', '@count guests')));
 * @endcode
 *
 * There are three styles of placeholders:
 * - !variable, which indicates that the text should be inserted as-is. This is
 * useful for inserting variables into things like e-mail.
 * @code
 * $message[] = t("If you don't want to receive such e-mails, you can change your settings at !url.", array('!url' => url("user/$account->uid", array('absolute' => TRUE))));
 * @endcode
 *
 * - @variable, which indicates that the text should be run through
 * check_plain, to escape HTML characters. Use this for any output that's
 * displayed within a Drupal page.
 * @code
 * drupal_set_title($title = t("@name's blog", array('@name' => $account->name)));
 * @endcode
 *
 * - %variable, which indicates that the string should be HTML escaped and
 * highlighted with theme_placeholder() which shows up by default as
 * <em>emphasized</em>.
 * @code
 * $message = t('%name-from sent %name-to an e-mail.', array('%name-from' => $user->name, '%name-to' => $account->name));
 * @endcode
 *
 * When using t(), try to put entire sentences and strings in one t() call.
 * This makes it easier for translators, as it provides context as to what
 * each word refers to. HTML markup within translation strings is allowed, but
 * should be avoided if possible. The exception are embedded links; link
 * titles add a context for translators, so should be kept in the main string.
 *
 * Here is an example of incorrect usage of t():
 * @code
 * $output .= t('<p>Go to the @contact-page.</p>', array('@contact-page' => l(t('contact page'), 'contact')));
 * @endcode
 *
 * Here is an example of t() used correctly:
 * @code
 * $output .= '<p>'. t('Go to the <a href="@contact-page">contact page</a>.', array('@contact-page' => url('contact'))) .'</p>';
 * @endcode
 *
 * Avoid escaping quotation marks wherever possible.
 *
 * Incorrect:
 * @code
 * $output .= t('Don\'t click me.');
 * @endcode
 *
 * Correct:
 * @code
 * $output .= t("Don't click me.");
 * @endcode
 *
 * Because t() is designed for handling code-based strings, in almost all
 * cases, the actual string and not a variable must be passed through t().
 *
 * Extraction of translations is done based on the strings contained in t()
 * calls. If a variable is passed through t(), the content of the variable
 * cannot be extracted from the file for translation.
 *
 * Incorrect:
 * @code
 * $message = 'An error occurred.';
 * drupal_set_message(t($message), 'error');
 * $output .= t($message);
 * @endcode
 *
 * Correct:
 * @code
 * $message = t('An error occurred.');
 * drupal_set_message($message, 'error');
 * $output .= $message;
 * @endcode
 *
 * The only case in which variables can be passed safely through t() is when
 * code-based versions of the same strings will be passed through t() (or
 * otherwise extracted) elsewhere.
 *
 * In some cases, modules may include strings in code that can't use t()
 * calls. For example, a module may use an external PHP application that
 * produces strings that are loaded into variables in Drupal for output.
 * In these cases, module authors may include a dummy file that passes the
 * relevant strings through t(). This approach will allow the strings to be
 * extracted.
 *
 * Sample external (non-Drupal) code:
 * @code
 * class Time {
 * public $yesterday = 'Yesterday';
 * public $today = 'Today';
 * public $tomorrow = 'Tomorrow';
 * }
 * @endcode
 *
 * Sample dummy file.
 * @code
 * // Dummy function included in example.potx.inc.
 * function example_potx() {
 * $strings = array(
 * t('Yesterday'),
 * t('Today'),
 * t('Tomorrow'),
 * );
 * // No return value needed, since this is a dummy function.
 * }
 * @endcode
 *
 * Having passed strings through t() in a dummy function, it is then
 * okay to pass variables through t().
 *
 * Correct (if a dummy file was used):
 * @code
 * $time = new Time();
 * $output .= t($time->today);
 * @endcode
 *
 * However tempting it is, custom data from user input or other non-code
 * sources should not be passed through t(). Doing so leads to the following
 * problems and errors:
 * - The t() system doesn't support updates to existing strings. When user
 * data is updated, the next time it's passed through t() a new record is
 * created instead of an update. The database bloats over time and any
 * existing translations are orphaned with each update.
 * - The t() system assumes any data it receives is in English. User data may
 * be in another language, producing translation errors.
 * - The "Built-in interface" text group in the locale system is used to
 * produce translations for storage in .po files. When non-code strings are
 * passed through t(), they are added to this text group, which is rendered
 * inaccurate since it is a mix of actual interface strings and various user
 * input strings of uncertain origin.
 *
 * Incorrect:
 * @code
 * $item = item_load();
 * $output .= check_plain(t($item['title']));
 * @endcode
 *
 * Instead, translation of these data can be done through the locale system,
 * either directly or through helper functions provided by contributed
 * modules.
 * @see hook_locale()
 *
 * During installation, st() is used in place of t(). Code that may be called
 * during installation or during normal operation should use the get_t()
 * helper function.
 * @see st()
 * @see get_t()
 *
 * @param $string
 * A string containing the English string to translate.
 * @param $args
 * An associative array of replacements to make after translation. Incidences
 * of any key in this array are replaced with the corresponding value. Based
 * on the first character of the key, the value is escaped and/or themed:
 * - !variable: inserted as is
 * - @variable: escape plain text to HTML (check_plain)
 * - %variable: escape text and theme as a placeholder for user-submitted
 * content (check_plain + theme_placeholder)
 * @param $langcode
 * Optional language code to translate to a language other than what is used
 * to display the page.
 * @return
 * The translated string.
 */
function _t($string, $args = array(), $langcode = NULL, $reset = FALSE) {
	global $language, $cfg;
	static $locale_t = NULL;
	//@TODO se quiser fazer texto custom
	if ($reset) {
		// Reset in-memory cache.
		$locale_t = NULL;
	}
	if (! isset ( $string )) {
		// Return all cached strings if no string was specified
		return $locale_t;
	}
	$langcode = isset ( $langcode ) ? $langcode : $language ['language']->language;
	//@TODO verificr se esta consumindo muita memoria qualquer coisa cachear
	if (isset($language ['translate'])) {
		$po = new PO ();
		$po->merge_with ( $language ['translate'] );
		$string = $po->translate ( $string );
	}
	if (empty ( $args )) {
		return $string;
	} else {
		// Transform arguments before inserting them.
		foreach ( $args as $key => $value ) {
			switch ($key [0]) {
				case '@' :
					// Escaped only.
					$args [$key] = check_plain ( $value );
					break;
				
				case '%' :
				default :
					//@todo Escaped and placeholder.
					//$args[$key] = theme('placeholder', $value);
					break;
				
				case '!' :
			
		// Pass-through.
			}
		}
		return strtr ( $string, $args );
	}
}
/**
 * Format a string containing a count of items.
 *
 * This function ensures that the string is pluralized correctly. Since t() is
 * called by this function, make sure not to pass already-localized strings to
 * it.
 *
 * For example:
 * @code
 *   $output = format_plural($node->comment_count, '1 comment', '@count comments');
 * @endcode
 *
 * Example with additional replacements:
 * @code
 *   $output = format_plural($update_count,
 *     'Changed the content type of 1 post from %old-type to %new-type.',
 *     'Changed the content type of @count posts from %old-type to %new-type.',
 *     array('%old-type' => $info->old_type, '%new-type' => $info->new_type)));
 * @endcode
 *
 * @param $count
 *   The item count to display.
 * @param $singular
 *   The string for the singular case. Please make sure it is clear this is
 *   singular, to ease translation (e.g. use "1 new comment" instead of "1 new").
 *   Do not use @count in the singular string.
 * @param $plural
 *   The string for the plural case. Please make sure it is clear this is plural,
 *   to ease translation. Use @count in place of the item count, as in "@count
 *   new comments".
 * @param $args
 *   An associative array of replacements to make after translation. Incidences
 *   of any key in this array are replaced with the corresponding value.
 *   Based on the first character of the key, the value is escaped and/or themed:
 *    - !variable: inserted as is
 *    - @variable: escape plain text to HTML (check_plain)
 *    - %variable: escape text and theme as a placeholder for user-submitted
 *      content (check_plain + theme_placeholder)
 *   Note that you do not need to include @count in this array.
 *   This replacement is done automatically for the plural case.
 * @param $langcode
 *   Optional language code to translate to a language other than
 *   what is used to display the page.
 * @return
 *   A translated string.
 */
function format_plural($count, $singular, $plural, $args = array(), $langcode = NULL,$context =NULL) {
 global $language,$cfg;
$args['@count'] = $count;
  if ($count == 1) {
    return _t($singular, $args, $langcode);
  }
	$po = new Translations();
	$po->merge_with ( $language ['translate'] );
  // Get the plural index through the gettext formula.
  $plural = $po->translate_plural($singular, $plural, $count, $context);
   return _t($plural, $args, $langcode);
}
/**
 * Formata uma URL interna ou externa com uma tag de ancora.
 *
 * Esta função manipula corretamente atalhos de caminho, e adiciona uma atributo classe 'active'
 * para os links que apontarem para a atual pagina (para themas), então todas os links internos devem 
 * preferencialmente ser criados por esta função se possivel
 *
 * @param $text
 * O texto do link para a ancora.
 * @param $path
 * O caminho interno ou externo para onde esta apontando, como "node/34" ou
 * "http://example.com/foo". Antes da função url() ser chamada para construir a
 * URL de $path e $options, o resultado da URL é passado em
 * check_url() antes de ser inserida uma tag de ancora no html, para assegurar
 * um HTML bem formatado. veja url() para maiores informações.
 * @param $options
 * Um array associativo para opções adicionais, com os seguintes elementos
 * - 'attributes': Um array associativo para atributos HTML a serem aplicados 
 * na tag.
 * - 'html' (default FALSE): Quando $text é um HTML não só texto. Por exemplo
 * para fazer uma tag de link em uma imagem, este deve estar setado como TRUE, ou
 * será escapado da tag de imagem HTML.
 * - 'language': um objeto de lingua opcional. Se o caminho a ser ligado é interno 
 * para o site, $options['language'] é utilizado para procurar o alias para o URL,
 * e para determinar se o link está "ativo", ou apontando para a página atual 
 * (o idioma bem como o caminho deve corresponder). Este elemento então será usado por url().
 * - Adicionais elementos de $options a serem usados como opções para a função url().
 *
 * @return
 * Uma String HTML contendo o link.
 */
function _l($text, $path, $options = array()) {
	global $language;
	
	// une com o padrão
	$options += array ('attributes' => array (), 'html' => FALSE );
	
	// Append active class.
	if (($path == $_GET ['q'] || ($path == '<front>' && is_frontPage ())) && (empty ( $options ['language'] ) || $options ['language']->language == $language ['language']->language)) {
		if (isset ( $options ['attributes'] ['class'] )) {
			$options ['attributes'] ['class'] .= ' active';
		} else {
			$options ['attributes'] ['class'] = 'active';
		}
	}
	
	// Remove all HTML and PHP tags from a tooltip. For best performance, we act only
	// if a quick strpos() pre-check gave a suspicion (because strip_tags() is expensive).
	if (isset ( $options ['attributes'] ['title'] ) && strpos ( $options ['attributes'] ['title'], '<' ) !== FALSE) {
		$options ['attributes'] ['title'] = strip_tags ( $options ['attributes'] ['title'] );
	}
	
	return '<a href="' . check_url ( url ( $path, $options ) ) . '"' . get_attributes ( $options ['attributes'] ) . '>' . ($options ['html'] ? $text : check_plain ( $text )) . '</a>';
}
/**
 * Define uma mensagem com o estatus da operação realizada.
 *
 * se esta função for chamada sema tributos ela ira retornar todas as mensagens registradas sem limpar
 *
 * @param $message
 * A mensagem deve começar com uma letra maiuscula e terminar com pornto final.
 * @param $type
 * tipo da mensagem. uma das seguntes são possiveis:
 * - 'status'
 * - 'warning'
 * - 'error'
 * @param $repeat
 * Se estiver false e a mensage já estiver sido setada, então a mensagem nãos erá repetida.
 */
function setMessage($message = NULL, $type = 'status', $repeat = TRUE) {
	if ($message) {
		if (! isset ( $_SESSION ['messages'] )) {
			$_SESSION ['messages'] = array ();
		}
		
		if (! isset ( $_SESSION ['messages'] [$type] )) {
			$_SESSION ['messages'] [$type] = array ();
		}
		
		if ($repeat || ! in_array ( $message, $_SESSION ['messages'] [$type] )) {
			$_SESSION ['messages'] [$type] [] = $message;
		}
	}
	
	// messages not set when DB connection fails
	return isset ( $_SESSION ['messages'] ) ? $_SESSION ['messages'] : NULL;
}
/**
 * Retorna todas mensagens que foram definidas
 *
 * @param $type
 * (optional) retorna apenas um dos tipos de mensagem.
 * @param $clear_queue
 * (optional) se definida como FALSE para que a fila de mensagens não seja limpa
 * @return
 * Um array assossiativo, a chave é o tipo da mensagem, e o valor um array com as mensagens. Se o parametro
 * $type for definida retornara apenas mensagens desse tipo.
 * ou um vazio se não quiser mensagens. Se $type não for definido
 * todas as mensages serão retornadas de todos os tipos, ou um array vazio se não houver menssagens.
 */
function getMessages($type = NULL, $clear_queue = TRUE) {
	$messages = setMessage ();
	if (isset($messages)) {
		if ($type) {
			if ($clear_queue) {
				unset ( $_SESSION ['messages'] [$type] );
			}
			if (isset ( $messages [$type] )) {
				return array ($type => $messages [$type] );
			}
		} else {
			if ($clear_queue) {
				unset ( $_SESSION ['messages'] );
			}
			return $messages;
		}
	}
	return array ();
}
/**
 * Parse an array into a valid urlencoded query string.
 *
 * @param $query
 * The array to be processed e.g. $_GET.
 * @param $exclude
 * The array filled with keys to be excluded. Use parent[child] to exclude
 * nested items.
 * @param $parent
 * Should not be passed, only used in recursive calls.
 * @return
 * An urlencoded string which can be appended to/as the URL query string.
 */
function query_string_encode($query, $exclude = array(), $parent = '') {
	$params = array ();
	
	foreach ( $query as $key => $value ) {
		$key = rawurlencode ( $key );
		if ($parent) {
			$key = $parent . '[' . $key . ']';
		}
		
		if (in_array ( $key, $exclude )) {
			continue;
		}
		
		if (is_array ( $value )) {
			$params [] = query_string_encode ( $value, $exclude, $key );
		} else {
			$params [] = $key . '=' . rawurlencode ( $value );
		}
	}
	
	return implode ( '&', $params );
}
function getModuleCore() {
	return array ('Locale' );
}
function listModulos($refresh = FALSE, $bootstrap = TRUE, $sort = FALSE, $fixed_list = NULL){
  static $list=array(), $sorted_list=array();
  global $cfg;
  if ($refresh || $fixed_list) {
    $list = array();
    $sorted_list = NULL;
    if ($fixed_list) {
      foreach ($fixed_list as $name => $module) {
        getFilename('module', $name, $module['modulo_file']);
        $list[$name] = $name;
      }
    }
    else {
      $mysql = new MYSQL($cfg);
    	if ($bootstrap) {
        $result = $mysql->SqlSelect("SELECT modulo_name, modulo_file FROM {system} WHERE modulo_type = 'module' AND modulo_status = 1 AND modulo_core = 1 ORDER BY modulo_weight ASC, modulo_file ASC");
      }
      else {
        $result = $mysql->SqlSelect("SELECT modulo_name, modulo_file FROM {system} WHERE modulo_type = 'module' AND modulo_status = 1 ORDER BY modulo_weight ASC, modulo_file ASC");
      }
      while ($module = mysql_fetch_object($result)) {
        if (file_exists($module->modulo_file)) {
          // Determine the current throttle status and see if the module should be
          // loaded based on server load. We have to directly access the throttle
          // variables, since throttle.module may not be loaded yet.
          $throttle = ($module->throttle && variable_get('throttle_level', 0) > 0);
          if (!$throttle) {
            getFilename('module', $module->modulo_name, $module->modulo_file);
            $list[$module->modulo_name] = $module->modulo_name;
          }
        }
      }
    }
  }
  $GLOBALS['modulos'] &= $list;
  if ($sort) {
    if (!isset($sorted_list)) {
      $sorted_list = $list;
      ksort($sorted_list);
    }
    return $sorted_list;
  }
  return $list;

}

function getFilename($type, $name, $filename = NULL) {
	global $cfg;
	static $files = array ();
	$GLOBALS['files'] &= $files;
	$mysql = new MYSQL($cfg);
	if (! isset ( $files [$type] )) {
		$files [$type] = array ();
	}
	
	if (! empty ( $filename ) && file_exists ( $filename )) {
		$files [$type] [$name] = $filename;
	} elseif (isset ( $files [$type] [$name] )) {
		// nothing
	} // Verify that we have an active database connection, before querying
// the database.  This is required because this function is called both
	// before we have a database connection (i.e. during installation) and
	// when a database connection fails.
	elseif ($cfg['db_name'] && (($file = $mysql->dbResult ( $mysql->SqlSelect ( "SELECT filename FROM {system} WHERE name = '%s' AND type = '%s'", $name, $type ) )) && file_exists ( $file ))) {
		$files [$type] [$name] = $file;
	} else {
		// Fallback to searching the filesystem if the database connection is
		// not established or the requested file is not found.
		$dir = (($type == 'theme_engine') ? 'themes/engines' : "${type}s");
		$file = (($type == 'theme_engine') ? "$name.engine" : "$name.$type");
		$config = SITE_MODULOS;
		foreach ( array ("$config$dir/$file", "$config$dir/$name/$file", "$dir/$file", "$dir/$name/$file" ) as $file ) {
			if (file_exists ( $file )) {
				$files [$type] [$name] = $file;
				break;
			}
		}
	}
	
	if (isset ( $files [$type] [$name] )) {
		return $files [$type] [$name];
	}
}

function loadFile($type, $name) {
	static $files = array ();
	
	if (isset ( $files [$type] [$name] )) {
		return TRUE;
	}
	
	$filename = getFilename ( $type, $name );
	
	if ($filename) {
		include_once "$filename";
		$files [$type] [$name] = TRUE;
		
		return TRUE;
	}
	
	return FALSE;
}
function module_hook($module, $hook) {
  return function_exists($module .'_'. $hook);
}
function module_invoke() {
  $args = func_get_args();
  $module = $args[0];
  $hook = $args[1];
  unset($args[0], $args[1]);
  $function = $module .'_'. $hook;
  if (module_hook($module, $hook)) {
    return call_user_func_array($function, $args);
  }
}
