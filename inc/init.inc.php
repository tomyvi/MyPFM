<?php

//lancement chrono generation de page (==> footer.inc)
$temps_debut = microtime(true);

require_once(dirname(__FILE__) . "/config.inc.php");
require_once(dirname(__FILE__) . "/fonctions.inc.php");



//ouverture connexion MySQL
$link = mysql_connect($_config["mysql_server"],$_config["mysql_user"],$_config["mysql_pwd"]);
if (!$link) {
	die('Could not connect: ' . mysql_error());
}
mysql_select_db($_config["mysql_db"]);



//si on est pas sur la page de login, on teste si l'utilisateur est loggé
if(!$login_page && !$rss_page){
	session_start();
	if(!User::is_user_logged_in())
	{
	    $status = 401;
		$status_header = 'HTTP/1.1 ' . $status . ' ' . getStatusCodeMessage($status);
		// set the status
		header($status_header);
		if(!$_RPC) header("Location: login.php?u=".urlencode($_SERVER['REQUEST_URI']));
	    exit;
	}else{
		$_user = new User($_SESSION["user_id"]);
	}
}else{
	$_user = new User();
}




?>