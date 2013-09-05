<?php


require_once(dirname(__FILE__) ."/inc/init.inc.php");

require_once(dirname(__FILE__) . '/inc/html/header.inc');
?><center>
<div class="colonne_2">
<script>
$(document).ready(function(){
	$('#menu_synthese').addClass('current');
	$('.update_cpt_graph').click(function(){
		id_compte = $(this).parent().parent().data('id_compte');
		console.log('Update graph request for account ID ' + id_compte);
		location.href = './update_futures.php?idc='+id_compte;
		return false;
	});
});
</script>

<?
$lc = ListeComptes::getInstance();

$lc_max_transac_date = $lc->getMaxTransactionDate();
$last_month = date_last_month(date_last_month($lc_max_transac_date));

//global balance
foreach(Compte::getListe() as $cpt){
	
	if($data != "") $data .= ",\n";
	$solde_last_month_cpt = $cpt->getSolde($last_month);
	$solde_cpt = $cpt->getSolde();
	
	$solde_last_month += $solde_last_month_cpt;
	$solde += $solde_cpt;
	
	$data .= "{name: '".$cpt->libelle."',data: [".$solde_last_month_cpt.",".$solde_cpt."]}";
	
}

if($solde_last_month>=0){ $class_solde_last_month = "credit";}else{ $class_solde_last_month = "debit";}
if($solde>=0){ $class_solde = "credit";}else{ $class_solde = "debit";}

?>
<script>
var chart_epargne;
$(document).ready(function() {
	chart_epargne = new Highcharts.Chart({
		chart: {
			renderTo: 'chart_epargne',
			type: 'bar',
            width: 710,
			height: 200
		},
		title: {
			text: '<?php echo(_("Current balance")); ?>'
		},
		xAxis:{title: false, categories: ['J - 60', 'J']},
		tooltip: {
			formatter: function() {
				return this.series.name + ':' + Highcharts.numberFormat(this.y,0,',',' ') +' \u20AC';
			}},
		yAxis:{
			min: 0,
			title: false,
			labels: {
				formatter: function() {
					if(this.value >= 1000){
						return Highcharts.numberFormat(this.value / 1000,0,',',' ') +' k\u20AC';
					}else{
						return Highcharts.numberFormat(this.value,0,',',' ') +' \u20AC';
					}
				}
			}
		},
		legend: {
			enabled: false
		},
		credits: {
			enabled: false
		},
		plotOptions: {
			series: {
				stacking: 'normal',
				shadow: false
			}
		},
			series: [<?php echo $data; ?>]
	});
});
</script>
<div class="graph_epargne_container">
<div class='graph_epargne' id="chart_epargne"></div>
<div class='graph_epargne_montant'>
<?php
	
	echo "<div class='montant $class_solde_last_month'>".number_format($solde_last_month,2,","," ")." &#8364;</div>";
	echo "<div class='montant $class_solde'>".number_format($solde,2,","," ")." &#8364;</div>";
?>
	
	
		
</div>
</div>

<div class='liste_graph_cpt'>
<?


//graphique par compte
foreach(Compte::getListe() as $cpt){

	if($cpt->is_synthese){
		
		$max_transac_date = $cpt->getLatestTransactionDate();
		$date_ancienne = date_last_month($max_transac_date);
		
		$flux_p = $cpt->getFluxEntreDates(true, false, array(), "", $date_ancienne);
		$flux_n = 0 - $cpt->getFluxEntreDates(false, true, array(), "", $date_ancienne);
		?>
		<script>
			var chart_flux_<?php echo $cpt->id; ?>;
			$(document).ready(function() {
				chart_flux_<?php echo $cpt->id; ?> = new Highcharts.Chart({
					chart: {
						renderTo: 'chart_flux_<?php echo $cpt->id; ?>',
						type: 'bar'
					},
					legend: {
						enabled: false
					},
					credits: {
						enabled: false
					},
					title: {
						text: ''
					},
					xAxis: {
						categories: ['<?php echo _("Incomes"); ?>', '<?php echo _("Expenses"); ?>']
					},
					yAxis:{
						title: false,
						labels: {
							formatter: function() {
							   if(this.value >= 1000){
									return Highcharts.numberFormat(this.value / 1000,0,',',' ') +' k\u20AC';
								}else{
									return Highcharts.numberFormat(this.value,0,',',' ') +' \u20AC';
								}
							}
						}
					},
					plotOptions: {
						series: {
							stacking: 'normal'
						}
					},
					series: [{
						color: '#E16766',
						name:'<?php echo _("Expenses"); ?>',
						data: [0, <?php echo $flux_n;?>]
					},{
						color: '#67E168',
						name:'<?php echo _("Incomes"); ?>',
						data: [<?php echo $flux_p;?>, 0]
					}]
				});
			});
			
		</script>
		<?
		$soldes = $cpt->getSoldes($max_transac_date,$date_ancienne, "+1 day");
		if(count($soldes)>0){
			$data="";
			$data_futur="";
			$nb_points = 0;
			foreach($soldes as $date => $solde){ //0000-00-00
				$data .= "[Date.UTC(".substr($date,0,4).", ".(substr($date,5,2)-1).", ".substr($date,8,2)."), ".(0+number_format($solde,2,".",""))."]";
				
				$nb_points++;
				
				if($nb_points != count($soldes)){
					$data_futur .= "[Date.UTC(".substr($date,0,4).", ".(substr($date,5,2)-1).", ".substr($date,8,2)."), null]";
				}else{
					$data_futur .= "[Date.UTC(".substr($date,0,4).", ".(substr($date,5,2)-1).", ".substr($date,8,2)."), ".(0+number_format($solde,2,".",""))."]";
				}
				
				if($nb_points != count($soldes)){
					$data .= ",\n";
					$data_futur .= ",\n";
				}
				
			}
			
			//adding future transactions
			$cpt->getTransactionsFutures($max_transac_date);
			foreach($cpt->transactions_futures as $t){
				$date = $t->date_transaction;
				$solde += $t->montant;
				$data .= ",\n[Date.UTC(".substr($date,0,4).", ".(substr($date,5,2)-1).", ".substr($date,8,2)."), null]";
				$data_futur .= ",\n[Date.UTC(".substr($date,0,4).", ".(substr($date,5,2)-1).", ".substr($date,8,2)."), ".(0+number_format($solde,2,".",""))."]";
				
				//$data .= ",\n[Date.UTC(".substr($date,0,4).", ".(substr($date,5,2)-1).", ".substr($date,8,2)."), ".(0+number_format($solde,2,".",""))."]";
				
			}
			
			?>
		
			<script>
			var chart_<?php echo $cpt->id; ?>;
			$(document).ready(function() {
				chart_<?php echo $cpt->id; ?> = new Highcharts.Chart({
					chart: {
						renderTo: 'chart_solde_<?php echo $cpt->id; ?>',
						animation: false
					},
					title: {
						text: '<?php echo $titre_chart; ?>'
					},
					xAxis: {
						type: 'datetime',
						plotLines: [{
							color: '#0000FF',
							dashStyle:'ShortDot',
							width: 1,
							value: Date.UTC(<?php echo substr($max_transac_date,0,4); ?>, <?php echo (substr($max_transac_date,5,2)-1); ?>, <?php echo substr($max_transac_date,8,2); ?>)
						}]
					},
					yAxis:{
						title: false,
						labels: {
							formatter: function() {
							   return Highcharts.numberFormat(this.value,0,',',' ') +' \u20AC';
							}
						},
					},
					legend: {
						enabled: false
					},
					credits: {
						enabled: false
					},
					series: [
						{
							type:'area',
							name:'<?php echo _("Balance"); ?>',
							color: '#67E168',
							data: [<?php echo $data;?>]
						},
						{
							type:'area',
							name:'<?php echo _("Estimated future Balance"); ?>',
							color: '#67E168',
							dashStyle:'Dash',
							lineWidth:2,
							data: [<?php echo $data_futur;?>]
						}
					],
					plotOptions: {
						area: {
							marker: {
								enabled: false  
							}
						}
					}
				});
			});   
				
			</script>
			
			<?php
			$solde_courant = $cpt->solde_courant;
			$solde_futur = $solde;
			if($solde_courant>0){$style_solde_courant = "credit signe";}else{$style_solde_courant = "debit";}
			if($solde_futur>0){$style_solde_futur = "credit signe";}else{$style_solde_futur = "debit";}
			
			echo "
				<div class='graph_solde' data-id_compte='".$cpt->id."'>
					<input type='hidden' id='id_cpt_graph'/>
					<div class='titre_graph'><a href='./display.php#".$cpt->cle."' title='". _("Display account transactions")."'>".$cpt->libelle."</a> <span class='update_cpt_graph' title='"._("Update estimated future balance")."'></span></div>
					"._("Balance 30 days ago")." :
					<div class='graph_cpt_flux' id='chart_flux_".$cpt->id."'></div>
					"._("Estimated future balance")." :
					<div class='graph_cpt_solde' id='chart_solde_".$cpt->id."'></div>
					<div class='solde_fin_mois'>"._("Balance on")." ".affichedatecourte($max_transac_date)." : <span class='".$style_solde_courant."'>".number_format($solde_courant,2,","," ")." &#8364;</span>, "._("Projected balance")." : <span class='".$style_solde_futur."'>".number_format($solde_futur,2,","," ")." &#8364;</span></div>
				</div>
			";
		}
	}
	
}
?>
</div>
</div><!-- colonne_2 !-->
</center>
<?php
require_once(dirname(__FILE__) . '/inc/html/search.inc');
require_once(dirname(__FILE__) . '/inc/html/footer.inc');
?>