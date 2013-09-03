<script>
		

		
		
		
		$(document).ready(function(){
			
			
			
			$('#lien_add_budget').click(function(){
				$('#dialog-add-budget').dialog('open');				
			});
			
			$('#loading_spin_addbudget')
				.hide()  // hide it initially
				.ajaxStart(function() {
					$(this).show();
				})
				.ajaxStop(function() {
					$(this).hide();
				})
			;
			
			$('#dialog-add-budget').dialog({
				autoOpen: false,
				height: 250,
				width: 470,
				modal: true,
				buttons: {
					"Ajouter": function() {
						
						b_nom = $('#b_add_nom').val();
						b_montant = $('#b_add_montant').val().replace(',','.').replace(' ','');
						b_actif = 0;
						if($('#b_add_actif').attr('checked')) b_actif = 1;
						b_stats = 0;
						if($('#b_add_stats').attr('checked')) b_stats = 1;
						
						//verification des données
						error = false;
						if(b_nom == ""){
							alert('Merci de saisir un nom pour le budget !!');
							$('#b_add_nom').focus();
							return;
						}
						
						$.ajax({
							url: 'rpc.php?type=ADD_BDG&nom='+b_nom+'&montant='+b_montant+'&actif='+b_actif+'&statistiques='+b_stats,
							contentType: "application/x-www-form-urlencoded;charset=UTF-8",
							context: $(this),
							scriptCharset: "UTF-8",
							success: function(data) {
								
								if(data != ""){ 
									$( this ).dialog( "close" );
									if(data.status){
										location.href = location.href
									}else{
										notif = noty({
											layout: 'top',
											type: 'error',
											timeout: 3000,
											text: data.error
										});
									}									
								}else{
									alert('Erreur lors de la création du budget !!');
								}					
							}
						});	
					},
					"Annuler": function() {
						$( this ).dialog( "close" );
					}
				},
				close: function() {
					$('#b_add_nom').val('');
					$('#b_add_montant').val('');
					$('#b_add_actif').attr('checked',false);
					$('#b_add_stats').attr('checked',false);
				}		
			});
			
		});
	</script>
	<div id="dialog-add-budget" title="Ajouter un budget" class="dialog">

		<form>
			<p>Veuillez saisir les informations pour le budget :</p>
			<div class="diag_ligne">
				<div class="diag_libelle align_droite"><label for="b_add_nom">Nom :</label></div>
				<div class="diag_champ"><input name="b_add_nom" id="b_add_nom" size="45" maxlength="20"></div>
			</div>
			<div class="diag_ligne">
				<div class="diag_libelle align_droite"><label for="b_add_montant">Montant :</label></div>
				<div class="diag_champ"><input name="b_add_montant" id="b_add_montant" size="8" maxlength="255" alt="montant_msk"></div>
			</div>
			<div class="diag_ligne">
				<div class="diag_libelle align_droite"><label for="b_add_actif">Actif :</label></div>
				<div class="diag_champ"><input type="checkbox" name="b_add_actif" id="b_add_actif" checked='checked'></div>
			</div>
			<div class="diag_ligne">
				<div class="diag_libelle align_droite"><label for="b_add_stats">Afficher dans les statistiques :</label></div>
				<div class="diag_champ"><input type="checkbox" name="b_add_stats" id="b_add_stats" checked='checked'></div>
			</div>
			<span id="loading_spin_addbudget" ><img src="./img/ajax-loader-spinner.gif" /> Chargement en cours...</span>			
		</form>
	</div>