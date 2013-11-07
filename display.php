<?php
$_AFFICHER_COMPTE = true;

require_once(dirname(__FILE__) ."/inc/init.inc.php");

$liste_comptes = Compte::getListe();
$default_id_compte = $liste_comptes[0]->id;



require_once(dirname(__FILE__) . '/inc/html/header.inc');
?>
<div class="attente_container">
	<?php echo _("Loading your accounts, please wait"); ?>...<br/><br/><img src="./img/waiting-spin.gif"/>
	<!--<div id="loading_progress"></div>!-->
	</div>
<div class="comptes_container" style="display:none;">
<?
require_once(dirname(__FILE__) . '/inc/html/account_list.inc');
?>
<script> 
	var notif;
	$(document).ready(function(){
		$('#menu_comptes').addClass('current');
		$('#lien_edit_cpt').hide();
		$('#lien_undo_import').hide();
		$('.entete_compte').hover(function(){$('#lien_edit_cpt').show();},function(){$('#lien_edit_cpt').hide();});
	});
</script>
<div class="colonne_2">
	<div class="entete_compte">
	<div style="display:table-row">
	<div class="titre_compte" id="libelle_compte">...</div>
	<div class="titre_compte align_droite"><?php echo _("Balance on"); ?> <span id="date_max_transac_compte" data-date_max_transac_compte="0">...</span> : <span id="solde_cpt" data-solde_cpt="0" class="credit">... #8364;</span></div>
	</div><div style="display:table-row">
	<div class="sstitre_compte">
		<a href="#" target="_blank" id="etablissement">...</a>
		- <span id="numero_compte">...</span>
		<span class="lien" id="lien_edit_cpt" title="Alt+e"> - <?php echo _("Edit"); ?></span>
	</div>
	<div class="sstitre_compte align_droite">
		<span  style="display:inline;opacity:0;width:1px;overflow:hidden;">
		<form action="import.php" method="post" enctype="multipart/form-data" id="form_import" name="upload" style="opacity:0;" accept-charset="UTF-8">
			<input type="file" name="file" id="file_import" style="width:0px;height:0px;" />
		</form>
		</span>
		<span  id="import_link" class="lien"><?php echo _("Import"); ?></span>
		<span  id="lien_undo_import" class="lien"><?php echo _("Cancel"); ?></span>
		 - <?php echo _("Last import on"); ?> : <span id="last_import_date">...</span>
	</div>
	</div>
	</div>
	<div>
		<div id="overlay">
			<div id="overlay_block"></div>
			<span id="overlay_texte"><?php echo _("Loading"); ?>...</span>
		</div>
		<table class="entete_liste_transactions table_header" id="table_transac_head">
			<thead>
				<tr>
					<th class="date"><?php echo _("Date"); ?></th>
					<th class="edit_transac"></th>
					<th class="libelle"><?php echo _("Label"); ?></th>
					<th class="categorie"><?php echo _("Category"); ?></th>
					<th class="montant"><?php echo _("Amount"); ?></th>
					<th class="solde"><?php echo _("Balance"); ?></th>
				</tr>
			</thead>
		</table>
		<div class='liste_transactions scrollable'>
			<table class="liste_transactions table_content" id="table_transac" data-nb_transactions_chargees="0" data-nb_total_transactions="0">
				<tbody class="liste_transactions_body"></tbody>
			</table>
		</div>
	</div>
	<script>
		var default_id_compte = <?php echo $default_id_compte; ?>;
		var nb_transac = <?php echo $_config['nb_transac_par_page']; ?>;
		var id_user_cpt = 0;
		var categories = <?php echo json_encode(Categorie::getListe()); ?>;
		
		function show_overlay(){
			var tablePos = $('div.liste_transactions').offset();
			
			$('#overlay').css('top', tablePos.top);
			$('#overlay').css('left', tablePos.left + 2.5);
			
			$('#overlay').width($('div.liste_transactions').width());
			$('#overlay_block').width($('div.liste_transactions').width());
			
			$('#overlay').show();
		}
		
		function hide_overlay(){
			$('#overlay').hide();
		}
		
		
		
		//initial load
		$(document).ready(function(){
			
			$('#loading_progress').progressbar({ value: false});
			
			hide_overlay();
			
			//stop async ajax calls (prevents load of transactions before account list)
			$.ajaxSetup({'async': false});
			
			
			$.when(get_listeComptes()).done(function(a3){
				
				cle_compte = location.hash.replace('#', '');
				id_compte = $("li.compte[data-cle='" + cle_compte + "']").data('id');
				if(id_compte == '' || id_compte == null) id_compte = default_id_compte;
				
				$.when(get_compte(id_compte)).done(function(a4, a5){
					$('.attente_container').hide();
					$('.comptes_container').show();
					$('.liste_comptes').animate({ scrollTop: $("li[data-id=" + id_compte + "]").position().top - $('.liste_comptes').position().top }, 'slow');
					sync_column_width('.table_header','.table_content');					
				});
			});
			
			$.ajaxSetup({'async': true});
			
			$('.loading_spin')
				.hide()  // hide it initially
				.ajaxStart(function() {
					$(this).show();
				})
				.ajaxStop(function() {
					$(this).hide();
				})
			;
			
		
			//infiniscroll setup
			$('div.liste_transactions').bind('scroll', function()
			  {
				if($(this).scrollTop() + $(this).innerHeight() >= $(this)[0].scrollHeight){
				  if(! search_enabled){
						get_transactions();	
					}
				}
			  });
			
			$(window).unload(function() {
			  alert($.active + "requetes actives !");
			});
			
			$('.conteneur').height(630);
		
		});
		
		
	</script>
	</div><!-- colonne_2 !-->
	</div>
<?php
require_once(dirname(__FILE__) . '/inc/html/search.inc');
require_once(dirname(__FILE__) . '/inc/html/dialog_edit_cpt.inc');
require_once(dirname(__FILE__) . '/inc/html/dialog_edit_transaccat.inc');
require_once(dirname(__FILE__) . '/inc/html/dialog_edit_transac.inc');
require_once(dirname(__FILE__) . '/inc/html/dialog_add_transac.inc');
require_once(dirname(__FILE__) . '/inc/html/dialog_import_transac.inc');
require_once(dirname(__FILE__) . '/inc/html/dialog_handle_duplicates.inc');
require_once(dirname(__FILE__) . '/inc/html/footer.inc'); //add cpt in footer
?>