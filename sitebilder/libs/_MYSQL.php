<?php
####################################################################
## funções PARA MANIPULAÇÃO DE BANCO DE DADOS
## CLASS.MYSQL.PHP VERSÃO 2.0  - 18/01/2010 
## RENATO INNOCENTI                           					  ##
## EMAIL: renato.innocenti@gmail.com                              ##
####################################################################
error_reporting(E_ALL & ~ E_NOTICE);
####################################################################
## CRIA INSTANCIA PARA CONSULTAS MYSQL  
## AS ENTRADAS SÃO REFERENTES AO MYSQL 
## Classe: MYSQL 
####################################################################
define('DB_QUERY_REGEXP', '/(%d|%s|%%|%f|%b|%n)/');
class MYSQL{
public $conexao;
public $sql;
private $caption=array();
/*SELECT [STRAIGHT_JOIN]
 [SQL_SMALL_RESULT] [SQL_BIG_RESULT] [SQL_BUFFER_RESULT]
 [SQL_CACHE | SQL_NO_CACHE] [SQL_CALC_FOUND_ROWS] [HIGH_PRIORITY]
 [DISTINCT | DISTINCTROW | ALL]
 expressão_select,...
 [INTO {OUTFILE | DUMPFILE} 'nome_arquivo' opções_exportação]
 [FROM tabelas_ref
 [WHERE definição_where]
 [GROUP BY {inteiro_sem_sinal | nome_col | formula} [ASC | DESC], ...
 [WITH ROLLUP]]
 [HAVING where_definition]
 [ORDER BY {inteiro_sem_sinal | nome_coluna | formula} [ASC | DESC], ...]
 [LIMIT [offset,] row_count | row_count OFFSET offset]
 [PROCEDURE nome_procedimento(lista_argumentos)]
 [FOR UPDATE | LOCK IN SHARE MODE]]
 */
	######################################################################
	## INICIA UMA CONECÇÃO MYSQL 
	## MYSQL($smarty); 
	## $smarty = OBJETO => OBJETO DE CONFIGURAÇÃO 
	######################################################################
	function MYSQL(&$smarty){
		@mysql_close();
        $this->conexao = mysql_connect($smarty->cfg[db_server],$smarty->cfg[db_user],$smarty->cfg[db_passwd]);
        $off = mysql_select_db($smarty->cfg[db_name],$this->conexao);
        $this->db_name=$smarty->cfg[db_name];
		$GLOBALS['linkysql'] = $this->conexao;
	}
		
	######################################################################
	## RETORNA O RANDLE DA CONECÇÃO ATUAL 
	## Connection(); 
    ######################################################################
    function Connection(){
        return $this->conexao;
	}
    ####################################################################
    ## METODO FAZ A CONFIGURAÇÃO DA LISTAGEM, NÃO EXIBE NADA  
    ## CreateSelectSQL($params, &$smarty) 
    ## $params = ARRAY => RECEBE TODOS OS PARAMETROS DA TAG  
    ## $smarty = OBJETO => RECEBE O OBJETO DO TEMPLATE  
    ##      @SetDb = string - Nome da tabela a ser acessada 
    ##      @SetTable = string/array - Nome das tabelas a serem 
    ##          selecionadas.
	##		[OPCIONAIS]	
    ##      @SetCombine = string/array - Nome das tabelas a serem 
    ##          adicinadas na seleção    
    ##      @SetDistinct = string - tabela sql que será acessada 
    ##      @SetCombineDirect = LEFT|RIGHT direção da união 
    ##      @SetCombineCol = String - Tabela a ser comparada  
    ##      @SetListAdm = string - Tabela com o valor de ID do usuario
    ##      @SetListAdmNivel = String - Nivel de permição da listagem 
    ##      @SetListAprovar = int/bolan = tabela com valores de 
    ##          linha liberada para listagem 
    ##      @SetListExtra = SQL - comando SQL para o WHERE 
    ##      @SetSearch = string - Palavras a serem buscadas 
    ##      @SetOrder = DESC|ASC - Direção de listagem das buscas
    ##      @SetOrdenar = string/array - Tabela a ser ordenada 
    ##      @SetStart = int - Numero do registro inicial
    ##      @SetMax = int - Numero de resultados a serem buscados 
    ####################################################################  
    function CreateSelectSQL($params,&$smarty){
		extract($params);
        if(empty($SetDb)){
            return $smarty->getFinishError("ERROR_008",array("",'(SetDb)'));
		}elseif(empty($SetTable)){
			return $smarty->getFinishError("ERROR_008",array("",'(SetTable)'));
		}elseif(!empty($SetCombine) && empty($SetCombineCol)){
			return $smarty->getFinishError("ERROR_008",array("",'(SetCombineCol)'));
		}else{
			$this->sql = "SELECT ";
			if(!empty($SetDistinct)){
				$this->sql .= "DISTINCT ";
				$this->PREFIX_TABLE = 1; 
			}
			if(is_array($SetTable)){
				$this->sql .= implode(", ",$SetTable);
			}else{
				$this->sql .= $SetTable; 
			}
			$this->caption = $SetTable;
            $this->sql .= " FROM ";
            if(is_array($SetDb)){
                $this->sql .= implode(", ",$SetDb);
            }else{
                $this->sql .= $SetDb;
            }
            if(!empty($SetCombine)){
				if(!empty($SetCombineDirect)){
					$SetCombineDirect = " LEFT";
				}
                if(is_array($SetCombine) && is_array($SetCombineCol)){
                    if(count($SetCombine) != count($SetCombineCol)){
                        return $smarty->getFinishError("ERROR_009",array('(SetCombine,SetCombineCol)','('.count($SetCombine).','.count($SetCombineCol).')'));
					}
                    for($x=0; $x< count($SetCombine);$x++){
                        $this->sql .= $SetCombineDirect.' JOIN '.$SetCombine[$x].' USING ('.$SetCombineCol[$x].') ';
                    }
                }else{
                    $this->sql .= $SetCombineDirect.' JOIN '.$SetCombine.' USING ('.$SetCombineCol.')';
                }               
            }
            #### Clausulas WHERE
            $where = " WHERE ";
			if((!empty($SetListAdm) && !empty($SetListAdmNivel)) && 
                ($SetListAdmNivel < $smarty->getVars('member_nivel'))
				){
                $this->sql .= $where. $SetListAdm . '<= \'' . $smarty->getVars('ID_MEMBER').'\'';
                $where = " AND ";
            }
            if(!empty($SetListAprovar)){
                $this->sql .= $where. $SetListAprovar . '= \'1\'';
                $where = " AND ";
            }
            if(!empty($SetListExtra)){
                $this->sql .= $where. $SetListExtra;
                $where = " AND ";
            }
            if(!empty($SetSearch)){
                $sql['busca']= htmlentities($SetSearch,ENT_QUOTES);
                $buscas = explode(' ', $sql['busca']);
                $sq =($where != " WHERE ")?" AND (":" WHERE ";
                if(is_array($SetDb)){
                    foreach($SetDb as $valor){
                        foreach($this->ListaCampos($smarty,$valor) as $v){
                            $sq .= " $v LIKE ";
                            foreach($buscas as $bus){
                                $sq .= "'%".$bus."%' OR ";
                            }
                        }
                    }
                }else{
                    foreach($this->ListaCampos($smarty,$SetDb) as $v){
                        $sq .= " $v LIKE ";
                        foreach($buscas as $bus){
                            $sq .= "'%".$bus."%' OR ";
                        }
                    }
                    
                }
                
                $sq = substr($sq, 0, -4);
                $sq .=($where != " WHERE ")?")":"";
                $this->sql .= $sq;
            }
            if(empty($SetOrder)){
                $SetOrder = "DESC";
			}
            if(!empty($SetOrdenar) && is_array($SetOrdenar)){
				$this->sql .= " ORDER BY ";
				foreach($SetOrdenar as $chave => $valor){
					$sq[]= $chave.' '.$valor;
				}
				$this->sql .= implode(", ",$sq);
			}elseif(is_string($SetOrdenar)){
                $this->sql .= " ORDER BY ".$SetOrdenar." ".$SetOrder;
            }
            if(!empty($SetStart) && !empty($SetMax)){
                $this->SetLimit($SetMax,$SetStart);
            }
        }
        return $this->sql;
    }
	######################################################################
    ## SETA OS VALORES DE LIMIT PARA O SQL  
    ## SetLimit($SetMax,$SetStart);  
    ## $SetMax = int => Numero de resultados a serem buscados 
    ## $SetStart = STRING => Numero do registro inicial 
    ######################################################################
    function SetLimit($SetMax,$SetStart){
        $this->sql .= " LIMIT ".$SetMax.",".$SetStart;
    }
    ######################################################################
    ## eXECUTA UMA BUSCA NO BANCO DEDADOS SQL 
    ## SqlSelect($sql,$files,$line); 
    ## $sql = Querry Sql  
    ## $files = Arquivo executando o querry (__FILE__) 
    ## $line = linha (__LINE__) 
    ######################################################################
    function SqlSelect($sql,$files=false,$line=false){
		if(is_array($sql)){
			foreach($sql as $querry){
				$this->sql[] = $querry;
				if($hdl = mysql_query($querry,$this->conexao)){
				$hendle[] = $hdl;
				}else{
					if($files == true)$this->error_db($querry,$files,$line);
					return false;
				}
			}
			return $hendle; 
		}else{
			$this->sql = $sql;
			$hdl = mysql_query($sql,$this->conexao);
			if($hdl == false){
				if($files == true)$this->error_db($sql,$files,$line);
				return false;
			}else{
				return $hdl;
			}
		}
	}
    ######################################################################
    ## RETORNA UM ERRO DE SEL JUNTO COM SUA SINTAX 
    ## error_db($sql,$files,$line); 
    ## $sql = Querry Sql 
    ## $files = Arquivo executando o querry (__FILE__) 
    ## $line = linha (__FILE__) 
    ######################################################################
    function error_db($sql,$file,$line){
		print "SQL:".$sql ."<br/>File: ".basename($file)."<br/>Line: ".$line."<br/>".mysql_error();
		exit;
	}
    ####################################################################
    ## ATUALIZA UMA TABELA DE ACORDO COM OS VALORES DENTRO DE UM ARRAY
    ## AS ENTRADAS SÃO REFERENTES AO MYSQL 
    ## FUNÇÃO: SqlUpdate($tabela,$valores[,$item]) 
    ## $tabela = string/array - Nome das tabelas para consulta 
    ## $valores = string/array - valores para atualizar 
    ## $unico = string - condição de atualização 
    ####################################################################
    function SqlUpdate($tabela,$valores,$unico=false){
        if(is_array($tabela)){
            $tabelas =  implode(",", $tabela);
        }else{
            $tabelas =  $tabela;
		}
        $sql = "UPDATE $tabela SET ";
		if(is_array($valores)){
				foreach($valores as $chave => $valor){
					if((stripos($valor, 'NOW') !== false) && stripos($valor, 'NOW')== 0){
                        $x[] =  "$chave = $valor";
					}elseif($valor==''){
						$x[] = "$chave = NULL";
					}elseif(is_bool($valor)){
						$valor = ($valor == false)?"FALSE":"TRUE";
						$x[] = "$chave = $valor";
                    }else{
                        $x[] = "$chave = \"$valor\"";
					}
				}
				$sql .= implode(",", $x);
		}else{
			$sql .= $valores;
		}
        if($unico != false)$sql .= " WHERE ".$unico;
        return $sql;
    }
    ######################################################################
    ## REMOVE UMA CONSULTA APARTIT DE UM COMPARAÇÃO SQL UNICA 
    ## AS ENTRADAS SÃO REFERENTES AO MYSQL
    ## FUNÇÃO: SqlDelete($tabela,$unico) 
    ## $tabela = string/array - Nome das tabelas para consulta
    ## $unico = sql where - condição de remoção 
    ######################################################################
    function SqlDelete($tabela,$unico=false){
        if(is_array($tabela)){
            $tabelas =  implode(",", $tabela);
        }else{
            $tabelas =  $tabela;
		}
        $sql = "DELETE FROM $tabelas ";
        if($unico != false){
            $sql .= " WHERE $unico";
		}
        return $sql;
    }
    ####################################################################
    ## INSERE VALORES DENTRO DE UMA TABELA                            ##
    ## AS ENTRADAS SÃO REFERENTES AO MYSQL                            ##
    ## FUNÇÃO: SqlInsert($tabela,$valores)                            ##
    ## $tabela = string - Nome das tabelas para consulta              ##
    ## $valores = array - valores para inserir na tabela       		  ##
    ####################################################################
    function SqlInsert($tabelas,$valores=false){
		if($valores == false){
			foreach($tabelas as $tabela => $valores){
				$s = "INSERT INTO ".$GLOBALS['cfg']['db_prefix'].$tabela;
				foreach($valores as $nvalores){
					$nvalores = array_map('f_checkValue',$nvalores);
					foreach($nvalores as $chave => $valor){
						$tab[] = "`".$chave."`";
						$val[] = $valor;
					}
					$nvalor[] =  "(".implode(", ", $val).")";
					$ntab = implode(", ", $tab);
					unset($tab);
					unset($val);
				}
				$nval =  implode(", ", $nvalor);
				$s.= " ($ntab) VALUES $nval";
				unset($nvalor);
				$sql[] = $s;
			}
		}else{
			$valores = array_map('f_checkValue',$valores);
			$sql = "INSERT INTO ".$GLOBALS['cfg']['db_prefix'].$tabelas;
			foreach($valores as $chave => $valor){
				$tab[] = $chave;
				$val[] = $valor;
			}
			$chaves =  implode(", ", $tab);
			$valor =  implode(", ", $val);
			$sql .= " ($chaves) VALUES ($valor)";
		}
        return $sql;
    }
    ######################################################################
    ## RETORNA O NOME DAS COLUNAS DE UMA TABELA 
    ## FUNÇÃO: ListaCampos($smarty,$tabela,$pre) 
    ## $smarty = OBJETO => OBJETO DE CONFIGURAÇÃO
    ## $tabela = string - Nome das tabelas para consulta
    ## $pre = BOLEAN - Se true coloca no nome da tabela na frente
    ## RETORNA UM ARRAY COM OS NOMES 
    ######################################################################
    function ListaCampos(&$smarty,$tabela,$pre=false){
        $fields = @mysql_list_fields($smarty->cfg[db_name],$tabela, $this->conexao);
        $columns = @mysql_num_fields($fields);
        for ($i = 0; $i < $columns; $i++) {
            $tab[] = ($pre != false)?mysql_field_name($fields, $i):$tabela.'.'.mysql_field_name($fields, $i);
        }
        return $tab;
    }
	
	function CheckDB($name){
		return $db_selected = mysql_select_db($name, $this->conexao);

	}
	function CheckTable($name){
		if(mysql_num_rows(mysql_query("SHOW TABLES LIKE `".$name."`"))==1)
			return true;
			else
			return false;
	}
	
	function db_query_range($query) {
	  $args = func_get_args();
	  $count = array_pop($args);
	  $from = array_pop($args);
	  array_shift($args);
	  if (isset($args[0]) and is_array($args[0])) { // 'All arguments in one array' syntax
		$args = $args[0];
	  }
	  db_query_callback($args, TRUE);
	  $query = preg_replace_callback(DB_QUERY_REGEXP, 'db_query_callback', $query);
	  $query .= ' LIMIT '. (int)$from .', '. (int)$count;
	  return $query;
	}
	function dbResult($result) {
		if ($result && mysql_num_rows($result) > 0) {
			$array = mysql_fetch_row($result);
			return $array[0];
  }
  return FALSE;
}
}
?>