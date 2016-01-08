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
class Login
{
    function CheckLogin($form,&$smarty){
     $mysql = new MYSQL($smarty);
     $request = $mysql->SqlSelect("SELECT * FROM {$smarty->cfg[prefix]}members WHERE member_name='$form[login_user]'",__FILE__,__LINE__);
            $attempt = str_repeat('*', strlen($pass));
            if (mysql_num_rows($request) < 1){
                return $smarty->getFinishError('error_02', array("",' - ' . htmlspecialchars($form[login_user])));
            }else{
                $perfil = mysql_fetch_array($request,MYSQL_ASSOC);
                $md5_passwrd = $this->md5_hmac($form['login_password'], strtolower($form['login_user']));
                if ($perfil['member_password'] != $md5_passwrd){
                    return $smarty->getFinishError('error_04');    
                }
            }
        
    }
    function SetLogin(&$smarty, $form=false, $pageredir="index"){
            $ck = new Check('POST');            
            $mysql = new MYSQL($smarty);
            if($ck->form['cookieleng'] == $smarty->get_config_vars('Login00')){
		 	$cookielength      = 0;
            $cookieneverexp    = 'on';
            }else{
            	$cookielength = $ck->form['cookieleng'];
                }
            $user = $ck->form['login_user'];
            $pass = $ck->form['login_password'];
            $request = $mysql->SqlSelect("SELECT * FROM {$smarty->cfg[prefix]}members WHERE member_name='$user'",__FILE__,__LINE__);
            $perfil = mysql_fetch_array($request,MYSQL_ASSOC);
            $md5_passwrd = $this->md5_hmac($pass, strtolower($user));
                    switch($cookielength){
                        case "1":
                                 $cookielength = strtotime("+30 minutes");
                                 break;
                        case "2":
                                 $cookielength = strtotime("+1 hour");
                                 break;
                        case "3":
                                 $cookielength = strtotime("+1 day");
                                 break;
                        case "4":
                                 $cookielength = strtotime("+1 month");
                                 break;
                        case "5":
                                 $cookielength = strtotime("+1 year");
                                 break;
                        default:
                                 $cookielength = strtotime("+1 year");
                                 }
                    $password = $this->md5_hmac($md5_passwrd, 'ys');
                    $cookie_url = explode($smarty->cfg["separate"], $this->url_parts($smarty->get_config_vars('http'),$smarty->cfg["separate"]));
                    $cookie = serialize(array($perfil['ID_MEMBER'], $password));
                    $ctime =  $cookielength;
                    //print $smarty->cfg['cookie'].' , ' .$cookie.' , '. $ctime.' , '. $cookie_url[1].' , '. $cookie_url[0];
                    setcookie($smarty->cfg['cookie'], $cookie, $ctime, $cookie_url[1], $cookie_url[0]);
                    $lastLog = time();
                    $memIP = $_SERVER[REMOTE_ADDR];
                    $valores = array('member_lastlogin' =>$lastLog,'member_ip' =>$memIP);
                    $sql = $mysql->SqlUpdate($smarty->cfg["prefix"].'members',$valores,"member_name='$user'");
                    $result = $mysql->SqlSelect($sql);
                    $identify = INET_ATON();
                    $sql = $mysql->SqlDelete($smarty->cfg["prefix"].'log_online',"identity='$identify'");
                    $result = $mysql->SqlSelect($sql);
                    $redir = $smarty->get_config_vars('http').$smarty->cfg['index'].'?page='.$pageredir;
                    header("location:$redir");              
		}
	####################################################################
    ## EFETUA O LOGOUT DO SITE                                        ##
    ## Logout()                                                       ##
    ####################################################################
    function SetLogout(&$smarty,$redir='index'){
        $mysql = new MYSQL($smarty);
        $sql = $mysql->SqlDelete($smarty->cfg["prefix"].'log_online',"identity='".$smarty->perfil["ID_MEMBER"]."'");
        $result = mysql_query($sql);
        $cookie_url = explode($smarty->cfg["separate"], $this->url_parts($smarty->get_config_vars('http'),$smarty->cfg["separate"]));
        setcookie($smarty->cfg["cookie"], '', time() - 3600, $cookie_url[1], $cookie_url[0]);
        $smarty->perfil = $smarty->LoadPerfilGuest();
        $redir = $smarty->get_config_vars('http').$smarty->cfg['index'].'?page='.$redir;
        header("location:$redir");
          }
	####################################################################
    ## CODIFICA O ENDEREÇO HTTP                                       ##
    ## url_parts()                                                    ##
    ####################################################################
        function url_parts($uri,$sep){
                $cookie_dom = ''; $cookie_dir = '/';
                $forum = str_replace("http://", "", $uri);
                $forum_atual= "htp|://".$forum;
                $url .= $forum_atual . "/";
                $pos = strpos($url, '//');
                if ($pos > 0 && strncmp(strtolower($url), 'http:', $pos) == 0){
                        $urlpos = strpos($url, '/', $pos + 2);
                        if ($urlpos > 0){
                                $cookie_dom = substr($url, $pos + 2, $urlpos - $pos - 2);
                                $cookie_dir = substr($url, $urlpos);
                                }
                        }
                        $separate = $sep;
                return "$cookie_dom$separate$cookie_dir";
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