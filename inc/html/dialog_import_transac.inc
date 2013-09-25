<script>
	var mon_xhr;
	var progressbar ;
	var progressTitle ;
	var progressLabel ;
	var reader;
	
	$(document).ready(function(){
		
		$('#import_link').click(function(){
			$('#file_import').click();
		});
		
		$('#file_import').change(function(evt){
			server_import_transactions();
			$(this).val('');
		});
		
		$('#dialog-import-transac').dialog({
			autoOpen: false,
			dialogClass: "alert",
			height: 120,
			width: 300,
			modal: true,
			close: function() {console.log("dialog-import-transac closed");}	
		});
		
		progressbar = $( "#progressbar" );
		progressTitle = $( "#progresstitle" );
		progressLabel = $( ".progress-label" );
	 
		progressbar.progressbar({
			value: false,
			change: function() {
				progressLabel.text( progressbar.progressbar( "value" )  + "%" );
				console.log("Progress " + progressbar.progressbar( "value" )  + "%");
			},
			complete: function(){
				$('#abort_link').hide();
				console.log("Progress 100%");
				$(this).progressbar("value", false);
			}
		});
		
		
	});
	
	
	//SERVER IMPORT
	function progressHandlingFunction(e){
		if(e.lengthComputable){
			percentComplete = Math.round(e.loaded * 100 / e.total);
			progressbar.progressbar( "value", percentComplete / 2);
		}
	}
	function uploadCanceled(e){
		console.log("Annulation upload !");
		$('#dialog-import-transac').dialog('close');
		var notif = noty({
			layout: 'top',
			type: 'alert',
			timeout: 10000,
			text: '<?php echo _("Operation canceled"); ?> !'
		});
	}
	
	function server_import_transactions(){
		console.log("server_import_transactions");
		
		$('#abort_link').click(function(){
			mon_xhr.abort();
		});
		
		progressTitle.text('<?php echo _("Uploading file"); ?> :');
		
		id_compte = $('.actif').data('id');
		
		var formData = new FormData($('form')[0]);
		
		mon_xhr = $.ajax({
			url: 'import.php?idc='+id_compte,  //server script to process data
			type: 'POST',
			dataType:"json",
			xhr: function() {  // custom xhr
				var myXhr = $.ajaxSettings.xhr();
				if(myXhr.upload){ // check if upload property exists
					$('#dialog-import-transac').dialog('open');
					$('#abort_link').show();
					myXhr.upload.addEventListener('progress',progressHandlingFunction, false); // for handling the progress of the upload
					myXhr.upload.addEventListener('abort', uploadCanceled, false);
				}
				return myXhr;
			},
			//Ajax events
			beforeSend: function( jqXHR,  settings ){return true;},
			success: function (data, status){
				if(data.status){
					console.log("server import status OK !");
					progressTitle.text('<?php echo _("Processing file"); ?> :');
					$('#abort_link').hide();
					
					//progressbar.progressbar( "value", 0);
					$.each(data.compte.transactions, function(key, value){
						
						percentComplete = Math.round((key+1) * 100 / data.nb_transactions);
						progressbar.progressbar( "value", 50 + (percentComplete)/2);
						
						prepend_transaction(value);
					});
					//progressbar.progressbar( "value", 100);
					
					console.log("Solde après import = " + $('#solde_cpt').data('solde_cpt'));
					
					var notif = noty({
						layout: 'top',
						type: 'success',
						timeout: 10000,
						text: data.nb_transactions + ' <?php echo _("transactions(s) have been imported"); ?>!'
					});
					
					if(data.duplicates != null && data.nb_duplicates > 0){
						progressTitle.text('<?php echo _("Processing duplicated"); ?> :');
						progressbar.progressbar( "value", 0);
						
						console.log("data.duplicates is not null");
						var noty_2 = null;
						clear_duplicate_decision_list();
						var nb_duplicates = 0;
						$.each(data.duplicates, function(key, duplicate){
							nb_duplicates = nb_duplicates + 1;
							
							percentComplete = Math.round((nb_duplicates) * 100 / data.nb_duplicates);
							progressbar.progressbar( "value", percentComplete);
						
							
							call_for_duplicate_decision(duplicate);
							
							//mise au 1er plan de la fenetre de progression
							$('#dialog-import-transac').dialog('moveToTop');
							if(noty_2 == null){
								noty_2 = noty({
									layout: 'top',
									type: 'warning',
									timeout: 10000,
									text: nb_duplicates + ' <?php echo _("transaction(s) already exist, impossible to duplicate them"); ?> !'
								});
							}else{
								noty_2.setText(nb_duplicates + ' <?php echo _("transaction(s) already exist, impossible to duplicate them"); ?> !');
							}
						});
						progressbar.progressbar( "value", 100);
						show_duplicate_decision();
					}
					
					//reset bindings
					set_edit_transac_bindings();
					
					//mise à jour des soldes par transaction
					reset_solde_transactions();
					
					$('#lien_undo_import').data('import_id', data.import_id);
					$('#lien_undo_import').show();
					$('#import_link').hide();
					
					$('#lien_undo_import').unbind('click');
					$('#lien_undo_import').click(function(){
						undo_import($('#lien_undo_import').data('import_id'));
					});
					
				}else{
					alert(data.error);
				}		
				$('#dialog-import-transac').dialog('close');
			},
			error: function (data, status, e){
				//alert(e);
			},
			// Form data
			data: formData,
			//Options to tell JQuery not to process data or worry about content-type
			cache: false,
			contentType: false,
			processData: false
		});
		return false;
	}
	
	function undo_import(import_id){
		
		if(import_id != ''){
		
			$.getJSON('./import.php?undoid=' + import_id, function(data){
				
				if(data.status){
					id_compte = $('.actif').data('id');
					get_compte(id_compte);
					
					$('#lien_undo_import').data('import_id', '');
					$('#lien_undo_import').hide();
					$('#import_link').show();
					
					var notif = noty({
						layout: 'top',
						type: 'success',
						timeout: 10000,
						text: '<?php echo _("Import has successfully been canceled"); ?>, ' +data.nb_transactions+ ' <?php echo _("transaction(s) deleted"); ?> !'
					});
					
				}else{
					
					var notif = noty({
						layout: 'top',
						type: 'error',
						timeout: 3000,
						text: data.error
					});
					
				}
				
				
			});
		}else{
			console.log("Appel undo_import sans ID");
		}
	}
</script>
<div id="dialog-import-transac" class="dialog" title="<?php echo _("Transaction import"); ?>">
	<style>
	  .progress-label {
		float: left;
		margin-left: 50%;
		margin-top: 5px;
		font-weight: bold;
		text-shadow: 1px 1px 0 #fff;
	  }
	  #progressbar .ui-progressbar-value {
		background-color: #ccc;
	  }
	</style>
	<div id="progresstitle">&nbsp;</div>
	<div id="progressbar"><div class="progress-label"></div></div>
	<div>
		<span id="abort_link" class="lien"><?php echo _("Cancel"); ?></span>
	</div>

</div>