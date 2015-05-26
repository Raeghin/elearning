<?php

include('MultiSafepay.combined.php');
include('MultiSafepay.config.php');

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

$message = $status;
echo "<script type='text/javascript'>alert('$message');</script>";
?>


<html>
  <body>
    <p>
      Thank you for your order. Your transaction status = <?php echo $status;?>
    </p>
    <br />
    <p>Your transaction details:</p>
    <pre>
    <?php print_r($msp->details); ?>
    </pre>
    <p>
      <a href="index.php">Back</a>
    </p>
  </body>
</html>