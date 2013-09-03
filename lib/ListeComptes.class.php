<?php

class ListeComptes{
	
	//http://www.croes.org/gerald/blog/le-singleton-en-php/17/
	private static $_instance = false;
	
	
	public $comptes = array();
	public $transactions = array();
	public $liste_categories = array();
	public $liste_budgets = array();
	
	private function __construct (){}
	private function __clone (){}
	
	public static function getInstance (){
      //Si l'instance n'existe pas encore, alors elle est créée.
      if (self::$_instance === false){
         self::$_instance = new ListeComptes ();
      }
      //L'instance existe, on peut la retourner à l'extérieur.
      return self::$_instance;
   }
	
	function getNbComptes($charger_cloture = false){
		if($charger_cloture){
			$where = 'cloture = 1';
		}else{
			$where = 'cloture = 0';
		}
		
		$q = "SELECT COUNT(id) AS nb_comptes FROM ".db_table_name('comptes')." WHERE $where AND id_utilisateur = ".$_SESSION["user_id"]." ORDER BY ordre ASC";
		
		$res = mysql_query($q);

		if (!$res) {
			throw new MyException('Invalid query: ' . mysql_error());
		}
		
		$result = mysql_fetch_assoc($res);
		return $result['nb_comptes'];
	}
	
	function getListeComptesParType($id_type){
		
		unset($this->comptes);
		$this->comptes = array();
		
		$q = "SELECT id FROM ".db_table_name('comptes')." WHERE id_type = $id_type AND id_utilisateur = ".$_SESSION["user_id"]." ORDER BY ordre ASC";
			
			$res = mysql_query($q);

			if (!$res) {
				throw new MyException('Invalid query: '.$q . mysql_error());
			}

			while ($result = mysql_fetch_assoc($res)) {

				$c = new Compte($result['id']);
				$this->comptes[] = $c;
				
			}
	}
	
	public function getFluxParMois($is_positif = true, $is_negatif = true, $id_comptes = array(), $id_categories = array(), $date_fin = "", $date_debut = ""){
		
		$flux = array();
		
		foreach(Compte::getListe() as $c){
			if(count($id_comptes)>0){
				foreach($id_comptes as $id_compte){
					if($c->id == $id_compte){
						$flux_cpt = $c->getFluxParMois($is_positif, $is_negatif, $id_categories, $date_fin, $date_debut);
						if(count($flux_cpt)>0){
							foreach($flux_cpt as $mois => $flux_cpt_mois){
								$flux[$mois] += $flux_cpt_mois;
							}
						}
					}
				}
			}else{

				$flux_cpt = $c->getFluxParMois($is_positif, $is_negatif, $id_categories, $date_fin, $date_debut);

				if(count($flux_cpt)>0){

					foreach($flux_cpt as $mois => $flux_cpt_mois){

						$flux[$mois] += $flux_cpt_mois;

					}

				}

			}

			

		}

		ksort($flux);

		return $flux;

		

	}

	

	public function getFluxParMoisParCategorie($id_comptes = array(), $id_categories = array(), $date_fin = "", $date_debut = ""){

		

		$flux = array();

		

		foreach(Compte::getListe() as $c){

			

			if(count($id_comptes)>0){

				foreach($id_comptes as $id_compte){

					if($c->id == $id_compte){

						$flux_cpt = $c->getFluxParMoisParCategorie($id_categories, $date_fin, $date_debut);

						if(count($flux_cpt)>0){

							foreach($flux_cpt as $mois => $flux_cpt_mois){

								if(count($flux_cpt_mois)>0){

									foreach($flux_cpt_mois as $categorie => $flux_cpt_mois_cat){

										$flux[$mois][$categorie] += $flux_cpt_mois_cat;

									}

								}					

							}

						}

					}

				}

			}else{

				$flux_cpt = $c->getFluxParMoisParCategorie($id_categories, $date_fin, $date_debut);

				if(count($flux_cpt)>0){

					foreach($flux_cpt as $mois => $flux_cpt_mois){

						if(count($flux_cpt_mois)>0){

							foreach($flux_cpt_mois as $categorie => $flux_cpt_mois_cat){

								$flux[$mois][$categorie] += $flux_cpt_mois_cat;

							}

						}					

					}

				}

			}

			

			

			

		}

		

		return $flux;

		

	}

	

	public function getFluxParCategorie($is_positif = true, $is_negatif = true, $id_comptes = array(), $id_categories = array(), $date_fin = "", $date_debut = ""){

		$flux = array();

		

		foreach(Compte::getListe() as $c){

			

			if(count($id_comptes)>0){

				foreach($id_comptes as $id_compte){

					if($c->id == $id_compte){

						$flux_cpt = $c->getFluxParCategorie($is_positif, $is_negatif, $id_categories, $date_fin, $date_debut);

						if(count($flux_cpt)>0){

							foreach($flux_cpt as $categorie => $flux_cpt_cat){

								$flux[$categorie] += $flux_cpt_cat;

							}

								

						}

					}

				}

			}else{

				$flux_cpt = $c->getFluxParCategorie($is_positif, $is_negatif, $id_categories, $date_fin, $date_debut);

				if(count($flux_cpt)>0){

					foreach($flux_cpt as $categorie => $flux_cpt_cat){

						$flux[$categorie] += $flux_cpt_cat;

					}

						

				}

			}

			

			

			

		}

		

		return $flux;

	}

	

	public function chargerTransactions($id_comptes = array(), $id_categories = array(), $id_budgets = array(), $date_fin = null, $date_debut = null, $query = null){

		

		if(count($id_comptes)>0){

			$where_cpt = " AND (";

			$nb = 0;

			foreach($id_comptes as $id_compte){

				$where_cpt .= "t.id_compte = $id_compte";

				$nb++;

				if($nb != count($id_comptes)){

					$where_cpt .=" OR ";

				}

			}

			$where_cpt .=")";

		}

		if(count($id_categories)>0){

			$from_cat = ", ".db_table_name('categories')." AS cat";

			$where_cat = " AND t.id_categorie=cat.id  AND (";

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

		if(count($id_budgets)>0){

			$from_budg = ", ".db_table_name('budgets')." AS b";

			$where_budg = " AND t.id_budget=b.id  AND (";

			$nb = 0;

			foreach($id_budgets as $id_budget){

				$where_budg .= "t.id_budget = $id_budget";

				$nb++;

				if($nb != count($id_budgets)){

					$where_budg .=" OR ";

				}

			}

			$where_budg .=")";

		}

		if($date_debut != null && $date_debut != "") $where_debut = " AND t.date_transaction >= '$date_debut'";

		if($date_fin != null & $date_fin != "") $where_fin = " AND t.date_transaction <= '$date_fin'";

		

		if($query != null && $query != ""){

			$where_query = " AND ( ";

			if(is_numeric($query)){

				$where_query .= "(t.montant >= $query * (1-0.15) AND t.montant <= $query * (1+0.15))";

				$where_montant = true;

			}elseif(is_string($query)){

				if($where_montant) $where_query .= " OR ";

				$where_query .= "t.libelle LIKE '%$query%' OR t.commentaire LIKE '%$query%' OR b.nom LIKE '%$query%' ";
				
				if($from_budg == ''){
					$from_budg = ", ".db_table_name('budgets')." AS b";
					$where_budg = " AND t.id_budget=b.id ";
					$where_query .= "";
				}

			}else{
				$where_query .="1";
			}

			$where_query .=")";

		}

		

		$q = "SELECT t.id AS id FROM ".db_table_name('transactions')." AS t, ".db_table_name('comptes')." AS cpt $from_cat $from_budg

					WHERE t.id_compte=cpt.id $where_cpt $where_cat $where_budg $where_debut $where_fin $where_query AND t.id_utilisateur = ".$_SESSION["user_id"]."

					ORDER BY date_transaction DESC, cpt.ordre ASC

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

	

	public function getSoldes($id_comptes = array(), $date_fin = "", $date_debut = "", $interval = "+1 week"){

		
		

		$min_date = '2009-04-04';

		

		$max_date = $this->getMaxTransactionDate($id_comptes);

		

		

		

		

		if($date_fin == "" || $date_fin > $max_date) $date_fin = $max_date;

		

		if($date_debut == "" || $date_debut < $min_date){

			$date_debut = $min_date;

		}else if($date_debut > $max_date){

			$date_debut = $max_date;

		}

		

		$dates = generateDateArray($date_debut, $date_fin, $interval);

		

		

		$solde = array();

		

		//pour chaque date trouvée, recherche du solde des comptes



		foreach(Compte::getListe() as $c){

			if(count($id_comptes)>0){

				foreach($id_comptes as $id_compte){

					if($c->id == $id_compte){

						$soldes[$c->id] = $c->getSoldes($date_fin, $date_debut, $interval);

					}

				}

			}else{

				$soldes[$c->id] = $c->getSoldes($date_fin, $date_debut, $interval);

			}		

		}







		foreach($dates as $date){

			$solde[$date] = 0;

			

			foreach($soldes as $solde_cpt){

				$solde[$date] += $solde_cpt[$date];

			}

			

			

		}

		return $solde;

	}

	

	

	public function getMaxTransactionDate($id_comptes = array()){

		

		
		

		$min_date = '2009-04-04';

		

		$max_date = $min_date;

		foreach(Compte::getListe() as $c){

			if(count($id_comptes) > 0){

				foreach($id_comptes as $id_compte){

					if($c->id == $id_compte){

						$max_date_cpt = $c->getLatestTransactionDate();

						if($max_date < $max_date_cpt) $max_date = $max_date_cpt;

					}

				}

			}else{

				$max_date_cpt = $c->getLatestTransactionDate();

				if($max_date < $max_date_cpt) $max_date = $max_date_cpt;

			}

		}

		

		

		return $max_date;

		

	}
	
	
}



?>