$('document').ready(function(){
	//http://www.catswhocode.com/blog/using-keyboard-shortcuts-in-javascript
	var isMaj = false;
	var isCtrl = false;
	var isAlt = false;
	$(document).keyup(function (e) {
		if(e.which == 16) isMaj=false;
		if(e.which == 17) isCtrl=false;
		if(e.which == 18) isAlt=false;
	}).keydown(function (e) {
		if(e.which == 16) isMaj=true;
		if(e.which == 17) isCtrl=true;
		if(e.which == 18) isAlt=true;
		
		if(e.which == 67 && isCtrl == true && isMaj == true) {
			$('#lien_add_cpt').click();
			return false;
		}
		if(e.which == 67 && isCtrl == true) {
			$('#lien_add_transac').click();
			return false;
		}
		if(e.which == 69 && isCtrl == true && isMaj == true) {
			$('#lien_edit_cpt').click();
			return false;
		}
		
	});

});