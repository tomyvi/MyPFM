
<script type="text/javascript">
	
	
	function do_login(){
		if ($('#login').val() === '') {
			alert('Veuillez saisir votre login !');
			$('#login').focus();
			return false;
		}

		if ($('#pwd').val() === '') {
			alert('Veuillez saisir votre mot de passe !');
			$('#pwd').focus();
			return false;
		}

		$('#username').val($('#login').val());
		$('#response').val(hex_md5($('#challenge').val() + hex_md5($('#pwd').val())));
		
		$('#submitForm').submit();

		return false;
	}
	
	$(document).ready(function(){
		
		if($('#login').val() != ''){
			$('#pwd').focus();
		}else{
			$('#login').focus();
		}
		
		$('input[type=button]').click(function(event){
			do_login();
		});
		
		$('input').keydown(function(event) {
			if (event.keyCode == 13) {
				do_login();
			}
		});
		
		$('#loginForm').submit(function(event){
			do_login();
			return false;
		});
	});
</script>

<div class="login_container">
	<form id='loginForm'>
		<input type="hidden" id="challenge" name="challenge" value="<?php echo $_SESSION["challenge"]; ?>"/>
		<div class="login_box">
			<div class="row">
				<span class="libelle">Login :</span>
				<span class="donnee"><input type="text" name="login" id="login" tabindex="1"/></span>
			</div>
			<div class="row">
				<span class="libelle">Mot de passe :</span>
				<span class="donnee"><input type="password" name="pwd" id="pwd" tabindex="2" /></span>
			</div>
			<div class="errormsg"><center>
			<? if(isset($errormsg)){
				echo $errormsg;
			 }else{
			 	echo "&nbsp;";
			 } ?>
			</center></div>
			<div class="row"><center>
				<input type="button" value="Login" tabindex="3"/>
			</center></div>
		</div>
	</form>
	<form id="submitForm" action="login.php" method="post">
			<input type="hidden" id="username" name="username"/>
			<input type="hidden" id="response" name="response"/>
			<input type="hidden" id="dest" name="dest" value="<? echo $_REQUEST["dest"]; ?>" />
	</form>
</div>