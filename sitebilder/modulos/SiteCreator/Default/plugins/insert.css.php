<?
function smarty_insert_css($params, &$smarty)
{
	$cam = $smarty->get_config_vars('patch')."css/*.css";
    foreach(@glob($cam) as $fn){
        $link = dirname($_SERVER["PHP_SELF"])."/css/".basename($fn);
        $x = str_replace($smarty->get_config_vars('patchsite'), '', $link);
        $css .= '@import "'.$x.'";';
    }
    return $css;	 
}
?>