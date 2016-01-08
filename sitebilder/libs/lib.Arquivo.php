<?php
######################################################################
## CLASSE PARA MANIPULAЧУO DE ARQUIVOS 
## CLASS.MYSQL.PHP VERSУO 2.0  - 08/12/2006 
## CRIADO POR ART-2 => RENATO INNOCENTI 
## EMAIL: r.innocenti@uol.com.br 
######################################################################
error_reporting(E_ALL & ~ E_NOTICE);
######################################################################
## CRIA INSTANCIA PARA MANIPULAЧУO DE ARQUIVOS 
## AS ENTRADAS SУO REFERENTES A CAMINHOS E ARQUIVOS
## Classe: Arquivo
######################################################################
define('THUMBNAIL_DIR_IMG','/thumbs');
class Arquivo {
var $origem;
var $error;
var $filelog = array();
var $MIMETYPES = array(
	'avi' => array('video/avi'),
	'exe' =>array('application/octet-stream'),
	'gif' => array('image/gif'),
	'htm' =>array('text/html'),
	'html' =>array('text/html'),
	'js' =>array('application/x-javascript'),
	'css' =>array('text/css'),
	'jpg' => array('image/jpeg','image/pjpeg'),
	'jepg' => array('image/pjpeg','image/jpeg'),
	'lng'   =>array('text/plain'),
	'mp3' => array('audio/mpeg'),
	'mid' => array('audio/mid','audio/midi'),
	'mpg' => array('video/mpeg'),
	'msi' =>array('application/x-msi'),
	'png'   => array('image/png','image/x-png'),
	'php'   =>array('application/x-httpd-php','application/x-httpd-php-source',),
	'rar'   =>array('application/x-rar-compressed','audio/x-pn-realaudio-plugin'),
	'sql'   =>array('text/x-sql'),
	'swf'   =>array('application/x-shockwave-flash'),
	'txt'   =>array('text/plain'),
	'xhtml' =>array('application/xhtml+xml','text/html'),
	'xml'   =>array('text/xml','application/xml'),
	'wav'   =>array('audio/wav'),
	'zip'   =>array('application/zip','application/x-zip-compressed'),
);
 var $SAFEMIMETYPES = array(
 'avi' => array('video/avi'),
 'gif' => array('image/gif'),
 'jpg' => array('image/jpeg','image/pjpeg'),
 'jepg' => array('image/pjpeg','image/jpeg'),
 'mp3' => array('audio/mpeg'),
 'mid' => array('audio/mid','audio/midi'),
 'mpg' => array('video/mpeg'),
 'msi' =>array('application/x-msi'),
 'png'   => array('image/png','image/x-png'),
 'rar'   =>array('application/x-rar-compressed','audio/x-pn-realaudio-plugin'),
 'swf'   =>array('application/x-shockwave-flash'),
 'txt'   =>array('text/plain'),
 'wav'   =>array('audio/wav'),
 'zip'   =>array('application/zip','application/x-zip-compressed'),
 );
	####################################################################
	## Abre um diretorio seguro
	## NewDir($dirname,$chmod);
	## $dirname = Nome do Diretorio a ser aberto 
	## $chmod = premiss?o da pasta a ser criada 
	####################################################################
	function NewDir($dirname,$chmod='0775'){
		if(!is_dir($dirname)){
			@mkdir($dirname, $chmod);
		}
    }
    #######################################################
    ## Seta o caminho de destino de um arquivo 
    ## SetDestino($destino) 
    ## $destino = path do destino do arquivo 
    ## ie. ./teste/teste 
    #######################################################
    function SetDestino($destino){
        if(is_dir($destino)){
			$this->destino = $this->ChangePath(realpath($destino));
			return true;
        }else{
			$this->destino = $this->Gera_dir($destino); 
			return false;
        }
    }
    #######################################################
    ## Seta o caminho de Origem de um arquivo
    ## SetOrigem($destino) 
    ## $origem = path do destino do arquivo
    ## ie. ./teste/teste
    #######################################################
    function SetOrigem($origem){
        $og = $this->ChangePath(realpath($origem));
        if($og == false){
            $paths = explode('/',$origem);
            foreach($paths as $pasta){
                if($pasta== '.' || $pasta== '..' || $pasta== ''){
                    $pastainit .=  $pasta."/";
                }else{
                    $cpasta[]=$pasta;
				}
            }
            if($pastainit == true){
                $og  = $this->ChangePath(realpath($pastainit)).'/'.implode('/',$cpasta);
            }else{
                $og= '/'.implode('/',$cpasta);
            }         
        }  		
		$this->origem = $og;
		if(!file_exists($this->origem))return false;
		else return $this->origem;
	}       
    ##################################################################
    ## Configura as propriedades do arquivo e verifica o arquivo 
    ## SetPropriedades($prop)
    ## $prop = array - com as propriedades do arquivo de upload
    ## retorna erro de upload 
    ##################################################################
    function SetPropriedades($prop = false){
        $this->prop['filename'] =(isset($prop['name']))?$prop['name']:basename($this->origem);
        if(isset($this->destino)){
            $finalfile = $this->destino.'/'.$this->prop['filename'];
        }else{
            $finalfile = $this->origem;
		}
        if(file_exists($finalfile)){
            $this->finalfile = $this->DubleFile($finalfile);
            if($this->finalfile != $finalfile){
                $this->prop['filename'] =basename($this->finalfile);
			}
        }else{
            $this->finalfile = $finalfile;
        }
        $ext = $this->ExtFile($this->prop['filename']);
        $this->prop['name'] = $ext[0];
        $this->prop['extension'] = $ext[1]; 
        $this->prop['size'] =(isset($prop['size']))?$prop['size']:filesize($this->origem);
        if(function_exists('getimagesize') && ($this->prop['extension'] == 'jpeg' || $this->prop['extension'] == 'jpg' || $this->prop['extension'] == 'png' || $this->prop['extension'] == 'gif')){
            $tmp = getimagesize($this->origrm);
            $this->prop['img_h'] =  $tmp[1];
            $this->prop['img_w'] =  $tmp[0];
        }
        if(isset($prop['error']) && $prop['error'] > 0){
            switch($prop['error']){
                case '1':
                    $this->error = 'ERROR_018';
                break;
                case '2':
                    $this->error = 'ERROR_019';
                break;
                case '3':
                    $this->error = 'ERROR_020';
                break;
                default:
                    $this->error = 'ERROR_021';
                break;
            }
        }     
    }
    ####################################################################
    ## Checa se hс espaчo na HD do servidor 
    ## FreeDisc($por) 
    ## $por = porcentagem de espaчo livre de seguranчa 
    ####################################################################
    function FreeDisc($por=5){
        $df = disk_free_space($_SERVER["DOCUMENT_ROOT"]);
        $td =  disk_total_space ( $_SERVER["DOCUMENT_ROOT"] );
        $pora = ($por*$td)/100;
        if($pora > $df) return true;
    }
    ####################################################################
    ## RETORNA A O valor em Bytes 
    ## ConvertBytes($fbytes,$tipo); 
    ## $fbytes = valor em bytes 
    ## $tipo = Formato da conversуo (KB,MB,GB)
    ####################################################################
    function ConvertBytes($fbytes,$tipo="KB"){
        settype($fbytes, int);
        if($tipo == 'KB'){
            $fsout = $fbytes / 1024;
            if($fsout > 900){$tipo = 'MB';}
        }
        if($tipo == 'MB'){
            $fsout = ($fbytes / 1024)/1024;
            if($fsout > 900){$tipo = 'GB';}
        }
        if($tipo == 'GB'){ $fsout = (($fbytes / 1024)/1024)/1024;}
        $fsout = round($fsout, 2);
        $fsout = $fsout . ' ' . $tipo;
        return $fsout;
	}        
    ####################################################################
    ## Remove todos os arquivos de uma pasta  de acordo com um tipo   ##
    ## DelFiles($str[*.*])                                            ##
    ## $str = Patch com o nome do arquivo (aceita variaveis do DOS)   ##
    ## Retorna o true quando deletar tudo                             ##
    ####################################################################
    function DelFiles($str,$all='/*'){
        foreach(glob($str.$all) as $fn){
            if(is_dir($fn)){
                $this->DelFiles($fn); 
                @rmdir($fn);
            }else{
              @unlink($fn);
            }
        }
		@rmdir($str);
		clearstatcache();
    }   
    ####################################################################
    ## COPIA TODOS OS ARQUIVOS E PASTAS PARA O DESTINO
    ## CopyAllFiles($origem,$destino) 
    ## $origem = patch completo caminho para o arquivo de origem
    ## $destino = patch completo caminho para o arquivo de destino
    ####################################################################
    function CopyAllFiles($origem,$destino,$all='/*'){
        $fn = @glob($origem.$all);
        if(is_array($fn)){
            foreach( $fn as $pasta_interna){
                if(is_dir($pasta_interna)){
                    $pasta_atual = str_replace($origem, '', $pasta_interna);
                    $pasta_destino = $destino.$pasta_atual;
                    $this->NewDir($pasta_destino,0775);
                    $this->CopyAllFiles($pasta_interna,$pasta_destino);
                }else{
                    $destinofull = $destino.str_replace($origem, '', $pasta_interna);
                    @copy($pasta_interna,$destinofull);
                }     
            }
        }        
    }
    ####################################################################
    ## GERA PASTAS APARTIR DE UM PATCH DE DESTINO 
    ## Gera_dir($destino,$close)
    ## $destino = patch completo caminho para o arquivo de destino
    ## $close = int Chmod para fechar a pasta
    ####################################################################
    function Gera_dir($destino,$close=0775){
        $paths = explode('/',$destino);
        foreach($paths as $pasta){
            if($pasta== '.' || $pasta== '..' || $pasta== ''){
                $pastainit .=  $pasta."/";
            }else{
                $cpasta[]=$pasta;
			}
        }
        if($pastainit == true){
            $initial = $this->ChangePath(realpath($pastainit)).'/'.implode('/',$cpasta);
        }else{
            $initial= '/'.implode('/',$cpasta);
        }
		$paths = explode('/',$initial);
        $dire = "";
        foreach($paths as $pasta){
            if($pasta== '')continue;
			$dire .= "/".$pasta;
			if(!is_dir($dire)){
				@chmod(dirname($dire),0777);
				$this->NewDir($dire,$close);
				@chmod(dirname($dire),$close);
			}
        }
		return $dire;
	}
    
    ##################################################################
    ## RETORNA OS NOMES DOS ARQUIVOS E PASTAS
    ## loadContent($type, $all)
    ## $type = file|dir|all tipo de itens a serem listados 
    ## $all  = condiчѕes de tipo a ser listado (DOS)
    ##################################################################	
	function loadContent($type='file',$all='/*'){
        switch($type){
            case 'file':
                foreach(glob($this->origem.$all) as $files){
                    if(!is_dir($files)){$out[] = $files;}
				}
                return $out;
                break;
            case 'dir':
                return glob($this->origem.$all, GLOB_ONLYDIR);
                break;
            case 'all':
                return glob($this->origem.$all);
                break;
            default:
                return glob($this->origem.$all, GLOB_ONLYDIR);                
        }
	}
	##################################################################
    ## CRIA UM ARQUIVO COM UM CONTEUDO DINAMICO
    ## CreateFile($conteudo) 
    ## $conteudo = string - conteudo a ser inserido no arquivo
    ##################################################################
	function CreateFile($conteudo){
        $file = (!$this->origem)?$this->destino:$this->origem;
		if(!$handle = fopen($file,"w+")){
			return false;
		}
        if(!fwrite($handle, trim($conteudo))){
            fclose($handle);
            return false;
        }
	fclose($handle);
	clearstatcache();
	return true;
    }
    #######################################################################################
    ## Extrai a extenчуo de um arquivo e o seu nome
    ## ExtFile($filename) 
    ## $filename = nome original do arquivo (ex: arquivo.zip) 
    ## Retorna um array contendo 0=> Nome 1=> Extenчуo
    #######################################################################################
    function ExtFile($filename){
        $ext = strrchr($filename,".");
        $nomen = strlen($filename);
        $extn = strlen($ext);
        $q = $nomen - $extn;
        $nome = substr ($filename,0,$q);
        $tipo = explode('.',$ext);
        $file = array($nome,$tipo[1]);
        return $file;
	}
    ##################################################################
    ## Cria um nome de arquivo novo se existente
    ## DubleFile($pachdestino)
    ## $pachdestino = nome original do arquivo (ex: ./arquivo.zip)
    ## Retorna o Patch com o novo nome do arquivo
    ##################################################################
    function DubleFile($pachdestino){
        $filename = $this->ExtFile(basename($pachdestino));
        if(ereg("^([a-zA-Z0-9_]+)?([_])([0-9]{1,5})$",$filename[0],$registro)){
            $filename[0] = $registro[1];
        }
        while(file_exists($pachdestino)){
            $x++;
            $ffinal =  $filename[0].'_'.$x.'.'.$filename[1];
            $pachdestino = dirname($pachdestino).'/'.$ffinal;
		}
        return $pachdestino;
    }
    ##################################################################
    ## Verifica e seta o tipo mime do arquivo 
    ## SetMimetype($filename,$check,$mime) 
    ## $filename = caminho do arquivo (ex: ./arquivo.zip)
    ## $check = true verifica se щ mime valido
    ## $mime = Se o arquivo ja apresenta um tipo mime р verificar
    ## Retorna erro de checagem
    ##################################################################
    function SetMimetype($filename,$check=false,$mime=false){
        $ext = $this->ExtFile(basename($filename));
        if($mime == false){
            if(function_exists('mime_content_type')){
                $mime = mime_content_type($filename);
            }      
        }
        if(in_array($mime,$this->MIMETYPES[$ext[1]])){
            $this->prop['mimetype'] = $mime;
        }elseif(isset($this->MIMETYPES[$ext[1]])){
            $this->prop['mimetype'] = $this->MIMETYPES[$ext[1]][0];
        }else{
            $this->prop['mimetype'] = null;
        }
        if(is_array($check)){
            if(!in_array($ext[1],$check)){
                $this->prop['mimetype'] = null;
                return true;
            }
        }
    }
    ##################################################################
    ## Move o arquivo para o destino selecionado
    ## UploadFile()
    ## Retorna erro de envio
    ##################################################################
    function UploadFile($remove=false){
        @chmod($this->destino,0777);
        if(!copy($this->origem,$this->finalfile)){
            return true;
        }
        if($remove != false){
            unlink($this->origem);
		}
        $this->origem = $this->finalfile;
        unset($this->finalfile);
        @chmod($this->destino,0775);
        clearstatcache();
    }
    ####################################################################
    ## Cria um thumbnail do objeto ativo Automaticamente
    ## criar_thumbnail($origem,$largura,$pre,$destino,$formato) 
    ## $largura = tamanho em pixels do thumbnail 
    ## $destino = patch onde serс criado o thumbnail 
    ## Retorna True se executado 
    ####################################################################
    function Thumbnail($largura,$pre='thb_',$resol=100,$destino=false){
        if($destino == false){
            $destino = $this->destino.THUMBNAIL_DIR_IMG.'/';
        }
        $thumbnail = $destino.$pre.$this->prop['filename'];
        $this->Gera_dir($destino);
        $thb['thb_name'] = $pre.$this->prop['filename'];        
        if($this->prop['extension'] == 'jpg' || $this->prop['extension'] == 'jpeg'){
            $im = imagecreatefromjpeg($this->origem);
        }elseif($this->prop['extension'] == 'png'){
            $im = imagecreatefrompng($this->origem);
        }elseif($this->prop['extension'] == 'gif'){
            $im = imagecreatefromgif($this->origem);
        }else{
            return true;
		}
        $this->prop['imgx'] = imagesx($im);
        $this->prop['imgy'] = imagesy($im);
        if($this->prop['imgx'] > $this->prop['imgy']){
            ## Imagem na Horizontal (Landscape)
            if($this->prop['imgx'] > $largura){
                $thb['thb_imgx'] = $largura;
                $thb['thb_imgy'] = ($this->prop['imgy'] * $largura)/$this->prop['imgx'];               
            }else{
                $thb['thb_imgx'] = $this->prop['imgx'];
                $thb['thb_imgy'] = $this->prop['imgy'];
            }
        }else{
            ## Imagem na Vertical (Retrato)
            if($this->prop['imgy'] > $largura){
                $thb['thb_imgy'] = $largura;
                $thb['thb_imgx'] = ($this->prop['imgx'] * $largura)/$this->prop['imgy'];    
            }else{
                $thb['thb_imgx'] = $this->prop['imgx'];
                $thb['thb_imgy'] = $this->prop['imgy'];
            } 
        }
        if(function_exists('imagecopyresampled')){
            if(function_exists('imageCreateTrueColor')){
                $ni = imageCreateTrueColor($thb['thb_imgx'],$thb['thb_imgy']);
            }else{
                $ni = imagecreate($thb['thb_imgx'],$thb['thb_imgy']);
			}
            if(!@imagecopyresampled($ni,$im,0,0,0,0,$thb['thb_imgx'],$thb['thb_imgy'],$this->prop['imgx'],$this->prop['imgy'])){
                imagecopyresized($ni,$im,0,0,0,0,$thb['thb_imgx'],$thb['thb_imgy'],$this->prop['imgx'],$this->prop['imgy']);
			}
        }else{
            $ni = imagecreate($thb['thb_imgx'],$thb['thb_imgy']);
            imagecopyresized($ni,$im,0,0,0,0,$thb['thb_imgx'],$thb['thb_imgy'],$this->prop['imgx'],$this->prop['imgy']);
        }
        if($this->prop['extension'] == 'jpg' || $this->prop['extension'] == 'jpeg'){
            imagejpeg($ni,$thumbnail,$resol);
		}elseif($this->prop['extension']=='png'){
            imagepng($ni,$thumbnail);
        }elseif($this->prop['extension']=='gif'){
            imagegif($ni,$thumbnail);
		}
        @chmod($thumbnail,0775);
        clearstatcache();
        return $thb;
    }
	##################################################################
    ## Arruma as Barras para windows 
    ## ChangePath($p_path, $p_remove) 
    ## $p_path = path com o endere?o a ser mudado 
    ## $p_remove  = remover a Letra do windows
    ##################################################################
    public function ChangePath($p_path, $p_remove=true){
        if (stristr(php_uname(), 'windows')) {
            if (($p_remove) && (($v_position = strpos($p_path, ':')) != false)) {
                $p_path = substr($p_path, $v_position+1);
            }
            if ((strpos($p_path, '\\') > 0) || (substr($p_path, 0,1) == '\\')) {
                $p_path = strtr($p_path, '\\', '/');
            }
        }
        return $p_path;
    }
    ####################################################################
    ## Cria um registro com as aчѕes efetuadas pelo zip/unzip
    ## FileLogs($unicname) 
    ## $unicname = Um nome unico para diferenciar cada execuчуo 
    ####################################################################
    function FileLogs($unicname){
        if($this->zipinfile != null){
			## Log com zipfile
			$this->filelog[$unicname] = array_merge($this->arquivo,$this->zipinfile);
        }else{
            $this->filelog[$unicname] = $this->prop;
        }
        unset($this->prop);
        unset($this->thumbnail);
        unset($this->origem);
        unset($this->destino);
        unset($this->zipinfile);
        clearstatcache();         
    }
	
	####################################################################
    ## Constroi um objeto de artibutos para 
    ## Str2Attr($string,$ignore)
    ## $string = Texto retirado da tag que serс transformada
	## $ignore = Atributos que devem ser ignorados no processo
    ####################################################################
    function Str2Attr($string,$ignore){
		if(preg_match_all("/([a-zA-Z0-9_]+)=\"([a-zA-Z0-9_]+)\"/siU", $attrs, $matches, PREG_SET_ORDER)){
			foreach($matches as $chave){
				if(is_array($ignore)){
					if(in_array($chave[1],$ignore)){
						continue;
					}
				}else{
					if($ignore == $chave[1]){
						continue;
					}
				}
				$attr[$chave[1]]= $chave[2];
			}        
		}
		return $attr;
	}
	####################################################################
    ## Constroi um objeto para inclusуo de templates dinamicos
    ## ATRTplConvert($tipo,$attrs)
    ## $tipo = tipo de execuчуo de template (insert, include, vars, fckobj)
	## $attrs = Atributos que serуo tratados ou acrecentado ao template (uma string)
	####################################################################
    function ATRTplConvert($tipo,$attrs){
        $attr = $this->Str2Attr($attrs,'class');
        switch($tipo){
            case 'insert':
                foreach($attr as $chave => $valor){
                    if(trim($chave) =="" || trim($valor) =="")continue; 
                    $atributos .= " ".$chave.'="'.$valor.'"';
                }
                return "{insert".$atributos."}";
            break;
            case 'include':
                foreach($attr as $chave => $valor){
                    if(trim($chave) =="" || trim($valor) =="")continue; 
                    $atributos .= " ".$chave.'="'.$valor.'"';
                }
                return "{include".$atributos."}";
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
}
?>