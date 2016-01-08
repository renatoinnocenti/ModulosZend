<?php

class SelectGera {

function geraSelectOption() {
    $x = glob("../*", GLOB_ONLYDIR);
    $a = '<select onchange="return_file(this.value);">';
    foreach($x as $i => $value) {
        $a .= '<option >'.str_replace("../", "", $value).'</option>';
    }
    $a .= '</select>';
    return $a;
}

function allFiles($dir) {
   $y = "../".$dir."/pt-br*.*"; 
    $x = glob($y);

if (is_array($x)) {
$a = '<select onchange="return_file_contents(this.value, '."'$dir'".');">
	 <option selected="selected">Escolha</option>';
    foreach($x as $i => $value) {
        $a .= '<option >'.str_replace("../$dir/", "", $value).'</option>';
    }
    $a .= '</select>';
    } else {
	 $a = '<input type="button" value="Gerar arquivo pt-br" onclick="criaPtbr('."'$dir'".');">';
	 }

	 return $a;

}


function allContents($file, $dir) {
 
 	$q = explode (';',file_get_contents("../$dir/$file"));
 	$j = '<form>';
 	$i = 1;
 	foreach ($q as $i=>$value) {
		$j .= '<input type="text" id="item'.$i.'" name="itens" value="'.$value.'" size="100"><br />'; 
		$i++;
	} 
	$j .= '<input type="button" value="Gerar Inglês" onclick="geraLng('."'en'".','."'$dir'".');">
	<input type="button" value="Gerar Japones" onclick="geraLng('."'jp'".','."'$dir'".');"
	</form>';
	
   

	 return $j;

}

function writeLng($lng,$dir, $content) {
 
$w="";
 	
	 if($lng=='en') {
		 	$file = "./xml/en.$dir.conf";
 			if (!file_exists($file)) $arq = fopen($file,"x+");
 			else {
				return $file.' - arquivo existe';
				break;	
			}
	} 
	 if($lng=='jp') {
		$file = "./xml/jp.$dir.conf";
 			if (!file_exists($file)) $arq = fopen($file,"x+");
 				else {
				return $file.' - arquivo existe';
				break;	
			}
	}
	
 	$q = explode ('#',$content); 
	 $z = array_pop($q);	
 	
 	foreach ($q as $i=>$value) {
		fwrite($arq, $value.';'."\n");
	} 
	 

	 return $lng;

}


}
?>
