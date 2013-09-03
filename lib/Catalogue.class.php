<?php

abstract class Catalogue {

	public $id = "";
	public $nom = "";
	public $ordre;
	public $created;
	public $modified;
	
	public $table;
	public $where = "1";
	public $sort = "nom ASC";
	public $sens = "ASC";
	
    public function __construct($id = 0) {
		
		if($this->table == ""){
			throw new MyException('Nom de table du catalogue non défini !!');
		}
		
		return $this->charger($id);

	}

	public function charger($id){
		
		$q = "SELECT * FROM ".db_table_name($this->table)." WHERE $this->where AND id='$id' AND id_utilisateur = " . $_SESSION["user_id"];

		$res = mysql_query($q);
	    if (!$res) {
	    	throw new MyException('Invalid query: '.$q . mysql_error());
		}

		$nb_res = mysql_num_rows($res);

    	if($nb_res != 1) {
    		return false;
    	}else{
    		$result = mysql_fetch_assoc($res);
			
			foreach($result as $key => $value){
				$this->{$key} = utf8_encode(stripslashes($value));
			}
			
    		return true;
    	}
	}

	public function ajouter() {
		
		
		$field_list = "";
		$value_list = "";
		foreach(get_class_vars(get_called_class()) as $var => $value){
			
			if ($var != "id" && $var != "created" && $var != "modified" && $var != "table" && $var != "where" && $var != "sort" && $var != "sens"){
				if ($field_list != "") $field_list .= ", ";
				$field_list .= $var;
				
				if ($value_list != "") $value_list .= ", ";
				$value_list .= "'".addslashes($value)."'";
			}
		}
		
		$q = "INSERT INTO ".db_table_name($this->table)." ($field_list, created, modified, id_utilisateur) VALUES ($value_list, NOW(), NOW(), ".$_SESSION["user_id"].")";

		$res = mysql_query($q);
		if (!$res) {
			return false;
		}
		$this->id = mysql_insert_id();
		
		return true;

	}

	public function supprimer() {
		$q = "DELETE FROM ".db_table_name($this->table)." WHERE id = '$this->id' AND id_utilisateur = " . $_SESSION["user_id"];

		$res = mysql_query($q);
		if (!$res) {
			return false;
		}
		
		return true;

	}
	
	public function update(){
		
		$field_list = "";
			
		foreach(get_class_vars(get_called_class()) as $var => $value){
				
			if ($var != "id" && $var != "created" && $var != "modified"){
				if ($field_list != "") $field_list .= ", ";
				$field_list .= $var . " = '" . addslashes($value) ."'";
				
			}
		}
		
		
		$q = "UPDATE ".db_table_name($this->table)." SET $field_list, modified = NOW() WHERE id = $this->id AND id_utilisateur = " . $_SESSION["user_id"];
		
		echo $q;exit;
		
		$res = mysql_query($q);
		if (!$res) {
			return false;
		}
		
		return true;
		
	}
	
	public static function getListe(){
	
		$class_name = get_called_class();
		
		$ma_class = new $class_name();
		
		$q = "SELECT id FROM ".db_table_name($ma_class->table)." WHERE $ma_class->where AND id_utilisateur = ".$_SESSION["user_id"]." ORDER BY $ma_class->sort $ma_class->sens";
		$res = mysql_query($q);
		if (!$res) {
			throw new MyException('Invalid query: '.$q . mysql_error());
		}

		$nb_res = mysql_num_rows($res);

		if($nb_res == 0) {
			return array();
		}else{
			while($result = mysql_fetch_assoc($res)){
				$return[] = new $class_name($result['id']);
			}
		}
		return $return;
	}
	
	
	
}
?>