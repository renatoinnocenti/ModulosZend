<?
class Traducao extends Arquivo {
    function loadFileContents(){
	   preg_match_all('/([0-9A-Za-z_]+)[ ]*? =[ ]*? \'(.+)(\'\r|\'\n|\'\r\n|\'$)/siU',file_get_contents($this->origem), $q);
	   return $q;
	}
   function loadFormTraducao($vec){
        $comb  = $vec[1];
 	    $comb2 = $vec[2];
	   for ($i=0;$i<count($comb);$i++) {
	       $j .= '<input type="text" readonly="readonly" id="label'.($i+1).'" size="15" value="'.$comb[$i].'" />
			 <input type="text" id="'.$comb[$i].'" name="'.$comb[$i].'" value="'.str_replace("'","",$comb2[$i]).'" size="60" /><br/>'; 
        }
        $j .= '<input type="text" id="novachave" size="15" value="" name="novachave" />
			 <input type="text" id="novovalor" name="novovalor" value="" size="60" /><br/>'; 
        return $j;
    }
    function geraLngFile($f){
		$form = Check::RegForm($f);
        foreach($form as $chave => $valor){
            if($chave == 'modulosload'){
                $modulo = $valor;
            }elseif($chave == 'lng_files'){
                $lng = $valor;
            }elseif($chave == 'configprincipal'){
                $princ = $valor;
            }elseif($chave == 'novachave'){
                $novachave = $valor;
            }elseif($chave == 'novovalor'){
                $novovalor = $valor;
            }else{
                $conteudo .= trim($chave." = '".$valor)."'\n";
            }
        }
        $conteudo .= trim($novachave." = '".$novovalor)."'\n";
        return $conteudo;
               
	}	
}
?>