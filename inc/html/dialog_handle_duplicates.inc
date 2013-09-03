<script>
	function clear_duplicate_decision_list(){
		$('#table_duplicates_duplicates').find('tbody').html('');
		$('#table_duplicates_transactions').find('tbody').html('');
	}
	
	function call_for_duplicate_decision(duplicate){
		//param duplicate : L'existante=['transaction'] + La nouvelle=['duplicate']
		
		console.log("call_for_duplicate_decision");
		
		if(duplicate.duplicate.montant > 0){
			class_montant_duplicate = 'credit signe';
		}else{
			class_montant_duplicate = 'debit';
		}
		
		if(duplicate.transaction.montant > 0){
			class_montant_transaction = 'credit signe';
		}else{
			class_montant_transaction = 'debit';
		}
		$('#table_duplicates_duplicates').find('tbody').append("<tr class='ligne_transac duplicate' data-duplicate_id='"+duplicate.duplicate.duplicate_id+"' data-id_compte='"+duplicate.duplicate.id_compte+"' data-id_import='"+duplicate.duplicate.id_import+"' data-montant='"+duplicate.duplicate.montant+"' data-date_transaction='"+duplicate.duplicate.date_transaction+"'>"+
				"<td><input type='checkbox' name='' value='' /></td>"+
				"<td class='date'>"+format_date(duplicate.duplicate.date_transaction)+"</td>"+
				"<td></td>"+
				"<td class='libelle nowrap'>"+duplicate.duplicate.libelle+"</td>"+
				"<td class='categorie nowrap'>Sans catégorie</td>"+
				"<td class='montant lib_montant "+class_montant_duplicate+"'>"+number_format(duplicate.duplicate.montant)+"</td>"+
			"</tr>");
		
		//verification si la ligne n'a pas déjà été affichée
		existe = false;
		$('#table_duplicates_transactions').find('tr.transaction').each(function(){
			if($(this).data('id') == duplicate.transaction.id) existe = true;
		});
		if(existe === false){
			
			if(duplicate.transaction.categorie === null) duplicate.transaction.categorie = {nom:""};
			
			$('#table_duplicates_transactions').find('tbody').append("<tr class='ligne_transac transaction' data-id='"+duplicate.transaction.id+"'>"+
					"<td></td>"+
					"<td class='date'>"+format_date(duplicate.transaction.date_transaction)+"</td>"+
					"<td></td>"+
					"<td class='libelle nowrap'>"+duplicate.transaction.libelle+"</td>"+
					"<td class='categorie nowrap'>"+duplicate.transaction.categorie.nom+"</td>"+
					"<td class='montant lib_montant "+class_montant_transaction+"'>"+number_format(duplicate.transaction.montant)+"</td>"+
				"</tr>");
		}
		
		$('tr.duplicate').unbind('mouseover mouseout');
		$('tr.duplicate').mouseover(function(){
			$('tr.transaction[data-id=' + $(this).data('duplicate_id') + ']').css('background','#92CCF9');
			$('#table_duplicates_transactions').parent().scrollTo($('tr.transaction[data-id=' + $(this).data('duplicate_id') + ']'));
		}).mouseout(function(){
			$('tr.transaction[data-id=' + $(this).data('duplicate_id') + ']').css('background','');
		});
		
		$('#table_duplicates_duplicates').find('input[type=checkbox]').unbind('click');
		$('#table_duplicates_duplicates').find('input[type=checkbox]').click(function(){
			if(this.checked){
				$(this).parent().parent().css('background','#C6EFCE');
			}else{
				$(this).parent().parent().css('background','');
			}
		});
		
		$('.scrollable').getNiceScroll().resize();	
		
		return true;
	}
	
	function show_duplicate_decision(){
		//si la fenetre n'est pas affichée, on l'affiche
		if($("#dialog-handle-duplicate").dialog( "isOpen" ) === false){
			$("#dialog-handle-duplicate").dialog( "open" );
		}
	}
	
	
	$(document).ready(function(){
		
		
		sync_column_width('#table_duplicates_duplicates_head','#table_duplicates_duplicates');
		sync_column_width('#table_duplicates_transactions_head','#table_duplicates_transactions');
		
		$('#dialog-handle-duplicate').dialog({
			autoOpen: false,
			dialogClass: "alert",
			height: screen.height-200,
			width: screen.width-150,
			modal: true,
			buttons: {
				"Ajouter": function() {
					var nb_error = 0;
					$('#table_duplicates_duplicates').find('input[type=checkbox]').each(function(){
						console.log("Nouvelle checkbox, cochée ? " + this.checked);
						//ajout des lignes cochées
						if(this.checked){
							console.log("Validation ajout => ");
							duplicate_tr = $(this).parent().parent();
							console.log(duplicate_tr);
							
							var t_id_transac = 'NEWID_' + Math.floor(Math.random() * 11113);
							id_compte = duplicate_tr.data('id_compte');
							t_date_transaction = duplicate_tr.data('date_transaction');
							t_libelle = duplicate_tr.find("td[class='libelle nowrap']").html();
							t_id_categorie = 0;
							t_lib_categorie = "Sans catégorie";
							t_montant = duplicate_tr.data('montant');
							id_import = duplicate_tr.data('id_import');
							
							transaction = {id:t_id_transac, date_transaction:t_date_transaction, libelle:t_libelle, categorie:{nom:t_lib_categorie, id:t_id_categorie}, montant:t_montant};
							console.log(transaction);
							prepend_transaction(transaction);
							blink_transaction(transaction);
							
							$.ajax({
								url: 'rpc.php?type=ADD_TRANSAC&force=1&id='+t_id_transac+'&id_compte='+id_compte+'&date='+t_date_transaction+'&libelle='+t_libelle+'&id_categorie='+t_id_categorie+'&montant='+t_montant+'&id_import='+id_import,
								scriptCharset: "UTF-8",
								context: $(this),
								contentType: "application/x-www-form-urlencoded;charset=UTF-8",
								success: function(data) {
									
									if(data.transaction != ""){
										transaction = {id:data.old_id};
										update_transaction_id(transaction,data.transaction.id);
									}else if(! data.status){
										transaction = {id:data.old_id};
										remove_transaction(transaction);
										nb_error = nb_error + 1;
										
										if(noty_ko == null){
											noty_ko = noty({
												layout: 'top',
												type: 'warning',
												timeout: 10000,
												text: nb_error + ' à l\'ajout des transactions (Dernier msg : '+ data.error +') !'
											});
										}else{
											noty_ko.setText(nb_error + ' à l\'ajout des transactions (Dernier msg : '+ data.error +') !');
										}
									}					
								}
							});
						}else{
							
						}
					});
					//reset bindings
					set_edit_transac_bindings();
					
					//mise à jour des soldes par transaction
					reset_solde_transactions();
					
					$('#dialog-handle-duplicate').dialog("close");
				},
				"Fermer": function(){
					$('#dialog-handle-duplicate').dialog("close");
				}
			}
		});
		
		
	});
</script>
<div id="dialog-handle-duplicate" class="dialog" title="Gestion des transactions existantes">
	<div class='handle_duplicates_duplicates'>
	<h3>Liste des transactions à importer :</h3>(survolez une ligne avec votre souris pour identifier la transaction existante correspondante).<br/>	
	Veuillez cocher les lignes à importer :
	<form>
		<table class="liste_transactions table_header" id="table_duplicates_duplicates_head">
			<thead>
				<tr>
					<th></th>
					<th class="date">Date</th>
					<th class="edit_transac"></th>
					<th class="libelle">Libellé</th>
					<th class="categorie">Catégorie</th>
					<th class="montant">Montant</th>
				</tr>
			</thead>
		</table>
		<div class='scrollable' style="max-height:200px;">
			<table class="liste_transactions table_content" id="table_duplicates_duplicates">
			<tbody></tbody>
		</table>
		</div>
	</form></div>
	<div class='handle_duplicates_transactions'>
		<h3>Liste des transactions existantes :</h3>
		<table class="liste_transactions table_header" id="table_duplicates_transactions_head">
			<thead>
				<tr>
					<th></th>
					<th class="date">Date</th>
					<th class="edit_transac"></th>
					<th class="libelle">Libellé</th>
					<th class="categorie">Catégorie</th>
					<th class="montant">Montant</th>
				</tr>
			</thead>
		</table>
		<div class='scrollable' style="max-height:200px;">
			<table class="liste_transactions table_content" id="table_duplicates_transactions">
			<tbody></tbody>
		</table>
		</div>
	</div>
</div>