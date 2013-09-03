<?php

class Budget {
	
	public $id;
	public $nom = "";
	public $montant = 0;
	public $solde = 0;
	public $actif = false;
	public $statistiques = false;
	public $created;
	public $modified;
	
	public function __construct($id_budget = "") {

		if($id_budget !=""){
			return $this->get($id_budget);
		}

    }
	
	public function get($id_budget){
		
		$q = "SELECT * FROM ".db_table_name('budgets')." WHERE id='$id_budget' AND id_utilisateur = " . $_SESSION["user_id"];

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
				$this->id = $result['id'];
				$this->nom = stripslashes($result['nom']);
				$this->montant = $result['montant'];
				$this->solde = $result['solde'];
				$this->actif = ($result['actif'] == 1);
				$this->statistiques = ($result['statistiques'] == 1);
			}
			
    		return true;
    	}
	}
	
	public function create(){
		
		$nom = addslashes($this->nom) ;
		$montant = addslashes($this->montant) ;
		$actif = ($this->actif)?1:0;
		$statistiques = ($this->statistiques)?1:0;
		
		$q = "INSERT INTO ".db_table_name('budgets')." (nom, montant, actif, statistiques, created, modified, id_utilisateur) VALUES ('$nom', '$montant', '$actif', '$statistiques', NOW(), NOW(), '".$_SESSION["user_id"]."')";
		
		$res = mysql_query($q);
		if (!$res) {
			throw new MyException('Invalid query: ' . mysql_error());
		}

		$this->id = mysql_insert_id();
		
		
		return true;
	}
	
	public function update(){
		
		if($this->id == ''){
			throw new MyException("Cannot update Budget '$this->nom' without ID !!");
		}
		
		//utf_8 encodage réalisé par RPC
		$nom = addslashes($this->nom) ;
		$montant = addslashes($this->montant) ;
		$actif = ($this->actif)?1:0;
		$statistiques = ($this->statistiques)?1:0;
		
		$q = "UPDATE ".db_table_name('budgets')." SET nom = '$nom', montant = '$montant', actif = '$actif', statistiques = '$statistiques', modified = NOW() WHERE id = '$this->id' AND id_utilisateur = '".$_SESSION["user_id"]."'";
		
		$res = mysql_query($q);
		if (!$res) {
			throw new MyException("Invalid query: $q " . mysql_error());
			return false;
		}
		
		return true;
		
	}
	
	public function delete(){
		
		mysql_query("START TRANSACTION;");
		
		$q = "UPDATE ".db_table_name('transactions')." SET id_budget = 0 WHERE id_budget = '$this->id' AND id_utilisateur = " . $_SESSION["user_id"];
		$res = mysql_query($q);
		if (!$res) {
			mysql_query("ROLLBACK;");
			throw new MyException("Invalid query: $q " . mysql_error());
			return false;
		}
		
		if($this->id == ''){
			throw new MyException("Cannot update Budget '$this->nom' without ID !!");
		}
		
		$q = "DELETE FROM ".db_table_name('budgets')." WHERE id = '$this->id' AND id_utilisateur = " . $_SESSION["user_id"];

		$res = mysql_query($q);
		if (!$res) {
			mysql_query("ROLLBACK;");
			throw new MyException("Invalid query: $q " . mysql_error());
			return false;
		}
		
		mysql_query("COMMIT;");
		return true;
	}
	
	
	
	
	public function actualiserSolde(){
		
		if($this->id == ''){
			throw new MyException("Cannot update Budget '$this->nom' without ID !!");
		}
		
		//$q = "UPDATE ".db_table_name('budgets')." AS b INNER JOIN (SELECT t.id_budget AS id_budget, SUM(t.montant) AS total FROM ".db_table_name('transactions')." AS t INNER JOIN ".db_table_name('budgets')." AS b ON t.id_budget = b.id WHERE t.date_transaction >= b.date_debut AND t.date_transaction <= b.date_fin GROUP BY t.id_budget) AS my_t ON b.id = my_t.id_budget SET b.solde = my_t.total, modified = NOW() WHERE 1";
		$q = "UPDATE ".db_table_name('budgets')." AS b INNER JOIN (SELECT t.id_budget AS id_budget, SUM(t.montant) AS total FROM ".db_table_name('transactions')." AS t INNER JOIN ".db_table_name('budgets')." AS b ON t.id_budget = b.id WHERE 1 GROUP BY t.id_budget) AS my_t ON b.id = my_t.id_budget SET b.solde = my_t.total, modified = NOW() WHERE id_utilisateur = " . $_SESSION["user_id"];
		
		$res = mysql_query($q);

    	if (!$res) {
    		throw new MyException('Invalid query: ' . mysql_error());
		}
		return true;
	}
	
	public static function getListe(){
	
		$q = "SELECT id FROM ".db_table_name('budgets')." WHERE id_utilisateur = ".$_SESSION["user_id"]." ORDER BY created DESC";
		
		
		$res = mysql_query($q);
		if (!$res) {
			throw new MyException('Invalid query: '.$q . mysql_error());
		}

		$nb_res = mysql_num_rows($res);

		if($nb_res == 0) {
			return array();
		}else{
			while($result = mysql_fetch_assoc($res)){
				$return[] = new Budget($result['id']);
			}
		}
		return $return;
	}
	
	public function getTransactions(){
		
		if($this->id == ''){
			throw new MyException("Cannot update Budget '$this->nom' without ID !!");
		}
		
		$transactions = array();
				
		$q = "SELECT id FROM ".db_table_name('transactions')." WHERE id_budget = $this->id AND id_utilisateur = " . $_SESSION["user_id"];
		
		$res = mysql_query($q);

    	if (!$res) {
    		throw new MyException("Invalid query $q : " . mysql_error());
		}
		
		while ($result = mysql_fetch_assoc($res)) {

			$t = new Transaction($result['id']);
			$transactions[] = $t;
			
		}
		return $transactions;
	}
	
	public function getTransactionsParCategorie($id_categories = array()){
		
		if($this->id == ''){
			throw new MyException("Cannot update Budget '$this->nom' without ID !!");
		}
		
		$per_cat = array();
		
		if(count($id_categories) > 0) {
			
		}else{
			$transactions = $this->getTransactions();
			foreach($transactions as $t){
				if($per_cat[$t->id_categorie]['libelle'] == "") $per_cat[$t->id_categorie]['libelle'] = $t->getCategorie()->nom;
				
				$per_cat[$t->id_categorie]['montant'] += $t->montant;
				$per_cat[$t->id_categorie]['transactions'][] = $t;
				
			}
		}
		return $per_cat;
	}
}