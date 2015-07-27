<?php

$msp = new MultiSafepay();

/* 
 * Merchant Settings
 */
$msp->test                         = MSP_TEST_API;
$msp->merchant['account_id']       = MSP_ACCOUNT_ID;
$msp->merchant['site_id']          = MSP_SITE_ID;
$msp->merchant['site_code']        = MSP_SITE_CODE;
$msp->transaction['id']			   = $_GET['transactionid'];

$status = $msp->getStatus();
print_r($status);
print_r($msp);
?>

<div class="creditbody">  
	<div class="overview">
		<div class="title"><p><?php echo $this->get_string('titlecreditsuccess'); ?></p></div>
		<p><?php echo $this->get_string('creditsuccess'); ?></p>
	</div>
</div>

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

