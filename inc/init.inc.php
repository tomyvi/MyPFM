<?php

require_once(dirname(__FILE__) . "/config.inc.php");
require_once(dirname(__FILE__) . "/fonctions.inc.php");



$accept_language = explode(',',$_SERVER['HTTP_ACCEPT_LANGUAGE']);
$default_lang = $accept_language[0];


// Locale setup
define('LC_MESSAGES', 6);
putenv("LANG=".$default_lang);
setlocale(LC_ALL, $default_lang);

$domain = "messages";
bindtextdomain($domain, "./locale");
bind_textdomain_codeset($domain, 'UTF-8');
textdomain($domain);


//opening mysql connection
$link = mysql_connect($_config["mysql_server"],$_config["mysql_user"],$_config["mysql_pwd"]);
if (!$link) {
	die(_('Could not connect').' : ' . mysql_error());
}
mysql_select_db($_config["mysql_db"]);



//if not on login page, test if user is logged in
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