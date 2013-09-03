<?php

require_once(dirname(__FILE__) ."/inc/init.inc.php");


require_once(dirname(__FILE__) . '/inc/html/header.inc');

$lc = ListeComptes::getInstance();

foreach(Categorie::getListe() as $c){
	
	$moyenne = $c->getAverageTotalAmountPerMonth();
	$montant = $lc->getFluxParMoisParCategorie(array(), array($c->id), '', '');
	
	?>
	<div class='reporting-categorie-graphcontainer'>
		<div class='graph_categorie' data-id_categorie='<?php echo $c->id; ?>'>
			<div id="chart_categorie_<?php echo $c->id; ?>"></div>
			<div class="reporting-categorie-showmore">
				<span class='lien reporting-categorie-showmorelink'>Afficher détail</span> / 
				<span class="lien reporting-categorie-modifylink">Modifier</span>
			</div>
		</div>
	</div>
	<?php
	
}
?>
<script>
	$('#menu_etats').addClass('current');
</script>

<?php



require_once(dirname(__FILE__) . '/inc/html/footer.inc');

?>