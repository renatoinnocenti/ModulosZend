<?php
####################################################################
## ARQUIVO PARA INICIALIZAÇÃO DE SITES ATR2-JAPAN                 ##
## INDEX.PHP VERSÃO 2.0  - 19/01/2011                             ##
## RENATO INNOCENTI                           					  ##
## EMAIL: renato.innocenti@gmail.com                              ##
####################################################################
error_reporting(E_ALL & ~ E_NOTICE);

define('VERSION', '0.1b');
define('SYSTEM_NAME', 'SiteBilder');
define('SMARTY_CLASS', '../../Smarty3/libs/Smarty.class.php');
define('SITE_ROOT',$_SERVER['DOCUMENT_ROOT'].$_SERVER['REQUEST_URI']);
define('SITE_LIBS','./libs/');
define('SITE_DEBUG',true);

########################################################
###   CARREGANDO BIBLIOTECAS PRINCIPAIS           ######
########################################################
require(SMARTY_CLASS);
foreach(glob(SITE_LIBS."*.php") as $fn){
	require($fn);
	}

########################################################
######    CARREGANDO Criando Objetos              ######
########################################################
$mysite = new StartSite();
$mysite->inicializador(SITEBILDER_FULL);
//$mysite->LoadPages();

//$mysite->logMe($mysite->perfil['ID_MEMBER'],$mysite->cfg['logoutTime']);
//$mysite->display($mysite->actualpage["page_index"]);
if(SITE_DEBUG){
var_dump($GLOBALS);
print '<hr />$_SERVER <br/>';
var_dump($_SERVER);
print '<hr />$_REQUEST <br/>';
var_dump($_REQUEST);
print '<hr />$_GET <br/>';
var_dump($_GET);
print '<hr />$_POST <br/>';
var_dump($_POST);
print '<hr />$mysite <br/>';
var_dump(get_object_vars ( $mysite ));
print '<hr />';
}
?>