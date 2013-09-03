<?php

class Categorie extends Catalogue {
	
	public $table = "categories";
	public $sort = "ordre";
	
	public $pattern;
	public $ordre;
	
	
	public function setOrdre($ordre){
		$this->ordre = $ordre;
		
		$q = "UPDATE ".db_table_name($this->table)." SET ordre = $this->ordre WHERE id = $this->id AND id_utilisateur = ".$_SESSION["user_id"];
		
		
		$res = mysql_query($q);
		if (!$res) {
			return false;
		}
		
		return true;
		
		
	}
	
	public function getAverageTotalAmountPerMonth(){
		
		$q = "SELECT MONTH(t.date_transaction) AS mois, SUM(t.montant) AS montant FROM ".db_table_name('transactions')." AS t WHERE t.id_categorie = $this->id AND id_utilisateur = ".$_SESSION["user_id"] . " GROUP BY t.id_categorie";
		
		$res = mysql_query($q);

    	if (!$res) {
    		throw new MyException('Invalid query: ' . mysql_error());
		}
		
		$nb_mois = 0;
		while ($result = mysql_fetch_assoc($res)) {
			$nb_mois ++;
			$montant += $result['montant'];
		}
		
		if($nb_mois > 0){
			$moy = $montant / $nb_mois;
		}else{
			$moy = 0;
		}
		
		return $moy;
		
	}
	
}

?>