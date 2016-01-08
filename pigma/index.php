<?php
/**
 * Inicio do criador de site
 * @file: index.php version (1.5) - 07/02/2011
 * Renato Innocenti
 * Email:renato.innocenti@gmail.com
 **/

define ( 'VERSION', '0.3' );
define ( 'SYSTEM_NAME', 'SiteBilder' );
define ( 'SMARTY_CLASS', '../../Smarty3/libs/Smarty.class.php' );
define ( 'SITE_ROOT', $_SERVER ['DOCUMENT_ROOT'] . $_SERVER ['REQUEST_URI'] );
define ( 'SITE_HTTP', 'http://'.$_SERVER ['HTTP_HOST'] . $_SERVER ['REQUEST_URI'] );
define ( 'SITE_LIBS', './libs/' );
define ( 'SITE_MODULOS', './modulos/' );
define ( 'SITE_LOCALE', './locale/' );
define ( 'SITE_DEBUG', TRUE );
error_reporting(E_ALL & ~ E_NOTICE);
/*
 * carregando bibliotecas principais
 */
require (SMARTY_CLASS);

foreach ( glob ( SITE_LIBS . "*.php" ) as $fn ) {
	require_once ($fn);
}

/*
 * carregando objeto 
 */
$mysite = new StartSite ();
$mysite->initialize ( SITEBILDER_FULL );
print _t("Cancel");
print "<br />";
if (SITE_DEBUG) {
	var_dump ( $GLOBALS );
	print '<hr />$_SERVER <br/>';
	var_dump ( $_SERVER );
	print '<hr />$_REQUEST <br/>';
	var_dump ( $_REQUEST );
	print '<hr />$_GET <br/>';
	var_dump ( $_GET );
	print '<hr />$_POST <br/>';
	var_dump ( $_POST );
	print '<hr />$mysite <br/>';
	var_dump ( get_object_vars ( $mysite ) );
	print '<hr />';
}
?>