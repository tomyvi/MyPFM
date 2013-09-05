<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<title><?php echo _("My Personal Finance Manager"); ?></title>
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
	<li id='menu_accueil'><a href="./"><?php echo _("Home"); ?></a></li>
	
		<li id='menu_synthese'>
			<a href="./dashboard.php"><?php echo _("Dashboard"); ?></a>
		</li>
		<li id='menu_comptes'>
			<a href="./display.php"><?php echo _("Expenses & Incomes"); ?></a>
			<ul>
				<li><a href="./display.php"><?php echo _("Account list"); ?></a></li>
				<li><a href="javascript:void(0);" id="lien_add_cpt"><?php echo _("Add new account"); ?></a></li>
				<?php 
				if ($_AFFICHER_COMPTE) {
					?><li><a href="javascript:void(0);" id="lien_add_transac"><?php echo _("Add new transaction"); ?></a></li><?php
				}
				?>
				
			</ul>
		</li>
		<li id='menu_categories'>
			<a href="javascript:void(0);"><?php echo _("Categories & Budgets"); ?></a>
			<ul>
				<li><a href="javascript:void(0);" id='lien_add_budget'><?php echo _("Add new budget"); ?></a></li>
				<li><a href="javascript:void(0);"><?php echo _("Add new category"); ?></a></li>
				<?php 
				if ($_AFFICHER_COMPTE) {
					?><li><a href="javascript:void(0);"><?php echo _("Apply categories"); ?></a></li><?php
				}
				?>				
			</ul>
		</li> 
		<li id='menu_etats'>
			<a href="./report_graphs.php"><?php echo _("Reports"); ?></a>
			<ul>
				<li><a href="./report_graphs.php"><?php echo _("Expenses & Incomes"); ?></a></li>
				<li><a href="./report_categories.php"><?php echo _("Categories"); ?></a></li>
				<li><a href="./report_budgets.php"><?php echo _("Budgets"); ?></a></li>
			</ul>
		</li>		
		<li><a href="./login.php?out"><?php echo _("Logout"); ?></a></li>
		<li id='menu_search' class='menu_search'>
			<input placeholder='<?php echo _("Amount, category or label,..."); ?>' type='text' name='q' id='q' value='<?php echo addslashes($_GET['q']); ?>' size='30' tabindex='1' onfocus='this.select();' autocomplete='off'/>
			<img src='./img/06_magnifying_glass.png' height='12' width='12' onclick='do_search();'></li>
	</ul>
<?php } ?>

<div class="principal">