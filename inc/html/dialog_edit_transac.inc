<script>
	var transac;
	
	function set_edit_transac_bindings() {
		console.log("Set edit transac bindings");
		
		
		
		$('.edit_transac_icon').hide();
		
		$('.ligne_transac').unbind('hover');
		$('.ligne_transac').hover(function() {
			$(this).find('.edit_transac_icon').fadeIn(50);
		}, function() {
			$(this).find('.edit_transac_icon').fadeOut(50);
		});
		
		$('.edit_transac').unbind('click');
		$('.edit_transac').click(function() {
			edit_transac_id = $(this).parent().attr('id');
			$('#dialog-edit-transac').dialog('open');
			$.ajax({
				url: 'rpc.php?type=GET_TRANSAC&idt=' + edit_transac_id,
				dataType: "json",
				context: $(this),
				scriptCharset: "UTF-8",
				contentType: "application/x-www-form-urlencoded;charset=UTF-8",
				success: function(data) {
					transac = eval(data);
					$('#t_edit_id_transac').val(transac.id);
					$('#t_edit_id_compte').val(transac.id_compte);
					$('#t_edit_date').val(format_date(transac.date_transaction));
					$('#t_edit_lib').val(transac.libelle);
					$('#t_edit_comm').val(transac.commentaire);
					$('#t_edit_idcat').val(transac.id_categorie);
					$('#t_edit_idbudget').val(transac.id_budget);
					$('#t_edit_montant').val(number_format(transac.montant));
					$('#t_edit_date_import').html(format_date(transac.date_import));
					$('#t_edit_date_miseajour').html(format_date(transac.date_maj));
					$('input:text').setMask();
				}
			});
		});
	}
	
	$(document).ready(function() {
		set_edit_transac_bindings();
		
		$('#dialog-edit-transac').dialog({
			autoOpen: false,
			height: 370,
			width: 470,
			modal: true,
			buttons: {
				"<?php echo _("Edit"); ?>": function() {
					t_id_transac = $('#t_edit_id_transac').val();
					t_id_compte = $('#t_edit_id_compte').val();
					t_date = $('#t_edit_date').val();
					t_libelle = ($('#t_edit_lib').val());
					t_commentaire = ($('#t_edit_comm').val());
					t_id_categorie = $('#t_edit_idcat').val();
					t_id_budget = $('#t_edit_idbudget').val();
					t_lib_categorie = ($('#t_edit_idcat :selected').text());
					t_lib_budget = ($('#t_edit_idbudget :selected').text());
					t_montant = $('#t_edit_montant').val().replace(',', '.').replace(' ', '');
					
					//verification des données
					error = false;
					if (t_date == "") {
						alert('<?php echo _("Please fill in a transaction date"); ?> !!');
						$('#t_edit_date').focus();
						return;
					}
					if (t_libelle == "") {
						alert('<?php echo _("Please fill in a transaction label"); ?> !!');
						$('#t_edit_lib').focus();
						return;
					}
					if (t_montant == "") {
						alert('<?php echo _("Please fill in a transaction amount"); ?> !!');
						$('#t_edit_montant').focus();
						return;
					}
					
					transaction = {id:t_id_transac, date_transaction:deformat_date(t_date), libelle:t_libelle, categorie:{nom:t_lib_categorie, id:t_id_categorie}, montant:t_montant};
					
					$('#dialog-edit-transac').dialog("close");
					update_transaction_data(transac,transaction);
					
					if (error == false) {
						$.ajax({
							url: 'rpc.php?type=UPDATE_TRANSAC&id_transac=' + t_id_transac + '&date=' + t_date + '&libelle=' + t_libelle + '&commentaire=' + t_commentaire + '&id_categorie=' + t_id_categorie + '&id_budget=' + t_id_budget + '&montant=' + t_montant,
							scriptCharset: "UTF-8",
							context: $(this),
							contentType: "application/x-www-form-urlencoded;charset=UTF-8",
							success: function(data) {
								if (data.status) {
									
									//pour mise à jour des last_date_...
									update_compte_data(data.transaction.compte);
									
								} else {
									//rollback
									update_transaction_data(transaction,transac);
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
				"<?php echo _("Delete"); ?>": function() {
					t_id_transac = $('#t_edit_id_transac').val();
					id_compte = $('.actif').data('id');
					if (confirm('<?php echo _("Do you really want to delete transaction"); ?> ?')) {
						
						transaction = {id:t_id_transac}
						
						$('#dialog-edit-transac').dialog("close");
						remove_transaction(transaction);
									
									
									
						$.ajax({
							url: 'rpc.php?type=DELETE_TRANSAC&id_transac=' + t_id_transac,
							scriptCharset: "UTF-8",
							context: $(this),
							contentType: "application/x-www-form-urlencoded;charset=UTF-8",
							success: function(data) {
								if (data.status) {
									
								} else {
									prepend_transaction(data.transaction);
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
					$('#dialog-edit-transac').dialog("close");
				}
			},
			close: function() {
				$('#t_edit_date').val('');
				$('#t_edit_lib').val('');
				$('#t_edit_comm').val('');
				$('#t_edit_idcat').val('0');
				$('#t_edit_idbudget').val('');
				$('#t_edit_montant').val('');
			}
		});
		$("#t_edit_date").datepicker({
			dateFormat: 'dd/mm/yy',
			firstDay: 1,
			dayNamesShort: ['Dim', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam'],
			dayNamesMin: ['Di', 'Lu', 'Ma', 'Me', 'Je', 'Ve', 'Sa'],
			dayNames: ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'],
			monthNames: ['Janvier', 'Fevrier', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Aout', 'Septembre', 'Octobre', 'Novembre', 'Decembre'],
			maxDate: '+0d'
		});
	});
</script>
<div id="dialog-edit-transac" title="<?php echo _("Edit transaction"); ?>" class="dialog">
	<form>
		<input type="hidden" name="t_edit_id_transac" id="t_edit_id_transac" value=""
		/>
		<input type="hidden" name="t_edit_id_compte" id="t_edit_id_compte" value="" />
		<p><?php echo _("Please fill in transaction details"); ?> :</p>
		<div class="diag_ligne">
			<div class="diag_libelle align_droite">
				<label for="t_edit_lib"><?php echo _("Label"); ?> :</label>
			</div>
			<div class="diag_champ">
				<input name="t_edit_lib" id="t_edit_lib" size="45" maxlength="255">
			</div>
		</div>
		<div class="diag_ligne">
			<div class="diag_libelle align_droite">
				<label for="t_edit_comm"><?php echo _("Comment"); ?> :</label>
			</div>
			<div class="diag_champ">
				<input name="t_edit_comm" id="t_edit_comm" size="45" maxlength="255">
			</div>
		</div>
		<div class="diag_ligne">
			<div class="diag_libelle align_droite">
				<label for="t_edit_date"><?php echo _("Date"); ?> :</label>
			</div>
			<div class="diag_champ">
				<input name="t_edit_date" id="t_edit_date" size="12">
			</div>
		</div>
		<div class="diag_ligne">
			<div class="diag_libelle align_droite">
				<label for="t_edit_montant"><?php echo _("Amount"); ?> :</label>
			</div>
			<div class="diag_champ">
				<input name="t_edit_montant" id="t_edit_montant" size="8" alt="montant_msk">&#8364;</div>
		</div>
		<div class="diag_ligne">
			<div class="diag_libelle align_droite">
				<label for="t_edit_idcat"><?php echo _("Category"); ?> :</label>
			</div>
			<div class="diag_champ">
				<select data-placeholder="<?php echo _("Category"); ?>..." name="t_edit_idcat" id="t_edit_idcat">
					<option value=''></option>
					<?php 
					foreach(Categorie::getListe() as $cat){
						echo "<option value='".$cat->id."'>".htmlentities($cat->nom)."</option>\n";
					}
					?></select> <i>(<?php echo _("optional"); ?>)</i>

			</div>
		</div>
		<div class="diag_ligne">
			<div class="diag_libelle align_droite">
				<label for="t_edit_idbudget"><?php echo _("Budget"); ?> :</label>
			</div>
			<div class="diag_champ">
				<select data-placeholder="<?php echo _("Budget"); ?>..." name="t_edit_idbudget" id="t_edit_idbudget">
					<option value=''></option>
					<?php 
					foreach(Budget::getListe() as $b){
						echo "<option value='".$b->id."'>".htmlentities($b->nom)."</option>\n";
					}
					?>
					</select> <i>(<?php echo _("optional"); ?>)</i>

			</div>
		</div>
		<div class="diag_ligne info_transac_popup"><?php echo _("Import date"); ?> : <span id="t_edit_date_import"></span>
			<br/><?php echo _("Last update date"); ?> : <span id="t_edit_date_miseajour"></span>

		</div>	<span id="loading_spin_edittransac" class="loading_spin"><img src="./img/ajax-loader-spinner.gif" /> <?php echo _("Loading in progress"); ?>...</span>

	</form>
</div>