<?php
require_once(dirname(__FILE__) .'/inc/init.inc.php');



//recupération du compte
try{
	$c = new Compte($_GET['idc']);
}catch(Exception $e){
	$response['error'] = "Impossible de trouver le compte : " . $e->getMessage();
	$response['status'] = false;
	//echo json_encode($response);
	exit;
}

try{
	if($c->is_synthese){
		$c->setTransactionsFutures();
	}
	
	
}catch(Exception $e){
	$response['error'] = "Erreur à l'actualisation des données du compte : " + $e->getMessage();
	$response['status'] = false;
	//echo json_encode($response);
	exit;
}

header('Location:./afficher_synthese.php');

?>