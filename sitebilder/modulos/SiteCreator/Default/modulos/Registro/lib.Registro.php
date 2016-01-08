<?
####################################################################
## CLASSE PARA MANIPULAÇÃO DE REGISTROS                           ##
## CLASS.REGISTRO.PHP VERSÃO 1.0  - 02/06/2005                    ##
## CRIADO POR ART-2 => RENATO INNOCENTI                           ##
## EMAIL: r.innocenti@uol.com.br                                  ##
####################################################################
error_reporting(E_ALL & ~ E_NOTICE);
####################################################################
## IMPORTA AS VARIAVEIS NECESSÁRIAS                               ##
## FUNÇÃO: bilderXML($cfg,$page)                                  ##
## $cfg = array - contem as varias configurações do site          ##
## $page = string - contem a informação da pagina ativa           ##
####################################################################

class Registro{
    var $form = array();
    function NewRegistro(&$smarty,&$form){
        $this->form = $form;
        if($e = $this->RegAgree($smarty))
            $error[] = $e;
        if($e = $this->RegAntiFlood($smarty))
            $error[] = $e;
        if($e = $this->RegUser($smarty))
            $error[] = $e;
        if($e = $this->RegPass($smarty))
            $error[] = $e;
        if($e = $this->RegEmail($smarty))
            $error[] = $e;
        if($e = $this->RegReservedName($smarty))
            $error[] = $e;
        $this->form['member_registro'] = time();
        $this->form['member_ip'] = $_SERVER['REMOTE_ADDR'];
        $mysql = new MYSQL($smarty);
        $request = $mysql->SqlSelect("SELECT ID_MEMBER FROM {$smarty->cfg[prefix]}members",__FILE__,__LINE__);
        if(mysql_affected_rows() < 1){
            $this->form['member_group'] = 'Administrador';
            $this->form['member_nivel'] = 5;
            }else{
                $this->form['member_group'] = '';
                $this->form['member_nivel'] = 1;
                }
        if(count($error)> 0 ){
            foreach($error as $saida)
                $out .= $saida;
            return $out;
            }else{
                $pass = $this->form['member_password'];
                $this->form['member_password'] = $this->md5_hmac($this->form['member_password'], strtolower($this->form['member_name']));
                $tabela = $smarty->cfg['prefix'].'members';
                $sql = $mysql->SqlInsert($tabela,$this->form);
                $request = $mysql->SqlSelect($sql,__FILE__,__LINE__);
                return $smarty->getFinish("registro_sucesso_01");
                }
    }
    ####################################################################
    ## VERIFICA SE O USUÁRIO ACIETOU AS REGRAS DE REGISTRO            ##
    ## FUNÇÃO: RegAgree()                                             ##
    ####################################################################
    function RegAgree(&$smarty){
        if(($smarty->cfg['regagree'] == 1) && (!isset($this->form['regagree']))){
            return $smarty->getFinishError('registro_erro_01'); 
        }
        unset($this->form['regagree']);
        return NULL;
    }
    ####################################################################
    ## PROPRIEDADE DE VERIFICAÇÃO ANTIFLOOD                           ##
    ## FUNÇÃO: RegAntiFlood ()                                        ##
    ####################################################################
    function RegAntiFlood(&$smarty){
        if($smarty->cfg['antiflood']== 1){
            }
    }
    ####################################################################
    ## VERIFICA AS PROPRIEDADES DE REGISTRO DE NOME DE USUARIO        ##
    ## FUNÇÃO: RegUser()                                              ##
    ####################################################################
    function RegUser(&$smarty){
        if(Check::MaxString($this->form['member_name'],25) == true)
            return $smarty->getFinishError('registro_erro_02',array("",' - (25)'));
        if(Check::FieldEmpty($this->form['member_name']) == true)
            return $smarty->getFinishError('registro_erro_03');
        if(Check::NotString($this->form['member_name']) == true)
            return $smarty->getFinishError('registro_erro_04',array(htmlspecialchars($this->form['member_name']).' - ',""));        
        if($this->form['member_name'] == '_' || $this->form['member_name'] == '|')
            return $smarty->getFinishError('registro_erro_04',array(htmlspecialchars($this->form['member_name']).' - ',''));
        if(strstr($this->form['member_name'], 'Guest'))
            return $smarty->getFinishError('registro_erro_05',array(""," - ".htmlspecialchars($this->form['member_name'])));
        $mysql = new MYSQL($smarty);
        $result = $mysql->SqlSelect("SELECT value FROM {$smarty->cfg[prefix]}banned WHERE (type='user' && value='{$this->form[member_name]}')",__FILE__,__LINE__);        
        if(mysql_affected_rows() > 0)
            return $smarty->getFinishError('registro_erro_06',array(""," - ".htmlspecialchars($this->form['member_name'])));
        $result = $mysql->SqlSelect("SELECT ID_MEMBER FROM {$smarty->cfg[prefix]}members WHERE member_name='{$this->form[member_name]}'",__FILE__,__LINE__);
        if(mysql_affected_rows() > 0)
            return $smarty->getFinishError('registro_erro_07',array(""," - ".htmlspecialchars($this->form['member_name'])));
        }
    ####################################################################
    ## VERIFICA AS PROPRIEDADES DE REGISTRO DE SENHA DE USUARIO       ##
    ## FUNÇÃO: RegPass()                                              ##
    ####################################################################
    function RegPass(&$smarty){
        if($this->form['member_password'] !=  $this->form['member_password2'])
            return $smarty->getFinishError('registro_erro_08',array(""," - ".htmlspecialchars($this->form['member_name'])));
        if($this->form['member_password'] == '')
            return $smarty->getFinishError('registro_erro_09',array(""," - ".htmlspecialchars($this->form['member_name'])));
            
        unset($this->form['member_password2']);
        }
    ####################################################################
    ## VERIFICA AS PROPRIEDADES DE REGISTRO DE E-MAIL DE USUARIO      ##
    ## FUNÇÃO: RegEmail()                                              ##
    ####################################################################
    function RegEmail(&$smarty){
        if($this->form['member_email'] == '')
            return $smarty->getFinishError('registro_erro_10',array(""," - ".htmlspecialchars($this->form['member_email'])));            
        if(Check::NotEmail($this->form['member_email']) == true)
            return $smarty->getFinishError('registro_erro_11',array(""," - ".htmlspecialchars($this->form['member_email'])));            
        $mysql = new MYSQL($smarty);
        $result = $mysql->SqlSelect("SELECT value FROM {$smarty->cfg[prefix]}banned WHERE (type='email' && value='{$this->form[member_name]}')",__FILE__,__LINE__);
        if(mysql_affected_rows() > 0)
            return $smarty->getFinishError('registro_erro_13',array(""," - ".htmlspecialchars($this->form['member_email'])));
        $request = $mysql->SqlSelect("SELECT ID_MEMBER FROM {$smarty->cfg[prefix]}members WHERE member_email='{$this->form[member_name]}' AND ID_MEMBER <> '$smarty->perfil[ID_MEMBER]'",__FILE__,__LINE__);
        if(mysql_affected_rows() > 0)
            return $smarty->getFinishError('registro_erro_12',array(""," - ".htmlspecialchars($this->form['member_email'])));
        }
    ####################################################################
    ## PROPRIEDADE DE VERIFICAÇÃO DE NOME RESERVADOS                  ##
    ## FUNÇÃO: RegReservedName()                                      ##
    ####################################################################
    function RegReservedName(&$smarty){
        $mysql = new MYSQL($smarty);
        $request = $mysql->SqlSelect("SELECT * FROM {$smarty->cfg[prefix]}reserved_names",__FILE__,__LINE__);
        if(mysql_affected_rows() > 0){
            $reserve = array();
            while ($row = mysql_fetch_array($request)){
                if($row['setting'] == 'word')
                    $reserve[] = trim($row['value']);
                else
                    ${$row['setting']} = trim($row['value']);
                }
            $namecheck = ($matchcase == '1' ? $this->form['member_name'] : strtolower($this->form['member_name']));
            foreach ($reserve as $reserved){
                $reservecheck = ($matchcase == '1') ? $reserved : strtolower($reserved);
                if($matchuser == '1'){
                    if($matchword == '1'){
                        if($namecheck == $reservecheck)
                            return $smarty->getFinishError('registro_erro_05',array(""," - ".$reserved));
                    }else{
                        if(strstr($namecheck,$reservecheck))
                            return $smarty->getFinishError('registro_erro_05',array(""," - ".$reserved));
                    }
                }
            }
        }
    }
    ####################################################################
    ## PROPRIEDADE DE ENVIO DE EMAIL DE BOAS VINDAS                   ##
    ## FUNÇÃO: RegEmailWell()                                         ##
    ####################################################################
    function RegEmailWell(&$smarty,&$form){
        if($smarty->cfg['emailwell']== 1){
            $smail = new MyMail($smarty);
            $smail->SetRemetentes($smarty->cfg['email'],$smarty->cfg['site_name']);
            $smail->SetTo($form['member_email'],$form['member_real'].$smarty->get_config_vars('tratamento'));
            return $smail->SandMails($form,$smarty->get_config_vars('registro_sucesso_02').$form['member_real'],'email_wellcome.tpl');
            }
    }
    ####################################################################
    ## CODIFICA A SENHA DO FORUM                                      ##
    ## md5_hmac()                                                     ##
    ####################################################################
        function md5_hmac($data, $key){
                if(strlen($key) > 64)
                        $key = pack('H*', md5($key));
                $key  = str_pad($key, 64, chr(0x00));
                $k_ipad = $key ^ str_repeat(chr(0x36), 64);
                $k_opad = $key ^ str_repeat(chr(0x5c), 64);
                return md5($k_opad . pack('H*', md5($k_ipad . $data)));
                }
        
}
?>