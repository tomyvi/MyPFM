</div>
<div class="clearboth"></div>
<div class="footer">
</div>
</div></center>
<script>
	$(document).ready(function(){
		$('.scrollable').niceScroll();
	});
<?php 
	if(!$login_page) { ?>
			$(document).ready(function(){
				$(document).idleTimeout({
					inactivity:  900000, //20 Minute default (how long before showing the notice) (milli secondes)
					noconfirm: 60000,
					sessionAlive:300000,
					alive_url:'./rpc.php?type=PING',
					click_reset: true,
					redirect_url:'./login.php?out&auto&dest=' + location.pathname + location.hash,
					logout_url: false,
					dialogTitle: '<?php echo _("Automatic logout"); ?>',
					  dialogText: '<?php echo _("After 15 minutes without activity, you will be logged out !"); ?>.',
					  dialogButton: '<?php echo _("Stay logged in"); ?>'
				});
			});
	<?php } ?>
</script>
<?php
require_once(dirname(__FILE__) . '/dialog_add_cpt.inc');
require_once(dirname(__FILE__) . '/dialog_add_budget.inc');
?>
</body>
</html>