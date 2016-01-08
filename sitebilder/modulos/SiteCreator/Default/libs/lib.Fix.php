<?php
####################################################################
## CLASSE PARA ALTERNATIVAS DE FUNCÕES                            ##
## CLASS.FIX.PHP VERSÃO 2.0  - 23/05/2006                         ##
## CRIADO POR ART-2 => RENATO INNOCENTI                           ##
## EMAIL: r.innocenti@uol.com.br                                  ##
####################################################################
error_reporting(E_ALL & ~ E_NOTICE);
function cmp($a, $b){
    return strcmp($a["label"], $b["label"]);
}
####################################################################
## Divide um array em Colunas onde $num  é o numero de colunas    ##
## $input = (array) - valores listados          				  ##
## $num = (int) - Nº de colunas          					      ##
## $preserve_keys = (bool) - mantém as chaves  		              ##
####################################################################
function array_chunk_fixed($input, $num, $preserve_keys = FALSE) {
    $count = count($input) ;
	if($count)
	   $input = array_chunk($input, ceil($count/$num), $preserve_keys) ;
    $input = array_pad($input, $num, array()) ;
	return $input ;
}
####################################################################
## Converte a moeda base para outra baseado no bbc.com.br         ##
## $valor = (int/float) Montante a ser convertido                 ##
## $cota = (int) Moeda base (220 - Dolar, 790 Real)               ##
## $moeda = (int) Moeda a ser convertida						  ##
## $taxa = (array) Valores das moedas (SiteAtr->SiteCotacao)	  ##
## ConversorMoeda(1,"045","040",$_SESSION['taxa']);				  ##
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
## Cauculo um numero inteiro para o IP                            ##
## INET_ATON()                                                    ##
####################################################################
function INET_ATON(){
	$myip = explode('.',$_SERVER[REMOTE_ADDR]);
	$myipnew = $myip[0]*256^3 + $myip[1]*256^2 + $myip[2]*256 + $myip[3];
	return $myipnew;
}
####################################################################
## Cria uma URL com os itens utilizados                           ##
## CriaUrl($exclui);                                              ##
## $exclui = termo a ser excluido da URL                          ##
## Retorna uma string com os atuais uso de URL                    ##
####################################################################
function CriaUrl($tipo = 'GET',$exclui = false){
    list($nome_arq, $voided) = explode("?", $_SERVER[REQUEST_URI]);
    $cgi = ($tipo == 'GET')?$_GET: $_POST;
    reset ($cgi);
    while(list($key, $value) = each($cgi)) {
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
####################################################################
## Cria uma URL com os itens utilizados                           ##
## CriaUrl($exclui);                                              ##
## $exclui = termo a ser excluido da URL                          ##
## Retorna uma string com os atuais uso de URL                    ##
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
## Cria uma querry string apartir de um vetor(array)              ##
## CriaUrlVec($vector,$exclui = false);                           ##
## $vector = array com os valores a serem inceridos na querry     ##
## $exclui = termo a ser excluido da URL                          ##
## Retorna uma string com os atuais uso de URL                    ##
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
## Retorna um IP em forma de numero                                 ##
## IP2NUM($dotted_ip_address);                                      ##
## $dotted_ip_address = string com o valor de IP com pontos         ##
######################################################################
function IP2NUM($dotted_ip_address){
	return $ip_number = sprintf("%u", ip2long($dotted_ip_address));
	}
######################################################################
## Retorna um IP de Numeros em forma IP com Pontos                  ##
## NUM2IP($ip_number);                                              ##
## $ip_number = string com o valor de IP numerico                   ##
######################################################################
function NUM2IP($ip_number){
	return $dotted_ip_address = long2ip($ip_number);
	}

function pre_editor($tpl_source, &$smarty){
    $bb = $smarty->get_config_vars('patchsite').'sites/'.$smarty->FCKCFG['ID_SITE'].'/';
    $searcharray= array("/(\<head)(.*)?(\>)(.*)(\<\/head\>)/siU");
    $replacearray = array("");
    return preg_replace($searcharray, $replacearray, $tpl_source);
}
?>
