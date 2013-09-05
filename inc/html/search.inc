<script>
	
	var search_enabled = false;
	
	var tr_class = '';
	var lib_id_comptes = "";
	var lib_id_categories = "";
	var date_debut;
	var date_fin;
	var query;
	var id_cats;
	var id_cpts;
		
	function do_search(){
		console.log("RECHERCHE Lancement");
		
		search_enabled = true;
		
		//cache le contenu actuel
		$('.colonne_1').hide();
		$('.colonne_2').hide();
		
		$('#transac_tbody').html('');
		$('#loadin_search_spinner').show();
		$("#idcpt").multiselect("close");
		$("#idcat").multiselect("close");
		$('#search_result').show();
		
		console.log("RECHERCHE Interface OK");
		
		date_debut = $('#date_debut').val();
		date_fin = $('#date_fin').val();
		query = $('#q').val();
		
		lib_id_comptes = "";
		lib_id_categories = "";
		
		$('#idcpt option:selected').each(function(){
			lib_id_comptes = lib_id_comptes + "&idc[]=" + $(this).val();
		});
		$('#idcat option:selected').each(function(){
			lib_id_categories = lib_id_categories + "&idca[]=" + $(this).val();
		});
		console.log("RECHERCHE lib_id_comptes="+lib_id_comptes);
		console.log("RECHERCHE lib_id_categories="+lib_id_categories);
		
		$.getJSON('rpc.php?type=GET_TRANSAC_SEARCH&q='+query+'&'+lib_id_comptes+'&'+lib_id_categories+'&dd='+date_debut+'&df='+date_fin+'',function(data){
			console.log("RECHERCHE RPC OK");
			has_result = false;
			$.each(data, function(key, value){
				console.log("RECHERCHE Nouvelle ligne");
			
				has_result = true;
				if(tr_class == 'pair'){
					tr_class = '';
				}else{
					tr_class = 'pair';
				}
				
				
				$('#liste_transactions_resultat').append("<tr class='"+tr_class+"'>"+
									"<td class=''>"+value.lib_compte+"</td>"+
									"<td class='date'>"+value.date+"</td>"+
									"<td class='libelle nowrap'>"+value.libelle+"</td>"+
									"<td class='categorie lien nowrap' id='lib_cat_"+value.id+"' onclick=\"pop_edit_cat('"+value.id+"','"+value.cat_id+"');\" title=\""+value.categorie+"\">"+value.categorie+"</td>"+
									"<td class='lib_montant debit'>"+value.debit+"</td>"+
									"<td class='lib_montant credit'>"+value.credit+"</td>"+
									"</tr>");
			});
			if(has_result != true){
				console.log("RECHERCHE no result");
				$('#liste_transactions_resultat').append("<tr><td colspan='6' class='align_centre'>Aucun r&eacute;sultat</td></tr>");
			}
			
			$('#loadin_search_spinner').hide();
		});
		
		console.log("RECHERCHE Fin OK");
		
	}
	
	function remove_search(){
		$('.colonne_1').show();
		$('.colonne_2').show();
		$('#search_result').hide();
		$('#q').val('');
		search_enabled = false;
	}


</script>

<div id="search_result" style="display:none;">
	<span class="lien" id="hide_search">&lt;&lt; Retour
	<?php
		if(isset($_GET['idc'])){
			$c = new Compte($_GET['idc']);
			echo "&agrave; '".$c->libelle."'";
		}else{
			echo "&agrave; la liste des comptes";
		}
	?>
	</span><br/><br/>
	<script>
		$('#hide_search').click(function(){
			remove_search();
		});
	</script>
	<form onsubmit="do_search()">
		<script>
			$('document').ready(function(){
				var multiselect_options = {
						//header: false,
						close: function(){
							
						},
						checkAll: function(){
						  $.uniform.update("input:checkbox");
					   },
					   uncheckAll: function(){
						  $.uniform.update("input:checkbox");
					   },
						selectedList: 2
					}
				$('#idcpt').multiselect(multiselect_options);
				$('#idcat').multiselect(multiselect_options);
				
			});
		</script>
			Compte : 
		<select name="idcpt[]" id="idcpt" multiple="multiple">
			<?php 
				
				foreach($lc->comptes as $cpt){
					$selected = "";
					if($_GET['idc'] == $cpt->id){
						$selected = "selected='selected'";							
					}
					echo "<option value='".$cpt->id."' $selected>".$cpt->libelle."</option>\n";
				}
			?>
		</select>
		Cat&eacute;gorie : 
		<select name="idcat[]" id="idcat" multiple="multiple">
			<?php 
				foreach(Categorie::getListe() as $cat){
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
		Date min : <input type="text" name="dd" id="date_debut" size="10"/>
		Date max : <input type="text" name="df" id="date_fin" size="10"/>
		<input type="button" name="afficher" id="afficher" value="Afficher" onclick="do_search()"/>
	</form>

	
	<table class="liste_transactions" id="liste_transactions_resultat" width="100%">
		<thead>
			<tr>
				<th>Compte</th>
				<th>Date</th>
				<th>Libell&eacute;</th>
				<th>Cat&eacute;gorie</th>
				<th colspan='2'>Montant</th>
			</tr>
		</thead>
		<tbody id="transac_tbody">
		</tbody>
	</table>
	<div class="loadin_search_spinner" id="loadin_search_spinner"><img src="./img/ajax-loader-spinner.gif" /> Chargement des r&eacute;sultats...</div>

</div>