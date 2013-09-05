<script>
		
		$(document).ready(function(){
		
			$('#lien_edit_cpt').click(function(){
				$('#dialog-edit-cpt').dialog('open');
				$.ajax({
					url: 'rpc.php?type=GET_CPT_DATA&idc='+$('.actif').data('id'),
					dataType: "json",
					scriptCharset: "UTF-8",
					contentType: "application/x-www-form-urlencoded;charset=UTF-8",
					success: function(data) {
						compte = eval(data);
						
						$('#t_edit_cpt_id').val(compte.id);
						$('#t_edit_cpt_lib').val(compte.libelle);
						$('#t_edit_cpt_liblong').val(compte.libelle_long);
						$('#t_edit_cpt_etab').val(compte.etablissement);
						$('#t_edit_cpt_url').val(compte.url_acces);
						$('#t_edit_cpt_id_type').val(compte.id_type);
						$('#t_edit_cpt_no').val(compte.no_compte);
						$('#t_edit_cpt_soldeouv').val(number_format(compte.solde_ouverture));
						
						$('#t_edit_cpt_cloture').attr('checked',(compte.cloture == 1));
						$('#t_edit_cpt_is_synthese').attr('checked',(compte.is_synthese == 1));
						
						$('input:text').setMask();
						
					}
				});
			});
			
			
			
			$('#dialog-edit-cpt').dialog({
				autoOpen: false,
				height: 410,
				width: 470,
				modal: true,
				buttons: {
					"<?php echo _("Edit"); ?>": function() {
						
						t_cpt_id = $('#t_edit_cpt_id').val();
						t_cpt_lib = ($('#t_edit_cpt_lib').val());
						t_cpt_liblong = ($('#t_edit_cpt_liblong').val());
						t_cpt_etab = ($('#t_edit_cpt_etab').val());
						t_cpt_url = $('#t_edit_cpt_url').val();
						t_cpt_id_type = ($('#t_edit_cpt_id_type').val());
						t_cpt_lib_type = ($('#t_edit_cpt_id_type :selected').text());
						t_cpt_no = $('#t_edit_cpt_no').val();
						t_cpt_soldeouv = $('#t_edit_cpt_soldeouv').val().replace(',','.').replace(' ','');
						t_cpt_cloture = 0;
						if($('#t_edit_cpt_cloture').attr('checked')) t_cpt_cloture = 1;
						t_cpt_is_synthese = 0;
						if($('#t_edit_cpt_is_synthese').attr('checked')) t_cpt_is_synthese = 1;
						
						//verification des données
						error = false;
						if(t_cpt_lib == ""){
							alert('<?php echo _("Please fill in an account name"); ?> !!');
							$('#t_edit_cpt_lib').focus();
							return;
						}
						
						if(t_cpt_liblong == ""){
							t_cle = t_cpt_lib.replace(" ","_");
							
						}else{
							t_cle = t_cpt_liblong.replace(" ","_");
						}
						
						compte = {id:t_cpt_id, cle:t_cle, libelle:t_cpt_lib, libelle_long:t_cpt_liblong, etablissement:t_cpt_etab, url_acces:t_cpt_url, no_compte:t_cpt_no, solde_ouverture:t_cpt_soldeouv, type:{id:t_cpt_id_type, libelle:t_cpt_lib_type}}
						
						$('#dialog-edit-cpt').dialog('close');
						if(t_cpt_cloture == 1){
							$('.comptes_container').hide();
							$('.attente_container').show();
							
							get_listeComptes();
							get_compte(default_id_compte);		

							$('.attente_container').hide();
							$('.comptes_container').show();
												
						}else{
							update_compte_data(compte);
						}	
						
						$.ajax({
							url: 'rpc.php?type=UPDATE_CPT&id_cpt='+t_cpt_id+'&libelle='+t_cpt_lib+'&libelle_long='+t_cpt_liblong+'&etablissement='+t_cpt_etab+'&url_acces='+t_cpt_url+'&id_type='+t_cpt_id_type+'&no_compte='+t_cpt_no+'&solde_ouverture='+t_cpt_soldeouv+'&cloture='+t_cpt_cloture+'&is_synthese='+t_cpt_is_synthese,
							contentType: "application/x-www-form-urlencoded;charset=UTF-8",
							context: $(this),
							scriptCharset: "UTF-8",
							success: function(data) {
								
								if(data.status){ 
									
									notif = noty({
										layout: 'top',
										type: 'success',
										timeout: 3000,
										text: '<?php echo _("The account"); ?> ' + data.compte.libelle_long + ' <?php echo _("has been updated"); ?> !'
									});									
								}else{
									$('#dialog-edit-cpt').dialog('close');
									notif = noty({
										layout: 'top',
										type: 'error',
										timeout: 3000,
										text: data.error
									});
								}					
							}
						});	
					},
					"Supprimer": function() {
						
						if(confirm("<?php echo _("Do you really want to delete this account ?\n(All transactions will be erased)"); ?>")){
							t_cpt_id = $('#t_edit_cpt_id').val();
							compte = {id:t_cpt_id};
							
							$('#dialog-edit-cpt').dialog('close');
							remove_compte(compte);
							get_compte(default_id_compte);
							$.ajax({
								url: 'rpc.php?type=DELETE_CPT&id_cpt='+t_cpt_id,
								contentType: "application/x-www-form-urlencoded;charset=UTF-8",
								context: $(this),
								scriptCharset: "UTF-8",
								success: function(data) {
									if(data.status){ 
										
										notif = noty({
											layout: 'top',
											type: 'success',
											timeout: 3000,
											text: '<?php echo _("The account has been deleted"); ?> !'
										});									
									}else{
										$('#dialog-edit-cpt').dialog('close');
										notif = noty({
											layout: 'top',
											type: 'error',
											timeout: 3000,
											text: data.error
										});
									}	
								}
							});
						}else{
							$( '#dialog-edit-cpt' ).dialog( "close" );
						}
					},
					"<?php echo _("Cancel"); ?>": function() {
						$( '#dialog-edit-cpt' ).dialog( "close" );
					}
				},
				close: function() {
					$('#t_edit_cpt_id').val('');
					$('#t_edit_cpt_lib').val('');
					$('#t_edit_cpt_liblong').val('');
					$('#t_edit_cpt_etab').val('');
					$('#t_edit_cpt_url').val('');
					$('#t_edit_cpt_id_type').val('');
					$('#t_edit_cpt_no').val('');
					$('#t_edit_cpt_soldeouv').val('');
					$('#t_edit_cpt_cloture').attr('checked',false);
					$('#t_edit_cpt_is_synthese').attr('checked',false);
				}		
			});
			
		});
	</script>
	<div id="dialog-edit-cpt" title="<?php echo _("Edit account"); ?>" class="dialog">

		<form>
			<input type="hidden" id="t_edit_cpt_id" value="" />
			<p><?php echo _("Please fill in account details"); ?> :</p>
			<div class="diag_ligne">
				<div class="diag_libelle align_droite"><label for="t_edit_cpt_lib"><?php echo _("Label"); ?> :</label></div>
				<div class="diag_champ"><input name="t_edit_cpt_lib" id="t_edit_cpt_lib" size="45" maxlength="20"></div>
			</div>
			<div class="diag_ligne">
				<div class="diag_libelle align_droite"><label for="t_edit_cpt_liblong"><?php echo _("Detailed label"); ?> :</label></div>
				<div class="diag_champ"><input name="t_edit_cpt_liblong" id="t_edit_cpt_liblong" size="45" maxlength="255"></div>
			</div>
			<div class="diag_ligne">
				<div class="diag_libelle align_droite"><label for="t_edit_cpt_etab"><?php echo _("Bank"); ?> :</label></div>
				<div class="diag_champ"><input name="t_edit_cpt_etab" id="t_edit_cpt_etab" size="45" maxlength="255"></div>
			</div>
			<div class="diag_ligne">
				<div class="diag_libelle align_droite"><label for="t_edit_cpt_url"><?php echo _("URL"); ?> :</label></div>
				<div class="diag_champ"><input name="t_edit_cpt_url" id="t_edit_cpt_url" size="45" maxlength="255"></div>
			</div>
			<div class="diag_ligne">
				<div class="diag_libelle align_droite"><label for="t_edit_cpt_id_type"><?php echo _("Account type"); ?> :</label></div>
				<div class="diag_champ">
					<select  name="t_edit_cpt_id_type" id="t_edit_cpt_id_type">
					<?php
						foreach(TypeCompte::getListe() as $type_compte){
							
							echo "<option value='".$type_compte->id."'>".htmlentities($type_compte->libelle_long)."</option>\n";
							
						}
						
					?>
					</select>
					
				</div>
			</div>
			<div class="diag_ligne">
				<div class="diag_libelle align_droite"><label for="t_edit_cpt_no"><?php echo _("Account number"); ?> :</label></div>
				<div class="diag_champ"><input name="t_edit_cpt_no" id="t_edit_cpt_no" size="45" maxlength="26"></div>
			</div>
			<div class="diag_ligne">
				<div class="diag_libelle align_droite"><label for="t_edit_cpt_soldeouv"><?php echo _("Opening balance"); ?> :</label></div>
				<div class="diag_champ"><input name="t_edit_cpt_soldeouv" id="t_edit_cpt_soldeouv" size="8" maxlength="255" alt="montant_msk"></div>
			</div>
			<div class="diag_ligne">
				<div class="diag_libelle align_droite"><label for="t_edit_cpt_cloture"><?php echo _("Closed"); ?> :</label></div>
				<div class="diag_champ"><input type="checkbox" name="t_edit_cpt_cloture" id="t_edit_cpt_cloture"></div>
			</div>
			<div class="diag_ligne">
				<div class="diag_libelle align_droite"><label for="t_edit_cpt_is_synthese"><?php echo _("Display in dashboard"); ?> :</label></div>
				<div class="diag_champ"><input type="checkbox" name="t_edit_cpt_is_synthese" id="t_edit_cpt_is_synthese"></div>
			</div>
			<span id="loading_spin_editcpt" class="loading_spin" ><img src="./img/ajax-loader-spinner.gif" /> <?php echo _("Loading in progress"); ?>...</span>
			
		</form>
	</div>