<?
######################################################################
## CLASSE PARA MANIPULAÇÃO DE EMAILS                                ##
## CLASS.MAIL.PHP VERSÃO 1.0  - 7/01/2006                           ##
## CRIADO POR RENATO INNOCENTI                                      ##
## EMAIL: r.innocenti@uol.com.br                                    ##
######################################################################
error_reporting(E_ALL & ~ E_NOTICE);
######################################################################
## CRIA INSTANCIA PARA ENVIO DE E-MAIL                              ##
## AS ENTRADAS SÃO REFERENTES ENDEREÇOS DE EMAILS                   ##
## Classe: MyMail                                                   ##
######################################################################
class MyMail{
    var $smarty=array();
    
    ######################################################################
    ## METODO INICIALIZADOR, INSERE O OBJETO DE TEMPLATE NO OBJETO      ##
    ## MyMail(&$smarty)                                                 ##
    ## $smarty = OBJETO => RECEBE O OBJETO DO TEMPLATE                  ##
    ######################################################################
    function MyMail(&$smarty){
        $this->smarty = $smarty;
    }
    ######################################################################
    ## METODO CRIA UM OBJETO DE REMETENTES DO EMAIL                     ##
    ## SetRemetentes($email,$nome=false)                                ##
    ## $email = STRING/ARRAY => contem os e-mails a serem enviados      ##
    ## $nome = STRING/ARRAY => contem os nome  a serem enviados         ##
    ##      [OPCIONAIS]                                                 ##
    ## $email = int => Contem o ID do usuario paa apanhar e-mail e nome ##
    ######################################################################
    function SetRemetentes($email,$nome=false){
        if(is_array($email)){
            foreach($email as $send){
                $out = $this->Listmail($email,$nome);
                $this->remetente .= ($out != true)?'':$out.',';
            }
        }else{
            $this->remetente = $this->Listmail($email,$nome);
        }
    }
    ######################################################################
    ## METODO CRIA UM OBJETO DE DESTINATÁRIOS DO EMAIL                  ##
    ## SetTo($email,$nome=false)                                        ##
    ## $email = STRING/ARRAY => contem os e-mails a serem enviados      ##
    ## $nome = STRING/ARRAY => contem os nome  a serem enviados         ##
    ##      [OPCIONAIS]                                                 ##
    ## $email = int => Contem o ID do usuario paa apanhar e-mail e nome ##
    ######################################################################
    function SetTo($email,$nome=false){
        if(is_array($email)){
            foreach($email as $send){
                $out = $this->Listmail($email,$nome);
                $this->to .= ($out != true)?'':$out.',';
            }
        }else{
            $this->to = $this->Listmail($email,$nome);
        }
    }
    ######################################################################
    ## METODO ENVIA O E-MAIL DAS CONFIGURAÇÕES                          ##
    ## SandMails($form,$assunto,$template)                              ##
    ## $form = ARRAY => entradas de dados POST/GET                      ##
    ## $assunto = STRING => texto do assunto do E-mail                  ##
    ##      [OPCIONAIS]                                                 ##
    ## $template = patch para o template html do email                  ##
    ######################################################################
    function SandMails($form,$assunto,$template='pemail.tpl'){
        $form['baseimg']= $this->smarty->get_config_vars('http');
        $this->smarty->assign($form);
        $conteudo = $this->smarty->fetch($this->smarty->modulo_dir.$template);
        $headers  = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=".$this->smarty->get_config_vars('encoding')."\r\n";
        $headers .= "From: ".$this->remetente."\r\n";
        if($this->to != true)
            return $this->smarty->getFinishError('ERROR_004');
        if($this->remetente != true)
            return $this->smarty->getFinishError('ERROR_005');
        $sand = mail($this->to, $assunto, $conteudo, $headers);
        if($sand != true)
            return $this->smarty->getFinishError('ERROR_006');
        $smarty->clear_assign($form);
    }
    ######################################################################
    ## METODO Buscar endereço de membros cadastrados                    ##
    ## Listmail($email,$nome)                                           ##
    ## $email = STRING/ARRAY => contem os e-mails a serem enviados      ##
    ## $nome = STRING/ARRAY => contem os nome  a serem enviados         ##
    ######################################################################
    function Listmail($email,$nome=false){
        if(is_numeric($email)){
            $mysql = new MYSQL($this->smarty);
            $result = $mysql->SqlSelect("SELECT member_real,member_email FROM {$this->smarty->cfg[prefix]}members WHERE ID_MEMBER = '$email'");
            if(mysql_affected_rows()>0){
                $member = mysql_fetch_array($result,MYSQL_ASSOC);
                return $member['member_real'].'<'.$member['member_email'].'>';
            }
        }elseif($nome != false){
            if(Check::NotEmail($email) != true)
                return $nome.'<'.$email.'>';
        }else{
            if(Check::NotEmail($email) != true)
                return $email;
        }
    } 
}
?>