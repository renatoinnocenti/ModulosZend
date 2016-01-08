<?
  function loginForm(&$smarty){
    if(isset($_COOKIE[$smarty->cfg['cookie']])){
     return $smarty->fetch($smarty->modulo_dir."wellcome.tpl");
     }else{
    return $smarty->fetch($smarty->modulo_dir."login.tpl");
  	}
}
function login_check(&$smarty,$form=false){
    $login = new Login();
    $errors = $login->CheckLogin($form,$smarty);
    if($errors == true){
        return $errors;
    }else{
        $login->SetLogin($smarty);
    }
}
function login_off(&$smarty){
    $login = new Login();
    $login->SetLogout($smarty);
	}
?>