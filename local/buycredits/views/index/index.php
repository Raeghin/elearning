<script>
	require(['jquery'], function($) {
		$("input[name='amount_credits']").keyup(function() {
			var amount = $(this).val();
			var price = parseInt(amount) * 35;
	
			$("input[name='price']").val(answer);    
		});
	});
</script>

<div class="creditbody">  
	<div class="overview">
		<div class="title"><p><?php echo $this->get_string('titlecreditoverview'); ?></p></div>
		<span><?php echo $this->get_string('creditamount') . ": <b>\t" . $this->model->getcredits($this->get_user()->id); ?></b></span>
		<br /><br />
		<?php 
		foreach ($this->model->getcredithistorie($this->get_user()->id, 10) as $value) {
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
			<div class="label"><?php echo $this->get_string('labelcreditamount'); ?>: </div><input type="text" name="amount_credits" />
			<br />
			<?php echo $this->model->getissueroptions(); ?>
			<input name="price" />
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

