<?php

  $db = $_POST['banco'];
  $conex = mysql_connect($_POST['servidor'],$_POST['login'],$_POST['senha']);
  mysql_select_db($db,$conex);
  
  $sqlTables = mysql_query("SHOW TABLES");
  for($x = 0; $x < mysql_num_rows($sqlTables);$x++){
  	$tables[$x] = mysql_result($sqlTables, $x);
  }
  
  foreach($tables as $table){
  	$sqlColumns = "SELECT column_name,data_type,column_default,column_key,extra FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '".$db."' AND TABLE_NAME = '".$table."'";
  	
	$columns = mysql_query($sqlColumns);
	
	$class = str_replace("_"," ",$table);
	$class = str_replace(" ","",ucwords($class));
	
	unset($colunas);
	
	while($linha = mysql_fetch_object($columns)){
		$colunas[] = array("coluna"=>str_replace('_'.$table,'',$linha->column_name),
						   "colunaDB"=>$linha->column_name,	
						   "tipo"=>$linha->data_type,
						   "default"=>$linha->column_default,
						   "key"=>$linha->column_key,
						   "extra"=>$linha->extra);	
	}
	
	$class_name[] = ucwords($class);
	
	$row[] = "<?php \n final class ".ucwords($class)."{ \n\n";
	  foreach($colunas as $coluna){
  		$row[] = "\t private $".$coluna['coluna']."; \n";
 	 }
	 
	 $row[] = "\n\t public function __construct(){ \n";
	 foreach($colunas as $coluna){
	 	if(empty($coluna['default'])){
	 		if($coluna['tipo'] == 'int' || $coluna['tipo'] == 'double'){ $dado = '0';}
			else{ $dado = ' \'\' ';}
	 	}else{
	 		if($coluna['tipo'] == 'int' || $coluna['tipo'] == 'double'){ $dado = $coluna['default'];}
			else{ $dado = ' \''.$coluna['default'].'\' ';}
	 	}
  		$row[] = "\t\t \$this->".$coluna['coluna']." = ".$dado."; \n";
 	 }
	 $row[] = "\t} \n\n";
	 
	 foreach($colunas as $coluna){
	 	$row[] = "\t public function set".ucfirst($coluna['coluna'])."($".$coluna['coluna']."){ \n".
	 		 "\t\t \$this->".$coluna['coluna']." = $".$coluna['coluna']."; \n".
	 		 "\t } \n\n".
	 		 "\t public function get".ucfirst($coluna['coluna'])."(){ \n".
	 		 "\t\t return \$this->".$coluna['coluna']."; \n".
	 		 "\t} \n\n";
	 }
	$row[] = "} \n ?>";
	
	$row[] = "dbclass";
	
	$row[] = "<?php \ninclude_once 'Connection.class.php'; \n";
	$row[] = "final class DB".ucwords($class)."{ \n \n";
	$row[] = "\t public function __construct(){ \n";
	$row[] = "\t \t \$conexao = new Connection(); \n";
	$row[] = "\t } \n \n";
	$row[] = "\t public function insert(\$obj){ \n";
	$row[] = "\t \t \$sql = 'INSERT INTO ".$table." ('; \n"; $vt = 1;
			 foreach($colunas as $coluna){
			 	if($coluna['extra'] != 'auto_increment'){
			 		$row[] = "\t\t \$sql .= '".$coluna['colunaDB'].(sizeof($colunas) == $vt ? '' : ',')."';\n";
			 	}
			 	$vt++;
			 }
		  $row[] = "\t\t \$sql .= ') VALUES ('; \n"; $vt = 1;
			 foreach($colunas as $coluna){
			 	if($coluna['extra'] != 'auto_increment'){
			 		$row[] = "\t\t \$sql .= ".($coluna['tipo'] == 'int' || $coluna['tipo'] == 'double' ? "\$obj->get".ucfirst($coluna['coluna'])."()".(sizeof($colunas) == $vt ? "" : ".','") : "'\''.\$obj->get".ucfirst($coluna['coluna'])."().'\'".(sizeof($colunas) == $vt ? "" : ",")."'").";\n";
			 	}
			 	$vt++;
			 }
		  $row[] = "\t\t \$sql .= ')'; \n";
		  $row[] = "\t\t mysql_query(\$sql); \n";
		  $row[] = "\t} \n\n";	

	$row[] = "\t public function update(\$obj){\n".
		 "\t\t \$sql = 'UPDATE ".$table." SET '; \n"; $vt = 1;
			 foreach($colunas as $coluna){
			 	if($coluna['extra'] != 'auto_increment'){
			 		$row[] = "\t\t \$sql .= '".$coluna['colunaDB']." = '.".($coluna['tipo'] == 'int' || $coluna['tipo'] == 'double' ? "\$obj->get".ucfirst($coluna['coluna'])."()".(sizeof($colunas) == $vt ? "" : ".','") : "'\''.\$obj->get".ucfirst($coluna['coluna'])."().'\' ".(sizeof($colunas) == $vt ? "" : ",")."'").";\n";
			 	}
			 	$vt++;
			 }
		 $row[] = "\t\t \$sql .= 'WHERE ".$colunas[0]['colunaDB']." = '.\$obj->get".ucfirst($colunas[0]['coluna'])."()".";\n".
		 	  "\t\t mysql_query(\$sql); \n".
		 	  "\t} \n\n";
	
	$row[] = "\t public function delete(\$obj){ \n".
		 "\t\t \$sql = 'DELETE FROM ".$table." WHERE ".$colunas[0]['colunaDB']." = '.\$obj->get".ucfirst($colunas[0]['coluna'])."();\n".
		 "\t\t mysql_query(\$sql); \n".
		 "\t} \n\n";
	
	$row[] = "\t public function select(\$obj){ \n".
		 "\t\t \$sql = 'SELECT * FROM ".$table." WHERE ".$colunas[0]['colunaDB']." = '.\$obj->get".ucfirst($colunas[0]['coluna'])."(); \n".
		 "\t\t \$rs = mysql_query(\$sql); \n".
		 "\t\t if(mysql_num_rows(\$rs) > 0 ){ \n";
		 foreach($colunas as $coluna){
		 	$row[] = "\t\t\t \$obj->set".ucfirst($coluna['coluna'])."(mysql_result(\$rs, 0, '".$coluna['colunaDB']."')); \n";
		 }
		 $row[] = "\t\t} \n".
		 	  "\t\t return \$obj; \n".
		 	  "\t} \n\n";
		 	  
	$row[] = "\t public function selectAll(){\n".
		 "\t\t \$sql = 'SELECT * FROM ".$table."'; \n".
		 "\t\t \$rs = mysql_query(\$sql); \n".
		 "\t\t for(\$i = 0; \$i < mysql_num_rows(\$rs); \$i++){ \n".
		 "\t\t\t \$obj = new ".ucwords($table)."(); \n";
		 foreach($colunas as $coluna){
		 	$row[] = "\t\t\t \$obj->set".ucfirst($coluna['coluna'])."(mysql_result(\$rs, \$i, '".$coluna['colunaDB']."')); \n";
		 }
		 $row[] = "\t\t\t \$objs[\$i] = \$obj; \n".
		 	  "\t\t } \n".
		 	  "\t\t if(!is_array(\$objs)){ throw new Exception(\"\");} \n".
		 	  "\t\t return \$objs; \n".
		 	  "\t } \n";
		 
	
	$row[] = "} \n ?>";
	
	$classes[] = $row;
	unset($row);
  }
  
  //print_r($classes);
  $x = 0;
  foreach($classes as $classe){
  	$arq = fopen('temp/'.$class_name[$x].'.class.php','w');
  	for($i = 0;$i < sizeof($classe);$i++){
  		if($classe[$i] == 'dbclass'){
  			fclose($arq);
  			$arq = fopen('temp/DB'.$class_name[$x].'.class.php','w');
  		}else{
  			fwrite($arq, $classe[$i]);
  		}
  	}
  	fclose($arq);
  	$x++;
  }
  
  $arqs = scandir('temp/');
  
  echo 'Path destino '.$_POST['path'].'<br /><br />';
  $sucess = 0;
  for($x = 3;$x < sizeof($arqs);$x++){
  	if(copy('temp/'.$arqs[$x], '../'.$_POST['path'].'/'.$arqs[$x])){
  		$status =  'Copiado com sucesso!';	
  		$sucess++;
  	}else{
  		$status =  'Falha ao copiar arquivo!';
  	}
  	echo $arqs[$x].' - '.$status.'<br />';
  	unlink('temp/'.$arqs[$x]);
  }
  echo 'Arquivos copiados com sucesso: '.$sucess.'/'.(sizeof($arqs) - 3).'<br /><br />';
  echo 'Copia Finalizada!';
?>
