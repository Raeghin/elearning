<script src="assets/js/jquery.min.js"></script>
<script>
	$(function() {
		
		$("input[name='amount_credits']").keyup(function() {
			refreshprice();
		});
	});
	
	function refreshprice() 
	{
		var amount = $("input[name='amount_credits']").val();
		var price = parseInt(amount) * 35;
	
		$("input[name='price']").val(price);    
		$("#totalprice").text(price);
	}
</script>

<div class="creditbody">  
	<div class="overview">
		<div class="title"><p><?php echo $this->get_string('titlecreditoverview'); ?></p></div>
		<span><?php echo $this->get_string('creditamount') . ": <b>\t" . $this->model->getcredits($this->get_user()->id); ?></b></span>
		<br /><br />
		<?php 
		foreach ($this->model->getcredithistorie($this->get_user()->id, 5) as $value) {
			$date = new DateTime();
			$date->setTimestamp($value->dateofpurchase);
			
			$amount = $value->amount;
			if($amount > 0)
				echo $date->format('d-m-Y') . ": " . $value->amount . " " . $this->get_string('creditsbought');
			else
				echo "<font color=\"red\">" . $date->format('d-m-Y') . ": " . $value->amount . " " . $this->get_string('creditsspent') . "</font>";
			echo '<br />';
		} ?>
		
		
	</div>
	
	<div class="overview">
		<div class="title"><p><?php echo $this->get_string('titlebuycredits'); ?></p></div>
		
		<form action="<?php echo $this->model->getpaymentlink(); ?>" method="POST">
			<p><?php echo $this->get_string('buycreditexplanation'); ?>.</p>
			<div><p><?php echo $this->get_string('labelcreditamount'); ?>: <input type="text" name="amount_credits" size="5"/> <img src="assets/img/refresh.png" height="18px" onclick="refreshprice()"></p></div>
			<p><b><?php echo $this->get_string('totalprice'); ?>:</b> € <span id="totalprice">0</span>,-</p>
			<p><?php echo $this->get_string('idealpayment'); ?>.</p>
			
			<?php echo $this->model->getissueroptions(); ?>
			<input name="price" type="hidden"/>
			<input name="uid" type="hidden" value="<?php echo $this->get_user()->id; ?>"/>
			<input name="gateway" value="IDEAL" type="hidden"/>
			<input name="firstname" value="<?php echo $this->get_user()->firstname; ?>" type="hidden"/>
			<input name="lastname" value="<?php echo $this->get_user()->lastname; ?>" type="hidden"/>
			<input name="locale" value="<?php echo $this->get_user()->lang; ?>" type="hidden"/>
			<input name="city" value="<?php echo $this->get_user()->city; ?>" type="hidden"/>
			<input name="country" value="<?php echo $this->get_user()->country; ?>" type="hidden"/>
			<input name="address" value="<?php echo $this->get_user()->address; ?>" type="hidden"/>
			<input name="email" value="<?php echo $this->get_user()->email; ?>" type="hidden"/>
			
			<input type="submit" value="Naar iDeal" />
		</form>
	</div>
	<div class="overview">
		
	</div>
</div>

