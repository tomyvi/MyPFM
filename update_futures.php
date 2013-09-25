<?php
require_once(dirname(__FILE__) .'/inc/init.inc.php');



//recupération du compte
try{
	$c = new Compte($_GET['idc']);
}catch(Exception $e){
	$response['error'] = _("Account not found")." : " . $e->getMessage();
	$response['status'] = false;
	//echo json_encode($response);
	exit;
}

try{
	$c->actualiserSolde();
	if($c->is_synthese){
		$c->setTransactionsFutures();
	}
	
	
}catch(Exception $e){
	$response['error'] = _("Error when updating account data")." : " + $e->getMessage();
	$response['status'] = false;
	//echo json_encode($response);
	exit;
}

header('Location:./dashboard.php');

?>