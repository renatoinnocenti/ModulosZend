<?php
####################################################################
## CLASSE PARA ALTERNATIVAS DE FUNCÕES    
## CLASS.FIX.PHP VERSÃO 2.0  - 23/05/2006 
## CRIADO POR ART-2 => RENATO INNOCENTI
## EMAIL: r.innocenti@uol.com.br 
####################################################################
error_reporting(E_ALL & ~ E_NOTICE);
function cmp($a, $b){
    return strcmp($a["label"], $b["label"]);
}
####################################################################
## Divide um array em Colunas onde $num  é o numero de colunas 
## $input = (array) - valores listados
## $num = (int) - Nº de colunas
## $preserve_keys = (bool) - mantém as chaves
####################################################################
function array_chunk_fixed($input, $num, $preserve_keys = FALSE) {
    $count = count($input) ;
	if($count)
	   $input = array_chunk($input, ceil($count/$num), $preserve_keys) ;
    $input = array_pad($input, $num, array()) ;
	return $input ;
}
####################################################################
## Converte a moeda base para outra baseado no bbc.com.br   
## $valor = (int/float) Montante a ser convertido 
## $cota = (int) Moeda base (220 - Dolar, 790 Real)  
## $moeda = (int) Moeda a ser convertida
## $taxa = (array) Valores das moedas (SiteAtr->SiteCotacao)
## ConversorMoeda(1,"045","040",$_SESSION['taxa']);	
####################################################################
function ConversorMoeda($valor,$moeda,$cota,$taxa){
    settype($price,'float');
    if($cota != $moeda && (isset($taxa[$moeda]) || ($cota == 790 || $moeda == 790))){
        switch($cota){
            case 790:
            		$price = $valor * $taxa[$moeda][4];
            		break;
            case 220:
            	    if($taxa[$moeda][1] == 'A')
                        $price = $valor / $taxa[$moeda][6];
            		elseif($moeda == 790)
                        $price = $valor / $taxa[$cota][4];
            		else
            			$price = $valor * $taxa[$moeda][6];
            		break;
            case ($moeda == 790):
                    $price = $valor * $taxa[$cota][4];
            		break;
            case ($moeda == 220):
            		if($taxa[$cota][1] == 'A')
                        $price = $valor / $taxa[$cota][6];
            		elseif($cota == 790)
            			$price = $valor / $taxa[$moeda][4];
            		else
            			$price = $valor * $taxa[$cota][6];
            		break;
            default:
            		$price = ($taxa[$cota][4] / $taxa[$moeda][4]) * $valor;
            		break;
        }
    }else{
        $price = $valor;
    }
    return $price;
}
####################################################################
## Usa o caminho reverso no caso de proxys para determinar o Real IP
## ip_address() 
####################################################################
function ip_address() {
  static $ip_address = NULL;

  if (!isset($ip_address)) {
    $ip_address = $_SERVER['REMOTE_ADDR'];
    if (variable_get('reverse_proxy', 0) && array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER)) {
      // If an array of known reverse proxy IPs is provided, then trust
      // the XFF header if request really comes from one of them.
      $reverse_proxy_addresses = variable_get('reverse_proxy_addresses', array());
      if (!empty($reverse_proxy_addresses) && in_array($ip_address, $reverse_proxy_addresses, TRUE)) {
        // If there are several arguments, we need to check the most
        // recently added one, i.e. the last one.
        $ip_address_parts = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $ip_address = array_pop($ip_address_parts);
      }
    }
  }

  return $ip_address;
}
####################################################################
## Cauculo um numero inteiro para o IP 
## INET_ATON() 
####################################################################
function INET_ATON(){
	$myip = explode('.',$_SERVER[REMOTE_ADDR]);
	$myipnew = $myip[0]*256^3 + $myip[1]*256^2 + $myip[2]*256 + $myip[3];
	return $myipnew;
}
####################################################################
## Cria uma URL com os itens utilizados 
## CriaUrl($exclui);  
## $exclui = termo a ser excluido da URL 
## Retorna uma string com os atuais uso de URL 
####################################################################
function uri2vector($page,$uri){
    $vec = explode($page.'?',$uri);
    $vet = explode('&',$vec[1]);
    foreach($vet as $vetitem){
        $item = explode('=',$vetitem);
        $querry[$item[0]] =$item[1];   
    }
    return $querry;
}
####################################################################
## Cria uma querry string apartir de um vetor(array)  
## CriaUrlVec($vector,$exclui = false); 
## $vector = array com os valores a serem inceridos na querry 
## $exclui = termo a ser excluido da URL 
## Retorna uma string com os atuais uso de URL  
####################################################################
function CriaUrlVec($vector,$exclui = false){
    while(list($key, $value) = each($vector)) {
        if(is_array($exclui)){
            if(in_array($key,$exclui))continue;
        }else{
            if($exclui != false){
                if($key == $exclui)continue;
            }
        }
        if($key == "atual")continue;
        $query_string .= "&" . $key . "=" . $value;
    }
    return $query_string;
}
######################################################################
## Retorna um IP em forma de numero  
## IP2NUM($dotted_ip_address);  
## $dotted_ip_address = string com o valor de IP com pontos
######################################################################
function IP2NUM($dotted_ip_address){
	return $ip_number = sprintf("%u", ip2long($dotted_ip_address));
	}
######################################################################
## Retorna um IP de Numeros em forma IP com Pontos 
## NUM2IP($ip_number); 
## $ip_number = string com o valor de IP numerico 
######################################################################
function NUM2IP($ip_number){
	return $dotted_ip_address = long2ip($ip_number);
	}

######################################################################
## Inicia o cronometro de tempo em milisegundos 
## timer_start($name); 
## $name = string com um nome de referencia do arquivo 
######################################################################
function timer_start($name) {
  global $timers;

  list($usec, $sec) = explode(' ', microtime());
  $timers[$name]['start'] = (float)$usec + (float)$sec;
  $timers[$name]['count'] = isset($timers[$name]['count']) ? ++$timers[$name]['count'] : 1;
}
######################################################################
## Faz a leitura do tempo decorrido
## timer_read($name); 
## $name = string com um nome de referencia do arquivo 
######################################################################
function timer_read($name) {
  global $timers;

  if (isset($timers[$name]['start'])) {
    list($usec, $sec) = explode(' ', microtime());
    $stop = (float)$usec + (float)$sec;
    $diff = round(($stop - $timers[$name]['start']) * 1000, 2);

    if (isset($timers[$name]['time'])) {
      $diff += $timers[$name]['time'];
    }
    return $diff;
  }
}
######################################################################
## para o objeto de tepo no fim da execução da página
## timer_stop($name); 
## $name = string com um nome de referencia do arquivo 
######################################################################
function timer_stop($name) {
  global $timers;

  $timers[$name]['time'] = timer_read($name);
  unset($timers[$name]['start']);

  return $timers[$name];
}
######################################################################
## FUNÇÃO PARA TRANSFORAR UM ARRAY EM UMA STRING
## f_serialize($value); 
## $value = array a ser transformado
######################################################################
function f_serialize($value){
	if(is_array($value)){
		$value = serialize($value);
	}
	return $value;
}
######################################################################
## FUNÇÃO PARA TRANSFORAR UMA STRING CRIADA APARTIR DE UM ARRAY EM UM ARRAY NOVAMENTE
## f_unserialize($value); 
## $value = array a ser transformado
######################################################################
function f_unserialize($value){
	$pos = strpos($value, 'a:');
	if(($pos !== false) && ($pos==0)){
		$value = unserialize($value);
	}
	return $value;
}

function f_checkValue($value){
	if(is_bool($value)){
		$value = ($value == false)?"FALSE":"TRUE";
	}elseif(is_string($value) && $value == 'NOW()'){
		$value = "NOW()";
	}elseif(($value == '' || is_null($value) )&& !is_int($value)){
		$value = "NULL";
	}elseif(is_int($value)){
		$value = $value;
	}elseif(is_string($value)){
		$value = "'".$value."'";
	}
	return $value;
}
################################################################################################
## EXTRAI UMA PARCELA DE UM ARRAY INFORMANDO UMA CHAVE SEM PERDER AS ASSOCIAÇÕES
## array_slice_assoc ($array, $key, $length, [$preserve_keys])
## $array = array  - a ser separado
## $key = string - nome da chave que iniciará a index
## $length = integer - numero de posições a serem rastreadas
## $preserve_keys = bolean - se falso reorganiza a array, se não mantem a ordem, padrão = true
################################################################################################
function array_slice_assoc ($array, $key, $length, $preserve_keys = true)
{
   $offset = array_search($key, array_keys($array));

   if (is_string($length))
      $length = array_search($length, array_keys($array)) - $offset;

   return array_slice($array, $offset, $length, $preserve_keys);
}


function db_query_callback($match, $init = FALSE) {
  static $args = NULL;
  if ($init) {
    $args = $match;
    return;
  }
  switch ($match[1]) {
    case '%d': // We must use type casting to int to convert FALSE/NULL/(TRUE?)
      $value = array_shift($args);
      // Do we need special bigint handling?
      if ($value > PHP_INT_MAX) {
        $precision = ini_get('precision');
        @ini_set('precision', 16);
        $value = sprintf('%.0f', $value);
        @ini_set('precision', $precision);
      }
      else {
        $value = (int) $value;
      }
      // We don't need db_escape_string as numbers are db-safe.
      return $value;
    case '%s':
      return db_escape_string(array_shift($args));
    case '%n':
      // Numeric values have arbitrary precision, so can't be treated as float.
      // is_numeric() allows hex values (0xFF), but they are not valid.
      $value = trim(array_shift($args));
      return is_numeric($value) && !preg_match('/x/i', $value) ? $value : '0';
    case '%%':
      return '%';
    case '%f':
      return (float) array_shift($args);
    case '%b': // binary data
      return db_encode_blob(array_shift($args));
  } 
}

function db_escape_string($text) {
  global $linkysql;
  return mysql_real_escape_string($text,$linkysql);
}
function db_encode_blob($data) {
  global $linkysql;
  return "'". mysql_real_escape_string($data, $linkysql) ."'";
}
?>
