<?
####################################################################
## CLASSE PARA MANIPULAÇÃO BANCO DE DADOS                         ##
## CLASS.MYSQL.PHP VERSÃO 2.0  - 08/12/2006                       ##
## CRIADO POR ART-2 => RENATO INNOCENTI                           ##
## EMAIL: r.innocenti@uol.com.br                                  ##
####################################################################
error_reporting(E_ALL & ~ E_NOTICE);
####################################################################
## CRIA INSTANCIA PARA CONSULTAS MYSQL                            ##
## AS ENTRADAS SÃO REFERENTES AO MYSQL                            ##
## Classe: MYSQL                                                  ##
####################################################################
class MYSQL
{   
    var $conexao;
    var $db_name;
    var $sql;
    var $caption=array();
    
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
    ## INICIA UMA CONECÇÃO MYSQL                                        ##
    ## MYSQL($smarty);                                                  ##
    ## $smarty = OBJETO => OBJETO DE CONFIGURAÇÃO                       ##
    ######################################################################
    function MYSQL(&$smarty){
        @mysql_close();
        $this->conexao = mysql_connect($smarty->cfg[db_server],$smarty->cfg[db_user],$smarty->cfg[db_passwd]);
        mysql_select_db($smarty->cfg[db_name],$this->conexao);
        $this->db_name=$smarty->cfg[db_name];
    }
        
    ######################################################################
    ## RETORNA O RANDLE DA CONECÇÃO ATUAL                               ##
    ## Connection();                                                    ##
    ######################################################################
    function Connection(){
        return $this->conexao;
    }
    ####################################################################
    ## METODO FAZ A CONFIGURAÇÃO DA LISTAGEM, NÃO EXIBE NADA          ##
    ## CreateSelectSQL($params, &$smarty)                             ##
    ## $params = ARRAY => RECEBE TODOS OS PARAMETROS DA TAG           ##
    ## $smarty = OBJETO => RECEBE O OBJETO DO TEMPLATE                ##
    ##      @SetTable = string - Nome da tabela a ser acessada        ##
    ##      @SetCaption = string/array - Nome das tabelas a serem     ##
    ##          selecionadas.                                         ##
    ##      @SetCombine = string/array - Nome das tabelas a serem     ##
    ##          adicinadas na seleção                                 ##
    ##      [OPCIONAIS]                                               ##
    ##      @SetDistinct = string - tabela sql que será acessada      ##
    ##      @SetCombineDirect = LEFT|RIGHT direção da união           ##
    ##      @SetCombineCol = String - Tabela a ser comparada          ##
    ##      @SetListAdm = string - Tabela com o valor de ID do usuario##
    ##      @SetListAdmNivel = String - Nivel de permição da listagem ##
    ##      @SetListAprovar = int/bolan = tabela com valores de       ##
    ##          linha liberada para listagem                          ##
    ##      @SetListExtra = SQL - comando SQL para o WHERE            ##
    ##      @SetSearch = string - Palavras a serem buscadas           ##
    ##      @SetOrder = DESC|ASC - Direção de listagem das buscas     ##
    ##      @SetOrdenar = string/array - Tabela a ser ordenada        ##
    ##      @SetStart = int - Numero do registro inicial              ##
    ##      @SetMax = int - Numero de resultados a serem buscados     ##
    ####################################################################  
    function CreateSelectSQL($params,&$smarty){
        extract($params);
        if(empty($SetTable)){
            return $smarty->getFinishError("ERROR_008",array("",'(SetTable)'));
        }elseif(empty($SetCaption)){
            return $smarty->getFinishError("ERROR_008",array("",'(SetCaption)'));
        }elseif(!empty($SetCombine) && empty($SetCombineCol)){
            return $smarty->getFinishError("ERROR_008",array("",'(SetCombineCol)'));
        }else{
            $this->sql = "SELECT ";
            if(!empty($SetDistinct)){
                $this->sql .= "DISTINCT ";
                $this->PREFIX_TABLE = 1;                
            }
            if(is_array($SetCaption)){
                $this->sql .= implode(", ",$SetCaption);
            }else{
               $this->sql .= $SetCaption; 
            }
            $this->caption = $SetCaption;
            $this->sql .= " FROM ";
            if(is_array($SetTable)){
                $this->sql .= implode(", ",$SetTable);
            }else{
                $this->sql .= $SetTable;
            }
            if(!empty($SetCombine)){
                if(!empty($SetCombineDirect))
                    $SetCombineDirect = " LEFT";
                if(is_array($SetCombine) && is_array($SetCombineCol)){
                    if(count($SetCombine) != count($SetCombineCol))
                        return $smarty->getFinishError("ERROR_009",array('(SetCombine,SetCombineCol)','('.count($SetCombine).','.count($SetCombineCol).')'));
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
                if(is_array($SetTable)){
                    foreach($SetTable as $valor){
                        foreach($this->ListaCampos($smarty,$valor) as $v){
                            $sq .= " $v LIKE ";
                            foreach($buscas as $bus){
                                $sq .= "'%".$bus."%' OR ";
                            }
                        }
                    }
                }else{
                    foreach($this->ListaCampos($smarty,$SetTable) as $v){
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
            if(!empty($SetOrdenar)){
                $this->sql .= " ORDER BY ".$SetOrdenar." ".$SetOrder;
            }
            if(!empty($SetStart) && !empty($SetMax)){
                $this->SetLimit($SetMax,$SetStart);
            }
        }
        return $this->sql;
    }
    

    ######################################################################
    ## SETA OS VALORES DE LIMIT PARA O SQL                              ##
    ## SetLimit($SetMax,$SetStart);                                     ##
    ## $SetMax = int => Numero de resultados a serem buscados           ##
    ## $SetStart = STRING => Numero do registro inicial                 ##
    ######################################################################
    function SetLimit($SetMax,$SetStart){
        $this->sql .= " LIMIT ".$SetMax.",".$SetStart;
    }
    ######################################################################
    ## eXECUTA UMA BUSCA NO BANCO DEDADOS SQL                           ##
    ## SqlSelect($sql,$files,$line);                                    ##
    ## $sql = Querry Sql                                                ##
    ## $files = Arquivo executando o querry (__FILE__)                  ##
    ## $line = linha (__FILE__)                                         ##
    ######################################################################
    function SqlSelect($sql,$files=__FILE__,$line=__LINE__){
        $hdl = mysql_query($sql,$this->conexao) or $this->error_db($sql,$files,$line);
        return $hdl;
        }
    ######################################################################
    ## rETORNA UM ERRO DE SEL JUNTO COM SUA SINTAX                      ##
    ## error_db($sql,$files,$line);                                     ##
    ## $sql = Querry Sql                                                ##
    ## $files = Arquivo executando o querry (__FILE__)                  ##
    ## $line = linha (__FILE__)                                         ##
    ######################################################################
    function error_db($sql,$file,$line){
        $error = "SQL:".$sql ."<br/>File: ".basename($file)."<br/>Line: ".$line."<br/>".mysql_error();
        print ereg_replace("(\r\n|\n|\r|\t)", "<br />", $error);
        }
    ####################################################################
    ## ATUALIZA UMA TABELA DE ACORDO COM OS VALORES DENTRO DE UM ARRAY##                                  ##
    ## AS ENTRADAS SÃO REFERENTES AO MYSQL                            ##
    ## FUNÇÃO: SqlUpdate($tabela,$valores[,$item])                    ##
    ## $tabela = string/array - Nome das tabelas para consulta        ##
    ## $valores = string/array - valores para atualizar               ##
    ## $unico = string - condição de atualização                      ##
    ####################################################################
    function SqlUpdate($tabela,$valores,$unico=false){
        if(is_array($tabela))
            $tabelas =  implode(",", $tabela);
        else
            $tabelas =  $tabela;
        $sql = "UPDATE $tabela SET ";
        if(count($valores >1)){
            foreach($valores as $chave => $valor){
                if($valor != ''){
                    if($valor == 'NOW()')
                        $sql .= "$chave = $valor";
                    else
                        $sql .= "$chave = '$valor'";
                }else{
                    $sql .= "$chave = NULL";
                }
                $sql .=", ";
            }
            $sql = substr($sql, 0, -2);
        }else{
            if($valores[key($valores)] != '')
                $sql .= key($valores)." = '".$valores[key($valores)]."'";
            else
                $sql .= key($valores)." = NULL";
        }
        if($unico != false)
            $sql .= " WHERE $unico";
        return $sql;
    }
    ######################################################################
    ## REMOVE UMA CONSULTA APARTIT DE UM COMPARAÇÃO SQL UNICA           ##                                  ##
    ## AS ENTRADAS SÃO REFERENTES AO MYSQL                              ##
    ## FUNÇÃO: SqlDelete($tabela,$unico)                                ##
    ## $tabela = string/array - Nome das tabelas para consulta          ##
    ## $unico = sql where - condição de remoção                         ##
    ######################################################################
    function SqlDelete($tabela,$unico=false){
        if(is_array($tabela))
            $tabelas =  implode(",", $tabela);
        else
            $tabelas =  $tabela;
        $sql = "DELETE FROM $tabelas ";
        if($unico != false)
            $sql .= " WHERE $unico";
        return $sql;
    }
    ####################################################################
    ## INSERE VALORES DENTRO DE UMA TABELA                            ##
    ## AS ENTRADAS SÃO REFERENTES AO MYSQL                            ##
    ## FUNÇÃO: SqlInsert($tabela,$valores)                            ##
    ## $tabela = string - Nome das tabelas para consulta              ##
    ## $valores = string/array - valores para inserir na tabela       ##
    ####################################################################
    function SqlInsert($tabela,$valores){
        $sql = "INSERT INTO $tabela";
        if(is_array($valores)){
            foreach($valores as $chave => $valor){
                $val1[] = $chave;
                if($valor == 'NOW()')
                    $val2[] = "NOW()";
                else
                    $val2[] = "'".$valor."'";
            }
        }
        $chaves =  implode(", ", $val1);
        $valor =  implode(", ", $val2);
        $sql .= " ($chaves) VALUES ($valor)";
        return $sql;
    }
    ######################################################################
    ## RETORNA O NOME DAS COLUNAS DE UMA TABELA                         ##
    ## FUNÇÃO: ListaCampos($smarty,$tabela,$pre)                        ##
    ## $smarty = OBJETO => OBJETO DE CONFIGURAÇÃO                       ##
    ## $tabela = string - Nome das tabelas para consulta                ##
    ## $pre = BOLEAN - Se true coloca no nome da tabela na frente       ##
    ## RETORNA UM ARRAY COM OS NOMES                                    ##
    ######################################################################
    function ListaCampos(&$smarty,$tabela,$pre=false){
        $fields = @mysql_list_fields($smarty->cfg[db_name],$tabela, $this->conexao);
        $columns = @mysql_num_fields($fields);
        for ($i = 0; $i < $columns; $i++) {
            $tab[] = ($pre != false)?mysql_field_name($fields, $i):$tabela.'.'.mysql_field_name($fields, $i);
        }
        return $tab;
    }
}
?>