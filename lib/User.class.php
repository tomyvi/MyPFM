<?php

class User {
	public $id;
	public $nom;
	public $login;
	
	public $logged_in = false;

	public function __construct($iduser = "") {

		if($iduser !=""){
			return $this->charger($iduser);
		}

    }

	function charger($iduser){
		$q = "SELECT * FROM ".db_table_name('utilisateurs')." WHERE id = '$iduser'";
    	$res = mysql_query($q);

    	if (!$res) {
    		throw new MyException('Invalid query: ' . mysql_error());
		}

		$nb_res = mysql_num_rows($res);

    	if($nb_res != 1) {
    		return false;
    	}else{
    		$result = mysql_fetch_assoc($res);


			$this->id = $result["id"];
			$this->nom = $result["nom"];
			$this->login = $result["login"];
			$this->logged_in = true;
			return true;
    	}

	}

    function validate($login, $response){
		global $_config;

    	$login = addslashes(trim($login));
    	$q = "SELECT * FROM ".db_table_name('utilisateurs')." WHERE login = '$login'";
    	$res = mysql_query($q);

    	if (!$res) {
    		throw new MyException('Invalid query: ' . mysql_error());
		}

		$nb_res = mysql_num_rows($res);

    	if($nb_res != 1) {
    		return false;
    	}else{
    		$result = mysql_fetch_assoc($res);
			$logged_in = (md5($_SESSION["challenge"].$result["password"]) == $response);

    		if($logged_in){
    			$this->id = $result["id"];
    			$this->nom = $result["nom"];
    			$this->login = $result["login"];
    			$this->logged_in = true;
    			$this->log_access();
    			return true;
    		}else{
    			return false;
    		}
    	}
    }

    function log_access(){
		global $_config;

    }

    static function generateChallenge(){
    	srand();
		$challenge = "";
		for ($i = 0; $i < 80; $i++) {
		    $challenge .= dechex(rand(0, 15));
		}
		return $challenge;

    }

    static function is_user_logged_in(){

		
		return $_SESSION['user_logged_in'];
	}

}
?>