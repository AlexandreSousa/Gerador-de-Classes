<?php
class Connect{
	private $user = "";
	private $pass = "";
	private $server = "";
	private $banco = "";
	
	public function __construct(){
		$conexao = mysql_connect($this->server,$this->user,$this->pass) or die ("Erro ao conectar com MySQL".mysql_error());
		$db = mysql_select_db($this->banco,$conexao) or die ("Erro ao conectar ao banco".mysql_error());
	}
	
	private function query($sql){
		$result = mysql_query($sql);
		return $result;
	}
	
	public function insert($tabela,$colunas,$dados){
		$sql = "INSERT INTO ".$tabela." (";
		foreach($colunas as $coluna){
			$sql .= $coluna;
		}
	}
}
?>
