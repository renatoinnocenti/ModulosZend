<?
function smarty_insert_editores($params, &$smarty)
{
    // Automatically calculates the editor base path based on the _samples directory.
    // This is usefull only for these samples. A real application should use something like this:
    // $oFCKeditor->BasePath = '/fckeditor/' ;	// '/fckeditor/' is the default value.
    function reloadlng($str,&$smarty){
        foreach (glob("./modulos/*",GLOB_ONLYDIR) as $dirname) {
            foreach(glob($dirname.'/'.$smarty->cfg['language_atual'].'.*.conf') as $filename){
                $smarty->config_load(realpath($filename),"atual");
            }
        }
        if($smarty->get_config_vars($str)==''){
            return $str;
        }
        return $smarty->get_config_vars($str);
    }
    if(!isset($params['InstanceName']) || empty($params['InstanceName'])){
        $smarty->trigger_error('fckeditor: required parameter "InstanceName" missing');
    }
    if(isset($smarty->FCKCFG['Width'])){
        $base_arguments['Width'] = $smarty->FCKCFG['Width'];
    }elseif(isset($params['Width'])){
        $base_arguments['Width'] = $smarty->params['Width'];
    }else{
        $base_arguments['Width'] = '100%';
    }
    if(isset($smarty->FCKCFG['Height'])){
        $base_arguments['Height'] = $smarty->FCKCFG['Height'];
    }elseif(isset($params['Height'])){
        $base_arguments['Height'] = $smarty->params['Height'];
    }else{
        $base_arguments['Height'] = '380';
    }
    if(isset($smarty->FCKCFG['ToolbarSet'])){
        $base_arguments['ToolbarSet'] = $smarty->FCKCFG['ToolbarSet'];
    }elseif(isset($params['ToolbarSet'])){
        $base_arguments['ToolbarSet'] = $smarty->params['ToolbarSet'];
    }else{
        $base_arguments['ToolbarSet'] = 'Basic';
    }
    if(isset($smarty->FCKCFG['patchSiteTemplate'])){
        $base_arguments['patchSiteTemplate'] = $smarty->FCKCFG['patchSiteTemplate'];
    }elseif(isset($params['patchSiteTemplate'])){
        $base_arguments['patchSiteTemplate'] = $smarty->params['patchSiteTemplate'];
    }else{
        $base_arguments['patchSiteTemplate'] = './templates';
    }
    $config_arguments = array(
        'DocType' => '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">',
        'AutoDetectLanguage'=>false,
        'DefaultLanguage'=>$_SESSION['language'],
        'CustomConfigurationsPath' => "atrconfig.js",
        'LinkDlgHideTarget' => true,
        'DisableObjectResizing'=>true,
        'DisableFFTableHandles' =>true,
        'LinkDlgHideTarget'=>true,
        'LinkDlgHideAdvanced'=>true,
        'ImageDlgHideLink'=>true,
        'ImageDlgHideAdvanced'=>true,
        'FlashDlgHideAdvanced'=>true,
        'FormatOutput'=>true,
        'FormatSource'=>true,                                                    
        );
// Use all other parameters for the config array (replace if needed)
$other_arguments = array_diff_assoc($params, $base_arguments);
$config_arguments = array_merge($config_arguments, $other_arguments);
// parametro Value
$oFCKeditor = new FCKeditor($params['InstanceName']) ;
$oFCKeditor->Config = $config_arguments;

    $file = new Arquivo();
    $sBasePath = ''.$smarty->get_config_vars('modulo_dir').'FCKeditor/';
    if($smarty->FCKCFG['ID_SITE']){
        $sBaseSrc = $smarty->get_config_vars('patchsite').'sites/'.$smarty->FCKCFG['ID_SITE'].'/';
        $file->SetOrigem('./'.$sBaseSrc.'css');
        $css = $file->loadContent('file','/*.css');
        $oFCKeditor->Config['EditorAreaCSS'] = $smarty->get_config_vars('http').'sites/css/'.$smarty->FCKCFG['ID_SITE'].'/'.basename($css[0]);
        $oFCKeditor->Config['BaseHref'] = $smarty->get_config_vars('http').'sites/'.$smarty->FCKCFG['ID_SITE'].'/';
    }else{
        $sBaseSrc = $smarty->get_config_vars('patchsite');
        $file->SetOrigem($smarty->get_config_vars('patch').'css');
        $css = $file->loadContent('file','/*.css');
        $oFCKeditor->Config['EditorAreaCSS'] = $smarty->get_config_vars('http').'css/'.basename($css[0]);
        $oFCKeditor->Config['BaseHref'] = $smarty->get_config_vars('http');
    }

$oFCKeditor->BasePath	= $sBasePath ;
$valor = (isset($params['Value']))?
                            $params['Value']:
                            $smarty->FCKCFG['Value'] ;
if(@is_file(realpath($valor))){
    $file->SetOrigem($valor,true);
    $pfile = file_get_contents($file->origem);
    if(basename($valor) != 'feet.tpl')
        $pfile ='<div id="'.$smarty->get_config_vars('page_main').'">'.$pfile.'</div>';
    $searcharray= array(
                        "/(\<head)(.*)?(\>)(.*)(\<\/head\>)/siU",
                        "/(\{)(insert)(.*)(name=\"(.*)\")(.*)\}/siU",
                        "/([^\"])(\{\#)(.*)(\#\})/esiU",
                        "/([^\"])(\{[$])(.*)(\})/siU",
                        "/[^\"]\{(.*)-\>(.*)(\s(.*))?(\})/siU");
   $replacearray = array(
                        '',
                        '<span fck="\\2" class="FCK__Inserts"\\3\\4\\6>\\2 \\5</span>',
                        '"<span fck=\"vars\" class=\"FCK__vars\" value=\"\\3\">".reloadlng("\\3",$smarty)."</span>"',
                        '<span fck="\\3" class="FCK__vars">'.$smarty->get_config_vars('fkc_ref').'\\3</span>',
                        '<span fck="fckobj" class="FCK__vars" fck2="\\1" fck3="\\2" \\3>'.$smarty->get_config_vars('fkc_obj').': \\2</span>',);
    $oFCKeditor->Value = preg_replace($searcharray, $replacearray, $pfile);
    $smarty->clear_config('atual');
}
$oFCKeditor->Config['ImageBrowserURL'] = $smarty->get_config_vars('http').$smarty->get_config_vars('modulo_dir').'FCKeditor/editorftp/browser.html?ServerPath='.$sBaseSrc.'templates/&Type=Image&Connector=connectors/connector.php';

$oFCKeditor->Config['LinkBrowserURL'] = $smarty->get_config_vars('http').$smarty->get_config_vars('modulo_dir').'FCKeditor/editorftp/browser.html?ServerPath='.$sBaseSrc.'templates/&Connector=connectors/connector.php';

$oFCKeditor->Config['FlashBrowserURL'] = $smarty->get_config_vars('http').$smarty->get_config_vars('modulo_dir').'FCKeditor/editorftp/browser.html?ServerPath='.$sBaseSrc.'templates/&Type=Flash&Connector=connectors/connector.php';


$oFCKeditor->Width = $base_arguments['Width'];
$oFCKeditor->Height = $base_arguments['Height'];
$oFCKeditor->ToolbarSet =$base_arguments['ToolbarSet'];


$oFCKeditor->Config['AutoDetectLanguage'] = (isset($params['AutoDetectLanguage']))?$params['AutoDetectLanguage']:$config_arguments['AutoDetectLanguage'] ;
$oFCKeditor->Config['DefaultLanguage'] = (isset($params['DefaultLanguage']))?$params['DefaultLanguage']:$config_arguments['DefaultLanguage'] ;
$oFCKeditor->Config['CustomConfigurationsPath'] = (isset($smarty->cfg['CustomConfigurationsPath']))?$smarty->cfg['CustomConfigurationsPath']:$config_arguments['CustomConfigurationsPath'];
return $oFCKeditor->CreateHtml();
}
?>