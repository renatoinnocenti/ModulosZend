<?php
####################################################################
## CLASSE PARA MANIPULAÇÃO DE LOGIN/LOGOUT                        ##
## CLASS.LOGUIN.PHP VERSÃO 1.0  - 05/01/2006                      ##
## CRIADO POR ART-2 => RENATO INNOCENTI                           ##
## EMAIL: r.innocenti@uol.com.br                                  ##
####################################################################
error_reporting(E_ALL & ~ E_NOTICE);
####################################################################
## CRIA UMA INSTANCIA PARA LOGIN E LOGOUT DO SITE                 ##
## as entradas são referencias para o mysql                       ##
####################################################################
define('SITECREATOR_DIR_DEFAULT','./modulos/SiteCreator/Default');
define('SITECREATOR_DIR_CSS','./modulos/SiteCreator/css');
define('SITECREATOR_DIR_SITE','./sites/');
define('SITECREATOR_DIR_IMG','/Image');
define('SITECREATOR_DIR_Flash','/Flash');

class SiteCreator extends Arquivo
{ 
    function SaveSiteEditor(&$smarty,&$form){
        $smarty->FCKCFG['ID_SITE'] = $form['idde'];
        $smarty->FCKCFG['patchSite'] = SITECREATOR_DIR_SITE.$smarty->FCKCFG['ID_SITE'];
        $smarty->FCKCFG['patchSiteTemplate'] = $smarty->FCKCFG['patchSite'].'/templates';
        ######################################
        ## Separar os templete do conteudo  ##
        ######################################
        preg_match_all("/(\<!-- SITEBILDER:START:CONTEUDO --\>)(.*)(\<!-- SITEBILDER:END:CONTEUDO --\>)/siU",$form[$form['page_tpl']],$out, PREG_PATTERN_ORDER);
        $fncpage = $out[2][0];
        $searcharray= array(
                            "/(\<!-- SITEBILDER:START:CONTEUDO --\>)(.*)(\<!-- SITEBILDER:END:CONTEUDO --\>)/siU",
                            '/(\<span)(.*)(fck="(.*)")(.*)(\>)(.*)(\<\/span\>)/esiU'
                            );
        $replacearray = array('{insert name="fncpages"}','$this->ATRTplConvert("\\4","\\2 \\5")');
        $head = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
{include file="header.tpl"}
<body>';
        $foot = '</body></html>';
        $conteudo = $head.preg_replace($searcharray, $replacearray, $form[$form['page_tpl']]).$foot;
        $this->SetOrigem($smarty->FCKCFG['patchSiteTemplate'].'/index.tpl');
        if(!$this->CreateFile($conteudo)){
            return true;
        }
        $this->SetOrigem($smarty->FCKCFG['patchSiteTemplate'].'/'.$form['page_tpl']);
        $conteudo = preg_replace($searcharray, $replacearray, $fncpage);
        
        if(!$this->CreateFile($conteudo)){
            return true;
        }
    }
    function LoadSiteEditor(&$smarty,&$form){
        $smarty->FCKCFG['ID_SITE'] = $form['idde'];
        $smarty->FCKCFG['patchSite'] = SITECREATOR_DIR_SITE.$smarty->FCKCFG['ID_SITE'];
        $smarty->FCKCFG['patchSiteTemplate'] = $smarty->FCKCFG['patchSite'].'/templates';
        $smarty->FCKCFG['ToolbarSet'] = 'EditorCompleto';
        $smarty->FCKCFG['nameTemplate'] = (!$form['page_tpl'])?'IndexPage.tpl':$form['page_tpl'];
        $smarty->FCKCFG['Value'] = $smarty->FCKCFG['patchSiteTemplate'].'/'.$smarty->FCKCFG['nameTemplate'];
        $smarty->FCKCFG['pagelist'] = $smarty->FCKCFG['patchSiteTemplate']."/*.tpl";
        $smarty->assign($smarty->FCKCFG);
        
    }
    function RemoveSite(&$smarty,&$form){
            $this->SetOrigem(SITECREATOR_DIR_SITE.$form['idde']);
            $this->DelFiles($this->origem);        
    }
    function CopyDefultSite($destino){
        ## preparar a pasta de destino
        $this->SetDestino($destino);
        $this->SetOrigem(SITECREATOR_DIR_DEFAULT);
        ## Copiar os arquivos default do site;
        $this->CopyAllFiles($this->origem,$this->destino);             
    }
    function CopyCss($css,$destino){
        $destinocss = $destino.'/css';
        $this->SetDestino($destinocss);
        $this->SetOrigem(SITECREATOR_DIR_CSS.'/'.$css);
        $this->CopyAllFiles($this->origem,$this->destino,'/*.css');
        $destinoimg = $destino.'/templates/Image';
        $this->SetDestino($destinoimg);
        $this->SetOrigem(SITECREATOR_DIR_CSS.'/'.$css.SITECREATOR_DIR_IMG);
        $this->CopyAllFiles($this->origem,$this->destino);
        $destinofls = $destino.'/templates/Flash';
        $this->SetDestino($destinofls);
        $this->SetOrigem(SITECREATOR_DIR_CSS.'/'.$css.SITECREATOR_DIR_FLASH);
        $this->CopyAllFiles($this->origem,$this->destino);
        $destino = $destino.'/templates';
        $this->SetDestino($destino);
        $this->SetOrigem(SITECREATOR_DIR_CSS.'/'.$css);
        $this->CopyAllFiles($this->origem,$this->destino,'/*.tpl');
        
    }
    function createCfgfile($destino,$param=false){
        $camd = $destino."/configs";
        $this->SetDestino($destino);
        $root = str_replace($this->ChangePath($_SERVER["DOCUMENT_ROOT"]), '', $this->destino);
        $setcnfg = 'patch = \''.$this->destino.'/\''."\r\n";
        $setcnfg .= 'ID_SITE = \''.$param['ID_SITE'].'\''."\r\n";
        $setcnfg .= 'patch_tmp = \''.$this->ChangePath($_ENV["TEMP"]).'/\''."\r\n";
        $setcnfg .= 'http = \'http://'.$_SERVER["HTTP_HOST"].$root.'/\''."\r\n";
        $setcnfg .= 'patchsite = \''.$root.'/\''."\r\n";
        $setcnfg .= 'page_main = \'centro\''."\r\n";
        $setcnfg .= 'template_dir = \'templates/\''."\r\n";
        $setcnfg .= 'compile_dir = \'templates_c/\''."\r\n";
        $setcnfg .= 'config_dir = \'configs/\''."\r\n";
        $setcnfg .= 'cache_dir = \'cache/\''."\r\n";
        $setcnfg .= 'modulo_dir = \'modulos/\''."\r\n";
        $setcnfg .= 'sites_dir = \'sites/\''."\r\n";
        $setcnfg .= 'img_dir = \'templates/Image/\'';
        $this->SetDestino($camd);
        $cam = $destino."/configs/patch.conf";
        $this->SetOrigem($cam);
        $this->CreateFile($setcnfg);      
    }
    function createTemplates($destino,$num = 1){
        $camd = $destino."/templates";
        $this->SetDestino($camd);
        for($x=1;$x<=$num;$x++){
            $cam = $destino."/templates/interna".$x.".tpl";
            $this->SetOrigem($cam);
            $this->CreateFile("");
        }
    }
        
}

?>