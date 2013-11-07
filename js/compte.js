solde_global = 0;
solde_users = new Array();


function get_listeComptes(){
	console.log('Chargement liste de comptes');
	
	$.getJSON('./rpc.php?type=GET_LISTE_CPT', function(data){
		
		comptes = eval(data);
		
		chaine = "";
		
		$('.liste_comptes').html('');
		
		for(var i=0 ; i < comptes.length ; i++)
		{
			compte = comptes[i];
			append_compte(compte);
			
		}
		set_listecomptes_bindings();
		
	});
	
}


var compte_clos_loaded = false;
function get_comptes_clos(){
	
	$('.lien_afficher_cpt_clos').html("<img src='./images/ajax-loader-spinner.gif' /> Chargement...");
			
	$.ajax({
		url: 'rpc.php?type=GET_LISTE_CPT_CLOS',
		dataType: "json",
		scriptCharset: "UTF-8",
		contentType: "application/x-www-form-urlencoded;charset=UTF-8",
		success: function(data) {
			comptes = eval(data);
			
			for(var i=0 ; i < comptes.length ; i++)
			{
				compte = comptes[i];
				compte.type.libelle = "Clos";
				append_compte(compte);
				
				
				
			}
			compte_clos_loaded = true;
			set_listecomptes_bindings();
			$('.lien_afficher_cpt_clos').html('');
			$('.liste_comptes').animate({ scrollTop: $('.liste_comptes').height() }, 'slow');
		}
	});	
	
	
	
}


function get_compte(id){
	console.log('Chargement compte ' + id);
	
	
	show_overlay();
	
	$('#lien_undo_import').hide();
	$('#import_link').show();
	
	$.getJSON('./rpc.php?type=GET_CPT&idc=' + id, function(compte){
		console.log('Reçu compte libelle ' + compte.libelle);
		
		//si compte clot, chargement dans la liste des comptes clos
		if(compte.cloture == 1 && compte_clos_loaded == false){
			get_comptes_clos();
		}
		
		//changement de compte actif
		$(".actif").removeClass('actif');
		$("li[data-id=" + id + "]").addClass('actif');
		
		$('#table_transac').data('nb_total_transactions',compte.nb_transactions);
		$('#table_transac').data('nb_total_transactions_chargees',0);
		
		//mise à jour de l'interface
		update_compte_data(compte);
		
		$('div.liste_transactions').animate({ scrollTop: 0 }, 'slow');
		
		//chargement des transactions
		$('#table_transac').find('tbody').html('');
		
		$.each(compte.transactions, function(key, value){
			append_transaction(value);
		});
		
		// ajout de lignes vides si pas nb_transac à afficher
		if(nb_transac > compte.nb_transactions){
			console.log((nb_transac - compte.nb_transactions) + ' lignes vides à ajouter');
			for(var i=0; i <= 24 - 1 - compte.nb_transactions; i++){ //(nb_transac - compte.nb_transactions)
				$('#table_transac').find('tbody').append("<tr><td colspan='6'>&nbsp;</td></tr>");
			}
		}
		
		sync_column_width('.table_header','.table_content');
		
		$('.scrollable').getNiceScroll().resize();
		
		//re-bind edit transac popup
		set_edit_transac_bindings();
		set_edit_categorie_bindings();
		reset_solde_transactions();
		
		hide_overlay();
		
		
		
	});			
}		

function update_compte_data(compte){
	//par defaut le compte mis à jour ne peut etre que le compte actif
	compte.id = $('.actif').data('id');
	
	console.log("Mise à jour des données du compte " + compte.id);
	console.log(compte);
	
	if(compte.cle != null) location.hash = compte.cle;
	
	if(compte.libelle_long != null || compte.libelle != null ){
		if(compte.libelle_long != null && compte.libelle_long != ''){
			$('#libelle_compte').html(compte.libelle_long);
			document.title = compte.libelle_long;
			//modification du compte dans la liste
			$('#cpt_'+compte.id).attr('title', compte.libelle_long);
		}else{
			$('#libelle_compte').html(compte.libelle);
			document.title = compte.libelle;	
			//modification du compte dans la liste
			$('#cpt_'+compte.id).attr('title', compte.libelle);			
		}
		
		$('#cpt_'+compte.id).find('.lib_compte').html(compte.libelle);
		
	}
	
	
	if(compte.etablissement != null) $('#etablissement').html(compte.etablissement);
	if(compte.url_acces != null) $('#etablissement').attr('href',compte.url_acces);
	if(compte.no_compte != null) $('#numero_compte').html(compte.no_compte);
	
	if(compte.latest_transaction_date != null){
		$('#date_max_transac_compte').data('date_max_transac_compte', compte.latest_transaction_date);
		$('#date_max_transac_compte').html(format_date(compte.latest_transaction_date));
	}
	
	if(compte.latest_import_date != null){
		$('#last_import_date').data('last_import_date', compte.latest_import_date);
		$('#last_import_date').html(format_date(compte.latest_import_date));
	}
	
	if(compte.solde_ouverture != null){
		old_solde_ouverture = Number($('#solde_cpt').data('solde_ouverture_cpt'));
		console.log("Ancien solde ouverture = " + old_solde_ouverture);
		console.log("Nouveau solde ouverture = " + compte.solde_ouverture);
		$('#solde_cpt').data('solde_ouverture_cpt',compte.solde_ouverture);
		//modification du compte dans la liste
		$('#cpt_'+compte.id).find('.solde_compte').data('solde_ouverture',compte.solde_ouverture);
		
		//si pas de nouveau solde courant envoyé, on le recalcule !
		if(compte.solde_courant == null){
			old_solde_courant = Number($('#solde_cpt').data('solde_cpt'));
			compte.solde_courant = old_solde_courant + Number(compte.solde_ouverture) - old_solde_ouverture;
			console.log("Ancien solde courant = " + old_solde_courant);
			console.log("Nouveau solde courant = " + compte.solde_courant);
		
		}
		
	}
	
	if(compte.solde_courant != null){
		old_solde_courant = Number($('#solde_cpt').data('solde_cpt'));
		
		$('#solde_cpt').data('solde_cpt',compte.solde_courant);
		$('#solde_cpt').html(number_format(compte.solde_courant)+" &#8364;");
		if(compte.solde_courant >= 0){
			$('#solde_cpt').addClass('credit signe').removeClass('debit');
		}else{
			$('#solde_cpt').removeClass('credit signe').addClass('debit');
		}
		
		//modification du compte dans la liste
		$('#cpt_'+compte.id).find('.solde_compte').data('solde_compte',compte.solde_courant);
		$('#cpt_'+compte.id).find('.solde_compte').html(number_format(compte.solde_courant) + ' &#8364;');
		if($('#cpt_'+compte.id).find('.solde_compte').data('solde_compte') > 0){
			$('#cpt_'+compte.id).find('.solde_compte').removeClass('debit').addClass('credit signe');
		}else{
			$('#cpt_'+compte.id).find('.solde_compte').removeClass('credit signe').addClass('debit');
		}
	
	}
	
	if((compte.solde_ouverture != null && compte.solde_ouverture != old_solde_ouverture) || (compte.solde_courant != null && compte.solde_courant != old_solde_courant)){
		reset_solde_transactions();
	}
	
	if(compte.type != null){
		//modification du compte dans la liste
		$('#cpt_'+compte.id).find('.type_compte').html(compte.type.libelle);
		$('#cpt_'+compte.id).find('.type_compte').data('id_type', compte.type.id);
	}
	
	//mise à jour solde global du ptf 
	/*
	solde_global = Number($('#solde_global').data('solde_global'));
	solde_global = solde_global + ecart_solde
	
	if(solde_global >= 0){
		$('#solde_global').removeClass('debit').addClass('credit signe');
	}else{
		$('#solde_global').removeClass('credit signe').addClass('debit');
	}
	t_solde_global = number_format(solde_global)+" &#8364;";
	$('#solde_global').html(t_solde_global);
	$('#solde_global').data('solde_global', solde_global);
	*/
	
	
}


function append_compte(compte){
	console.log('Nx compte ' + compte.libelle);		
			
	if(compte.solde_courant >= 0){
		style = 'credit signe';
	}else{
		style = 'debit';
	}
	
	$('.liste_comptes').append("<li class='compte' data-id='" + compte.id + "' data-cle='" + compte.cle + "' id='cpt_" + compte.id + "' title=\"" + compte.libelle_long + "\">"+
		"<div class='lib_compte nowrap'>" + compte.libelle + "</div>"+
		"<div class='sstitre_compte'>" +
		"	<div class='type_compte' data-id_type='"+compte.type.id+"'>" + compte.type.libelle + "</div>"+
		"	<div class='solde_compte align_droite " + style + "' data-solde_compte='"+compte.solde_courant+"' data-solde_ouverture='"+compte.solde_ouverture+"' id='solde_cpt_" + compte.id + "'>" + number_format(compte.solde_courant) + ' &#8364;' + "</div>"+
		"</div>"+
		"</li>");
	
	solde_global = solde_global + eval(compte.solde_courant);
	solde_users[eval(compte.id_titulaire)] = solde_users[compte.id_titulaire] + eval(compte.solde_courant);
	
	if(compte.id_titulaire > 0) solde_users[compte.id_titulaire] = solde_users[compte.id_titulaire] + compte.solde_courant;
		
	$('.scrollable').getNiceScroll().resize();
	
	//if(id_compte != 0) $('li[data-id=' + id_compte + ']').addClass('actif');
	
	$('#solde_global').data('solde_global', solde_global);
	$('#solde_global').html(number_format(solde_global) + ' &#8364;');
	
	if(solde_global >= 0){
		$('#solde_global').addClass('credit signe');
		$('#solde_global').removeClass('debit');
	}else{
		$('#solde_global').removeClass('credit signe');
		$('#solde_global').addClass('debit');
	}
}

function prepend_compte(compte){
	
	console.log('Nx compte ' + compte.libelle);		
			
	if(compte.solde_courant >= 0){
		style = 'credit signe';
	}else{
		style = 'debit';
	}
	
	$('.liste_comptes').prepend("<li class='compte' data-id='" + compte.id + "' data-cle='" + compte.cle + "' id='cpt_" + compte.id + "' title=\"" + compte.libelle_long + "\">"+
		"<div class='lib_compte nowrap'>" + compte.libelle + "</div>"+
		"<div class='sstitre_compte'>" +
		"	<div class='type_compte' data-id_type='"+compte.type.id+"'>" + compte.type.libelle + "</div>"+
		"	<div class='solde_compte align_droite " + style + "' data-solde_compte='"+compte.solde_courant+"' data-solde_ouverture='"+compte.solde_ouverture+"' id='solde_cpt_" + compte.id + "'>" + number_format(compte.solde_courant) + ' &#8364;' + "</div>"+
		"</div>"+
		"</li>");
	
	solde_global = solde_global + eval(compte.solde_courant);
	solde_users[eval(compte.id_titulaire)] = solde_users[compte.id_titulaire] + eval(compte.solde_courant);
	
	if(compte.id_titulaire > 0) solde_users[compte.id_titulaire] = solde_users[compte.id_titulaire] + compte.solde_courant;
		
	$('.scrollable').getNiceScroll().resize();
	
	//if(id_compte != 0) $('li[data-id=' + id_compte + ']').addClass('actif');
	
	$('#solde_global').data('solde_global', solde_global);
	$('#solde_global').html(number_format(solde_global) + ' &#8364;');
	
	if(solde_global >= 0){
		$('#solde_global').addClass('credit signe');
		$('#solde_global').removeClass('debit');
	}else{
		$('#solde_global').removeClass('credit signe');
		$('#solde_global').addClass('debit');
	}
	
}

function remove_compte(compte){
	
	if(compte.id != null){
		
		$('#cpt_'+compte.id).remove();
		
		solde_global = solde_global - eval(compte.solde_courant);
		
		if(compte.id_titulaire > 0) solde_users[compte.id_titulaire] = solde_users[compte.id_titulaire] - eval(compte.solde_courant);
			
		$('.scrollable').getNiceScroll().resize();
		
		//if(id_compte != 0) $('li[data-id=' + id_compte + ']').addClass('actif');
		
		$('#solde_global').data('solde_global', solde_global);
		$('#solde_global').html(number_format(solde_global) + ' &#8364;');
		
		if(solde_global >= 0){
			$('#solde_global').addClass('credit signe');
			$('#solde_global').removeClass('debit');
		}else{
			$('#solde_global').removeClass('credit signe');
			$('#solde_global').addClass('debit');
		}
		
		if(compte.id == default_id_compte){
			default_id_compte = $('.liste_comptes').first('li').data('id');			
		}
		
	}
}