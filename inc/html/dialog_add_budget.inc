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
					"<?php echo _("Add"); ?>": function() {
						
						b_nom = $('#b_add_nom').val();
						b_montant = $('#b_add_montant').val().replace(',','.').replace(' ','');
						b_actif = 0;
						if($('#b_add_actif').attr('checked')) b_actif = 1;
						b_stats = 0;
						if($('#b_add_stats').attr('checked')) b_stats = 1;
						
						//verification des données
						error = false;
						if(b_nom == ""){
							alert('<?php echo _("Please fill in a budget name"); ?> !!');
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
									alert('<?php echo _("Error while creating budget"); ?> !!');
								}					
							}
						});	
					},
					"<?php echo _("Cancel"); ?>": function() {
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
	<div id="dialog-add-budget" title="<?php echo _("Add new budget"); ?>" class="dialog">

		<form>
			<p><?php echo _("Please fill in budget data"); ?> :</p>
			<div class="diag_ligne">
				<div class="diag_libelle align_droite"><label for="b_add_nom"><?php echo _("Name"); ?> :</label></div>
				<div class="diag_champ"><input name="b_add_nom" id="b_add_nom" size="45" maxlength="20"></div>
			</div>
			<div class="diag_ligne">
				<div class="diag_libelle align_droite"><label for="b_add_montant"><?php echo _("Amount"); ?> :</label></div>
				<div class="diag_champ"><input name="b_add_montant" id="b_add_montant" size="8" maxlength="255" alt="montant_msk"></div>
			</div>
			<div class="diag_ligne">
				<div class="diag_libelle align_droite"><label for="b_add_actif"><?php echo _("Active"); ?> :</label></div>
				<div class="diag_champ"><input type="checkbox" name="b_add_actif" id="b_add_actif" checked='checked'></div>
			</div>
			<div class="diag_ligne">
				<div class="diag_libelle align_droite"><label for="b_add_stats"><?php echo _("Display in statistics"); ?> :</label></div>
				<div class="diag_champ"><input type="checkbox" name="b_add_stats" id="b_add_stats" checked='checked'></div>
			</div>
			<span id="loading_spin_addbudget" ><img src="./img/ajax-loader-spinner.gif" /> <?php echo _("Loading in progress"); ?>...</span>			
		</form>
	</div>