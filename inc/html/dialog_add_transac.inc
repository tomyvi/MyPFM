<script>
		
		$(document).ready(function(){
			$('#lien_add_transac').click(function(){
				$('#dialog-add-transac').dialog('open');
				$('input:text').setMask();
			});
			
			$('#loading_spin_addtransac')
				.hide()  // hide it initially
				.ajaxStart(function() {
					$(this).show();
				})
				.ajaxStop(function() {
					$(this).hide();
				})
			;
			
			$('#dialog-add-transac').dialog({
				autoOpen: false,
				height: 330,
				width: 450,
				modal: true,
				buttons: {
					"<?php echo _("Add"); ?>": function() {
						
						t_date = $('#t_add_date').val();
						t_libelle = ($('#t_add_lib').val());
						t_commentaire = ($('#t_add_comm').val());
						t_id_categorie = $('#t_add_idcat').val();
						t_lib_categorie = ($('#t_add_idcat :selected').text());
						t_id_budget = $('#t_add_idbudget').val();
						t_lib_budget = ($('#t_add_idbudget :selected').text());
						t_montant = $('#t_add_montant').val().replace(',','.').replace(' ','');
						
						//verification des données
						error = false;
						if(t_date == ""){
							alert('<?php echo _("Please fill in a transaction date"); ?> !!');
							$('#t_add_date').focus();
							return;
						}
						if(t_libelle == ""){
							alert('<?php echo _("Please fill in a transaction label"); ?> !!');
							$('#t_add_lib').focus();
							return;
						}
						if(t_montant == ""){
							alert('<?php echo _("Please fill in a transaction amount"); ?> !!');
							$('#t_add_montant').focus();
							return;
						}
						
						if(error == false){
							id_compte = $('.actif').data('id');
							t_date = deformat_date(t_date);
							
							t_id_transac = 'NEWID_' + Math.floor(Math.random()*111111);
							transaction = {id:t_id_transac, date_transaction:t_date, libelle:t_libelle, categorie:{nom:t_lib_categorie, id:t_id_categorie}, montant:t_montant};
					
							$( '#dialog-add-transac' ).dialog( "close" );
							prepend_transaction(transaction);
							blink_transaction(transaction);
							//reset bindings
							set_edit_transac_bindings();
							
							
							$.ajax({
								url: 'rpc.php?type=ADD_TRANSAC&force=0&id='+t_id_transac+'&id_compte='+id_compte+'&date='+t_date+'&libelle='+t_libelle+'&commentaire='+t_commentaire+'&id_categorie='+t_id_categorie+'&id_budget='+t_id_budget+'&montant='+t_montant,
								scriptCharset: "UTF-8",
								context: $(this),
    							contentType: "application/x-www-form-urlencoded;charset=UTF-8",
								success: function(data) {
									
									if(data.duplicate != null){
										transaction = {id:data.old_id};
										remove_transaction(transaction);
										clear_duplicate_decision_list();
										call_for_duplicate_decision(data.duplicate);
										show_duplicate_decision();
										notif = noty({
											layout: 'top',
											type: 'warning',
											timeout: 3000,
											text: '<?php echo _("Transaction already exists, unable to duplicate"); ?> !'
										});
										
									}else if(data.transaction != null){
										
										update_transaction_id(transaction,data.transaction.id);
									}else if(! data.status){
										remove_transaction(transaction);
										notif = noty({
											layout: 'top',
											type: 'error',
											timeout: 3000,
											text: data.error
										});
									}					
								}
							});	
							
						}
					},
					"<?php echo _("Cancel"); ?>": function() {
						$( '#dialog-add-transac' ).dialog( "close" );
					}
				},
				close: function() {
					$('#t_add_date').val('');
					$('#t_add_lib').val('');
					$('#t_add_comm').val('');
					$('#t_add_idcat').val('');
					$('#t_add_idbudget').val('');
					$('#t_add_montant').val('');
				}		
			});
			
			$("#t_add_date").datepicker({
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
	<div id="dialog-add-transac" title="<?php echo _("Add transaction"); ?>" class="dialog">

		<form>
			<p><?php echo _("Please fill in transaction details"); ?> :</p>
			<div class="diag_ligne">
				<div class="diag_libelle align_droite"><label for="t_add_lib"><?php echo _("Label"); ?> :</label></div>
				<div class="diag_champ"><input name="t_add_lib" id="t_add_lib" size="45" maxlength="255"></div>
			</div>
			<div class="diag_ligne">
				<div class="diag_libelle align_droite"><label for="t_add_comm"><?php echo _("Comment"); ?> :</label></div>
				<div class="diag_champ"><input name="t_add_comm" id="t_add_comm" size="45" maxlength="255"></div>
			</div>
			<div class="diag_ligne">
				<div class="diag_libelle align_droite"><label for="t_add_date"><?php echo _("Date"); ?> :</label></div>
				<div class="diag_champ"><input name="t_add_date" id="t_add_date" size="10"></div>
			</div>
			<div class="diag_ligne">
				<div class="diag_libelle align_droite"><label for="t_add_montant"><?php echo _("Amount"); ?> :</label></div>
				<div class="diag_champ"><input name="t_add_montant" id="t_add_montant" size="8" alt="montant_msk"> &#8364;</div>
			</div>
			<div class="diag_ligne">
				<div class="diag_libelle align_droite"><label for="t_add_idcat"><?php echo _("Category"); ?> :</label></div>
				<div class="diag_champ">
					<select data-placeholder="<?php echo _("Category"); ?>..." name="t_add_idcat" id="t_add_idcat" class="uniform">
						<?php 
							foreach(Categorie::getListe() as $cat){
								if($cat->id==0){
									$selected = "selected='selected'";
								}else{
									$selected = "";
								}
								echo "<option value='".$cat->id."' $selected>".htmlentities($cat->nom)."</option>\n";
							}
						?>
					</select> <i>(<?php echo _("optional"); ?>)</i>
				</div>
			</div>
			<div class="diag_ligne">
				<div class="diag_libelle align_droite"><label for="t_add_idbudget"><?php echo _("Budget"); ?> :</label></div>
				<div class="diag_champ">
					<select data-placeholder="<?php echo _("Budget"); ?>..." name="t_add_idbudget" id="t_add_idbudget" class="uniform">
						<option></option>
						<?php 

							foreach(Budget::getListe() as $b){

								echo "<option value='".$b->id."'>".htmlentities($b->nom)."</option>\n";

							}

						?>

					</select> <i>(<?php echo _("optional"); ?>)</i>
				</div>
			</div>
			<span id="loading_spin_addtransac" ><img src="./img/ajax-loader-spinner.gif" /> <?php echo _("Loading in progress"); ?>...</span>
		</form>
	</div>