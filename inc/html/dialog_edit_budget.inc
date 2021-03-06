<script>
		

		
		
		
		$(document).ready(function(){
			
			$('.reporting-budget-modifylink').click(function(){
				$('#dialog-edit-budget').dialog('open');
				id_budget = $(this).parent().parent().data('id_budget');
				$.ajax({
					url: 'rpc.php?type=GET_BDG&id_budget='+id_budget,
					dataType: "json",
					scriptCharset: "UTF-8",
					contentType: "application/x-www-form-urlencoded;charset=UTF-8",
					success: function(data) {
						budget = eval(data.budget);
						
						$('#b_edit_id').val(budget.id);
						$('#b_edit_nom').val(budget.nom);
						$('#b_edit_montant').val(number_format(budget.montant));
						
						$('#b_edit_actif').attr('checked',(budget.actif == 1));
						$('#b_edit_stats').attr('checked',(budget.statistiques == 1));
						
						$('input:text').setMask();
						
					}
				});
			});
			
			$('#loading_spin_editbudget')
				.hide()  // hide it initially
				.ajaxStart(function() {
					$(this).show();
				})
				.ajaxStop(function() {
					$(this).hide();
				})
			;
			
			$('#dialog-edit-budget').dialog({
				autoOpen: false,
				height: 270,
				width: 470,
				modal: true,
				buttons: {
					"<?php echo _("Save"); ?>": function() {
						
						b_id = $('#b_edit_id').val();
						b_nom = $('#b_edit_nom').val();
						b_montant = $('#b_edit_montant').val().replace(',','.').replace(' ','');
						b_actif = 0;
						if($('#b_edit_actif').attr('checked')) b_actif = 1;
						b_stats = 0;
						if($('#b_edit_stats').attr('checked')) b_stats = 1;
						
						//verification des données
						error = false;
						if(b_nom == ""){
							alert('<?php echo _("Please fill in a budget name"); ?> !!');
							$('#b_edit_nom').focus();
							return;
						}
						
						
						
						$.ajax({
							url: 'rpc.php?type=EDIT_BDG&id_budget='+b_id+'&nom='+b_nom+'&montant='+b_montant+'&actif='+b_actif+'&statistiques='+b_stats,
							contentType: "application/x-www-form-urlencoded;charset=UTF-8",
							context: $(this),
							scriptCharset: "UTF-8",
							success: function(data) {
								
								if(data != ""){ 
									$( this ).dialog( "close" );
									if(data.status){
										location.href = location.href;
									}else{
										notif = noty({
											layout: 'top',
											type: 'error',
											timeout: 3000,
											text: data.error
										});
									}									
								}else{
									alert('<?php echo _("Error while updating budget"); ?> !!');
								}					
							}
						});	
					},
					"<?php echo _("Delete"); ?>": function() {
						
						if(confirm("<?php echo _("Do you really want to delete budget ?\\n(All transaction affected to this budget will not be deleted but unaffected)"); ?>)")){
							id_budget = $(this).parent().parent().data('id_budget');
							$.ajax({
								url: 'rpc.php?type=DELETE_BDG&id_budget='+id_budget,
								dataType: "json",
								scriptCharset: "UTF-8",
								contentType: "application/x-www-form-urlencoded;charset=UTF-8",
								success: function(data) {
									if(data != ""){ 
										if(data.status){
											location.href = location.href;
										}else{
											notif = noty({
												layout: 'top',
												type: 'error',
												timeout: 3000,
												text: data.error
											});
										}									
									}else{
										alert('<?php echo _("Error while updating budget"); ?> !!');
									}	
									
								}
							});
						}
					},
					"<?php echo _("Cancel"); ?>": function() {
						$( this ).dialog( "close" );
					}
				},
				close: function() {
					$('#b_edit_id').val('');
					$('#b_edit_nom').val('');
					$('#b_edit_montant').val('');
					$('#b_edit_actif').attr('checked',false);
					$('#b_edit_stats').attr('checked',false);
				}		
			});
			
		});
	</script>
	<div id="dialog-edit-budget" title="<?php echo _("Edit budget"); ?>" class="dialog">

		<form>
			<p><?php echo _("Please fill in budget details"); ?> :</p>
			<input type='hidden' name='b_edit_id' id='b_edit_id' value=''/>
			<div class="diag_ligne">
				<div class="diag_libelle align_droite"><label for="b_edit_nom"><?php echo _("Name"); ?> :</label></div>
				<div class="diag_champ"><input name="b_edit_nom" id="b_edit_nom" size="45" maxlength="20"></div>
			</div>
			<div class="diag_ligne">
				<div class="diag_libelle align_droite"><label for="b_edit_montant"><?php echo _("Amount"); ?> :</label></div>
				<div class="diag_champ"><input name="b_edit_montant" id="b_edit_montant" size="8" maxlength="255" alt="montant_msk"></div>
			</div>
			<div class="diag_ligne">
				<div class="diag_libelle align_droite"><label for="b_edit_actif"><?php echo _("Active"); ?> :</label></div>
				<div class="diag_champ"><input type="checkbox" name="b_edit_actif" id="b_edit_actif"></div>
			</div>
			<div class="diag_ligne">
				<div class="diag_libelle align_droite"><label for="b_edit_stats"><?php echo _("Display in statistics"); ?> :</label></div>
				<div class="diag_champ"><input type="checkbox" name="b_edit_stats" id="b_edit_stats"></div>
			</div>
			<span id="loading_spin_editbudget" ><img src="./img/ajax-loader-spinner.gif" /> <?php echo _("Loading in progress"); ?>...</span>			
		</form>
	</div>