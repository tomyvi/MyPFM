<?php

//Source : http://marakana.com/blog/examples/php-implementing-secure-login-with-php-javascript-and-sessions-without-ssl.html

$login_page = true;
require_once(dirname(__FILE__) ."/inc/init.inc.php");





session_start();


$dest_url = urldecode($_REQUEST["dest"]);
if($dest_url == "") $dest_url = "./";


//traitement demande logout
if(array_key_exists("out",$_GET)){
	session_unset();
	$errormsg = "Vous avez bien été déconnecté !";
}
//traitement auto logout
if(array_key_exists("auto",$_GET)){
	$errormsg = "Suite à 15 minutes d'inactivité, vous avez été déconnecté automatiquement.";
}

//traitement accès login mais déjà loggé
if(User::is_user_logged_in())
{
    header("Location: ".$dest_url);
    exit;
}
if($_POST['username']!='' && $_POST['response']!=''){
	$user = new User();
	if($user->validate($_POST['username'],$_POST['response'])){
		$_SESSION['user_id'] = $user->id;
		$_SESSION['user_logged_in'] = $user->logged_in;
		header('Location: '.$dest_url);
		exit;
	}else{
		$errormsg = 'Login ou mot de passe incorrect !';
	}
}

//generation du challenge
if(!isset($_SESSION["challenge"])){
	$_SESSION["challenge"] = User::generateChallenge();

}


require_once(dirname(__FILE__) . "/inc/html/header.inc");
require_once(dirname(__FILE__) . "/inc/html/login.inc");
require_once(dirname(__FILE__) . "/inc/html/footer.inc");

?>