<?
function NewLng(&$smarty,$form=false){
    $language = ($form['lng_files']=="")?'pt-br':$form['lng_files'];
	$modulo = ($form['modulosload']!="")?'./modulos/'.$form['modulosload'].'/'.$language.'.'.$form['modulosload'].'.conf':'./configs/'.$language.'.conf';
    $modulo = ($form['file']=='principal')?'./configs/'.$language.'.conf':$modulo;
    $smarty->assign('country_lng',$language);
    switch($form['action']){
        case "loadform":
            $arq = new Traducao();
            $arq->SetOrigem($modulo);
            if(!is_file($arq->origem)){
	           $modulo = ($form['modulosload']!="")?'./modulos/'.$form['modulosload'].'/pt-br.'.$form['modulosload'].'.conf':'./configs/pt-br.conf';
                $arq = new Traducao();
                $arq->SetOrigem($modulo);
                if(!is_file($arq->origem)){
                    return $smarty->getFinishError('traducao_erro_07');
                }
            }               
            break;
        default:
            $arq = new Traducao();
            $arq->SetOrigem($modulo);
            if(is_file($arq->origem)==""){
                $arq->origem = $modulo; 
            }
            $conteudo = $arq->geraLngFile($form);
            if($arq->CreateFile($conteudo) == false){
                return $smarty->getFinishError('traducao_erro_08');
            }
    }
    $x = $arq->loadFileContents();
    if($x != true)
	   return $smarty->getFinishError('traducao_erro_05');
	$j = $arq->loadFormTraducao($x);
	        if($form['file']=='principal'){
                return $j.$smarty->fetch($smarty->modulo_dir."traducao_p2.tpl").$smarty->fetch($smarty->modulo_dir."traducao_p3.tpl");
            }else{
            return $j.$smarty->fetch($smarty->modulo_dir."traducao_p2.tpl");    
            }
} 
  	
  	

?>