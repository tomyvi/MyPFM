<script>		
		$(document).ready(function(){
			keypress.combo("alt shift c", function() {
				$('#lien_add_cpt').click();
			});
			
			$('#lien_add_cpt').click(function(){
				$('#dialog-add-cpt').dialog('open');				
			});
			
			$('#loading_spin_addcpt')
				.hide()  // hide it initially
				.ajaxStart(function() {
					$(this).show();
				})
				.ajaxStop(function() {
					$(this).hide();
				})
			;
			
			$('#dialog-add-cpt').dialog({
				autoOpen: false,
				height: 410,
				width: 470,
				modal: true,
				buttons: {
					"<?php echo _("Add"); ?>": function() {
						
						t_cpt_id = $('#t_add_cpt_id').val();
						t_cpt_lib = ($('#t_add_cpt_lib').val());
						t_cpt_liblong = ($('#t_add_cpt_liblong').val());
						t_cpt_etab = ($('#t_add_cpt_etab').val());
						t_cpt_url = $('#t_add_cpt_url').val();
						t_cpt_id_type = ($('#t_add_cpt_id_type').val());
						t_cpt_no = $('#t_add_cpt_no').val();
						t_cpt_soldeouv = $('#t_add_cpt_soldeouv').val().replace(',','.').replace(' ','');
						t_cpt_is_synthese = 0;
						if($('#t_add_cpt_is_synthese').attr('checked')) t_cpt_is_synthese = 1;
						
						//verification des données
						error = false;
						if(t_cpt_lib == ""){
							alert('<?php echo _("Please fill in an account name"); ?> !!');
							$('#t_add_cpt_lib').focus();
							return;
						}
						
						if(t_cpt_liblong == ""){
							t_cpt_cle = t_cpt_lib.replace(" ","_");
							
						}else{
							t_cpt_cle = t_cpt_liblong.replace(" ","_");
						}
						
						$.ajax({
							url: 'rpc.php?type=ADD_CPT&cle='+t_cpt_cle+'&&libelle='+t_cpt_lib+'&libelle_long='+t_cpt_liblong+'&etablissement='+t_cpt_etab+'&url_acces='+t_cpt_url+'&id_type='+t_cpt_id_type+'&no_compte='+t_cpt_no+'&solde_ouverture='+t_cpt_soldeouv+'&is_synthese='+t_cpt_is_synthese,
							contentType: "application/x-www-form-urlencoded;charset=UTF-8",
							context: $(this),
							scriptCharset: "UTF-8",
							success: function(data) {
								
								if(data != ""){ 
									$( this ).dialog( "close" );
									if(data.status){
										if(location.href.indexOf("display.php") !== -1){
											prepend_compte(data.compte);
											get_compte(data.compte.id);
										}else{
											location.href = './display.php#' + t_cpt_cle;
										}
									}else{
										notif = noty({
											layout: 'top',
											type: 'error',
											timeout: 3000,
											text: data.error
										});
									}									
								}else{
									alert('<?php echo _("Error when creating account"); ?> !!');
								}					
							}
						});	
					},
					"<?php echo _("Cancel"); ?>": function() {
						$( this ).dialog( "close" );
					}
				},
				close: function() {
					$('#t_add_cpt_id').val('');
					$('#t_add_cpt_lib').val('');
					$('#t_add_cpt_liblong').val('');
					$('#t_add_cpt_etab').val('');
					$('#t_add_cpt_url').val('');
					$('#t_add_cpt_id_type').val('');
					$('#t_add_cpt_no').val('');
					$('#t_add_cpt_soldeouv').val('');
					$('#t_add_cpt_is_synthese').attr('checked',false);
				}		
			});
			
		});
	</script>
	<div id="dialog-add-cpt" title="<?php echo _("Add account"); ?>" class="dialog">

		<form>
			<input type="hidden" id="t_add_cpt_id" value="" />
			<p><?php echo _("Please fill in account details"); ?> :</p>
			<div class="diag_ligne">
				<div class="diag_libelle align_droite"><label for="t_add_cpt_lib"><?php echo _("Label"); ?> :</label></div>
				<div class="diag_champ"><input name="t_add_cpt_lib" id="t_add_cpt_lib" size="45" maxlength="20"></div>
			</div>
			<div class="diag_ligne">
				<div class="diag_libelle align_droite"><label for="t_add_cpt_liblong"><?php echo _("Detailed label"); ?> :</label></div>
				<div class="diag_champ"><input name="t_add_cpt_liblong" id="t_add_cpt_liblong" size="45" maxlength="255"></div>
			</div>
			<div class="diag_ligne">
				<div class="diag_libelle align_droite"><label for="t_add_cpt_etab"><?php echo _("Bank"); ?> :</label></div>
				<div class="diag_champ"><input name="t_add_cpt_etab" id="t_add_cpt_etab" size="45" maxlength="255"></div>
			</div>
			<div class="diag_ligne">
				<div class="diag_libelle align_droite"><label for="t_add_cpt_url"><?php echo _("URL"); ?> :</label></div>
				<div class="diag_champ"><input name="t_add_cpt_url" id="t_add_cpt_url" size="45" maxlength="255"></div>
			</div>
			<div class="diag_ligne">
				<div class="diag_libelle align_droite"><label for="t_add_cpt_id_type"><?php echo _("Account type"); ?> :</label></div>
				<div class="diag_champ">
					<select  name="t_add_cpt_id_type" id="t_add_cpt_id_type">
					<?php
						foreach(TypeCompte::getListe() as $type_compte){
							
							echo "<option value='".$type_compte->id."'>".htmlentities($type_compte->libelle_long)."</option>\n";
							
						}
						
					?>
					</select>
					
				</div>
			</div>
			<div class="diag_ligne">
				<div class="diag_libelle align_droite"><label for="t_add_cpt_no"><?php echo _("Account number"); ?> :</label></div>
				<div class="diag_champ"><input name="t_add_cpt_no" id="t_add_cpt_no" size="45" maxlength="26"></div>
			</div>
			<div class="diag_ligne">
				<div class="diag_libelle align_droite"><label for="t_add_cpt_soldeouv"><?php echo _("Opening balance"); ?> :</label></div>
				<div class="diag_champ"><input name="t_add_cpt_soldeouv" id="t_add_cpt_soldeouv" size="8" maxlength="255" alt="montant_msk"></div>
			</div>
			<div class="diag_ligne">
				<div class="diag_libelle align_droite"><label for="t_add_cpt_is_synthese"><?php echo _("Display in dashboard"); ?> :</label></div>
				<div class="diag_champ"><input type="checkbox" name="t_add_cpt_is_synthese" id="t_add_cpt_is_synthese"></div>
			</div>
			<span id="loading_spin_addcpt" ><img src="./img/ajax-loader-spinner.gif" /> <?php echo _("Loading in progress"); ?>...</span>			
		</form>
	</div>