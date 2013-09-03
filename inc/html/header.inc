<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<title>Mes Comptes</title>
<link rel="shortcut icon" type="image/x-icon" href="./img/favicon.ico" />
<link rel="stylesheet" type="text/css" href="./css/commun.css" />
<link rel="stylesheet" type="text/css" href="./css/comptes.css" />
<link rel="stylesheet" type="text/css" href="./css/jquery/redmond/jquery-ui-1.8.16.custom.css">
<link rel="stylesheet" type="text/css" href="./css/jquery/jquery.multiselect.css" />
<link type="text/css" href='http://fonts.googleapis.com/css?family=Arimo:400,700,400italic,700italic' />
<script src='./js/jquery/jquery-1.7.min.js'></script>
<script src='./js/jquery/jquery-ui-1.8.16.custom.min.js'></script>
<script src='./js/jquery/jquery.multiselect.min.js'></script>
<script src='./js/jquery/jquery.meio.mask.min.js'></script>
<script src='./js/jquery/jquery-idleTimeout.js'></script>
<script src='./js/jquery/jquery.nicescroll.min.js'></script>
<script src='./js/jquery/jquery.scrollTo.min.js'></script>
<script src='./js/jquery/noty/jquery.noty.js'></script>
<script src='./js/jquery/noty/layouts/top.js'></script>
<script src='./js/jquery/noty/themes/default.js'></script>
<script src='./js/highcharts.js'></script>
<script src='./js/functions.js'></script>
<script src='./js/plugins.js'></script>
<script src='./js/compte.js'></script>
<script src='./js/transaction.js'></script>
<script src='./js/hotkeys.js'></script>
<script src='./js/my_php.js'></script>
</head>
<body>
<center>
<div class="conteneur">

<?php if(User::is_user_logged_in() == 1){ ?>
	<ul id='nav'>
	<li id='menu_accueil'><a href="./">Accueil</a></li>
	
		<li id='menu_synthese'>
			<a href="./afficher_synthese.php">Synth&egrave;se</a>
		</li>
		<li id='menu_comptes'>
			<a href="./afficher_compte.php">D&eacute;penses & Revenus</a>
			<ul>
				<li><a href="./afficher_compte.php">Liste des comptes</a></li>
				<li><a href="javascript:void(0);" id="lien_add_cpt">Ajouter un compte</a></li>
				<?php 
				if ($_AFFICHER_COMPTE) {
					?><li><a href="javascript:void(0);" id="lien_add_transac">Ajouter une transaction</a></li><?php
				}
				?>
				
			</ul>
		</li>
		<li id='menu_categories'>
			<a href="javascript:void(0);">Catégories & Budgets</a>
			<ul>
				<li><a href="javascript:void(0);" id='lien_add_budget'>Créer un nouveau budget</a></li>
				<li><a href="javascript:void(0);">Ajouter une cat&eacute;gorie</a></li>
				<?php 
				if ($_AFFICHER_COMPTE) {
					?><li><a href="javascript:void(0);">Appliquer les cat&eacute;gories</a></li><?php
				}
				?>				
			</ul>
		</li> 
		<li id='menu_etats'>
			<a href="./rapporter_graphique.php">Rapports</a>
			<ul>
				<li><a href="./rapporter_graphiques.php">Dépenses & revenus</a></li>
				<li><a href="./rapporter_categories.php">Catégories</a></li>
				<li><a href="./rapporter_budgets.php">Budgets</a></li>
			</ul>
		</li>		
		<li><a href="./login.php?out">D&eacute;connexion</a></li>
		<li id='menu_search' class='menu_search'>
			<input placeholder='Montant, cat&eacute;gorie ou libell&eacute;' type='text' name='q' id='q' value='<?php echo addslashes($_GET['q']); ?>' size='30' tabindex='1' onfocus='this.select();' autocomplete='off'/>
			<img src='./img/06_magnifying_glass.png' height='12' width='12' onclick='do_search();'></li>
	</ul>
<?php } ?>

<div class="principal">