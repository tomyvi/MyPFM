<script>


	function set_edit_categorie_bindings(){
		
		$('.categorie').unbind('click');
		
		$('.categorie').click(function(){
			
			my_id_transac = $(this).parent().attr('id');
			my_id_cat = $(this).data('id');
			
			$('#update_id_transac').val(my_id_transac);
			$('#update_id_cat').val(my_id_cat);
			
			$('.liste_cat').each(function(){
				if($(this).data('id')==my_id_cat){
					$(this).addClass('cat_selected');
				}else{
					$(this).removeClass('cat_selected');
				}
			});
			
			$( '#dialog-update-cat' ).dialog( "open" );
			
			
		});
		
	}
	$(document).ready(function(){
		set_edit_categorie_bindings();
		
		$('#dialog-update-cat').dialog({
			autoOpen: false,
			height: 290,
			width: 590,
			modal: true			
		});
		$('.liste_cat').click(function(){
			transac_id = $('#update_id_transac').val();
			new_cat_id = $(this).data('id');
			new_cat_nom = $(this).data('nom');
			
			console.log("MAJ categorie pour transac="+transac_id+", id="+new_cat_id);
			
			$('.liste_cats').find('li').removeClass('cat_selected');
			$('.liste_cats').find('li[data-id=' + new_cat_id + ']').addClass('cat_selected');
			
			console.log("MAJ UI categorie OK");
			
			old_html = $('#' + transac_id).find('.categorie').html();
			old_id = $('#' + transac_id).find('.categorie').data('id');
			old_title = $('#' + transac_id).find('.categorie').attr('title');
			old_montant = $('#' + transac_id).find('.montant').data('montant');
			
			$('#' + transac_id).find('.categorie').html(new_cat_nom);
			$('#' + transac_id).find('.categorie').data('id',new_cat_id);
			$('#' + transac_id).find('.categorie').attr('title', new_cat_nom);
			
			$('#dialog-update-cat').dialog("close");
			
			
			transaction = {id:transac_id,categorie:{id:old_id,nom:old_title},montant:old_montant};
			categorie = {id:new_cat_id, nom:new_cat_nom};
			update_transaction_categorie(transaction, categorie);
			
			$.ajax({
				url: 'rpc.php?type=SET_CAT&idt='+transac_id+'&idcat='+new_cat_id,
				context: $(this),
				success: function(data) {
					if(data.status){
						console.log("MAJ DB categorie OK");
						notif = noty({
							layout: 'top',
							type: 'success',
							timeout: 3000,
							text: 'Catégorie mise à jour !'
						});						
					}else{						
						$('#' + transac_id).find('.categorie').html(old_html);
						$('#' + transac_id).find('.categorie').data('id',old_id);
						$('#' + transac_id).find('.categorie').attr('title', old_title);
						
						
						console.log("MAJ DB categorie KO");
						notif = noty({
							layout: 'top',
							type: 'error',
							timeout: 3000,
							text: data.error
						});						
					}
				},
				error: function(){
					$('#' + transac_id).find('.categorie').html(old_html);
					$('#' + transac_id).find('.categorie').data('id',old_id);
					$('#' + transac_id).find('.categorie').attr('title', old_title);
					
					notif = noty({
						layout: 'top',
						type: 'error',
						timeout: 3000,
						text: 'Impossible de mettre à jour la catégorie !'
					});
				}
			});
			
		});
		$('.liste_cats').sortable({
			update:function(){
				serial = $('.liste_cats').sortable('serialize');
				//alert(serial);
				$.ajax({
					url: 'rpc.php?type=SORT_CAT&'+serial,
					success: function(data) {
						if(data != "" && data !="ERREUR"){
							
						}else{
							alert('Erreur lors de la mise à jour !!');
						}
					},
					error: function(){
						alert('Erreur lors de demande de mise à jour !!');
					}
				});
			}
		});
	
	});
</script>

<div id="dialog-update-cat" title="Editer la cat&eacute;gorie" class="dialog">
	<input type="hidden" name="id_transac" id="update_id_transac" />
	<ul class="liste_cats">
		<?php 
			
			foreach(Categorie::getListe() as $cat){
				echo "<li class='liste_cat lien' id='cat_".$cat->id."' data-id='".$cat->id."' data-nom='".$cat->nom."'>".$cat->nom."</li>\n";
			}
		?>
	</ul>
</div>