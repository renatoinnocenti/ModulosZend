<?php

function language_default($property = NULL) {
	global $cfg;
	$language = variable_get('language_default', (object) array('language' => 'en', 'name' => 'English', 'native' => 'English', 'direction' => 0, 'enabled' => 1, 'plurals' => 0, 'formula' => '', 'domain' => '', 'prefix' => '', 'weight' => 0, 'javascript' => ''));
	return $property ? $language->$property : $language;
}

function poFormater($linha){
	$arg = array(
		'singular'=> $linha['source'],
		'plural'=> $linha['plural'],
		'translations'=> array($linha['translation']),
		'references'=> explode(';;',$linha['location']),
		);
		if($linha['plural'] != NULL) $arg['translations'][] = $linha['tplural'];
		return $arg;			
}
function po_db($entry,$group='default',$x=1){
	foreach($entry->entries as $key){
		$linha = get_object_vars($key);
		$arg['locales_source'][$x] = array(
			'lid'=>$x,
			'location'=> implode(';;',$linha['references']),
			'textgroup'=> $group,
			'source'=> $linha['singular'],
			'version'=>$entry->headers['Project-Id-Version']
		);
		$arg['locales_target'][$x] = array(
			'translation'=>$linha['translations'][0],
			'language'=>strtolower($entry->headers['Language']),
			'lid'=>$x,
			'plid'=>0,
			'plural'=> false
		);
		if($linha[is_plural]==true){
			$y = $x+1;
				$arg['locales_source'][$y] = array(
				'lid'=>$y,
				'location'=> implode(';;',$linha['references']),
				'textgroup'=> $group,
				'source'=> $linha['plural'],
				'version'=>$entry->headers['Project-Id-Version']
			);
			$arg['locales_target'][$y] = array(
				'translation'=>$linha['translations'][1],
				'language'=>strtolower($entry->headers['Language']),
				'lid'=>$y,
				'plid'=> $x,
				'plural'=>true
			);
				$x++;
		}
		$x++;
	}
	return $arg;
}
function t($string,$arg = array(),$lang=null){
 global $languages,  $cfg;

 }
 
 function getALLfromIP($addr,$db) {
  // this sprintf() wrapper is needed, because the PHP long is signed by default
  $ipnum = sprintf("%u", ip2long($addr));
  $query = "SELECT * FROM ip NATURAL JOIN country_code WHERE ${ipnum} BETWEEN start AND end";
  $result = mysql_query($query, $db);
  if((! $result) or mysql_numrows($result) < 1) {
    //exit("mysql_query returned nothing: ".(mysql_error()?mysql_error():$query));
    return false;
  }
  return mysql_fetch_array($result);
}
function getCCfromIP($addr,$db) {
  $data = getALLfromIP($addr,$db);
  if($data) return $data['cc'];
    return false;
}
function getCOUNTRYfromIP($addr,$db) {
  $data = getALLfromIP($addr,$db);
  if($data) return $data['cn'];
    return false;
}
function getCCfromNAME($name,$db) {
  $addr = gethostbyname($name);
  return getCCfromIP($addr,$db);
}
function getCOUNTRYfromNAME($name,$db) {
  $addr = gethostbyname($name);
  return getCOUNTRYfromIP($addr,$db);
}

?>