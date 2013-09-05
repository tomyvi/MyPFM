<?php

require_once(dirname(__FILE__) ."/inc/init.inc.php");


require_once(dirname(__FILE__) . '/inc/html/header.inc');

?>
<div class="reporting-budget-header">
	<span class="lien" id="reporting-budget-showall-link"><?php echo _("Display all"); ?></span> / <span class="lien" id="reporting-budget-hideall-link"><?php echo _("Hide all"); ?></span>
</div>
<?php

foreach(Budget::getListe() as $b){
	
	if($b->statistiques){
	
		$data =  "{name: '"._("Remaining balance")."', data: [".($b->montant + $b->solde)."], color: '#E16766'}, ";
		$data .= "{name: '"._("Balance left")."', data: [".(0 - $b->solde)."], color: '#67E168'}";
		
		$per_cats = $b->getTransactionsParCategorie();
		$nb_pos = 0;
		$data2 = "";
		foreach($per_cats as $per_cat){
			
			if($per_cat['montant']<0){
				$data2 .= "['".$per_cat['libelle']."',".number_format(0 - $per_cat['montant'],2,".","")."]";
				$nb_pos++;
				if($nb_pos != count($per_cats)){
					$data2 .=",\n";
				}
			}
		}
		
		?>
		
		<script>
		$('#menu_etats').addClass('current');

		
		var chart_budget_<?php echo $b->id; ?>;
		var chart_cat_<?php echo $b->id; ?>;
		$(document).ready(function() {
			chart_budget_<?php echo $b->id; ?> = new Highcharts.Chart({
				chart: {
					renderTo: 'chart_budget_<?php echo $b->id; ?>',
					type: 'bar',
					height: 100
				},
				title: {
					text: '<?php echo addslashes($b->nom); ?>'
				},
				tooltip: {
					formatter: function() {
						return this.series.name + ':' + Highcharts.numberFormat(this.y,0,',',' ') +' \u20AC';
					}},
				yAxis:{
					min: 0,
					title: false,
					labels: {
						formatter: function() {
							if(false){ //this.value >= 1000){
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
			
			chart_cat_<?php echo $b->id; ?> = new Highcharts.Chart({
				chart: {
					renderTo: 'chart_cat_<?php echo $b->id; ?>',
					height: 250
				},
				title: {
					text: '<?php echo _("Expenses per category"); ?>'
				},
				plotOptions: {
					pie: {
						allowPointSelect: false,
						cursor: 'pointer',
						dataLabels: {enabled: true},
						showInLegend: false
					}
				},
				tooltip: {
					formatter: function() {
						return '<b>'+ this.point.name +'</b>: '+ (0 - this.point.y) +' EUR';
					}
				},credits: {
					enabled: false
				},
				series: [{
					type: 'pie',
					data: [<?php echo $data2; ?>],
					point:{
						events: {
							click: function(){
								//alert ('Category: '+ this.name +', value: '+ this.y);
							}
						}
					}
				}]
			});
			
		});
		
		
		</script>
		<div class='reporting-budget-graphcontainer'>
			<div class='graph_budget' data-id_budget='<?php echo $b->id; ?>'>
				<div>
					<div class="reporting-budget-transactions" id="budget_more_transac_<?php echo $b->id; ?>" style="display:none;">
						<table class="liste_transactions table_header" id='table_transac_header'>
							<thead>
								<tr>
									<th class="date"><?php echo _("Date"); ?></th>
									<th class="edit_transac"></th>
									<th class="libelle"><?php echo _("Label"); ?></th>
									<th class="categorie"><?php echo _("Category"); ?></th>
									<th class="montant"><?php echo _("Amount"); ?></th>
								</tr>
							</thead>
						</table>
						<div class="scrollable" style="max-height:335px;">
							<table class="liste_transactions table_content" id='table_transac'>
								<tbody>
	<?php
			for ($i = 0; $i <= 15; $i++) {
				echo "<tr><td colspan='5'>&nbsp;</td></tr>";
			}
	?>								
								</tbody>
							</table>
						</div>
					</div>
					<div class="reporting-budget-graphbar" id="chart_budget_<?php echo $b->id; ?>"></div>
					
				</div>
				<div class="reporting-budget-graphdepenses" id="chart_cat_<?php echo $b->id; ?>" style="display:hidden;"></div>
				<div class="reporting-budget-showmore">
					<span class='lien reporting-budget-showmorelink'><?php echo _("Show details"); ?></span> / 
					<span class="reporting-budget-modifylink"><span class='lien'><?php echo _("Edit"); ?></span></span>
					
				</div>
			</div>
		</div>
		
		
		
		<?php
		}
	}
	?>
	
	<script>
	
	function update_categorie_graph(id_budget, lib_categorie, montant){
		var found = false;
		$.each(eval('chart_cat_' + id_budget).series[0].points, function(index, point){
			if(point.name == lib_categorie){
				found = true;
				point.update(point.y + eval(montant));
				
				if(point.y == 0){
					point.remove();
				}
			}
		});
		
		if(!found){
			eval('chart_cat_' + id_budget).series[0].addPoint({name:lib_categorie,y:montant});
		}
		
	}
	
	function update_bar_graph(id_budget, delta_montant){
		eval('chart_budget_' + id_budget).series[0].points[0].update(eval('chart_budget_' + id_budget).series[0].points[0].y - delta_montant);
		eval('chart_budget_' + id_budget).series[1].points[0].update(eval('chart_budget_' + id_budget).series[1].points[0].y + delta_montant);
		
	}
	
	function fetchTransactions(id_budget){
		
		if(!isNaN(parseFloat(id_budget))){
			
			$.ajax({
				url: 'rpc.php?type=SEARCH_TRANSAC&id_budget[]='+id_budget,
				context: $(this),
				success: function(data) {
					if(data.status){
						console.log("Chargement OK");
						$('div[data-id_budget=' + id_budget + ']').find('tbody').html('');
						$.each(data.transactions, function(key, transaction){
							
							if(transaction.montant > 0){
								class_montant = 'credit signe';
							}else{
								class_montant = 'debit';
							}
							$('div[data-id_budget=' + id_budget + ']').find('tbody').append("<tr class='ligne_transac' id='"+transaction.id+"'>"+
								"<td class='date' data-date='"+transaction.date_transaction+"'>"+format_date(transaction.date_transaction)+"</td>"+
								"<td class='edit_transac lien'><img class='edit_transac_icon' src='./img/file_edit.png' width='16px' height='16px' title='Modifier la transaction "+transaction.id+"'/></td>"+
								"<td class='libelle' title=\""+transaction.libelle+"\">"+transaction.libelle+"</td>"+
								"<td class='categorie lien' data-id='"+transaction.categorie.id+"' title=\""+transaction.categorie.nom+"\">"+transaction.categorie.nom+"</td>"+
								"<td class='montant lib_montant "+class_montant+"' data-montant='"+transaction.montant+"'>"+number_format(transaction.montant)+" &#8364;</td>"+
							"</tr>");
						});	
						
						if(data.nb_transactions < 16){
							for(var i=0; i <= 16 - 1 - data.nb_transactions; i++){ //(nb_transac - compte.nb_transactions)
								$('div[data-id_budget=' + id_budget + ']').find('tbody').append("<tr><td colspan='5'>&nbsp;</td></tr>");
							}
						}
						
						$('.scrollable').getNiceScroll().resize();
						set_edit_categorie_bindings();
						set_edit_transac_bindings();
						setCategorieChangeBinding();
						sync_column_width('.table_header','.table_content');
						
					}else{						
						console.log("Chargement KO");
						notif = noty({
							layout: 'top',
							type: 'error',
							timeout: 3000,
							text: data.error
						});						
					}
				},
				error: function(){
					notif = noty({
						layout: 'top',
						type: 'error',
						timeout: 3000,
						text: '<?php echo _("Unable to load transactions for budget"); ?> ' + id_budget + ' !'
					});
				}
			});
			
		}
		
	}
	
	function toggleBudgetDisplay(id_budget, force){
		var visible = $('#budget_more_transac_'+id_budget).is(':visible');
		
		force = (typeof force === "undefined") ? '' : force;
		
		if(force === 'hide'){
			visible = true;
		}else if(force === 'show'){
			visible = false;
		}
		
		if(visible){
			$('#budget_more_transac_'+id_budget).hide();
			$('#chart_cat_'+id_budget).hide();
			$('div[data-id_budget=' + id_budget + ']').width('');
			$('div[data-id_budget=' + id_budget + ']').find('.reporting-budget-showmorelink').html('<?php echo _("Show details"); ?>');
			$('.scrollable').getNiceScroll().resize();
		}
		if(!visible){
			if($('div[data-id_budget=' + id_budget + ']').find('td.date').size() == 0){
				$.when(fetchTransactions(id_budget)).done(function(a3){
					$('div[data-id_budget=' + id_budget + ']').width('1005px');
					$('#budget_more_transac_'+id_budget).show();
					$('#chart_cat_'+id_budget).show();
					$('div[data-id_budget=' + id_budget + ']').find('.reporting-budget-showmorelink').html('<?php echo _("Hide details"); ?>');
					sync_column_width('#table_transac_header','#table_transac');
					$('.scrollable').getNiceScroll().resize();
				});
			}else{
				$('div[data-id_budget=' + id_budget + ']').width('1005px');
				$('#budget_more_transac_'+id_budget).show();
				$('#chart_cat_'+id_budget).show();
				$('div[data-id_budget=' + id_budget + ']').find('.reporting-budget-showmorelink').html('<?php echo _("Hide details"); ?>');
				sync_column_width('#table_transac_header','#table_transac');
				$('.scrollable').getNiceScroll().resize();
			}
			
			
		}
	}
	
	function setCategorieChangeBinding(){
		
		$('tr').unbind('categorieChange');
		$('tr').unbind('transactionChange');
		
		$('tr').on('categorieChange',function(event, transaction, categorie){
			
			id_budget = $(this).parent().parent().parent().parent().parent().parent().data('id_budget');
			console.log('EVENT categorieChange on transaction ' + transaction.id + ' for budget ' + id_budget);
			
			if(id_budget > 0){
				update_categorie_graph(id_budget, transaction.categorie.nom, eval(transaction.montant)); 
				update_categorie_graph(id_budget, categorie.nom, 0 - eval(transaction.montant));           // montant est négatif, on l'affiche en positif
			}
			
		});
		
		$('tr').on('transactionChange',function(event, old_transaction, new_transaction){
			id_budget = $(this).parent().parent().parent().parent().parent().parent().data('id_budget');
			console.log('EVENT transactionChange on transaction ' + transaction.id + ' for budget ' + id_budget);
			
			if(id_budget > 0 && old_transaction.montant != new_transaction.montant){
				update_bar_graph(id_budget, 0 - eval(new_transaction.montant - old_transaction.montant));  // montant est négatif, on l'affiche en positif
				
			}
			
		});
	}
	
	$(document).ready(function(){
		$('.reporting-budget-transactions').hide();
		$('.reporting-budget-graphdepenses').hide();
		
		
		
		//bindings
		$('.reporting-budget-showmorelink').click(function(){
			var id_budget = $(this).parent().parent().data('id_budget');
			toggleBudgetDisplay(id_budget);
		});
		
		$('#reporting-budget-showall-link').click(function(){
			$('.graph_budget').each(function(){
				var id_budget = $(this).data('id_budget');
				toggleBudgetDisplay(id_budget,'show');
			});
		});
		$('#reporting-budget-hideall-link').click(function(){
			$('.graph_budget').each(function(){
				var id_budget = $(this).data('id_budget');
				toggleBudgetDisplay(id_budget,'hide');
			});
		});
		
		
	});
	</script>
	
	
<?php

require_once(dirname(__FILE__) . '/inc/html/dialog_edit_transaccat.inc');
require_once(dirname(__FILE__) . '/inc/html/dialog_edit_transac.inc');
require_once(dirname(__FILE__) . '/inc/html/dialog_edit_budget.inc');

require_once(dirname(__FILE__) . '/inc/html/footer.inc');

?>