<?php

class Compte {
	
	public $id;
	public $cle;
	public $libelle;
	public $libelle_long;
	public $etablissement;
	public $url_acces;
	public $id_titulaire;
	public $id_type;
	public $no_compte;
	public $solde_ouverture;
	public $solde_courant;
	public $ordre;
	public $cloture;
	public $is_synthese = false;
	public $created;
	public $modified;

	public $type;		//TypeCompte Class
	public $titulaire; // User Class
	
	public $transactions = array(); // Transaction Class Array
	public $nb_transactions = 0;
	public $transactions_loaded = false;
	
	public $transactions_futures = array(); // Transaction Class Array
	
	public $latest_transaction_date;
	public $latest_import_date;
	
	

	
	public function __construct($id_compte = "") {

		if($id_compte !=""){
			return $this->charger($id_compte);
		}

    }
	
	public function charger($id_compte){
		
		$this->id = $id_compte;
		
		$q = "SELECT c.*, (SELECT COUNT(*) FROM ".db_table_name('transactions')." WHERE id_compte = $this->id) AS nb_transactions FROM ".db_table_name('comptes')." AS c WHERE c.id = $this->id AND c.id_utilisateur = ".$_SESSION["user_id"];
		
		$res = mysql_query($q);

    	if (!$res) {
    		throw new MyException('Invalid query: ' . mysql_error());
		}

    	$nb_res = mysql_num_rows($res);

    	if($nb_res != 1) {
    		return false;
    	}else{
    		$result = mysql_fetch_assoc($res);
    		
    		$this->cle = stripslashes($result['cle']);
			$this->libelle = stripslashes($result['libelle']);
			$this->libelle_long = stripslashes($result['libelle_long']);
			$this->etablissement = stripslashes($result['etablissement']);
			$this->url_acces = $result['url_acces'];
			$this->id_titulaire = $result['id_titulaire'];
			$this->id_type = $result['id_type'];
			$this->no_compte = $result['no_compte'];
			$this->solde_ouverture = $result['solde_ouverture'];
			$this->solde_courant = $result['solde_courant'];
			$this->ordre = $result['ordre'];
			$this->cloture = ($result['cloture'] == 1);
    		$this->is_synthese = ($result['is_synthese'] == 1);
    		$this->created = $result['created'];
    		$this->modified = $result['modified'];
    		
			$this->nb_transactions = $result['nb_transactions'];
    		
			
			$this->getType();
			
			$this->getLatestTransactionDate();
			$this->getLatestImportDate();

			
    		return true;
    	}
	}
	
	public function ajouter(){
		
		$cle = addslashes($this->cle) ;
		$libelle = addslashes($this->libelle) ;
		$libelle_long = addslashes($this->libelle_long) ;
		$etablissement = addslashes($this->etablissement) ;
		$url_acces = $this->url_acces;
		$id_titulaire = $this->id_titulaire;
		$id_type = $this->id_type;
		$no_compte = $this->no_compte;
		$solde_ouverture = $this->solde_ouverture;
		$is_synthese = ($this->is_synthese)?1:0;
		
		$q = "INSERT INTO ".db_table_name('comptes')." (cle, libelle, libelle_long, etablissement, url_acces, id_titulaire, id_type, no_compte, solde_ouverture, solde_courant, is_synthese, created, modified, id_utilisateur) VALUES ('$cle', '$libelle', '$libelle_long', '$etablissement', '$url_acces', '$id_titulaire', '$id_type', '$no_compte', '$solde_ouverture', '$solde_ouverture', '$is_synthese', NOW(), NOW(), '".$_SESSION["user_id"]."')";
		
		$res = mysql_query($q);
		if (!$res) {
			throw new MyException('Invalid query: ' . mysql_error());
		}

		$this->id = mysql_insert_id();
		
		
		return true;
	}
	
	public function update(){
		
		if($this->id == ''){
			throw new MyException("Cannot update Compte '$this->libelle' without ID !!");
		}
		
		//utf_8 encodage réalisé par RPC
		$cle = addslashes($this->cle) ;
		$libelle = addslashes($this->libelle) ;
		$libelle_long = addslashes($this->libelle_long) ;
		$etablissement = addslashes($this->etablissement) ;
		$url_acces = $this->url_acces ;
		$id_type = $this->id_type;
		$no_compte = $this->no_compte;
		$solde_ouverture = $this->solde_ouverture;
		$cloture = $this->cloture;
		$is_synthese = ($this->is_synthese)?1:0;
		
		
		
		
		$q = "UPDATE ".db_table_name('comptes')." SET cle = '$cle', libelle = '$libelle', libelle_long = '$libelle_long', etablissement = '$etablissement', url_acces = '$url_acces', id_type = '$id_type', no_compte = '$no_compte', solde_ouverture = $solde_ouverture, cloture = '$cloture', is_synthese = '$is_synthese', modified = NOW() WHERE id = '$this->id' AND id_utilisateur = '".$_SESSION["user_id"]."'";
		
		$res = mysql_query($q);
		if (!$res) {
			throw new MyException("Invalid query: $q " . mysql_error());
			return false;
		}
		
		return true;
	}
	
	public function delete(){
		
		mysql_query("START TRANSACTION;");
		
		//suppression des transactions
		$this->chargerTransactionsParNb(0, $this->nb_transactions);
		foreach($this->transactions as $t){
			try{
				$t->delete(true);
			}catch(Exception $e){
				mysql_query("ROLLBACK;");
				throw $e;
			}
		}
		
		//mise à jour du solde des budgets
		Budget::actualiserSolde();
		
		
		//suppression du compte
		$q = "DELETE FROM ".db_table_name('comptes')." WHERE id = '$this->id' AND id_utilisateur = '".$_SESSION["user_id"]."'";
		
		$res = mysql_query($q);
		if (!$res) {
			mysql_query("ROLLBACK;");
			throw new MyException("Invalid query: $q " . mysql_error());
			return false;
		}
		
		mysql_query("COMMIT;");
	
		return true;
	}
	
	public static function getListe($charger_clot = false){
		
		$comptes = array();
		
		if($charger_clot){
			$where = 'cloture = 1';
		}else{
			$where = 'cloture = 0';
		}
		
		$q = "SELECT id FROM ".db_table_name('comptes')." WHERE $where AND id_utilisateur = ".$_SESSION["user_id"]." ORDER BY ordre ASC";
		
		$res = mysql_query($q);

		if (!$res) {
			throw new MyException('Invalid query: ' . mysql_error());
		}

		while ($result = mysql_fetch_assoc($res)) {

			$c = new Compte($result['id']);
			$comptes[] = $c;
			
		}
		
		return $comptes;
	}
	
	public function chargerTransactionsParNb($start=0, $nb=30){
		
		$q = "SELECT id FROM ".db_table_name('transactions')." WHERE id_compte = '$this->id' AND id_utilisateur = ".$_SESSION["user_id"]." ORDER BY date_transaction DESC LIMIT $start, $nb";
		
		$res = mysql_query($q);

    	if (!$res) {
    		throw new MyException('Invalid query: ' . mysql_error());
		}

    	$nb_res = mysql_num_rows($res);

    	if($nb_res == 0) {
    		return false;
    	}else{
    		while($result = mysql_fetch_assoc($res)){
    		
				$t = new Transaction($result["id"]);
				$t->getCategorie();
				$this->transactions[] = $t;
				
			
			}
			$this->transactions_loaded = true;
    		return true;
    	}
		
	}
    public function chargerTransactionsParDate($id_categories = array(), $date_fin = "", $date_debut = ""){
		
		
		if(count($id_categories)>0){
			$where_cat = " AND (";
			$nb = 0;
			foreach($id_categories as $id_categorie){
				$where_cat .= "id_categorie = $id_categorie";
				$nb++;
				if($nb != count($id_categories)){
					$where_cat .=" OR ";
				}
			}
			$where_cat .=")";
		}
		if($date_debut != "") $where_debut = " AND date_transaction >= '$date_debut'";
		if($date_fin != "") $where_fin = " AND date_transaction <= '$date_fin'";
		
		$q = "SELECT t.id AS id FROM ".db_table_name('transactions')." AS t, ".db_table_name('categories')." AS cat
					WHERE id_compte = $this->id AND t.id_categorie=cat.id $where_cat $where_debut $where_fin AND t.id_utilisateur = ".$_SESSION["user_id"]."
					ORDER BY date_transaction DESC
					";
		
		$res = mysql_query($q);
		if (!$res) {
    		throw new MyException('Invalid query: ' .$q. mysql_error());
		}
		while($result = mysql_fetch_assoc($res)){
			$t = new Transaction($result['id']);
			$this->transactions[]=$t;
		}
		
		return true;
	
	}
    
	public function getTitulaire(){
		
		if(!isset($this->titulaire) || $this->titulaire->id != $this->id_titulaire) $this->titulaire = new User($this->id_titulaire);
		return $this->titulaire;
		
	}

	public function getType(){
		
		if(!isset($this->type) || $this->type->id != $this->id_type) $this->type = new TypeCompte($this->id_type);
		return $this->type;
		
	}

	public function getNbTransactions($date = ""){
		
		if($date != ""){
			$q = "SELECT COUNT(*) AS nb FROM ".db_table_name('transactions')." WHERE id_compte = $this->id AND date_transaction <= '$date' AND id_utilisateur = ".$_SESSION["user_id"]."";
			
			$res = mysql_query($q);
	
	    	if (!$res) {
	    		throw new MyException('Invalid query: ' . mysql_error());
			}
			
	    	$result = mysql_fetch_assoc($res);
	    	$retour = $result['nb'];
		}else{
			$retour = $this->nb_transactions;
		}
		
		return $retour;
		
	}
	
	public function getSolde($date_fin = ""){
		
		if($this->getNbTransactions($date_fin) == 0) {
			return $this->solde_ouverture;
		}
		
		$max_date = $this->getLatestTransactionDate();
		
		
		if($date_fin == "" || $date_fin > $max_date) $date_fin = $max_date;
		
		$q2 = "SELECT SUM(montant) AS solde FROM ".db_table_name('transactions')." WHERE id_compte = $this->id AND date_transaction <= '$date_fin' AND id_utilisateur = ".$_SESSION["user_id"];
		
		//$q2 = "SELECT solde FROM soldes WHERE id_compte = $this->id AND date = '$date_fin'";
		$res2 = mysql_query($q2);

		if (!$res2) {
			throw new MyException('Invalid query: $q2' . mysql_error());
		}

		$nb_res2 = mysql_num_rows($res2);

		if($nb_res2 == 1){
			$result2 = mysql_fetch_assoc($res2);
			$solde = $this->solde_ouverture + $result2['solde'];
		}else{
			return false;
		}
		
		return ($solde);
	}
	
	public function getSoldes($date_fin, $date_debut, $interval){
		
		$min_date = '2009-04-04';
		$max_date = $this->getLatestTransactionDate();
		
		$dates = generateDateArray($date_debut, $date_fin, $interval);
		
		
		$solde = array();
		
		//pour chaque date trouvée, recherche du solde des comptes
		foreach($dates as $date){
			$solde_cpt = $this->getSolde($date);
			$solde[$date] += $solde_cpt;
		}
		return $solde;
	}

	
	public function actualiserSolde($date_solde = ""){
		
		$this->solde_courant = $this->getSolde();
		
		$q = "UPDATE ".db_table_name('comptes')." SET solde_courant = ".$this->solde_courant." WHERE id = ".$this->id." AND id_utilisateur = ".$_SESSION["user_id"];
		
		$res = mysql_query($q);

		if (!$res) {
			throw new MyException('Invalid query: '.$q . mysql_error());
		}
			
    	return true;
	}
	
	public function updateCategories(){
		if(!$this->transactions_loaded) $this->chargerTransactionsParDate();
		
		foreach($this->transactions as $t){
			if($t->id_categorie == 0){
				$t->setCategorie();
			}
		}
	}
	
	public function setOrdre($ordre){
		$this->ordre = $ordre;
		
		$q = "UPDATE ".db_table_name('comptes')." SET ordre = ".$this->ordre." WHERE id = ".$this->id." AND id_utilisateur = ".$_SESSION["user_id"];
		$res = mysql_query($q);
		if (!$res) {
    		throw new MyException('Invalid query: ' . mysql_error());
		}
		
    	return true;
	}
	
	
	
	//extraction des flux entre 2 dates
	// utilisé dans : synthese.php
	public function getFluxEntreDates($is_positif = true, $is_negatif = true, $id_categories = array(), $date_fin = "", $date_debut = ""){
		
		if($is_positif && !$is_negatif){
			$where_positif = " AND t.montant > 0";
		}
		if($is_negatif && !$is_positif){
			$where_negatif = " AND t.montant < 0";
		}
		
		
		if(count($id_categories)>0){
			$where_cat = " AND (";
			$nb = 0;
			foreach($id_categories as $id_categorie){
				$where_cat .= "id_categorie = $id_categorie";
				$nb++;
				if($nb != count($id_categories)){
					$where_cat .=" OR ";
				}
			}
			$where_cat .=")";
		}else{
			//$where_cat = " AND cat.statistique = 1 ";
		}
		if($date_debut != "") $where_debut = " AND t.date_transaction >= '$date_debut'";
		if($date_fin != "") $where_fin = " AND t.date_transaction <= '$date_fin'";
		
		$q = "SELECT SUM(t.montant) as flux 
					FROM ".db_table_name('transactions')." AS t
					WHERE t.id_compte = $this->id $where_cat $where_debut $where_fin $where_positif $where_negatif AND t.id_utilisateur = ".$_SESSION["user_id"]."
					";
		
		$res = mysql_query($q);
		if (!$res) {
    		throw new MyException('Invalid query: ' .$q. mysql_error());
		}
		while($result = mysql_fetch_assoc($res)){
			$flux = $result['flux'];
		}
		
		return $flux;
	
	}
	
	// utilisé par : graph.php
	public function getFluxParMois($is_positif = true, $is_negatif = true, $id_categories = array(), $date_fin = "", $date_debut = ""){
		
		if($is_positif && !$is_negatif){
			$where_positif = " AND t.montant > 0";
		}
		if($is_negatif && !$is_positif){
			$where_negatif = " AND t.montant < 0";
		}
		
		
		if(count($id_categories)>0){
			$where_cat = " AND (";
			$nb = 0;
			foreach($id_categories as $id_categorie){
				$where_cat .= "id_categorie = $id_categorie";
				$nb++;
				if($nb != count($id_categories)){
					$where_cat .=" OR ";
				}
			}
			$where_cat .=")";
		}
		if($date_debut != "") $where_debut = " AND date_transaction >= '$date_debut'";
		if($date_fin != "") $where_fin = " AND date_transaction <= '$date_fin'";
		
		$q = "SELECT YEAR(date_transaction) AS annee, MONTH(date_transaction) AS mois, 1 AS jour, SUM(montant) as flux 
					FROM ".db_table_name('transactions')." AS t
					WHERE id_compte = $this->id $where_cat $where_debut $where_fin $where_positif $where_negatif AND t.id_utilisateur = ".$_SESSION["user_id"]."
					GROUP BY YEAR(date_transaction), MONTH(date_transaction)
					ORDER BY annee, mois";
		
		$res = mysql_query($q);
		if (!$res) {
    		throw new MyException('Invalid query: ' .$q. mysql_error());
		}
		while($result = mysql_fetch_assoc($res)){
			$flux[$result['annee']."-".str_pad($result['mois'],2,'0',STR_PAD_LEFT)."-".str_pad($result['jour'],2,'0',STR_PAD_LEFT)] = $result['flux'];
		}
		
		return $flux;
	
	}
	
	// utilisé par : graph.php
	public function getFluxParCategorie($is_positif = true, $is_negatif = true, $id_categories = array(), $date_fin = "", $date_debut = ""){
		
		if($is_positif && !$is_negatif){
			$where_positif = " AND t.montant > 0";
		}
		if($is_negatif && !$is_positif){
			$where_negatif = " AND t.montant < 0";
		}
		
		if(count($id_categories)>0){
			$where_cat = " AND (";
			$nb = 0;
			foreach($id_categories as $id_categorie){
				$where_cat .= "t.id_categorie = $id_categorie";
				$nb++;
				if($nb != count($id_categories)){
					$where_cat .=" OR ";
				}
			}
			$where_cat .=")";
		}
		if($date_debut != "") $where_debut = " AND t.date_transaction >= '$date_debut'";
		if($date_fin != "") $where_fin = " AND t.date_transaction <= '$date_fin'";
		
		$q = "SELECT cat.nom AS categorie, SUM(t.montant) as flux 
			FROM ".db_table_name('categories')." AS cat, ".db_table_name('transactions')." AS t 
			WHERE id_compte = $this->id AND t.id_categorie=cat.id $where_positif $where_negatif $where_cat $where_debut $where_fin AND t.id_utilisateur = ".$_SESSION["user_id"]."
			GROUP BY categorie
			ORDER BY cat.ordre
			";
		
		$res = mysql_query($q);
		if (!$res) {
    		throw new MyException('Invalid query: ' .$q. mysql_error());
		}
		while($result = mysql_fetch_assoc($res)){
			$flux[$result['categorie']] = $result['flux'];
		}
		
		return $flux;
		
	}
	
	public function getLatestTransactionDate(){
		
		if(!isset($this->latest_transaction_date)){
			$q = "SELECT MAX(date_transaction) AS max_date FROM ".db_table_name('transactions')." WHERE id_compte = $this->id AND id_utilisateur = ".$_SESSION["user_id"];
			
			$res = mysql_query($q);
			if (!$res) {
				throw new MyException('Invalid query: ' .$q. mysql_error());
			}
			
			$result = mysql_fetch_assoc($res);
			
			$this->latest_transaction_date = $result['max_date'];
		}
		return $this->latest_transaction_date;
		
	}
	
	public function getLatestImportDate(){
		
		if(!isset($this->latest_import_date)){
			$q = "SELECT MAX(created) AS max_date FROM ".db_table_name('transactions')." WHERE id_compte = $this->id AND id_utilisateur = ".$_SESSION["user_id"];
			
			$res = mysql_query($q);
			if (!$res) {
				throw new MyException('Invalid query: ' .$q. mysql_error());
			}
			
			$result = mysql_fetch_assoc($res);
			
			$this->latest_import_date = $result['max_date'];
		}
		return $this->latest_import_date;
		
	}
	
	public function getMinFutureTransactionDate(){
		
		$q = "SELECT MIN(date_transaction) AS min_date FROM ".db_table_name('transactions')."_futures WHERE id_compte = $this->id AND id_utilisateur = ".$_SESSION["user_id"];
		
		$res = mysql_query($q);
		if (!$res) {
    		throw new MyException('Invalid query: ' .$q. mysql_error());
		}
		
		$result = mysql_fetch_assoc($res);
		
		return $result['min_date'];
		
	}
	
	public function getTransactionsFutures($date_max_transac = null){
		
		if(!isset($date_max_transac)) $date_max_transac  = $this->getLatestTransactionDate();
		
		$q = "SELECT date, SUM(tf.montant) AS montant FROM ".db_table_name('transactions')."_futures AS tf WHERE id_compte = $this->id AND date > '$date_max_transac' AND date <= DATE_ADD('$date_max_transac', INTERVAL 1 MONTH) AND id_utilisateur = ".$_SESSION["user_id"]." GROUP BY date";
		
		$res = mysql_query($q);
		if (!$res) {
    		throw new MyException('Invalid query: ' .$q. mysql_error());
		}
		while($result = mysql_fetch_assoc($res)){
			$t = new Transaction();
			$t->date_transaction = $result['date'];
			$t->montant = $result['montant'];
			$this->transactions_futures[] = $t;
		}
		
		return true;
		
	}
	
	public function incrementeTransactionsFutures($date_max_transac = null){
		
		if(!isset($date_max_transac)) $date_max_transac  = $this->getLatestTransactionDate();
		
		$q = "UPDATE ".db_table_name('transactions')."_futures SET date = DATE_ADD(date, INTERVAL 1 MONTH) WHERE id_compte = $this->id AND date <= '$date_max_transac' AND id_utilisateur = ".$_SESSION["user_id"];
		
	
		
		$res = mysql_query($q);
		if (!$res) {
    		throw new MyException('Invalid query: ' .$q. mysql_error());
		}
		
		return true;
	}
	
	// recalcule les transactions récurrentes (ecrase le précédent calcul pour le compte donné)
	public function setTransactionsFutures(){
		
		$_SEUIL_MOY_MENSU = 1.5; //seuil au dela duquel si le total mois dépasse SEUIL x la moyenne, le mois est neutralisé
		$_INTERVAL_SQL = "1 YEAR"; // durée rétrospective en MySQL (function DATE_SUB)
		$_NB_MOIS_INTERVAL = 12; // nombre de mois de la durée pour le calcul de la moyenne


		
		
		/////// NEGATIF

		$q_neg = "SELECT t.id_compte, YEAR(t.date_transaction) AS annee, MONTH(t.date_transaction) AS mois, SUM(t.montant) AS total FROM ".db_table_name('transactions')." AS t INNER JOIN ".db_table_name('categories')." AS c ON t.id_categorie = c.id WHERE c.statistique = 1 AND t.montant < 0 AND t.date_transaction >= DATE_SUB( DATE_FORMAT(NOW() ,'%Y-%m-01'), INTERVAL $_INTERVAL_SQL) AND t.id_compte = $this->id AND t.id_utilisateur = ".$_SESSION["user_id"]." GROUP BY t.id_compte, annee, mois";
		$res_neg = mysql_query($q_neg);
		if (!$res_neg) {
			throw new MyException('Invalid query: ' . mysql_error());
		}

		$montant_moyen_neg = 0;
		$nb_mois_neg = 0;
		while($result_neg = mysql_fetch_assoc($res_neg)){

			$montant_mensuel_neg[$result_neg['annee'].'-'.$result_neg['mois']] = $result_neg['total'];
			$nb_mois_neg ++;
			$montant_moyen_neg += $result_neg['total'];
		}	
		if($nb_mois_neg > 0) $montant_moyen_neg = $montant_moyen_neg / $nb_mois_neg;

		//neutralisation des mois > à X fois la moyenne
		// creation de la condition de requete
		$where = "";
		$nb_mois_exclus = 0;
		$nb_mois_exclus_31_jours = 0;
		if(count($montant_mensuel_neg)>0){
			foreach($montant_mensuel_neg as $key => $value){
			
				if(abs($value) > $_SEUIL_MOY_MENSU * abs($montant_moyen_neg)){
					unset($montant_mensuel_neg[$key]);
					
					$t_key = explode('-', $key);
					
					//compteur de mois exclus
					$nb_mois_exclus ++;
					
					//compteur de mois de 31 jours exclus
					if(checkdate($t_key[1],31,$t_key[0])) $nb_mois_exclus_31_jours ++;
					
					$date = $key."-1";
					
					$where .= " AND NOT (t.date_transaction <= (SELECT DATE_SUB(DATE_ADD( DATE_FORMAT('$date' ,'%Y-%m-01'), INTERVAL 1 MONTH), INTERVAL 1 DAY)) AND t.date_transaction >= (SELECT DATE_FORMAT('$date' ,'%Y-%m-01'))) ";
				}
			}
		}
			
		//recuperation des moyennes mensuelles pour les mois non neutralisés	
		$q_neg = "SELECT t.id_compte, DAYOFMONTH(t.date_transaction) AS no_jour, SUM(t.montant) AS total FROM ".db_table_name('transactions')." AS t INNER JOIN ".db_table_name('categories')." AS c ON c.id=t.id_categorie WHERE c.statistique = 1 AND t.id_compte = $this->id AND t.montant < 0 AND t.date_transaction >= DATE_SUB( DATE_FORMAT(NOW() ,'%Y-%m-01'), INTERVAL $_INTERVAL_SQL) $where AND t.id_utilisateur = ".$_SESSION["user_id"]." GROUP BY t.id_compte, no_jour";
		
		$res_neg = mysql_query($q_neg);
		if (!$res_neg) {
			throw new MyException('Invalid query: ' . mysql_error());
		}

		$montant_quotidien_neg = array();
		for($i=1; $i<=31; $i++){
			$montant_quotidien_neg[$i] = 0;
		}
		//calcul de la moyenne mensuelle
		while($result_neg = mysql_fetch_assoc($res_neg)){
			if($result_neg['no_jour'] == 31){
				$montant_quotidien_neg[$result_neg['no_jour']] = floor($result_neg['total'] / ($_NB_MOIS_INTERVAL - $nb_mois_exclus_31_jours));
			}else{
				$montant_quotidien_neg[$result_neg['no_jour']] = floor($result_neg['total'] / ($_NB_MOIS_INTERVAL - $nb_mois_exclus));
			}
		}	

		
		//////////////// POSITIF
		$q_pos = "SELECT t.id_compte, YEAR(t.date_transaction) AS annee, MONTH(t.date_transaction) AS mois, SUM(t.montant) AS total FROM ".db_table_name('transactions')." AS t INNER JOIN ".db_table_name('categories')." AS c ON t.id_categorie = c.id WHERE c.statistique = 1 AND t.montant > 0 AND t.date_transaction >= DATE_SUB( DATE_FORMAT(NOW() ,'%Y-%m-01'), INTERVAL $_INTERVAL_SQL) AND t.id_compte = $this->id AND t.id_utilisateur = ".$_SESSION["user_id"]." GROUP BY t.id_compte, annee, mois";
		$res_pos = mysql_query($q_pos);
		if (!$res_pos) {
			throw new MyException('Invalid query: ' . mysql_error());
		}

		$montant_moyen_pos = 0;
		$nb_mois_pos = 0;
		while($result_pos = mysql_fetch_assoc($res_pos)){

			$montant_mensuel_pos[$result_pos['annee'].'-'.$result_pos['mois']] = $result_pos['total'];
			$nb_mois_pos ++;
			$montant_moyen_pos += $result_pos['total'];
		}	
		if($nb_mois_pos > 0) $montant_moyen_pos = $montant_moyen_pos / $nb_mois_pos;

		//neutralisation des mois > à X fois la moyenne
		// creation de la condition de requete
		$where = "";
		$nb_mois_exclus = 0;
		$nb_mois_exclus_31_jours = 0;
		if(count($montant_mensuel_pos) > 0){
			foreach($montant_mensuel_pos as $key => $value){
			
				if(abs($value) > $_SEUIL_MOY_MENSU * abs($montant_moyen_pos)){
					unset($montant_mensuel_pos[$key]);
					
					$t_key = explode('-', $key);
					
					//compteur de mois exclus
					$nb_mois_exclus ++;
					
					//compteur de mois de 31 jours exclus
					if(checkdate($t_key[1],31,$t_key[0])) $nb_mois_exclus_31_jours ++;
					
					$date = $key."-1";
					
					$where .= " AND NOT (t.date_transaction <= (SELECT DATE_SUB(DATE_ADD( DATE_FORMAT('$date' ,'%Y-%m-01'), INTERVAL 1 MONTH), INTERVAL 1 DAY)) AND t.date_transaction >= (SELECT DATE_FORMAT('$date' ,'%Y-%m-01'))) ";
				}
			}
		}
			
		//recuperation des moyennes mensuelles pour les mois non neutralisés	
		$q_pos = "SELECT t.id_compte, DAYOFMONTH(t.date_transaction) AS no_jour, SUM(t.montant) AS total FROM ".db_table_name('transactions')." AS t INNER JOIN ".db_table_name('categories')." AS c ON c.id=t.id_categorie WHERE c.statistique = 1 AND t.id_compte = $this->id AND t.montant > 0 AND t.date_transaction >= DATE_SUB( DATE_FORMAT(NOW() ,'%Y-%m-01'), INTERVAL $_INTERVAL_SQL) $where AND t.id_utilisateur = ".$_SESSION["user_id"]." GROUP BY t.id_compte, no_jour";
		$res_pos = mysql_query($q_pos);
		if (!$res_pos) {
			throw new MyException('Invalid query: ' . mysql_error());
		}

		$montant_quotidien_pos = array();
		for($i=1; $i<=31; $i++){
			$montant_quotidien_pos[$i] = 0;
		}
		//calcul de la moyenne mensuelle
		while($result_pos = mysql_fetch_assoc($res_pos)){
			if($result_pos['no_jour'] == 31){
				$montant_quotidien_pos[$result_pos['no_jour']] = floor($result_pos['total'] / ($_NB_MOIS_INTERVAL - $nb_mois_exclus_31_jours));
			}else{
				$montant_quotidien_pos[$result_pos['no_jour']] = floor($result_pos['total'] / ($_NB_MOIS_INTERVAL - $nb_mois_exclus));
			}
		}	

		///////// FINAL
		for($i=1; $i<=31; $i++){
			$montant_quotidien[$i] = $montant_quotidien_pos[$i] + $montant_quotidien_neg[$i];
		}
		
		
		//suppression des précédents transactions calculées
		$q = "DELETE FROM ".db_table_name('transactions')."_futures WHERE id_compte = $this->id AND id_utilisateur = ".$_SESSION["user_id"];
		$res = mysql_query($q);
		if (!$res) {
			throw new MyException("Invalid query $q: " . mysql_error());
		}
		
		$t_date_max_transac  = explode('-',$this->getLatestTransactionDate());
		
		foreach($montant_quotidien as $key => $value){
			
			if($value != 0){
				if(checkdate($t_date_max_transac[1], $key, $t_date_max_transac[0])){
					$date_transac = $t_date_max_transac[0].'-'.$t_date_max_transac[1].'-'.$key;
					$q = "INSERT INTO ".db_table_name('transactions')."_futures (id_compte, date, montant, libelle, id_utilisateur ) VALUES ($this->id, '$date_transac', $value, CONCAT('AUTO CALC DAY $key ON ', CURDATE()), '".$_SESSION["user_id"]."')";
					$res = mysql_query($q);
					if (!$res) {
						throw new MyException("Invalid query $q: " . mysql_error());
					}
				}
			}
		}
		
		$this->incrementeTransactionsFutures();
		
		return true;
		
	}
}

?>