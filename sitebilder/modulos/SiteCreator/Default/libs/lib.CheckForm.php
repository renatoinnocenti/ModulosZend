<?
####################################################################
## CLASSE PARA MANIPULAวรO DE FORMULARIOS E TEXTO                 ##
## CLASS.Form.PHP VERSรO 2.5  - 08/12/2006                        ##
## CRIADO POR RENATO INNOCENTI                                    ##
## EMAIL: r.innocenti@uol.com.br                                  ##
####################################################################
class Check
{
    var $form = array();
    var $error = array();
    var $limit;        
    ######################################################################
    ## LIMPA OS VALORES DE _GET E _POST                                 ##
    ## Check()                                                          ##
    ## Cria um objeto com os itens limpos                               ##
    ######################################################################
    function Check($limit=false){
        $this->limit = ($limit==false)?'All':$limit;
        if($limit == 'GET' || $limit == false){
            foreach($_GET as $key => $value){
                if(is_array($value)){
                    foreach($value as $nkey => $nvalue){
                        $this->form[$key][$nkey] = str_replace("\n", '', str_replace("\r", '', trim($nvalue)));
                    }
                }else{
                    $this->form[$key] = str_replace("\n", '', str_replace("\r", '', trim($value)));
                }
            }
        }
        if($limit == 'POST' || $limit == false){
            foreach($_POST as $key => $value){
                if(is_array($value)){
                    foreach($value as $nkey => $nvalue){
                        $this->form[$key][$nkey] = trim($nvalue);
                    }
                }else{
                    $this->form[$key] = trim($value);
                }
			}
		}
    }
    ######################################################################
    ## Checa as entradas de programa e retorna erro se houver           ##
    ## CheckForm()                                                      ##
    ######################################################################
    function CheckForm(){
        foreach($this->form as $chave => $valor){
            if(eregi("^([_]{2})([A-Z0-9]+)?([_]{2})$",$chave,$registros)){
                $item = explode(';',$valor);
            switch($registros[2]){
            ## O Conteudo verificado ้ Obrigatorio
                case "NOTNULL":
                    foreach($item as $ck_chave){
                        if(is_array($this->form[$ck_chave])){
                            foreach($this->form[$ck_chave] as $v2){
                                if($v2 == '' || $v2 == NULL || $v2 == FALSE)
								    $this->error['NOTNULL'][] = $v2;
							}                                                  
						}else{
                            if($this->form[$ck_chave] == '' || $this->form[$ck_chave] == NULL || $this->form[$ck_chave] == false)
                                $this->error['NOTNULL'][] = $ck_chave;
						}
					}
				    break;
			## O Conteudo verificado Nใo ้ um Numero valido
                case "NOTNUM":
                    foreach($item as $ck_chave){
                        if(is_array($this->form[$ck_chave])){
                            foreach($this->form[$ck_chave] as $v2){
                                if($v2 == null)continue;
                                if(!is_numeric($v2)){
                                    $this->error['NOTNUM'][] = $v2;
								}
							}                                                  
						}else{
                            if($this->form[$ck_chave] == null)continue;
                            if(!is_numeric($this->form[$ck_chave])){
                                $this->error['NOTNUM'][] = $ck_chave;
                            }
						}
					}
					break;
			## O Conteudo verificado Nใo ้ um texto de caracteres validos
                case "NOTTXT":
				    foreach($item as $ck_chave){
                        if(is_array($this->form[$ck_chave])){
                            foreach($this->form[$ck_chave] as $v2){
                                if($v2 == null)continue;
                                if($this->NotString($v2))
                                    $this->error['NOTTXT'][] = $v2;
							}                                                  
						}else{
                            if($this->form[$ck_chave] == null)continue;
							if($this->NotString($this->form[$ck_chave])){
                                $this->error['NOTTXT'][] = $ck_chave;
                            }
						}
					}
					break;
			## O Conteudo verificado Nใo ้ um Email valido
                case "NOTEMAIL":
				    foreach($item as $ck_chave){
					   if(is_array($this->form[$ck_chave])){
					       foreach($this->form[$ck_chave] as $v2){
                                if($v2 == null)continue;
								if($this->NotEmail($v2))
								    $this->error['NOTEMAIL'][] = $v2;
							}                                                  
						}else{
                            if($this->form[$ck_chave] == null)continue;
							if($this->NotEmail($this->form[$ck_chave]))
                                $this->error['NOTEMAIL'][] = $ck_chave;
						}
					}
				    break;
			## O Conteudo verificado Nใo ้ um URL valido
                case "NOTURL":
				    foreach($item as $ck_chave){
					   if(is_array($this->form[$ck_chave])){
					       foreach($this->form[$ck_chave] as $v2){
                                if($v2 == null)continue;
								if($this->NotUrl($v2))
								    $this->error['NOTURL'][] = $v2;
							}                                                  
						}else{
						    if($this->form[$ck_chave] == null)continue;
							if($this->NotUrl($this->form[$ck_chave]))
                                $this->error['NOTURL'][] = $ck_chave;
						}
					}
					break;
			## O Conteudo verificado Nใo ้ um Email valido
				case "NOTDATE":
				    foreach($item as $ck_chave){
					   if(is_array($this->form[$ck_chave])){
                            foreach($this->form[$ck_chave] as $v2){
							    if($v2 == null)continue;
								if($this->NotDate($v2))
								    $this->error['NOTDATE'][] = $v2;
							}                                                  
						}else{
                            if($this->form[$ck_chave] == null)continue;
							if($this->NotDate($this->form[$ck_chave]))
                                $this->error['NOTDATE'][] = $ck_chave;
						}
				    }
                    break;
                }
            }
        }
    }		
	####################################################################
    ## VERIFICA SE A STRING POSSUI CARACTERES VALIDOS                 ##
    ## NotString($string)                                             ##
    ## $string = STRING - STRING A SER VERIFICADA                     ##
    ## RETORNA VERDADEIRO SE POSSUIR CARACTERS INVALIDOS              ##
    ####################################################################
    function NotString($string){
        if(!preg_match("/^[\s0-9A-Za-z#%+,-\.:=?@^_??????????????????????????????]+$/", $string)){
            return true;
        }
    }
    ####################################################################
    ## VERIFICA SE A STRING ? UM EMAIL                                ##
    ## NotEmail($email)                                               ##
    ## $string = STRING - STRING A SER VERIFICADA                     ##
    ## RETORNA VERDADEIRO SE POSSUIR CARACTERS INVALIDOS              ##
    ####################################################################
    function NotEmail($email){
        if(!eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", $email)){
            return true;
        }
    }
    ####################################################################
    ## VERIFICA SE A STRING ? UM EMAIL                                ##
    ## NotUrl($www)                                                   ##
    ## $www = STRING - STRING de URL A SER VERIFICADA                 ##
    ## RETORNA VERDADEIRO SE POSSUIR CARACTERS INVALIDOS              ##
    ####################################################################
    function NotUrl($www){
        if(!ereg("^(http://)?([w{3}]|[a-z0-9]+)\.[a-z0-9_???????????????????????????]+(\.([a-z0-9]{2,4})(\.[a-z]{2,3})?(\/)?)$", $www)){
            return true;
        }
    }
    #####################################################################
    ## VERIFICA SE A STRING ? UM EMAIL                                 ##
    ## NotDate($date)                                                  ##
    ## $date = DATA - DATA NO FORMATO BR (dd/mm/aaaa)                  ##
    ## RETORNA VERDADEIRO SE POSSUIR CARACTERS INVALIDOS               ##
    #####################################################################
    function NotDate($datea){
        $d = explode('/',trim($datea));
        $z = strlen($d[2]);
        if($z != 4){
            return true;
        }elseif((checkdate($d[1], $d[0], $d[2])) == false){
            return true;
        }
    }
    ####################################################################
    ## VERIFICA O TAMANHO DE UMA STRING                               ##
    ## MaxString($string,$num)                                        ##
    ## $string = STRING - STRING A SER VERIFICADA                     ##
    ## $num = INT - NUMERO MAXIMO DE UMA STRING                       ##
    ## RETORNA VERDADEIRO SE FOR MAIOR QUE O LIMITE                   ##
    ####################################################################
    function MaxString($string, $num=10){
        if (strlen($string) > $num)
            return true;
    }
    ####################################################################
    ## VERIFICA SE O VALOR DA STRING ? NULA OU VAZIA                  ##
    ## FieldEmpty($string)                                            ##
    ## $string = STRING - STRING A SER VERIFICADA                     ##
    ## RETORNA VERDADEIRO EM CASO DE NULO                             ##
    ####################################################################
    function FieldEmpty($string){
        if($string == '' || $string == NULL){
            return true;
        }
    }
    ####################################################################
    ## FILTRA PADR?O DENTRO DE UM POST E ALTERA                       ##
    ## AS ENTRADAS S?O REFERENTES A QUALQUER $_POST DE FORMUL?RIO     ##
    ## FUN??O: RegForm($myarray)                                      ##
    ## $myarray = ARRAY - VALORES ENVIADOS DE UM FORMUL?RIO           ##
    ## RETORNA O POST FILTRADO                                        ##
    ####################################################################
    ## PREFIXOS:                                                      ##
    ## __ - ITEM DE CHECAGEM DE TABELA                                ##
    ####################################################################
    function RegForm($myarray){
        foreach($myarray as $chave => $valor){
            if($chave == 'page' || $chave=='Enviar'||$chave=='x'||$chave=='y'||$chave=='action'||$chave == "retorno") continue;
            if(eregi("^([_]{2})([A-Z0-9]+)?([_]{2})$",$chave,$registro))continue;
            $m[$chave] = $valor;
        }
        return $m;
    }
}
?>