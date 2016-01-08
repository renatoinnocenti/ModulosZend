<?php
####################################################################
## ARQUIVO PARA INICIALIZAÇÃO DE SITES ATR2-JAPAN                 ##
## INDEX.PHP VERS?O 1.0  - 19/09/2006                             ##
## CRIADO POR ATR-2 => RENATO INNOCENTI                           ##
## EMAIL: r.innocenti@uol.com.br                                  ##
####################################################################
error_reporting(E_ALL & ~ E_NOTICE);
session_start();
########################################################
###   CARREGANDO BIBLIOTECAS PRINCIPAIS           ######
########################################################
require('../../../../Smarty/libs/Smarty.class.php');
foreach(glob("./libs/*.php") as $fn){
	require($fn);
	}
########################################################
######    CARREGANDO Criando Objetos              ######
########################################################
$smarty = new Sitecfg();
$smarty->debugging = true;
$smarty->compile_check = true;
$smarty->caching = false;
$smarty->ConfigLocation();
$smarty->Confglanguage();
$smarty->assign($smarty->LoadPerfil());
$smarty->LoadPages();
$smarty->SetPages();
$smarty->LoadModulos();
########################################################
######    CARREGANDO BIBLIOTECAS SAJAX            ######
########################################################
$sajax_request_type = $smarty->get_config_vars('sajax_request'); //forma como os dados serao enviados
sajax_init(); //inicia o SAJAX
$sajax_debug_mode = 1;//$smarty->get_config_vars('sajax_debug'); //Debug do funcionamento através de ALERT, 1 = ligado, 0 = desligado
$sajax_request_type ='post';
sajax_export("loadPage","CheckForm","SemiPost"); // lista de funcoes a ser exportada
sajax_handle_client_request();
$SAJAX = sajax_get_javascript();
$smarty->assign('SAJAX',$SAJAX);

########################################################
$smarty->LoadFunction();
$smarty->display($smarty->actualpage["page_index"]);

########################################################
######               DEBUG INTERNO                ######
########################################################
//print_r($smarty->actualpage);
//print_r($_GET);
//print_r($_POST);
//print_r($_SESSION);
//print_r($_COOKIE);
?>