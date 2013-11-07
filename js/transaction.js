function get_transactions(){
			
	id_compte = $('.actif').data('id');
	start_transac = Number($('#table_transac').data('nb_total_transactions_chargees'));
	nb_tot_transac = Number($('#table_transac').data('nb_total_transactions'));
	
	if( start_transac <= nb_tot_transac){
							
		
		console.log('Compte ' + id_compte + ', chargement de ' + nb_transac + ' a partir de la transaction n° ' + start_transac);
		$('#loading_spinner').show();
		$.getJSON("./rpc.php?type=GET_TRANSAC_CPT&idc="+id_compte+"&start="+start_transac+"&nb="+nb_transac+"",function(data){
			
			//si chargement initial, vide le tableau
			$.each(data, function(key, value){
				append_transaction(value);
			});
			
			sync_column_width('.table_header','.table_content');
			
			$('.scrollable').getNiceScroll().resize();
			
			//re-bind edit transac popup
			set_edit_transac_bindings();
			set_edit_categorie_bindings();
			
			
			$('#loading_spinner').hide();			
		});	
	}
}
		
function append_transaction(transaction){
	
	console.log("Append transaction " + transaction.id + " dated " + transaction.date_transaction);
	
	if($('#table_transac').data('nb_total_transactions_chargees') > 0){
		solde_courant_cpt = Number($('.ligne_transac').last().find('.solde').data('solde'));
		montant_transac_precedente = Number($('.ligne_transac').last().find('.montant').data('montant'));
	}else{
		solde_courant_cpt = Number($('#solde_cpt').data('solde_cpt'));
		montant_transac_precedente = 0;
	}
	
	
	solde_courant_cpt = solde_courant_cpt - montant_transac_precedente;
	montant_transac_precedente = transaction.montant;
	
	if(transaction.montant > 0){
		class_montant = 'credit signe';
	}else{
		class_montant = 'debit';
	}
	
	if(solde_courant_cpt > 0){
		solde_format = "+"+number_format(solde_courant_cpt);
	}else{
		solde_format = number_format(solde_courant_cpt);
	}
	
	$('#table_transac').find('tbody').append("\n<tr class='ligne_transac' id='"+transaction.id+"'>"+
						"\n<td class='date' data-date='"+transaction.date_transaction+"'>"+format_date(transaction.date_transaction)+"</td>"+
						"\n<td class='edit_transac lien'><img class='edit_transac_icon' src='./img/file_edit.png' width='16px' height='16px' title='Modifier la transaction "+transaction.id+"'/></td>"+
						"\n<td class='libelle nowrap' title=\""+transaction.libelle+"\">"+transaction.libelle+"</td>"+
						"\n<td class='categorie lien nowrap' data-id='"+transaction.categorie.id+"' title=\""+transaction.categorie.nom+"\">"+transaction.categorie.nom+"</td>"+
						"\n<td class='montant lib_montant "+class_montant+"' data-montant='"+transaction.montant+"'>"+number_format(transaction.montant)+" &#8364;</td>"+
						"\n<td class='solde lib_montant' data-solde='"+solde_courant_cpt+"'>"+solde_format+" &#8364;</td>"+
						"\n</tr>");
	
	//incremente le numéro de la derniere transaction chargée
	$('#table_transac').data('nb_total_transactions_chargees', Number($('#table_transac').data('nb_total_transactions_chargees')) + 1);
	
}

function prepend_transaction(transaction){
	
	console.log("Prepend transaction " + transaction.id + " dated " + transaction.date_transaction + " cat ID " + transaction.id_categorie + " cat Name " + transaction.categorie.nom);
	
	solde_cpt = $('#solde_cpt').data('solde_cpt');
	
	//solde global du compte
	solde_cpt = Number(solde_cpt) + Number(transaction.montant);
	if(solde_cpt >= 0){
		t_solde = "+"+number_format(solde_cpt);
		$('#solde_cpt').removeClass('debit').addClass('credit signe');
	}else{
		t_solde = number_format(solde_cpt);
		$('#solde_cpt').removeClass('credit signe').addClass('debit');
	}
	t_solde_final = number_format(solde_cpt)+" &#8364;";
	$('#solde_cpt').data('solde_cpt',solde_cpt);
	$('#solde_cpt').html(t_solde_final);
	
	
	//solde dans la liste des comptes
	id_compte = $('.actif').data('id');
	$('#solde_cpt_'+id_compte).data('solde_cpt', solde_cpt);
	$('#solde_cpt_'+id_compte).html(t_solde_final);
	
	
	//solde global du ptf de comptes
	solde_global = Number(solde_global) + Number(transaction.montant);
	if(solde_global >= 0){
		$('#solde_global').removeClass('debit').addClass('credit signe');
	}else{
		$('#solde_global').removeClass('credit signe').addClass('debit');
	}
	t_solde_global = number_format(solde_global)+" &#8364;";
	$('#solde_global').data('solde_global', t_solde_global);
	$('#solde_global').html(t_solde_global);
	
	
	//solde global user, si le compte est rattaché à un utilisateur
	/*
	if(id_user_cpt > 0){
		solde_users[id_user_cpt] = Number(solde_users[id_user_cpt]) + Number(transaction.montant);
		if(solde_users[id_user_cpt] > 0){
			$('#solde_user_'+id_user_cpt).removeClass('debit').addClass('credit signe');
		}else{
			$('#solde_user_'+id_user_cpt).removeClass('credit signe').addClass('debit');
		}
		t_solde_user = number_format(solde_users[id_user_cpt])+" &#8364;";
		$('#solde_user_'+id_user_cpt).html(t_solde_user);
	}
	*/
	
	if(transaction.montant > 0){
		class_montant = 'credit signe';
	}else{
		class_montant = 'debit';
	}
	
	$('#table_transac').find('tbody').prepend("<tr class='ligne_transac' id='"+transaction.id+"'>"+
		"<td class='date' data-date='"+transaction.date_transaction+"'>"+format_date(transaction.date_transaction)+"</td>"+
		"<td class='edit_transac lien'><img class='edit_transac_icon' src='./img/file_edit.png' width='16px' height='16px' title='Modifier la transaction "+transaction.id+"'/></td>"+
		"<td class='libelle' title=\""+transaction.libelle+"\">"+transaction.libelle+"</td>"+
		"<td class='categorie lien' data-id='"+transaction.categorie.id+"' title=\""+transaction.categorie.nom+"\">"+transaction.categorie.nom+"</td>"+
		"<td class='montant lib_montant "+class_montant+"' data-montant='"+transaction.montant+"'>"+number_format(transaction.montant)+" &#8364;</td>"+
		"<td class='solde lib_montant' data-solde='"+solde_cpt+"'>"+t_solde+" &#8364;</td></tr>");
	
	sync_column_width('.table_header','.table_content');
	
	//incremente le numéro de la derniere transaction chargée
	$('#table_transac').data('nb_total_transactions_chargees', Number($('#table_transac').data('nb_total_transactions_chargees')) + 1);
	
	
}

function remove_transaction(transaction){
	console.log("Suppression transaction " + transaction.id);
	
	if(transaction.id != null){
		
		old_montant = Number($('#' + transaction.id).find('.montant').data('montant'));
		$('#'+transaction.id).remove();
		
		//mise à jour du solde courant du compte
		my_solde_courant = Number($('#solde_cpt').data('solde_cpt')) - old_montant;
		compte = {solde_courant:my_solde_courant};
		update_compte_data(compte);
		
		//recalcul des soldes par transaction
		reset_solde_transactions();
		sync_column_width('.table_header','.table_content');
	}
	
}

function update_transaction_data(old_transaction, transaction){

	console.log("Mise à jour des données de la transaction " + transaction.id);
	
	if(transaction.id != null){
		if(transaction.date_transaction != null) $('#' + transaction.id).find('.date').html(format_date(transaction.date_transaction));
		if(transaction.libelle != null) $('#' + transaction.id).find('.libelle').html(transaction.libelle);
		
		if(transaction.categorie != null){
			$('#' + transaction.id).find('.categorie').html(transaction.categorie.nom);
			$('#' + transaction.id).find('.categorie').data('id',transaction.categorie.id);
			$('#' + transaction.id).find('.categorie').attr('title', transaction.categorie.nom);
		}
		
		if(transaction.montant != null){
			old_montant = Number($('#' + transaction.id).find('.montant').data('montant'));
			
			$('#' + transaction.id).find('.montant').data('montant',transaction.montant);
			$('#' + transaction.id).find('.montant').html(number_format(transaction.montant) + " &#8364;");
			if(transaction.montant > 0){
				$('#' + transaction.id).find('.montant').addClass('credit signe').removeClass('debit');
				
			}else{
				$('#' + transaction.id).find('.montant').removeClass('credit signe').addClass('debit');
			}			
			
			if(transaction.montant != old_montant){
				
				//mise à jour du solde courant du compte
				my_solde_courant = Number($('#solde_cpt').data('solde_cpt')) + (transaction.montant - old_montant);
				compte = {solde_courant:my_solde_courant};
				update_compte_data(compte);
				
				//recalcul des soldes par transaction
				reset_solde_transactions();
			}
		}
		blink_transaction(transaction);
		sync_column_width('.table_header','.table_content');
		
		$('#' + transaction.id).trigger('transactionChange', [old_transaction, transaction]);
	}
}

function update_transaction_categorie(transaction, categorie){
	console.log('update_transaction_categorie');
	if(transaction.id != null && categorie.id != null){
	
		old_html = $('#' + transaction.id).find('.categorie').html();
		old_id = $('#' + transaction.id).find('.categorie').data('id');
		old_title = $('#' + transaction.id).find('.categorie').attr('title');
		
		$('#' + transaction.id).find('.categorie').html(categorie.nom);
		$('#' + transaction.id).find('.categorie').data('id',categorie.id);
		$('#' + transaction.id).find('.categorie').attr('title', categorie.nom);
		
		blink_transaction(transaction);
		sync_column_width('.table_header','.table_content');
		
		$('#' + transaction.id).trigger('categorieChange', [transaction, categorie]);
		
	}
}

function update_transaction_id(transaction, new_id){
	console.log("Changement d'ID de la transaction " + transaction.id + " -> " +new_id);
	
	
	$('#' + transaction.id).attr('id',new_id);
	$('#' + new_id).find('img').attr('title','Modifier la transaction '+new_id);
}

function reset_solde_transactions(){
	
	console.log("Mise à jour du solde par transaction");
	
	//lecture du solde final
	solde_courant_cpt = Number($('#solde_cpt').data('solde_cpt'));			
	montant_transac_precedente = 0;
	
	$('#table_transac > tbody  > tr').each(function() {
		
		solde_courant_cpt = solde_courant_cpt - montant_transac_precedente;
		montant_transac_precedente = Number($(this).find('.lib_montant').data('montant'));
		
		if(solde_courant_cpt > 0){
			solde_format = "+"+number_format(solde_courant_cpt);
		}else{
			solde_format = number_format(solde_courant_cpt);
		}
		
		$(this).find('.solde').data('solde_cpt',solde_courant_cpt);
		$(this).find('.solde').html(solde_format + " &#8364;");				
		
	});
	
}

//highlight updated transaction
function blink_transaction(transaction){
	
	$('#' + transaction.id).addClass('highlight', 100).removeClass('highlight', 100).addClass('highlight', 100).delay(100).removeClass('highlight', 100);
			
}