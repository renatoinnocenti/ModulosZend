<?
######################################################################
## ARQUIVO PARA INSERวรO DO AJAX DENTRO DO SISTEMA                  ##
## LIB.SAJAX.PHP VERS?O 1.0  - 27/10/2006                           ##
## CRIADO POR ATR-2 => RENATO INNOCENTI                             ##
## EMAIL: r.innocenti@uol.com.br                                    ##
######################################################################

######################################################################
## SETA OS valores POST de uma string JSON                          ##
## SetPostJson($idx,$json)                                          ##
## $idx => string = Nome do objeto do JSon                          ##
## $json => string JSON = Uma string no formato Json                ##
###################################################################### 
function SetPostJson($idx,$json){
    $jsonob = new Services_JSON();
    $output =  $jsonob->decode($json);
    $output2 = $output->$idx;
    foreach($output2 as $n){
        if($n == "" || $n == NULL)continue;
        $uri[$n[0]]=$n[1];
    }
    return $uri;
}
######################################################################
## SETA OS valores GET de uma querry String                         ##
## SetGet($vet)                                                     ##
## $vet => querry string = UMA QUERRY STRING                        ##
###################################################################### 
function SetGet($vet){
    $vet = explode('&',$vet);
    foreach($vet as $vetitem){
        $item = explode('=',$vetitem);
        $uri[$item[0]] =$item[1];   
    }
    return $uri;
}
######################################################################
## REDIRECIONA UM LINK JAVASCRIPT                                   ##
## loadPage($redir)                                                 ##
## $vet => querry string = UMA QUERRY STRING                        ##
######################################################################
function loadPage($redir){
    global $smarty;
    $_GET = SetGet($redir);
    $error = $smarty->SetPages();
    if($error == true)
        return $error;
    $error = $smarty->LoadModulos();
    if($error == true)
        return $error;
    $pagina = $smarty->LoadFunction();
    $out = ereg_replace("\'", "&#039;", $pagina);
    return ereg_replace("(\r\n|\n|\r|\t)", "", $out);       
}
######################################################################
## POSTA UM FORMULARIO PARA ELE MESMO RESPEITANDO A PRIORIDADE GET  ##
## SemiPost($Json,$id,$page);                                       ##
## $Json => string JSON = Uma string no formato Json                ##
## $id => string = Nome do objeto do JSon                           ##
## $page => querry string = UMA QUERRY STRING                       ##
######################################################################
function SemiPost($Json,$id,$page){
    global $smarty;
    $_POST = SetPostJson($id,$Json);
    $_GET = SetGet($page);
    $error = $smarty->SetPages();
    if($error == true)
        return $error;
    $error = $smarty->LoadModulos();
    if($error == true)
        return $error;
    $pagina = $smarty->LoadFunction();
    $out = ereg_replace("\'", "&#039;", $pagina);
    return ereg_replace("(\r\n|\n|\r|\t)", "", $out);  
}
######################################################################
## POSTA UM FORMULARIO FAZENDO AS CHECAGEM DE HIDDES                ##
## CheckForm($Json,$PREFIX);                                        ##
## $Json => string JSON = Uma string no formato Json                ##
## $id => string = Nome do objeto do JSon                           ##
######################################################################
function CheckForm($Json,$id){
    global $smarty;
    $_POST = SetPostJson($id,$Json);
    $error = $smarty->SetPages();
    if($error == true)
        return $error;
    $error = $smarty->LoadModulos();
    if($error == true)
        return $error;    
    $ck = new Check('POST');
	$ck->CheckForm();
    if(count($ck->error)> 0){
	 	return $errors = $smarty->getError($ck->error);
	 	}else{
            if(function_exists($smarty->actualpage["page_fnc"])){
                $ck->form = $ck->RegForm($ck->form);
                $out = ereg_replace("\'", "&#039;", $smarty->actualpage['page_fnc']($smarty,$ck->form));
                return ereg_replace("(\r\n|\n|\r|\t)", "", $out);
            }else{
                return $smarty->get_config_vars('ERROR_003').$smarty->actualpage["page_fnc"];
            }
        }
}
?>