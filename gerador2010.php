<?php
	$sp1 = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
	$sp2 = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';

  $db = "pvcBrazil";
  $conex = mysql_connect("localhost","root","sucesso");
  mysql_select_db($db,$conex);
  
  $sqlTables = mysql_query("SHOW TABLES");
  for($x = 0; $x < mysql_num_rows($sqlTables);$x++){
  	$tables[$x] = mysql_result($sqlTables, $x);
  }
  
  foreach($tables as $table){
  	$sqlColumns = "SELECT column_name,data_type,column_default FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '".$db."' AND TABLE_NAME = '".$table."'";
	$columns = mysql_query($sqlColumns);
	
	while($linha = mysql_fetch_object($columns)){
		$colunas[] = array("coluna"=>str_replace('_'.$table,'',$linha->column_name),
						   "colunaDB"=>$linha->column_name,	
						   "tipo"=>$linha->data_type,
						   "default"=>$linha->column_default);	
	}
	
	echo 'include_once \'connection.class.php\'; <br>
		  final class '.$table.'{ <br><br>';
	  foreach($colunas as $coluna){
  		echo $sp1.'private $'.$coluna['coluna'].'; <br>';
 	 }
	 
	 echo '<br>'.$sp1.'public function __construct(){ <br>';
	 foreach($colunas as $coluna){
	 	if(empty($coluna['default'])){
	 		if($coluna['tipo'] == 'int' || $coluna['tipo'] == 'double'){ $dado = '0';}
			else{ $dado = ' \'\' ';}
	 	}else{
	 		if($coluna['tipo'] == 'int' || $coluna['tipo'] == 'double'){ $dado = $coluna['default'];}
			else{ $dado = ' \''.$coluna['default'].'\' ';}
	 	}
  		echo $sp2.'$this->'.$coluna['coluna'].' = '.$dado.'; <br>';
 	 }
	 echo $sp1.'} <br><br>';
	 
	 foreach($colunas as $coluna){
	 	echo $sp1.'public function set'.ucfirst($coluna['coluna']).'($'.$coluna['coluna'].'){ <br>
		     '.$sp2.' $this->'.$coluna['coluna'].' = $'.$coluna['coluna'].'; <br>
			 '.$sp1.'} <br><br>';
			 
		echo $sp1.'public function get'.ucfirst($coluna['coluna']).'(){ <br>
		     '.$sp2.'return $this->'.$coluna['coluna'].'; <br>
			 '.$sp1.'} <br><br>';
	 }
	echo '}';
	
	echo '<br><br><br>';
	
	echo  $sp1.'final class DB'.$table.'{ <br><br>'.
		  $sp1.'public function __construct(){ <br>'.
		  $sp2.'$conexao = new connection(); <br>'.
		  $sp1.'} <br><br>';
		  
	echo $sp1.'public function insert($obj){ <br>'.
		 $sp2.'$sql = "INSERT INTO ( <br>'; $vt = 1;
		 foreach($colunas as $coluna){
		 	echo $sp2.'$sql .= \''.$coluna['colunaDB'].(sizeof($colunas) == $vt ? '' : ',').'\';<br>';
		 	$vt++;
		 }
		 echo $sp2.'$sql = \') VALUES (\'; <br>'; $vt = 1;
		 foreach($colunas as $coluna){
		 	echo $sp2.'$sql .= '.($coluna['tipo'] == 'int' || $coluna['tipo'] == 'double' ? '$obj->get'.ucfirst($coluna['coluna']).(sizeof($colunas) == $vt ? '' : '.\',\'') : '\'$obj->get'.ucfirst($coluna['coluna']).(sizeof($colunas) == $vt ? '' : ',').'\'').';<br>';
		 	$vt++;
		 }
		 echo $sp2.'mysql_query($sql); <br />';
	echo $sp1.'} <br /><br />';	

	echo $sp1.'public function update($obj){<br />'.
		 $sp2.'$sql = \'UPDATE '.$table.' SET \' <br />'; $vt = 1;
		 foreach($colunas as $coluna){
		 	echo $sp2.'$sql .= \''.$coluna['colunaDB'].' = \'.'.($coluna['tipo'] == 'int' || $coluna['tipo'] == 'double' ? '$obj->get'.ucfirst($coluna['coluna']).(sizeof($colunas) == $vt ? '' : '.\',\'') : '\'$obj->get'.ucfirst($coluna['coluna']).(sizeof($colunas) == $vt ? '' : ',').'\'').';<br>';
		 	$vt++;
		 }
		 echo $sp2.'$sql = \'WHERE '.$colunas[0]['colunaDB'].' = \'.$obj->get'.ucfirst($colunas[0]['coluna']).';<br />'.
		 	  $sp2.'mysql_query($sql); <br />';
	echo $sp1.'} <br /><br />';
	
	echo $sp1.'public function select($obj){ <br />'.
		 $sp2.'$sql = \'SELECT * FROM '.$table.' WHERE '.$colunas[0]['colunaDB'].' = \'.$obj->get'.ucfirst($colunas[0]['coluna']).'; <br />'.
		 $sp2.'$rs = mysql_query($sql); <br />'.
		 $sp2.'if(mysql_num_rows($rs) > 0 ){ <br />';
		 foreach($colunas as $coluna){
		 	echo $sp1.$sp2.'$obj->set'.ucfirst($coluna['coluna']).'(mysql_result($rs, 0, \''.$coluna['colunaDB'].'\'; <br />';
		 }
		 echo $sp2.'} <br />'.
		 	  $sp2.'return $obj; <br />'.
		 	  $sp1.'} <br /><br />';
		 	  
	echo $sp1.'public function selectAll(){<br />'.
		 $sp2.'$sql = \'SELECT * FROM '.$table.'\'; <br />'.
		 $sp2.'$rs = mysql_query($rs); <br />'.
		 $sp2.'for($i = 0; $i < mysql_num_rows($rs); $i++){ <br />';
		 foreach($colunas as $coluna){
		 	echo $sp1.$sp2.'$obj->set'.ucfirst($coluna['coluna']).'(mysql_result($rs, $i, \''.$coluna['colunaDB'].'\'; <br />';
		 }
		 echo $sp1.$sp2.'$objs[$i] = $obj; <br />'.
		 	  $sp2.'} <br />'.
		 	  $sp2.'if(!is_array($objs)){ throw new Exception("");} <br />'.
		 	  $sp2.'return $objs; <br />'.
		 	  $sp1.'} <br />';
		 
	
	echo '<br /><br /><br />';
  }
?>
