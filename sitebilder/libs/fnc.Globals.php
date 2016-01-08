<?php
##################################################################
## GRUPO DE FUNÇÕES PARA SER UTILIZADO EM ESCALA GLOBAL   		##
## FNC.GLOBALS.PHP VERSÃO 1.0  - 28/01/2011 					##
## RENATO INNOCENTI												##
## EMAIL: renato.innocenti@gmail.com                           	##
##################################################################
function sitebilder_valid_http_host($host) {
  return preg_match('/^\[?(?:[a-z0-9-:\]_]+\.?)+$/', $host);
}

/*
 * Encode special characters in a plain-text string for display as HTML.
 *
 * Also validates strings as UTF-8 to prevent cross site scripting attacks on
 * Internet Explorer 6.
 *
 * @param $text
 *   The text to be checked or processed.
 * @return
 *   An HTML safe version of $text, or an empty string if $text is not
 *   valid UTF-8.
 *
 * @see drupal_validate_utf8().
 */
function check_plain($text) {
  static $php525;

  if (!isset($php525)) {
    $php525 = version_compare(PHP_VERSION, '5.2.5', '>=');
  }
  // We duplicate the preg_match() to validate strings as UTF-8 from
  // drupal_validate_utf8() here. This avoids the overhead of an additional
  // function call, since check_plain() may be called hundreds of times during
  // a request. For PHP 5.2.5+, this check for valid UTF-8 should be handled
  // internally by PHP in htmlspecialchars().
  // @see http://www.php.net/releases/5_2_5.php
  // @todo remove this when support for either IE6 or PHP < 5.2.5 is dropped.

  if ($php525) {
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
  }
  return (preg_match('/^./us', $text) == 1) ? htmlspecialchars($text, ENT_QUOTES, 'UTF-8') : '';
}

function variable_get($name, $default) {
	global $cfg;
	return $cfg[$name] = isset($cfg[$name]) ? $cfg[$name] : $default;	
}
function install_goto($path) {
	global $base_url;
	header('Location: '. $base_url .'/'. $path);
	header('Cache-Control: no-cache'); // Not a permanent redirect.
	exit();
}
function _locale_get_predefined_list() {
  return array(
    "aa" => array("Afar"),
    "ab" => array("Abkhazian", "Ð°Ò§ÑÑƒÐ° Ð±Ñ‹Ð·ÑˆÓ™Ð°"),
    "ae" => array("Avestan"),
    "af" => array("Afrikaans"),
    "ak" => array("Akan"),
    "am" => array("Amharic", "áŠ áˆ›áˆ­áŠ›"),
    "ar" => array("Arabic", /* Left-to-right marker "â€­" */ "Ø§Ù„Ø¹Ø±Ø¨ÙŠØ©", LANGUAGE_RTL),
    "as" => array("Assamese"),
    "av" => array("Avar"),
    "ay" => array("Aymara"),
    "az" => array("Azerbaijani", "azÉ™rbaycan"),
    "ba" => array("Bashkir"),
    "be" => array("Belarusian", "Ð‘ÐµÐ»Ð°Ñ€ÑƒÑÐºÐ°Ñ"),
    "bg" => array("Bulgarian", "Ð‘ÑŠÐ»Ð³Ð°Ñ€ÑÐºÐ¸"),
    "bh" => array("Bihari"),
    "bi" => array("Bislama"),
    "bm" => array("Bambara", "Bamanankan"),
    "bn" => array("Bengali"),
    "bo" => array("Tibetan"),
    "br" => array("Breton"),
    "bs" => array("Bosnian", "Bosanski"),
    "ca" => array("Catalan", "CatalÃ "),
    "ce" => array("Chechen"),
    "ch" => array("Chamorro"),
    "co" => array("Corsican"),
    "cr" => array("Cree"),
    "cs" => array("Czech", "ÄŒeÅ¡tina"),
    "cu" => array("Old Slavonic"),
    "cv" => array("Chuvash"),
    "cy" => array("Welsh", "Cymraeg"),
    "da" => array("Danish", "Dansk"),
    "de" => array("German", "Deutsch"),
    "dv" => array("Maldivian"),
    "dz" => array("Bhutani"),
    "ee" => array("Ewe", "ÆÊ‹É›"),
    "el" => array("Greek", "Î•Î»Î»Î·Î½Î¹ÎºÎ¬"),
    "en" => array("English"),
    "eo" => array("Esperanto"),
    "es" => array("Spanish", "EspaÃ±ol"),
    "et" => array("Estonian", "Eesti"),
    "eu" => array("Basque", "Euskera"),
    "fa" => array("Persian", /* Left-to-right marker "â€­" */ "ÙØ§Ø±Ø³ÛŒ", LANGUAGE_RTL),
    "ff" => array("Fulah", "Fulfulde"),
    "fi" => array("Finnish", "Suomi"),
    "fj" => array("Fiji"),
    "fo" => array("Faeroese"),
    "fr" => array("French", "FranÃ§ais"),
    "fy" => array("Frisian", "Frysk"),
    "ga" => array("Irish", "Gaeilge"),
    "gd" => array("Scots Gaelic"),
    "gl" => array("Galician", "Galego"),
    "gn" => array("Guarani"),
    "gu" => array("Gujarati"),
    "gv" => array("Manx"),
    "ha" => array("Hausa"),
    "he" => array("Hebrew", /* Left-to-right marker "â€­" */ "×¢×‘×¨×™×ª", LANGUAGE_RTL),
    "hi" => array("Hindi", "à¤¹à¤¿à¤¨à¥à¤¦à¥€"),
    "ho" => array("Hiri Motu"),
    "hr" => array("Croatian", "Hrvatski"),
    "hu" => array("Hungarian", "Magyar"),
    "hy" => array("Armenian", "Õ€Õ¡ÕµÕ¥Ö€Õ¥Õ¶"),
    "hz" => array("Herero"),
    "ia" => array("Interlingua"),
    "id" => array("Indonesian", "Bahasa Indonesia"),
    "ie" => array("Interlingue"),
    "ig" => array("Igbo"),
    "ik" => array("Inupiak"),
    "is" => array("Icelandic", "Ãslenska"),
    "it" => array("Italian", "Italiano"),
    "iu" => array("Inuktitut"),
    "ja" => array("Japanese", "æ—¥æœ¬èªž"),
    "jv" => array("Javanese"),
    "ka" => array("Georgian"),
    "kg" => array("Kongo"),
    "ki" => array("Kikuyu"),
    "kj" => array("Kwanyama"),
    "kk" => array("Kazakh", "ÒšÐ°Ð·Ð°Ò›"),
    "kl" => array("Greenlandic"),
    "km" => array("Cambodian"),
    "kn" => array("Kannada", "à²•à²¨à³à²¨à²¡"),
    "ko" => array("Korean", "í•œêµ­ì–´"),
    "kr" => array("Kanuri"),
    "ks" => array("Kashmiri"),
    "ku" => array("Kurdish", "KurdÃ®"),
    "kv" => array("Komi"),
    "kw" => array("Cornish"),
    "ky" => array("Kirghiz", "ÐšÑ‹Ñ€Ð³Ñ‹Ð·"),
    "la" => array("Latin", "Latina"),
    "lb" => array("Luxembourgish"),
    "lg" => array("Luganda"),
    "ln" => array("Lingala"),
    "lo" => array("Laothian"),
    "lt" => array("Lithuanian", "LietuviÅ³"),
    "lv" => array("Latvian", "LatvieÅ¡u"),
    "mg" => array("Malagasy"),
    "mh" => array("Marshallese"),
    "mi" => array("Maori"),
    "mk" => array("Macedonian", "ÐœÐ°ÐºÐµÐ´Ð¾Ð½ÑÐºÐ¸"),
    "ml" => array("Malayalam", "à´®à´²à´¯à´¾à´³à´‚"),
    "mn" => array("Mongolian"),
    "mo" => array("Moldavian"),
    "mr" => array("Marathi"),
    "ms" => array("Malay", "Bahasa Melayu"),
    "mt" => array("Maltese", "Malti"),
    "my" => array("Burmese"),
    "na" => array("Nauru"),
    "nd" => array("North Ndebele"),
    "ne" => array("Nepali"),
    "ng" => array("Ndonga"),
    "nl" => array("Dutch", "Nederlands"),
    "nb" => array("Norwegian BokmÃ¥l", "BokmÃ¥l"),
    "nn" => array("Norwegian Nynorsk", "Nynorsk"),
    "nr" => array("South Ndebele"),
    "nv" => array("Navajo"),
    "ny" => array("Chichewa"),
    "oc" => array("Occitan"),
    "om" => array("Oromo"),
    "or" => array("Oriya"),
    "os" => array("Ossetian"),
    "pa" => array("Punjabi"),
    "pi" => array("Pali"),
    "pl" => array("Polish", "Polski"),
    "ps" => array("Pashto", /* Left-to-right marker "â€­" */ "Ù¾ÚšØªÙˆ", LANGUAGE_RTL),
    "pt-pt" => array("Portuguese, Portugal", "PortuguÃªs"),
    "pt-br" => array("Portuguese, Brazil", "PortuguÃªs"),
    "qu" => array("Quechua"),
    "rm" => array("Rhaeto-Romance"),
    "rn" => array("Kirundi"),
    "ro" => array("Romanian", "RomÃ¢nÄƒ"),
    "ru" => array("Russian", "Ð ÑƒÑÑÐºÐ¸Ð¹"),
    "rw" => array("Kinyarwanda"),
    "sa" => array("Sanskrit"),
    "sc" => array("Sardinian"),
    "sd" => array("Sindhi"),
    "se" => array("Northern Sami"),
    "sg" => array("Sango"),
    "sh" => array("Serbo-Croatian"),
    "si" => array("Sinhala", "à·ƒà·’à¶‚à·„à¶½"),
    "sk" => array("Slovak", "SlovenÄina"),
    "sl" => array("Slovenian", "SlovenÅ¡Äina"),
    "sm" => array("Samoan"),
    "sn" => array("Shona"),
    "so" => array("Somali"),
    "sq" => array("Albanian", "Shqip"),
    "sr" => array("Serbian", "Ð¡Ñ€Ð¿ÑÐºÐ¸"),
    "ss" => array("Siswati"),
    "st" => array("Sesotho"),
    "su" => array("Sudanese"),
    "sv" => array("Swedish", "Svenska"),
    "sw" => array("Swahili", "Kiswahili"),
    "ta" => array("Tamil", "à®¤à®®à®¿à®´à¯"),
    "te" => array("Telugu", "à°¤à±†à°²à±à°—à±"),
    "tg" => array("Tajik"),
    "th" => array("Thai", "à¸ à¸²à¸©à¸²à¹„à¸—à¸¢"),
    "ti" => array("Tigrinya"),
    "tk" => array("Turkmen"),
    "tl" => array("Tagalog"),
    "tn" => array("Setswana"),
    "to" => array("Tonga"),
    "tr" => array("Turkish", "TÃ¼rkÃ§e"),
    "ts" => array("Tsonga"),
    "tt" => array("Tatar", "TatarÃ§a"),
    "tw" => array("Twi"),
    "ty" => array("Tahitian"),
    "ug" => array("Uighur"),
    "uk" => array("Ukrainian", "Ð£ÐºÑ€Ð°Ñ—Ð½ÑÑŒÐºÐ°"),
    "ur" => array("Urdu", /* Left-to-right marker "â€­" */ "Ø§Ø±Ø¯Ùˆ", LANGUAGE_RTL),
    "uz" => array("Uzbek", "o'zbek"),
    "ve" => array("Venda"),
    "vi" => array("Vietnamese", "Tiáº¿ng Viá»‡t"),
    "wo" => array("Wolof"),
    "xh" => array("Xhosa", "isiXhosa"),
    "yi" => array("Yiddish"),
    "yo" => array("Yoruba", "YorÃ¹bÃ¡"),
    "za" => array("Zhuang"),
    "zh-hans" => array("Chinese, Simplified", "ç®€ä½“ä¸­æ–‡"),
    "zh-hant" => array("Chinese, Traditional", "ç¹é«”ä¸­æ–‡"),
    "zu" => array("Zulu", "isiZulu"),
  );
}
function LoadRules($id,$acesso='ALL',&$smarty){
	global $cfg;
	switch ($acesso){
		case 'ONLY_GUEST':
			return ($id != 0)? false:true;
			break;
		case 'NOT_GUEST':
			return ($id <= 0)? false:true;
			break;
		case 'ALL':
			return true;
			break;
		default:
			$mysql = new MYSQL($smarty);
			$result = $mysql->SqlSelect("SELECT r.name
											FROM {$cfg[db_prefix]}role r
											RIGHT JOIN  {$cfg[db_prefix]}members_roles m ON r.ID_RULE = m.ID_RULE
											WHERE m.ID_MEMBER = '{$id}'
											ORDER BY r.weight",__FILE__,__LINE__);
			if(mysql_affected_rows()> 0 ){
				return $linha= mysql_fetch_assoc($result);
			}else{
				return false;
			}
		break;
	}
}
####################################################################
## Cria um array com os indeces de uma requisição query 
## ReadUrl($uri); 
## $uri = uri a ser decodificado
## Retorna uma string com os atuais uso de URL 
####################################################################
function ReadUrl($uri=null){
	$url = array();
	if(!$uri && $_GET['q']){
		$uri = $_GET['q'];
		unset($_GET['q']);
		if(count($_GET)>0){
			$url['extra'] = $_GET;
		}
	}elseif(!$uri && $_POST){
		$uri = BilderUrl();
	}elseif(!$uri){
		return SiteBilderIndex();
	}
	$url += MapUrl($uri);
	return $url;
}
####################################################################
## Cria uma URL com os itens utilizados  
## CleanUrl($exclui); 
## $exclui = termo a ser excluido da URL
## Retorna uma string com os atuais uso de URL 
####################################################################
function MapUrl($querry){
	$q = explode('/', $querry);
	$map['node'] = array_shift($q);
	$int_fnc = (is_numeric($q[0]))?'idde':'function';
	if(count($q)>0)
		$map[$int_fnc] = array_shift($q);
	if(count($q)>0){
		if($int_fnc == 'idde'){
			$map['name'] = $q[0];
		}else{
			$map['args'] = $q;
		}
	}
	return $map;	
}
function BilderUrl(){
	print "cria vetor de um POST";
}
function SiteBilderIndex(){
	return $querry = array('node'=>'index');
}
?>