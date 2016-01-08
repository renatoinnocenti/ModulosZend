<?
function smarty_insert_editor($params, &$smarty)
{
// Automatically calculates the editor base path based on the _samples directory.
// This is usefull only for these samples. A real application should use something like this:
// $oFCKeditor->BasePath = '/fckeditor/' ;	// '/fckeditor/' is the default value.
if(!isset($params['InstanceName']) || empty($params['InstanceName']))
{
$smarty->trigger_error('fckeditor: required parameter "InstanceName" missing');
}
$base_arguments = array(
                        'Width'=>'100%',
                        'Height'=>'380',
                        'ToolbarSet'=>'BasicNoticias'
                        );
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
$sBasePath = $_SERVER['PHP_SELF'] ;
$sBasePath = ''.$smarty->get_config_vars('modulo_dir').'FCKeditor/';
if($smarty->FCKCFG['ID_SITE']){
    $sBaseSrc = $smarty->get_config_vars('patchsite').'sites/'.$smarty->FCKCFG['ID_SITE'].'/';
}else{
    $sBaseSrc = $smarty->get_config_vars('patchsite').'/';
}

// Use all other parameters for the config array (replace if needed)
$other_arguments = array_diff_assoc($params, $base_arguments);
$config_arguments = array_merge($config_arguments, $other_arguments);
$oFCKeditor->Config = $config_arguments;

// parametro Value
$oFCKeditor = new FCKeditor($params['InstanceName']) ;
$oFCKeditor->BasePath	= $sBasePath ;
$oFCKeditor->Value		= (isset($params['Value']))?
                            $params['Value']:
                            $smarty->FCKCFG['Value'] ;
/*$smarty->register_outputfilter("pre_editor");
$oFCKeditor->Value = $smarty->fetch(realpath($oFCKeditor->Value));*/
if(@is_file($oFCKeditor->Value)){
    $file = new Arquivo($oFCKeditor->Value);
    $pfile = file_get_contents($file->origem);
    $searcharray= array(
                        "/(\<head)(.*)?(\>)(.*)(\<\/head\>)/siU",
                        "/(src=\")(.*)(\")/siU",
                        "/(\{([^#|^$](.*)))(\s)(.*)?(\})/siU");
                        //"/(\<)(head)(\>)(.*)(\<\/)(head)(\>)/siU";
    $replacearray = array(
                            "",
                            "\\1$sBaseSrc\\2\\3",
                            '<div modulo="\\2" class="FCK__Inserts" \\5></div>');
    $oFCKeditor->Value = preg_replace($searcharray, $replacearray, $pfile);
}
$oFCKeditor->Config['ImageBrowserURL'] = $smarty->get_config_vars('http').$smarty->get_config_vars('modulo_dir').'FCKeditor/editorftp/browser.html?ServerPath='.$smarty->get_config_vars('patchsite').'sites/'.$smarty->FCKCFG['ID_SITE'].'/templates/&Type=Image&Connector=connectors/connector.php';

$oFCKeditor->Config['LinkBrowserURL'] = $smarty->get_config_vars('http').$smarty->get_config_vars('modulo_dir').'FCKeditor/editorftp/browser.html?ServerPath='.$smarty->get_config_vars('patchsite').'sites/'.$smarty->FCKCFG['ID_SITE'].'/templates/&Connector=connectors/connector.php';

$oFCKeditor->Config['FlashBrowserURL'] = $smarty->get_config_vars('http').$smarty->get_config_vars('modulo_dir').'FCKeditor/editorftp/browser.html?ServerPath='.$smarty->get_config_vars('patchsite').'sites/'.$smarty->FCKCFG['ID_SITE'].'/templates/&Type=Flash&Connector=connectors/connector.php';


$oFCKeditor->Width = (isset($params['Width']))? $params['Width'] : $base_arguments['Width'];
$oFCKeditor->Height = (isset($params['Height']))? $params['Height'] : $base_arguments['Height'];
$oFCKeditor->ToolbarSet = (isset($params['ToolbarSet']))? $params['ToolbarSet'] : $base_arguments['ToolbarSet'];


$oFCKeditor->Config['AutoDetectLanguage'] = (isset($params['AutoDetectLanguage']))?$params['AutoDetectLanguage']:$config_arguments['AutoDetectLanguage'] ;
$oFCKeditor->Config['DefaultLanguage'] = (isset($params['DefaultLanguage']))?$params['DefaultLanguage']:$config_arguments['DefaultLanguage'] ;
$oFCKeditor->Config['CustomConfigurationsPath'] = (isset($smarty->cfg['CustomConfigurationsPath']))?$smarty->cfg['CustomConfigurationsPath']:$config_arguments['CustomConfigurationsPath'];
$oFCKeditor->Config['EditorAreaCSS'] = (isset($params['EditorAreaCSS']))?$params['EditorAreaCSS']:$smarty->FCKCFG['EditorAreaCSS'];
$oFCKeditor->Config['BaseHref'] = $smarty->get_config_vars('http').'sites/'.$smarty->FCKCFG['ID_SITE'].'/';
return $oFCKeditor->CreateHtml();
}
?>