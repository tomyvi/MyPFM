<?php
require_once(dirname(__FILE__) ."/inc/init.inc.php");

$lc = ListeComptes::getInstance();



require_once(dirname(__FILE__) . '/inc/html/header.inc');



if($_GET['date_debut'] == ''){
	$interval = "1 YEAR";
	$q = "SELECT DAY(DATE_SUB(DATE(NOW()), INTERVAL $interval)) as jour_date_ancienne, MONTH(DATE_SUB(DATE(NOW()), INTERVAL $interval)) as mois_date_ancienne, YEAR(DATE_SUB(DATE(NOW()), INTERVAL $interval)) as annee_date_ancienne";
	$res = mysql_query($q);
	if (!$res) {
		throw new MyException('Invalid query: ' .$q. mysql_error());
	}
	$result = mysql_fetch_assoc($res);
	$_GET['date_debut'] = "01/".str_pad($result['mois_date_ancienne'],2,'0',STR_PAD_LEFT)."/".$result['annee_date_ancienne'];
}

?>
<script>
	$('#menu_etats').addClass('current');

	$(document).ready(function(){
		//$('.searchbox').hide();
		
		$('.type_graph').click(function(){
			$('#chart_type').val($(this).attr('id'));
			$('#chart_form').submit();
		});
	});
</script>
<div class="colonne_1">
	<div class="graph_menu">
		<div class="titre"><?php echo _("Expenses"); ?></div>
		<div class="type_graph lien" id="DEP_MOIS"><?php echo _("per month"); ?></div>
		<div class="type_graph lien" id="DEP_CAT"><?php echo _("per category"); ?></div>
		<div class="titre"><?php echo _("Incomes"); ?></div>
		<div class="type_graph lien" id="REV_MOIS"><?php echo _("per month"); ?></div>
		<div class="type_graph lien" id="REV_CAT"><?php echo _("per category"); ?></div>
		<div class="titre"><?php echo _("Net incomes"); ?></div>
		<div class="type_graph lien" id="REV_NET_MOIS"><?php echo _("per month"); ?></div>
		<div class="type_graph lien" id="REV_NET_CAT"><?php echo _("per category"); ?></div>
		<div class="titre"><?php echo _("Balance"); ?></div>
		<div class="type_graph lien" id="SOL_HEBDO"><?php echo _("weekly"); ?></div>
	</div>
</div><!-- colonne_1 !-->
<div class="colonne_2">
	
	<script>
		$('document').ready(function(){
			$('#chart_idcpt').multiselect({
					//header: false,
					checkAll: function(){$.uniform.update("input:checkbox");},
					uncheckAll: function(){$.uniform.update("input:checkbox");},
					checkAllText: '<?php echo _("All"); ?>',
					uncheckAllText: '<?php echo _("None"); ?>',
					noneSelectedText: '<?php echo _("Accounts"); ?>...',
					selectedText: '# <?php echo _("accounts"); ?>',
					selectedList:1
				});
			$('#chart_idcat').multiselect({
					//header: false,
					checkAll: function(){$.uniform.update("input:checkbox");},
					uncheckAll: function(){$.uniform.update("input:checkbox");},
					checkAllText: '<?php echo _("All"); ?>',
					uncheckAllText: '<?php echo _("None"); ?>',
					noneSelectedText: '<?php echo _("Categories"); ?>...',
					selectedText: '# <?php echo _("categories"); ?>',
					selectedList:1
				});
			
		});
	</script>
	<form action="./rapporter_graphiques.php" method="GET" id="chart_form">
	<select name="idcpt[]" id="chart_idcpt" multiple>
		<?php 
			
			foreach(Compte::getListe() as $cpt){
				$selected = "";
				if(isset($_GET['idcpt']) && count($_GET['idcpt'])>0){
					foreach($_GET['idcpt'] as $idcpt){
						
						if($idcpt == $cpt->id){
							$selected = "selected='selected'";
						}
					}
				}
				echo "<option value='".$cpt->id."' $selected>".$cpt->libelle."</option>\n";
			}
		?>
	</select>
	<select name="idcat[]" id="chart_idcat" multiple>
		<?php 
			foreach(Categorie::getListe() as $cat){
				$selected = "";
				
				if(isset($_GET['idcat']) && count($_GET['idcat'])>0){
					foreach($_GET['idcat'] as $idcat){
						if($idcat == $cat->id){
							$selected = "selected='selected'";
						}
					}
				}
				
				echo "<option value='".$cat->id."' $selected>".$cat->nom."</option>\n";
			}
		?>
	</select>
	<script>
	$('document').ready(function(){
		$("#date_debut").datepicker({
			dateFormat: 'dd/mm/yy',
			firstDay: 1,
			dayNamesShort: ['Dim', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam'],
			dayNamesMin: ['Di', 'Lu', 'Ma', 'Me', 'Je', 'Ve', 'Sa'],
			dayNames: ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'],
			monthNames: ['Janvier','Fevrier','Mars','Avril','Mai','Juin','Juillet','Aout','Septembre','Octobre','Novembre','Decembre'],
			maxDate: '+0d'
		});
		$("#date_fin").datepicker({
			dateFormat: 'dd/mm/yy',
			firstDay: 1,
			dayNamesShort: ['Dim', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam'],
			dayNamesMin: ['Di', 'Lu', 'Ma', 'Me', 'Je', 'Ve', 'Sa'],
			dayNames: ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'],
			monthNames: ['Janvier','Fevrier','Mars','Avril','Mai','Juin','Juillet','Aout','Septembre','Octobre','Novembre','Decembre'],
			maxDate: '+0d'
		});
	});
	</script>
	<?php echo _("from"); ?> : <input type="text" name="date_debut" id="date_debut" size="10" value="<?echo $_GET['date_debut']; ?>"/>
	<?php echo _("to"); ?> : <input type="text" name="date_fin" id="date_fin" size="10" value="<?echo $_GET['date_fin']; ?>"/>
	<input type="hidden" name="chart_type" id="chart_type" value="<?php echo $_GET['chart_type']; ?>" />
	<input type="button" value="<?php echo _("Display"); ?>" onclick="chart_form.submit();"/>
	</form><br/>
	
	
	
	
	<?php
	if($_GET['chart_type'] == '') $_GET['chart_type'] = "REV_NET_MOIS";
	if(isset($_GET['date_debut']) && $_GET['date_debut']!="") $date_debut = returnDate($_GET['date_debut']);
	if(isset($_GET['date_fin']) && $_GET['date_fin']!="") $date_fin = returnDate($_GET['date_fin']);
	if(!isset($_GET['idcat'])) $_GET['idcat'] = array_keys(Categorie::getListe());

			
	switch($_GET['chart_type']){
		case "DEP_CAT" :
		case "REV_CAT" :
			$neg = false;
			$pos = false;
			if($_GET['chart_type'] != "REV_CAT"){
				$neg = true;
			}
			if($_GET['chart_type'] != "DEP_CAT"){
				$pos = true;
			}
			
			//graph par catégorie
			$fluxs = $lc->getFluxParCategorie($pos,$neg,$_GET['idcpt'],$_GET['idcat'],$date_fin,$date_debut);
			
			$nb_pos = 0;
			foreach($fluxs as $categorie => $flux){
				$data .= "['".$categorie."',".number_format($flux,2,".","")."]";
				$nb_pos++;
				if($nb_pos != count($fluxs)){
					$data .=",\n";
				}
			}
			
			if($_GET['chart_type']=="DEP_CAT") $titre_chart = _("Expenses per category");
			if($_GET['chart_type']=="REV_CAT") $titre_chart = _("Incomes per category");
			
			
			?>
			
			<script>
				var chart;
			$(document).ready(function() {
			   chart = new Highcharts.Chart({
				  chart: {
					 renderTo: 'graph'
				  },
				  title: {
					 text: '<?php echo $titre_chart; ?>'
				  },
				  plotOptions: {
					 pie: {
						allowPointSelect: true,
						cursor: 'pointer',
						dataLabels: {
						   enabled: true
						},
						showInLegend: false
					 }
				  },
				   tooltip: {
					 formatter: function() {
						return '<b>'+ this.point.name +'</b>: '+ this.point.y +' EUR';
					 }
				  },credits: {
						enabled: false
					},
					series: [{
					 type: 'pie',
					 data: [<?php echo $data; ?>]
					  }
				  ]
			   });
			});
			</script>
			<?php
			break;
		case "DEP_MOIS" :
		case "REV_MOIS" :
		case "REV_NET_MOIS" :
			// 6 MONTH AVERAGE PLOT
			$neg = false;
			$pos = false;
			if($_GET['chart_type'] != "REV_MOIS"){
				$neg = true;
			}
			if($_GET['chart_type'] != "DEP_MOIS"){
				$pos = true;
			}
			
			$flux_mensu = $lc->getFluxParMois($pos, $neg, $_GET['idcpt'],$_GET['idcat'],$date_fin,$date_debut);
			
			
			//print_r($flux_mensu);exit;
			
			$nb_points = 0;
			$coeff_1 = 1;
			$coeff_2 = 0.90;
			$coeff_3 = 0.80;
			$coeff_4 = 0.70;
			$coeff_5 = 0;//.60;
			$coeff_6 = 0;//.50;
			$moiss = array_keys($flux_mensu);

			foreach($flux_mensu as $mois => $flux){
				
				if($nb_points % 3 == 0){
					//calcul moyenne flotante 3 mois
					if(!array_key_exists($nb_points,$moiss)){
						$flux_1 = 0;
					}else{
						$flux_1 = $flux_mensu[$moiss[$nb_points]];
					}
					if(!array_key_exists($nb_points-1,$moiss)){
						$flux_2 = 0;
					}else{
						$flux_2 = $flux_mensu[$moiss[$nb_points-1]];
					}
					if(!array_key_exists($nb_points-2,$moiss)){
						$flux_3 = 0;
					}else{
						$flux_3 = $flux_mensu[$moiss[$nb_points-2]];
					}
					if(!array_key_exists($nb_points-3,$moiss)){
						$flux_4 = 0;
					}else{
						$flux_4 = $flux_mensu[$moiss[$nb_points-3]];
					}
					if(!array_key_exists($nb_points-4,$moiss)){
						$flux_5 = 0;
					}else{
						$flux_5 = $flux_mensu[$moiss[$nb_points-4]];
					}
					if(!array_key_exists($nb_points-5,$moiss)){
						$flux_6 = 0;
					}else{
						$flux_6 = $flux_mensu[$moiss[$nb_points-5]];
					}
					
					$moy_flot = ($flux_1*$coeff_1 + $flux_2*$coeff_2 + $flux_3*$coeff_3 + $flux_4*$coeff_4 + $flux_5*$coeff_5 + $flux_6*$coeff_6)/($coeff_1+$coeff_2+$coeff_3+$coeff_4+$coeff_5+$coeff_6);
					$moy_flot = number_format($moy_flot,2,".","");
					$data_moy .= "[Date.UTC(".substr($mois,0,4).", ".(substr($mois,5,2)-1).", 1), ".$moy_flot."]";
					if($nb_points+1 != count($flux_mensu)){
						$data_moy .= ",\n";
					}
				}
				$data .= "[Date.UTC(".substr($mois,0,4).", ".(substr($mois,5,2)-1).", 1), ".number_format($flux,2,".","")."]";
				$nb_points++;
				
				if($nb_points != count($flux_mensu)){
					$data .= ",\n";
				}
			}
			
			if($_GET['chart_type'] != "DEP_MOIS"){
				// HISTOGRAMME POSITIF
				$flux_mensu_pos = $lc->getFluxParMois(true, false, $_GET['idcpt'],$_GET['idcat'],$date_fin,$date_debut);
				
				$nb_points = 0;
				foreach($flux_mensu_pos as $mois => $flux_pos){
					$data_pos .= "[Date.UTC(".substr($mois,0,4).", ".(substr($mois,5,2)-1).", 1), ".number_format($flux_pos,2,".","")."]";
					$nb_points++;
					
					if($nb_points != count($flux_mensu_pos)){
						$data_pos .= ",\n";
					}
				}
			}
			if($_GET['chart_type'] != "REV_MOIS"){
				// HISTOGRAMME NEGATIF
				$flux_mensu_neg = $lc->getFluxParMois(false, true, $_GET['idcpt'],$_GET['idcat'],$date_fin,$date_debut);
				$nb_points = 0;
				foreach($flux_mensu_neg as $mois => $flux_neg){
					$data_neg .= "[Date.UTC(".substr($mois,0,4).", ".(substr($mois,5,2)-1).", 1), ".(0+number_format($flux_neg,2,".",""))."]";
					$nb_points++;
					
					if($nb_points != count($flux_mensu_neg)){
						$data_neg .= ",\n";
					}
				}
			}
			if($_GET['chart_type']=="DEP_MOIS") $titre_chart = _("Monthly expenses");
			if($_GET['chart_type']=="REV_MOIS") $titre_chart = _("Monthly incomes");
			if($_GET['chart_type']=="REV_NET_MOIS") $titre_chart = _("Monthly net incomes");
			
			if(isset($_GET['idcpt']) && $_GET['idcpt'][0]!=""){
				if(count($_GET['idcpt'])==1){
					$c = new Compte($_GET['idcpt'][0]);
					$titre_chart .= " - ".$c->libelle;
				}else{
					$titre_chart .="";
				}
			}
			if(isset($_GET['idcat']) && $_GET['idcat'][0]!=""){
				if(count($_GET['idcat'])==1){
					$chart_cat = new Categorie($_GET['idcat'][0]);
					$titre_chart .= " - ".$chart_cat->nom;
				}else{
					$titre_chart .= "";
				}
			}else{
				$titre_chart .= " - "._("All categories");
			}


			?>
			<script>
			var chart;
			$(document).ready(function() {
				chart = new Highcharts.Chart({
					chart: {
						renderTo: 'graph',
						 zoomType: 'xy'
					},
					title: {
						text: '<?php echo $titre_chart; ?>'
					},
					xAxis: {
						type: 'datetime'
					},
					tooltip: {
						formatter: function() {
							var s = '<b>'+ Highcharts.dateFormat('%d/%m/%Y', this.x) +'</b>';	
							$.each(this.points, function(i, point) {
								s += '<br/>'+ point.series.name +': '+
									Highcharts.numberFormat(point.y,0,',',' ') +' \u20AC';
							});
							
							return s;
						},
						shared: true
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
						<?php if($_GET['chart_type'] != "DEP_MOIS"){ ?>
						{
							type:'column',
							color: '#67E168',
							name:'<?php echo _("Incomes"); ?>',
							data: [<?php echo $data_pos;?>]
						}
						<?php } ?>
						<?php if($_GET['chart_type'] == "REV_NET_MOIS") { ?>
						,
						<?php } ?>
						<?php if($_GET['chart_type'] != "REV_MOIS"){ ?>
						{
							type:'column',
							color: '#E16766',
							name:'<?php echo _("Expenses"); ?>',
							data: [<?php echo $data_neg;?>]
						}
						<?php } ?>
						<?php if($_GET['chart_type'] == "REV_NET_MOIS"){ ?>
						,{
							type:'line',
							name:'<?php echo _("Net incomes"); ?>',
							data: [<?php echo $data;?>]
						}
						<?php } ?>
						<?php if($_GET['chart_type'] != "REV_NET_MOIS"){ ?>
						,{
							type:'spline',
							name:'<?php echo _("Monthly average"); ?>',
							dashStyle:'ShortDot',
							data: [<?php echo $data_moy;?>]
						}
						<?php } ?>
					],
					plotOptions: {
						series: {
							cursor: 'pointer',	
							events: {
								click: function(event) {
									popup_transac(Highcharts.dateFormat('%Y-%m-%d', event.point.x),'MONTH');
								}
							},
							stacking: 'normal'
								
						},
						spline: {
							lineWidth: 4,
							enableMouseTracking:false,
							marker: {
								enabled: false  
							}
						}
						
					}
				});   
			});
		</script>
	<?php
			break;
		case "REV_NET_CAT" :
			
			//graph par catégorie
			$fluxs = $lc->getFluxParCategorie(true,true,$_GET['idcpt'],$_GET['idcat'],$date_fin,$date_debut);
			
			foreach($fluxs as $categorie => $flux){
				
				if($flux > 0){
					if($data_pos != "") $data_pos .= ",\n";
					$data_pos .= "['".$categorie."',".number_format($flux,2,".","")."]";
					
				}else{
					if($data_neg != "") $data_neg .= ",\n";
					$data_neg .= "['".$categorie."',".number_format($flux,2,".","")."]";
					
				}
			}
			
			$titre_chart =_("Net incomes per category");
			
			
			?>
			
			<script>
				var chart;
			$(document).ready(function() {
			   chart = new Highcharts.Chart({
				  chart: {
					 renderTo: 'graph'
				  },
				  title: {
					 text: '<?php echo $titre_chart; ?>'
				  },
				  plotOptions: {
					 pie: {
						allowPointSelect: true,
						cursor: 'pointer',
						dataLabels: {
						   enabled: true
						},
						showInLegend: false
					 }
				  },
				   tooltip: {
					 formatter: function() {
						return '<b>'+ this.point.name +'</b>: '+ this.point.y +' EUR';
					 }
				  },credits: {
						enabled: false
					},
					series: [{
						type: 'pie',
						name: '<?php echo _("Expenses"); ?>',
						data: [<?php echo $data_neg; ?>],
						center: [250, 180],
						size: 250
					},{
						type: 'pie',
						name: '<?php echo _("Incomes"); ?>',
						data: [<?php echo $data_pos; ?>],
						center: [700, 180],
						size: 250
					}
				  ]
			   });
			});
			</script>
			<?php
			break;
		case "SOL_HEBDO":
			$soldes = $lc->getSoldes($_GET['idcpt'],$date_fin,$date_debut, "+1 week");
			$nb_points = 0;
			foreach($soldes as $date => $solde){ //0000-00-00
				$data .= "[Date.UTC(".substr($date,0,4).", ".(substr($date,5,2)-1).", ".substr($date,8,2)."), ".number_format($solde,2,".","")."]";
				$nb_points++;				
				if($nb_points != count($soldes)){
					$data .= ",\n";
				}
				
				
			}
			//debug($data);
			$titre_chart = "";
			?>
			
			<script>
				$('document').ready(function(){
					//$('#chart_idcat').attr('disabled', 'disabled');
					$('#chart_idcat').multiselect("disable");
				});
				
				var chart;
				$(document).ready(function() {
					chart = new Highcharts.Chart({
					chart: {
						renderTo: 'graph',
						 zoomType: 'x'
					},
					title: {
						text: '<?php echo $titre_chart; ?>'
					},
					xAxis: {
						type: 'datetime'
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
							color: '#67E168',
							name:'<?php echo _("Balance"); ?>',
							data: [<?php echo $data;?>]
						}
					],
					plotOptions: {
						 series: {
							cursor: 'pointer',
							
						   events: {
								click: function(event) {
									popup_transac(Highcharts.dateFormat('%Y-%m-%d', event.point.x),'DAY');
								}
							}
							
						},
						area: {
							/*color: {
								linearGradient: [0, 0, 0, 300], 
								stops: [[-1, '#E167668'],[0, '#67E168'],[1, '#67E168']]
							},*/
							marker: {
							   enabled: false,
							   symbol: 'circle',
							   radius: 2,
							   states: {
								  hover: {
									 enabled: true
								  }
							   }
							}
						}
					  }
					});   
				});
				
			</script>
			
			<?php
			
			
			break;
	}
	?>
	<script>
		var tr_class = '';
		function popup_transac(date,type){
			
			var lib_id_comptes;
			var lib_id_categories;
			<?php
				if(is_array($_GET['idcpt'])){
					echo "lib_id_comptes = '&id_comptes[]=".implode('&id_comptes[]=',$_GET['idcpt'])."&';";
				}
				if(is_array($_GET['idcat'])){
					echo "lib_id_categories = '&id_categories[]=".implode('&id_categories[]=',$_GET['idcat'])."&';";
				}
			?>
			
			$( '#dialog_transac' ).dialog( "open" );
			$.getJSON('rpc.php?type=GET_TRANSAC_'+type+'&'+lib_id_comptes+'&'+lib_id_categories+'&date='+date+'',function(data){
					
				$.each(data, function(key, value){
					if(tr_class == 'pair'){
						tr_class = '';
					}else{
						tr_class = 'pair';
					}
					
					if(value.commentaire != ""){
						libelle = value.commentaire;
					}else{
						libelle = value.libelle;
					}
					$('#liste_transactions').append("<tr class='"+tr_class+"'>"+
										"<td class=''>"+value.lib_compte+"</td>"+
										"<td class='date'>"+value.date+"</td>"+
										"<td class='libelle nowrap'>"+libelle+"</td>"+
										"<td class='categorie lien nowrap' id='lib_cat_"+value.id+"' onclick=\"pop_edit_cat('"+value.id+"','"+value.cat_id+"');\" title=\""+value.categorie+"\">"+value.categorie+"</td>"+
										"<td class='lib_montant debit'>"+value.debit+"</td>"+
										"<td class='lib_montant credit'>"+value.credit+"</td>"+
										"</tr>");
				});
				
				$('#loading_spinner').hide();
				$('#liste_transactions').show();
			});
			
			
			
		}
	</script>
	<script src="./js/highcharts.js"></script>
	<div id="graph"></div>
	<br/><br/>
	
	<script>
		$('document').ready(function(){
			$('#liste_transactions').hide();
			$('#dialog_transac').dialog({
				autoOpen: false,
				height: 500,
				width: 950,
				modal: true,
				buttons: {
						"<?php echo _("Close"); ?>": function() {
							$( this ).dialog( "close" );
						}
					},
				close: function() {
					
					$('#transac_tbody').html('');
					$('#liste_transactions').hide();
					$('#loading_spinner').show();
					
				}		
			});
			
		});
	</script>
	<div id="dialog_transac">
		<table class="liste_transactions" id="liste_transactions">
			<thead>
				<tr>
					<th><?php echo _("Account"); ?></th>
					<th><?php echo _("Date"); ?></th>
					<th><?php echo _("Label"); ?></th>
					<th><?php echo _("Category"); ?></th>
					<th colspan='2'><?php echo _("Amount"); ?></th>
				</tr>
			</thead>
			<tbody id="transac_tbody">
			</tbody>
		</table>
		<div class="loading_spinner" id="loading_spinner"><img src="./images/ajax-loader-spinner.gif" /> <?php echo _("Loading transactions"); ?>...</div>
	</div>
	
	
</div> <!-- colonne_2 !-->


<?php
require_once(dirname(__FILE__) . '/inc/html/dialog_edit_cat.inc');
require_once(dirname(__FILE__) . '/inc/html/footer.inc');
?>