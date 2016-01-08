<?
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
    function setAttrs($tipo,$attrs){
        if(preg_match_all("/([a-zA-Z0-9_]+)=\"([a-zA-Z0-9_]+)\"/siU", $attrs, $matches, PREG_SET_ORDER)){
            foreach($matches as $chave)
            if($chave[1] != 'class')
            $attr[$chave[1]]= $chave[2];
        }
        switch($tipo){
            case 'insert':
                foreach($attr as $chave => $valor){
                    if(trim($chave) =="" || trim($valor) =="")continue; 
                    $atributos .= " ".$chave.'="'.$valor.'"';
                }
                return "{insert".$atributos."}";
            break;
            case 'vars':
                    return "{#".$attr['value']."#}";
            break;
            case 'fckobj':
                 $obj = $attr['fck2'];
                 unset($attr['fck2']);
                 $func = $attr['fck3'];
                 unset($attr['fck3']);
                 foreach($attr as $chave => $valor){
                    if(trim($chave) =="" || trim($valor) =="")continue; 
                    $atributos .= " ".$chave.'="'.$valor.'"';
                }
                return '{'.$obj.'->'.$func.$atributos."}";
            break;
            default:
                return '{$'.$tipo."}";
            break;
        }
    }
    function AdmTemplatesSave(&$smarty,&$form){
        unset($form['action']);
        $searcharray= array(
                            '/(\<div id="('.$smarty->get_config_vars('page_main').')"\>)(.*)(\<\/div\>)/siU',
                            '/(\<span)(.*)(fck="(.*)")(.*)(\>)(.*)(\<\/span\>)/esiU'
                            );
        $replacearray = array("\\3",'$this->setAttrs("\\4","\\2 \\5")');
        $conteudo = preg_replace($searcharray, $replacearray, $form[$form['page_tpl']]);
        $this->SetOrigem('./templates/'.$form['page_tpl']);
        $this->CreateFile($conteudo);
    }
}
?>