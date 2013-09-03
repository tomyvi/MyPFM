<?php


function __autoload($class_name){
	if (!require_once(dirname(__FILE__) ."/../lib/".$class_name.".class.php")) {
    		throw new MyException('Invalid query: ' . mysql_error());
	}
}

function db_table_name($table){
	global $_config;
	
	return $_config['table_prefix'].$table;
}


function debug($text) {
	global $_config;

	if($_config["debug"]){
		echo "<pre>";
		print_r($text);
		echo "</pre>\n";
	}
}


function date_last_month($date = ""){
	
	if($date != ""){
		$date = "'$date'";
	}else{
		$date = "DATE(NOW())";
	}
	
	$q = "SELECT DATE_SUB($date, INTERVAL 1 MONTH) as date_ancienne";
	
	$res = mysql_query($q);
	if (!$res) {
		throw new MyException('Invalid query: ' .$q. mysql_error());
	}
	$result = mysql_fetch_assoc($res);
	$date_ancienne = $result['date_ancienne'];
	return $date_ancienne;
}


function affichedate($date){
	if((substr($date,0,2) == "00") || $date == ""){
		return "jamais";
	}else{
		return substr($date,8,2)."/".substr($date,5,2)."/".substr($date,0,4);
	}
}
function affichedatecourte($date){
	if((substr($date,0,2) == "00") || $date == ""){
		return "jamais";
	}else{
		return substr($date,8,2)."/".substr($date,5,2);
	}
}
function returnDate($date){
	$tab_date = explode("/",$date);
	$annee = $tab_date[2];
	$mois = $tab_date[1];
	$jour = $tab_date[0];
	$date = $annee."-".$mois."-".$jour;
	
	return $date;
}



function generateDateArray($start_date, $end_date, $interval = "+1 day"){
	$date = array();
	
	$current_date = $start_date;
	
	while($current_date <= $end_date){
		
		$date[] = $current_date;
		$current_date = date('Y-m-d', strtotime(date("Y-m-d", strtotime($current_date)) . " " . $interval));

	}
	
	return $date;

}
function generateMonthArray($start_date, $end_date, $interval = "+1 month"){
	return generateDateArray($start_date, $end_date, $interval);

}


//http://www.gen-x-design.com/archives/create-a-rest-api-with-php/
function getStatusCodeMessage($status)
{
	// these could be stored in a .ini file and loaded
	// via parse_ini_file()... however, this will suffice
	// for an example
	$codes = Array(
		100 => 'Continue',
		101 => 'Switching Protocols',
		200 => 'OK',
		201 => 'Created',
		202 => 'Accepted',
		203 => 'Non-Authoritative Information',
		204 => 'No Content',
		205 => 'Reset Content',
		206 => 'Partial Content',
		300 => 'Multiple Choices',
		301 => 'Moved Permanently',
		302 => 'Found',
		303 => 'See Other',
		304 => 'Not Modified',
		305 => 'Use Proxy',
		306 => '(Unused)',
		307 => 'Temporary Redirect',
		400 => 'Bad Request',
		401 => 'Unauthorized',
		402 => 'Payment Required',
		403 => 'Forbidden',
		404 => 'Not Found',
		405 => 'Method Not Allowed',
		406 => 'Not Acceptable',
		407 => 'Proxy Authentication Required',
		408 => 'Request Timeout',
		409 => 'Conflict',
		410 => 'Gone',
		411 => 'Length Required',
		412 => 'Precondition Failed',
		413 => 'Request Entity Too Large',
		414 => 'Request-URI Too Long',
		415 => 'Unsupported Media Type',
		416 => 'Requested Range Not Satisfiable',
		417 => 'Expectation Failed',
		500 => 'Internal Server Error',
		501 => 'Not Implemented',
		502 => 'Bad Gateway',
		503 => 'Service Unavailable',
		504 => 'Gateway Timeout',
		505 => 'HTTP Version Not Supported'
	);

	return (isset($codes[$status])) ? $codes[$status] : '';
}
function sendStatusHeader($status){

	$status_header = 'HTTP/1.1 ' . $status . ' ' . getStatusCodeMessage($status);
	// set the status
	header($status_header);

}



?>