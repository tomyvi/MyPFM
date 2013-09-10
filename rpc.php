<?php
$_RPC = true;
require_once(dirname(__FILE__) ."/inc/init.inc.php");
header('Content-Type: application/json; charset=UTF-8');

$response = array();

if($_GET){
	
	$type = $_GET['type'];
	
	$lc = ListeComptes::getInstance();
	
	switch($type){
		case 'PING':
			
			break;
		case 'GET_TRANSAC_CPT':
			try {
				$id_compte = $_GET['idc'];
				$start = $_GET['start'];
				$nb = $_GET['nb'];
				
				$c = new Compte($id_compte);
				$c->chargerTransactionsParNb($start,$nb);
				
				$response = $c->transactions;
			}catch(Exception $e){
				
			}
			break;
		case "SET_CAT":
			try {
				$id_transac = $_GET['idt'];
				$id_cat = $_GET['idcat'];
				$t = new Transaction($id_transac);
			
				$t->setCategorie($id_cat);
				$response['status'] = true;
				$response['transaction'] = $t;
			}catch(Exception $e){
				$response['status'] = false;
				$response['error'] = "Impossible de sauvegarder la catégorie !\n" . $e->getMessage();
			}
			
			
			break;
		case "ADD_TRANSAC":
			$response['old_id'] = $_GET['id'];
			$id_compte = $_GET['id_compte'];
			$libelle = $_GET['libelle'];
			$date_transaction = $_GET["date"];
			$commentaire = $_GET['commentaire'];
			$id_categorie = $_GET['id_categorie'];
			$id_budget = $_GET['id_budget'];
			$montant = $_GET['montant'];
			$id_import = $_GET['id_import'];
			
			$t = new Transaction();
			$t->id_compte = $id_compte;
			$t->date_transaction = $date_transaction;
			$t->libelle = ($libelle);
			$t->commentaire = ($commentaire);
			$t->id_categorie = $id_categorie;
			$t->id_budget = $id_budget;
			$t->montant = $montant;
			$t->devise = "EUR";
			$t->id_import = $id_import;
			
			try {
				$check_for_duplicates = ($_GET['force']==0);
				$t->ajouter($check_for_duplicates);
				$t->getCategorie();
				
				
				$response['status'] = true;	
				$response['transaction'] = $t;
			}catch(DuplicateTransactionException $de){
				$response['status'] = true;	
				$response['duplicate']['transaction'] = new Transaction($t->getDuplicateID());
				$response['duplicate']['duplicate'] = $t;
				
			}catch(Exception $e){
				$response['status'] = false;
				$response['error'] = "Impossible d'ajouter la transaction !\n" . $e->getMessage();
			}
			
			
			
			break;
		case "GET_TRANSAC":
			try {
				$id_transac = $_GET['idt'];
				$t = new Transaction($id_transac);
				
				
				$response = $t;
			}catch(Exception $e){
				
			}
			break;
		case "UPDATE_TRANSAC":
			try {
				$t = new Transaction($_GET['id_transac']);
				
				if($t->montant != $_GET['montant']) $response['nouveau_solde'] = true;
				if($t->id_categorie != $_GET['id_categorie']) $response['nouvelle_categorie'] = true;
				if($t->id_budget != $_GET['id_budget']) $response['nouveau_budget'] = true;
				
				$tab_date = explode("/",$_GET["date"]);
				$annee = $tab_date[2];
				$mois = $tab_date[1];
				$jour = $tab_date[0];
				$_GET["date"] = $annee."-".$mois."-".$jour;
				
				$t->date_transaction = $_GET['date'];
				$t->libelle = ($_GET['libelle']);
				$t->commentaire = ($_GET['commentaire']);
				$t->montant = $_GET['montant'];
				$t->id_categorie = $_GET['id_categorie'];
				$t->id_budget = $_GET['id_budget'];
			
				$t->update();
				$t->getCompte();
				$t->getCategorie();
				
				$response['status'] = true;
				$response['transaction'] = $t;
			}catch(Exception $e){
				$response['status'] = false;
				$response['error'] = "Impossible de mettre à jour la transaction !\n" . $e->getMessage();
			}
			
			
			
			break;
		case "DELETE_TRANSAC":
			try {
				$t = new Transaction($_GET['id_transac']);
				$t->delete();
				$response['transaction'] = $t;
				$response['status'] = true;
			}catch(Exception $e){
				$response['status'] = false;
				$response['error'] = "Impossible de supprimer la transaction !\n" . $e->getMessage();
			}
			
			break;
		case "SEARCH_TRANSAC":
			$id_compte = null;
			$id_categorie = null;
			$id_budget = null;
			$query = null;
			$date_debut = null;
			$date_fin = null;
			
			if(array_key_exists('id_compte', $_GET)) $id_compte = $_GET['id_compte'];
			if(array_key_exists('id_categorie', $_GET)) $id_categorie = $_GET['id_categorie'];
			if(array_key_exists('id_budget', $_GET)) $id_budget = $_GET['id_budget'];
			if(array_key_exists('query', $_GET)) $query = $_GET['query'];
			if(array_key_exists('date_debut', $_GET)) $date_debut = $_GET['date_debut'];
			if(array_key_exists('date_fin', $_GET)) $date_fin = $_GET['date_fin'];
			
			try{
				$lc->chargerTransactions($id_compte,$id_categorie,$id_budget,$date_fin,$date_debut,$query);
				$response['status'] = true;
				$response['nb_transactions'] = count($lc->transactions);
				$response['transactions'] = $lc->transactions;
			}catch(Exception $e){
				$response['status'] = false;
				$response['error'] = "Impossible de charger les transactions !\n" . $e->getMessage();
			}
			
			break;
		case "GET_TRANSAC_MONTH":
		case "GET_TRANSAC_DAY":
			try {
			
				if($type == "GET_TRANSAC_MONTH"){
					$date_debut = substr($_GET['date'],0,8)."01";
					$date_fin = substr($_GET['date'],0,8)."31";
				}
				if($type == "GET_TRANSAC_DAY"){
					$date_debut = $_GET['date'];
					$date_fin = $_GET['date'];
				}
				if(is_array($_GET['id_comptes'])){
					$id_comptes = $_GET['id_comptes'];
				}else{
					$id_comptes = array();
				}
				if(is_array($_GET['id_categories'])){
					$id_categories = $_GET['id_categories'];
				}else{
					$id_categories = array();
				}
				
				$lc->chargerTransactions($id_comptes,$id_categories,array(),$date_fin,$date_debut);
				
				$return_arr = array();
				
				if(count($lc->transactions)>0){
					foreach($lc->transactions as $t){
						
						
						
						if($t->montant>0){
							$credit = number_format($t->montant,2,","," ")." &#8364;";
							$debit = "";
						}else{
							$debit = number_format($t->montant,2,","," ")." &#8364;";
							$credit = "";
						}
						
						$row_array['id'] = $t->id;
						$row_array['id_compte'] = $t->id_compte;
						$row_array['lib_compte'] = $t->getCompte()->libelle;
						$row_array['date'] = affichedate($t->date_transaction);
						$row_array['libelle'] = ("".$t->libelle);
						$row_array['commentaire'] = ("".$t->commentaire);
						$row_array['categorie'] = ("".$t->getCategorie()->nom);
						$row_array['cat_id'] = $t->getCategorie()->id;
						$row_array['credit'] = $credit;
						$row_array['debit'] = $debit;
						
						array_push($return_arr,$row_array);
					}
				}
				$response = $return_arr;
			}catch(Exception $e){
				
			}
			break;
		case "GET_TRANSAC_SEARCH":
			try {
			
				if($_GET['date_debut']!="") $date_debut = returnDate($_GET['dd']);
				if($_GET['date_fin']!="") $date_fin = returnDate($_GET['df']);
				
				if(is_array($_GET['idc'])){
					$id_comptes = $_GET['idc'];
				}else{
					$id_comptes = array();
				}
				if(is_array($_GET['idca'])){
					$id_categories = $_GET['idca'];
				}else{
					$id_categories = array();
				}
				
				
				$lc->chargerTransactions($id_comptes,$id_categories, array(),$date_fin,$date_debut,$_GET['q']);
				
				$return_arr = array();
				
				if(count($lc->transactions)>0){
					foreach($lc->transactions as $t){
						
						
						
						if($t->montant>0){
							$credit = number_format($t->montant,2,","," ")." &#8364;";
							$debit = "";
						}else{
							$debit = number_format($t->montant,2,","," ")." &#8364;";
							$credit = "";
						}
						
						$row_array['id'] = $t->id;
						$row_array['id_compte'] = $t->id_compte;
						$row_array['lib_compte'] = $t->getCompte()->libelle;
						$row_array['date'] = affichedate($t->date_transaction);
						$row_array['libelle'] = ("".$t->libelle);
						$row_array['commentaire'] = ("".$t->commentaire);
						$row_array['categorie'] = ("".$t->getCategorie()->nom);
						$row_array['cat_id'] = $t->getCategorie()->id;
						$row_array['montant'] = $t->montant;
						$row_array['credit'] = $credit;
						$row_array['debit'] = $debit;
						
						array_push($return_arr,$row_array);
					}
				}
				$response = $return_arr;
			}catch(Exception $e){
				
			}
			break;
			
		case "SORT_CAT":
			try {
				$cats = $_GET["cat"];
				for($i = 0; $i < count($cats); $i++){
					$cat = new Categorie($cats[$i]);
					$cat->setOrdre($i+1);
				}
				$response = "OK";
			}catch(Exception $e){
				
			}
			break;
			
		case "GET_LISTE_CAT":
			try {
				$response = Categorie::getListe();
			}catch(Exception $e){
				
			}
			break;
			
		case "GET_LISTE_CPT":
			try {
				$response = Compte::getListe(false);
			}catch(Exception $e){
				
			}
			break;
			
		case "GET_LISTE_CPT_CLOS":
			try {
				$response = Compte::getListe(true);
			}catch(Exception $e){
				
			}
			break;
		
		case "GET_CPT_DATA":
			try {
				$id_cpt = $_GET["idc"].$_GET['id_cpt'];
				$c = new Compte($id_cpt);
				$response = $c;
			
			}catch(Exception $e){
				
			}
			break;
			
		case "GET_CPT":
			try {
				$id_cpt = $_GET["idc"].$_GET['id_cpt'];
				$c = new Compte($id_cpt);
				$c->chargerTransactionsParNb(0,$_config['nb_transac_par_page']);
				
				$response = $c;
			
			}catch(Exception $e){
				
			}
			break;
			
		case "UPDATE_CPT":
			try{
			
				$c = new Compte($_GET['id_cpt']);
				
				if($c->solde_ouverture != $_GET['solde_ouverture']) $response['nouveau_solde'] = true;
				
				$c->libelle = $_GET['libelle'];
				$c->libelle_long = $_GET['libelle_long'];
				$c->etablissement = $_GET['etablissement'];
				$c->url_acces = $_GET['url_acces'];
				$c->id_type = $_GET['id_type'];
				$c->no_compte = $_GET['no_compte'];
				$c->solde_ouverture = $_GET['solde_ouverture'];
				$c->cloture = $_GET['cloture'];
				$c->is_synthese = $_GET['is_synthese'];		
				
				if($response['nouveau_solde']) $c->actualiserSolde();
			
				$c->update();
				$response['status'] = true;
				$response['compte'] = $c;
			}catch(Exception $e){
				$response['status'] = false;
				$response['error'] = "Impossible d'enregistrer les mises à jour !\n" . $e->getMessage();
			}
			
			
			break;
			
		case "ADD_CPT":
		
			try{
				$c = new Compte();
				
				$c->cle = $_GET['cle'];
				$c->libelle = $_GET['libelle'];
				$c->libelle_long = $_GET['libelle_long'];
				$c->etablissement = $_GET['etablissement'];
				$c->url_acces = $_GET['url_acces'];
				$c->id_type = $_GET['id_type'];
				$c->no_compte = $_GET['no_compte'];
				$c->solde_ouverture = $_GET['solde_ouverture'];
				$c->is_synthese = $_GET['is_synthese'];		
			
				$c->ajouter();
				$c->getType();
				$response['status'] = true;
				$response['compte'] = $c;
			}catch(Exception $e){
				$response['status'] = false;
				$response['error'] = "Impossible d'enregistrer le nouveau compte !\n" . $e->getMessage();
			}
			
			break;
			
		case "SORT_CPT":
			try {
				$cpts = $_GET["cpt"];
				for($i = 0; $i < count($cpts); $i++){
					$cpt = new Compte($cpts[$i]);
					$cpt->setOrdre($i+1);
				}
				$response = "OK";
			}catch(Exception $e){
				$response['status'] = false;
				$response['error'] = "Impossible d'enregistrer le nouveau compte !\n" . $e->getMessage();
			}
			break;
			
		case "DELETE_CPT":
			try{
				$c = new Compte($_GET['id_cpt']);
			
				$c->delete();
				$response['status'] = true;	
			}catch(Exception $e){
				$response['status'] = false;
				$response['error'] = "Impossible de supprimer le compte !\n" . $e->getMessage();
			}
			break;
		case "GET_BDG":
			try{
				$b = new Budget($_GET['id_budget']);
				$response['status'] = true;	
				$response['budget'] = $b;	
			}catch(Exception $e){
				$response['status'] = false;
				$response['error'] = "Impossible de charger le budget !\n" . $e->getMessage();
			}
			break;
		case "ADD_BDG":
			try{
				
				$b = new Budget();
				$b->nom = $_GET['nom'];
				$b->montant = $_GET['montant'];
				$b->actif = $_GET['actif'];
				$b->statistiques = $_GET['statistiques'];
				
				$b->create();
				$b->actualiserSolde();
				
				$response['status'] = true;
				$response['budget'] = $b;
				
			}catch(Exception $e){
				$response['status'] = false;
				$response['error'] = "Impossible d'ajouter le budget !\n" . $e->getMessage();
			}
			break;
		case "EDIT_BDG":
			try{
				
				$b = new Budget($_GET['id_budget']);
				
				if($b->montant != $_GET['montant']) $response['nouveau_solde'] = true;
				
				
				$b->nom = $_GET['nom'];
				$b->montant = $_GET['montant'];
				$b->actif = $_GET['actif'];
				$b->statistiques = $_GET['statistiques'];
				
				$b->update();
				
				if($response['nouveau_solde']) $b->actualiserSolde();
				
				
				
				$response['status'] = true;
				$response['budget'] = $b;
				
			}catch(Exception $e){
				$response['status'] = false;
				$response['error'] = "Impossible d'ajouter le budget !\n" . $e->getMessage();
			}
			break;
		case "DELETE_BDG":
			try{
				$b = new Budget($_GET['id_budget']);
			
				$b->delete();
				$response['status'] = true;	
			}catch(Exception $e){
				$response['status'] = false;
				$response['error'] = "Impossible de supprimer le budget !\n" . $e->getMessage();
			}
			break;
		
	}
}

echo json_encode($response);

?>