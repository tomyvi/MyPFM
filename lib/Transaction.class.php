<?php

class Transaction{

	public $id;
	public $id_compte;
	public $date_transaction;
	public $date_import;
	public $date_maj;
	public $libelle;
	public $commentaire;
	public $montant;
	public $devise;
	public $id_categorie = 0;
	public $id_import;
	public $id_budget = 0;
	
	public $duplicate_id;
	
	
	public $budget; // Budget Class
	public $categorie; // Cat_Transaction Class
	public $compte; //Compte Class
	
	
	public function __construct($id_transac = "") {
		if($id_transac !=""){
			return $this->charger($id_transac);
		}
    }
	
	public function charger($id_compte){
		$this->id = $id_compte;
		$q = "SELECT * FROM ".db_table_name('transactions')." WHERE id = $this->id AND id_utilisateur = ".$_SESSION["user_id"];
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
    		
    		$this->id_compte = $result['id_compte'];
			$this->date_transaction = $result['date_transaction'];
			$this->date_import = $result['created'];

			$this->date_maj = $result['modified'];

			$this->libelle = stripslashes($result['libelle']);
			$this->commentaire = stripslashes($result['commentaire']);
			$this->montant = $result['montant'];
			$this->devise = stripslashes($result['devise']);
			$this->id_categorie = $result['id_categorie'];
    		$this->id_import = $result['id_import'];
    		$this->id_budget = $result['id_budget'];
    		
    		$this->getCategorie();
    		if($this->id_budget > 0) $this->getBudget();
    		
    		
    		
			return true;
    	}
	}
	
	public function ajouter($check_duplicates = true){
		
		if(!$check_duplicates || !$this->exists()){
			
			$id_compte = $this->id_compte;
			$date_transaction = $this->date_transaction;
			$libelle = addslashes(($this->libelle));
			$commentaire = addslashes(($this->commentaire));
			$montant = $this->montant;
			$devise = addslashes($this->devise);
			$id_categorie = $this->id_categorie;
			$id_import = $this->id_import;
			$id_budget = $this->id_budget;
			
			$q = "INSERT INTO ".db_table_name('transactions')." (id_compte, date_transaction, created, libelle, commentaire, montant, devise, id_categorie, id_import, id_budget, id_utilisateur) VALUES ('$id_compte', '$date_transaction', NOW(), '$libelle', '$commentaire', '$montant', '$devise', '$id_categorie', '$id_import', '$id_budget', '".$_SESSION["user_id"]."')";
			//print_r($q);
			$res = mysql_query($q);
			if (!$res) {
				throw new MyException('Invalid query: '.$q . mysql_error());
			}

			$this->id = mysql_insert_id();
			$this->date_import = date("Y-m-d");
			
			if($this->id_categorie == 0) $this->setCategorie();
			if($this->id_budget != 0) Budget::actualiserSolde();
			$this->getCompte()->actualiserSolde();
			
			return true;
		}else{
			throw new DuplicateTransactionException('La transaction existe déjà !');
			return false;
		}
	}
	
	public function update(){
		
		// UTF8 encode realisé par RPC
		$id_compte = $this->id_compte;
		$date_transaction = $this->date_transaction;
		$libelle = addslashes($this->libelle);
		$commentaire = addslashes($this->commentaire);
		$montant = $this->montant;
		//$devise = addslashes($this->devise);
		$id_categorie = $this->id_categorie;
		$id_budget = $this->id_budget;
		
		$q = "UPDATE ".db_table_name('transactions')." SET date_transaction='$date_transaction', libelle='$libelle', commentaire='$commentaire', montant='$montant', devise='$devise', id_categorie='$id_categorie', id_budget = '$id_budget', modified = NOW() WHERE id = ".$this->id." AND id_utilisateur = ".$_SESSION["user_id"];
		
		$res = mysql_query($q);
		if (!$res) {
			throw new MyException('Invalid query: '.$q . mysql_error());
		}
		
		if($this->id_budget != 0) Budget::actualiserSolde();
		$this->getCompte()->actualiserSolde();
		
		$this->date_maj = date("Y-m-d");
		
		return true;
		
	}
	
	public function delete($batch = false){
	
		$q="DELETE FROM ".db_table_name('transactions')." WHERE id = '$this->id' AND id_utilisateur = ".$_SESSION["user_id"];
		$res = mysql_query($q);
		
		if (!$res) {
			throw new MyException('Invalid query: ' . mysql_error());
		}
		
		if(! $batch){
			if($this->id_budget != 0) Budget::actualiserSolde();
			$this->getCompte()->actualiserSolde();
		}
		return true;
		
	}
	
	public function exists(){
	
		$id_compte = $this->id_compte;
		$date_transaction = addslashes($this->date_transaction);
		$libelle = addslashes($this->libelle);
		$commentaire = addslashes($this->commentaire);
		$montant = $this->montant;
			
		$q = "SELECT * FROM ".db_table_name('transactions')." WHERE (id_compte = '$id_compte' AND ABS(DATEDIFF(date_transaction, '$date_transaction')) <= 30 AND montant = '$montant' AND libelle = '$libelle' AND id_utilisateur = ".$_SESSION["user_id"].")";

		$res = mysql_query($q);
		
		if (!$res) {
			throw new MyException('Invalid query: '.$q . mysql_error());
		}
		
		$nb_res = mysql_num_rows($res);

    	if($nb_res > 0) {
    		return true;
    	}else{
			return false;
		}
		
	}
	
	public function getDuplicateID(){
	
		$id_compte = $this->id_compte;
		$date_transaction = addslashes($this->date_transaction);
		$libelle = addslashes($this->libelle);
		$commentaire = addslashes($this->commentaire);
		$montant = $this->montant;
			
		$q = "SELECT id FROM ".db_table_name('transactions')." WHERE (id_compte = '$id_compte' AND date_transaction = '$date_transaction' AND montant = '$montant' AND libelle = '$libelle' AND id_utilisateur = ".$_SESSION["user_id"].")";

		$res = mysql_query($q);
		
		if (!$res) {
			throw new MyException('Invalid query: '.$q . mysql_error());
		}
		
		$nb_res = mysql_num_rows($res);

    	if($nb_res > 0) {
    		$result = mysql_fetch_assoc($res);
			$this->duplicate_id = $result['id'];
			return $this->duplicate_id;
    	}else{
			return false;
		}
		
	}
    
	public function getCompte(){
		
		if($this->compte == null || $this->compte->id != $this->id_compte) $this->compte = new Compte($this->id_compte);
		
		return $this->compte;
		
	}
	public function getCategorie(){
		
		if(!isset($this->categorie) || $this->categorie->id != $this->id_categorie) $this->categorie = new Categorie($this->id_categorie);
		
		return $this->categorie;
		
	}
	
	public function getBudget(){
		
		if($this->budget == null || $this->budget->id != $this->id_budget) $this->budget = new Budget($this->id_budget);
		
		return $this->budget;
		
	}
	

	public function setCategorie($new_cat_id = 0){
		
		if($new_cat_id == 0){
			foreach(Categorie::getListe() as $c){
				
				$patterns = explode(",",strtoupper($c->pattern));
				
				foreach($patterns as $pattern){
					
					if($pattern != "" && strpos(strtoupper($this->libelle),$pattern)){
						$new_cat_id = $c->id;
						break;
					}
				}
			}
		}
		
		$this->id_categorie = $new_cat_id;
		$this->categorie = new Categorie($this->id_categorie);
		return ($this->id_categorie > 0)?$this->update():true;
	}
	
	
}

?>