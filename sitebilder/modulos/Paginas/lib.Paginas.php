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

class Pagina extends Arquivo
{
    function AdmTemplates(&$smarty,&$form){
        $smarty->FCKCFG['patchSiteTemplate'] = './templates';
        $smarty->FCKCFG['ToolbarSet'] = 'EditorSimples';
        $smarty->FCKCFG['nameTemplate'] = $form['page_tpl'];
        $smarty->FCKCFG['Value'] = $smarty->FCKCFG['patchSiteTemplate'].'/'.$smarty->FCKCFG['nameTemplate'];
    }
    
    function AdmTemplatesSave(&$smarty,&$form){
        unset($form['action']);
        $searcharray= array(
                            '/(\<div id="('.$smarty->get_config_vars('page_main').')"\>)(.*)(\<\/div\>)/siU',
                            '/(\<span)(.*)(fck="(.*)")(.*)(\>)(.*)(\<\/span\>)/esiU'
                            );
        $replacearray = array("\\3",'$this->ATRTplConvert("\\4","\\2 \\5")');
        $conteudo = preg_replace($searcharray, $replacearray, $form[$form['page_tpl']]);
        $this->SetOrigem('./templates/'.$form['page_tpl']);
        $this->CreateFile($conteudo);
    }
}
?>