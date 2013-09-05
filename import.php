<?php
require_once(dirname(__FILE__) .'/inc/init.inc.php');
//header('Content-Type: application/json; charset=UTF-8');

$response = array();

//cancel previous import by ID
if(isset($_GET['undoid']) && $_GET['undoid'] != ''){
	
	$q = "SELECT COUNT(*) AS nb_transactions FROM ".db_table_name('transactions')." WHERE id_import = '".$_GET['undoid']."'";
	$res = mysql_query($q);
	$result = mysql_fetch_assoc($res);
	$nb_res = $result['nb_transactions'];
	
	$q = "DELETE FROM ".db_table_name('transactions')." WHERE id_import = '".$_GET['undoid']."'";
	$res = mysql_query($q);
	if (!$res) {
		//throw new MyException('Invalid query: '.$q . mysql_error());
		$response['error'] = _("Unable to delete transactions")." !! $q";
		$response['status'] = false;
		echo json_encode($response);
		exit;
	}

	$response['nb_transactions'] = $nb_res;
	$response['status'] = true;
	echo json_encode($response);
	exit;
	
}




if(count($_FILES) != 1){
	
	//header("Location:afficher.php?import=ko&error=no_file#".$_POST['id_compte']);
	
	$response['error'] = _("No file to process")." !!";
	$response['status'] = false;
	echo json_encode($response);
	exit;
}
if ($_FILES["file"]["error"] > 0){
	//throw new  MyException('Erreur : ' . $_FILES["file"]["error"]);
	
	$response['error'] = _("Unable to process file")." : ".$_FILES["file"]["error"]." !!";
	$response['status'] = false;
	echo json_encode($response);
	exit;
}




//recupération du compte
try{
	$c = new Compte($_GET['idc']);
}catch(Exception $e){
	$response['error'] = _("Account not found")." : " . $e->getMessage();
	$response['status'] = false;
	echo json_encode($response);
	exit;
}

//debug($c);
//exit;

//détection du type de traitement à appliquer
switch($_FILES['file']['type']){
	case 'application/vnd.ms-excel':
	case 'application/x-ecriture-txt':
		$file_type = 'CSV';
		
		break;
	case 'application/octet-stream':
	case 'application/x-ecriture-qif':
		if(strtolower(end(explode('.', $_FILES['file']['name']))) == 'qif'){
			$file_type = 'QIF';
		}if(strtolower(end(explode('.', $_FILES['file']['name']))) == 'csv'){
			$file_type = 'CSV';
		}else{
			
		}
		
		break;
	default :
		
		break;
}

switch($file_type){
	case 'CSV':
		$csv_file_path = $_FILES['file']['tmp_name'];
		$import_id = md5($csv_file_path.time());
		
		$possible_duplicates = array(); // transaction deja existantes
		$nb_transac = 0;
		if (($handle = fopen("$csv_file_path", "r")) !== FALSE) {
			 
			 //saute 2 lignes
			 $data = fgetcsv($handle, 1000, ",");
			 $data = fgetcsv($handle, 1000, ",");
			 
			 //test type import SG (carte ou compte)
			 $data = fgetcsv($handle, 1000, ",");
			 if($data[0] == "date d'effet"){ //import d'un fichier CARTE 
				$fichier_carte = true;
			}else{
				$fichier_carte = false;
			}
		 
			$date_min_transac = '2081-10-26';
			while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
			
				$num = count($data);
				$t = new Transaction();
				$t->id_compte = $c->id;
				$t->libelle = utf8_encode($data[2]);

				$t->montant = str_replace(',', '.', $data[3]);
				$t->devise = $data[4];
				
				if($fichier_carte){
					$t->date_transaction = substr($data[1],6,4)."-".substr($data[1],3,2)."-".substr($data[1],0,2);
					
				}else{
					$t->date_transaction = substr($data[0],6,4)."-".substr($data[0],3,2)."-".substr($data[0],0,2);
					
				}
				if($t->date_transaction < $date_min_transac) $date_min_transac = $t->date_transaction;
				
				//retraitement date des opérations CARTE VISA
				$t->id_import = $import_id;
				
				try{
					$t->ajouter();
					$c->transactions[] = $t;
				}catch(DuplicateTransactionException $de){
					$possible_duplicates[] = $t;
				}catch(Exception $e){
					$response['error'] = _("Error adding transaction, import canceled")." : " . $e->getMessage();
					$response['status'] = false;
					
					$q = "DELETE FROM ".db_table_name('transactions')." WHERE id_import = '".$import_id."'";
					$res = mysql_query($q);
					if (!$res) {
						//throw new MyException('Invalid query: '.$q . mysql_error());
						$response['error'] .= _("Unable to delete transaction")." (".count($c->transactions)." "._("transaction(s) imported"). ") !! $q";
						$response['status'] = false;
					}
					
					echo json_encode($response);
					exit;
				}
			}
			
			fclose($handle);
			try{
				$c->getLatestTransactionDate();
				$c->getLatestImportDate();
				$c->actualiserSolde();
				if($c->is_synthese){
					$c->setTransactionsFutures();
				}
				
				$c->transactions = array_reverse($c->transactions);
				$possible_duplicates = array_reverse($possible_duplicates);
				
				
				$response['status'] = true;
				$response['nb_transactions'] = count($c->transactions);
				$response['nb_duplicates'] = count($possible_duplicates);
				$response['compte'] = $c;
				$response['duplicates'] = $possible_duplicates;
				$response['import_id'] = $import_id;
			}catch(Exception $e){
				$response['error'] = _("Error when updating account data")." : " + $e->getMessage();
				$response['status'] = false;
				echo json_encode($response);
				exit;
			}
			
		}else{
		
			//throw new  MyException("impossible d'ouvrir le fichier $csv_file_path");
			$response['status'] = false;
			$response['error'] = _("Impossible to open file")." !!";
			
		}
		
		
		
		
		break;
	case 'QIF':
		
		//function to get values from QIF file 
		function get_qif_value($tag, $trn_str){ 
			
			foreach($trn_str as $line){
				
				if(substr($line,0,1) == $tag){
					return trim(substr($line,1,strlen($line)-1));
				}
			}
		} 
		
		
		
		$qif_file_path = $_FILES['file']['tmp_name'];
		$import_id = md5($qif_file_path.time());
		
		$possible_duplicates = array(); // transaction deja existantes
		$nb_transac = 0;
		
		if (($handle = fopen($qif_file_path, "r")) !== FALSE) {
			$data = fread( $handle, filesize($qif_file_path) );
			fclose($handle);
			
			//http://forums.devshed.com/php-development-5/parse-financial-ofx-qfx-qif-file-556764-2.html
			if(substr_count(strtolower($data),'!type:bank') == 1 || substr_count(strtolower($data),'!type:ccard') == 1){ 
				//QIF file import 
				$nb_trn = substr_count($data,"^"); 
				$start_trn_str = strpos($data,"\nD"); 
				$len_trn_str = strpos($data,"^",$start_trn_str) - $start_trn_str; 
				
				$i = 0; 
				$cpt_duplicates = 0;
				$transactions = array();
				while($i < $nb_trn){ 
				
					$t = new Transaction();
					$t->id_compte = $c->id;
					
					$trn_str = explode("\n",substr($data,$start_trn_str,$len_trn_str)); 

					$t->montant= str_replace(',','',str_replace('+','',get_qif_value('T', $trn_str))); 
					
					if(get_qif_value('M', $trn_str) != ''){
						$t->libelle= utf8_encode(get_qif_value('M', $trn_str)); 
					}else{
						$t->libelle= utf8_encode(get_qif_value('P', $trn_str)); 
					}	
					
					$t_date = explode('/', get_qif_value('D', $trn_str));
					if(strlen(end($t_date)) == 2 ) $t_date[2] = '20'.$t_date[2];
					
					if(checkdate($t_date[1], $t_date[0], $t_date[2])){
						$t->date_transaction = $t_date[2]."-".$t_date[1]."-".$t_date[0];
					}else{
						$t->date_transaction = "0000-00-00";
					}
					
					$t->id_import = $import_id;
				
					try{
						$t->ajouter();
						$c->transactions[] = $t;
					}catch(DuplicateTransactionException $de){
						$cpt_duplicates++;
						$possible_duplicates[$cpt_duplicates - 1]['transaction'] = new Transaction($t->getDuplicateID());
						$possible_duplicates[$cpt_duplicates - 1]['duplicate'] = $t;
						
					}catch(Exception $e){
						$response['error'] = _("Error adding transaction, import canceled")." : " . $e->getMessage();
						$response['status'] = false;
						
						$q = "DELETE FROM ".db_table_name('transactions')." WHERE id_import = '".$import_id."'";
						$res = mysql_query($q);
						if (!$res) {
							//throw new MyException('Invalid query: '.$q . mysql_error());
							$response['error'] .= _("Unable to delete transactions")." !! $q";
							$response['status'] = false;
						}
						
						echo json_encode($response);
						exit;
					}
				
					$start_trn_str = strpos($data,"\nD",$start_trn_str +2); 
					$len_trn_str = strpos($data,"^",$start_trn_str) - $start_trn_str; 
					$i++; 
				} 
				
				try{
					$c->getLatestTransactionDate();
					$c->getLatestImportDate();
					$c->actualiserSolde();
					if($c->is_synthese){
						$c->setTransactionsFutures();
					}
					
					$c->transactions = array_reverse($c->transactions);
					
					
					$response['status'] = true;
					$response['nb_transactions'] = count($c->transactions);
					$response['compte'] = $c;
					$response['duplicates'] = $possible_duplicates;
					$response['nb_duplicates'] = $cpt_duplicates;
					$response['import_id'] = $import_id;
				}catch(Exception $e){
					$response['error'] = _("Error when updating account data")." : " + $e->getMessage();
					$response['status'] = false;
					echo json_encode($response);
					exit;
				}
			}else{
				$response['status'] = false;
				$response['error'] = _("QIF file format incorrect")." !!";
			}
	
		}else{
			//throw new  MyException( "impossible d'ouvrir le fichier ".$qif_file_path);;
			$response['status'] = false;
			$response['error'] = _("Impossible to open file")." !!";
		}

		
		break;
	default:
		//header("Location:afficher.php?import=ko&error=file_type#".$_POST['id_compte']);
		$response['status'] = false;
		$response['error'] = _("File format incompatible with import feature")." (".$_FILES['file']['type'].") !!";
}

echo json_encode($response);

?>