<script>
	
	
	
	function set_listecomptes_bindings(){
		$('.compte').click(function() {
			get_compte($(this).data('id'));
		});
		
	}
	
	$('document').ready(function(){
			
			
		$('.liste_comptes').sortable({
			update:function(){
				serial = $('.liste_comptes').sortable('serialize');
				//alert(serial);
				$.ajax({
					url: 'rpc.php?type=SORT_CPT&'+serial,
					success: function(data) {
						if(data != "" && data !="ERREUR"){
							
						}else{
							alert('<?php echo _("Error during account sort order request"); ?> !!');
						}
					},
					error: function(){
						alert('<?php echo _("Error during account sort order request"); ?> !!');
					}
				});
			}
		});
		
		
		$('.lien_afficher_cpt_clos').click(function(){
			
			get_comptes_clos();
			
			
		});
		
		
		
	});

</script>

<div class="colonne_1">
	<ul class="liste_comptes scrollable"><li class="compte">...</li>
	</ul>
	
	<?php
	$lc = ListeComptes::getInstance();
	
	if($lc->getNbComptes(true)>0){
		echo "<div class='lien lien_afficher_cpt_clos'>"._("Show closed accounts")."</div>";
	}
	?>
	
	<div class='balances'>
		<div class='balance'><?php echo _("Global balance"); ?> : <div class='' id='solde_global' data-solde_global=""></div></div>
		
		<?php
		
		/*
		foreach($user_soldes as $uid => $user_solde){
			$u = new User($uid);
			if($user_solde>0){
				$style = "credit signe";
			}else{
				$style = "debit";
			}
			$t_user_solde = number_format($user_solde,2,","," ")." &#8364;";
			echo "
				<div class='balance'>"._("Balance")." ".$u->nom." :<div class='$style' id='solde_user_$uid'>$t_user_solde</div></div>
				<script>
					solde_users[$uid] = $user_solde;
				</script>
			";
		}
		*/
	?>
	</div>
</div><!-- colonne_1 !-->