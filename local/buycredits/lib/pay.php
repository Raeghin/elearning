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
$msp->merchant['notification_url'] = BASE_URL . 'notify.php?type=initial';
$msp->merchant['cancel_url']       = BASE_URL . 'index.php';
//$msp->merchant['redirect_url']     = BASE_URL . 'return.php';

/**
*	Price 	
*/
$credits = $_POST["amount_credits"];
// Staffel berekening
$price = round($credits * 3500);

echo($price);
/* 
 * Customer Details
 */
$msp->customer['locale']           = $_POST["locale"];
$msp->customer['firstname']        = $_POST["firstname"];
$msp->customer['lastname']         = $_POST["lastname"];
$msp->customer['zipcode']          = '1234AB';
$msp->customer['city']             = $_POST["city"];
$msp->customer['country']          = $_POST["country"]; 
$msp->customer['email']            = $_POST["email"];
$msp->parseCustomerAddress($_POST["address"]);

/* 
 * Transaction Details
 */
$msp->transaction['id']            = rand(100000000,999999999); // generally the shop's order ID is used here
$msp->transaction['currency']      = 'EUR';
$msp->transaction['amount']        = $price; // cents
$msp->transaction['description']   = 'Order #' . $msp->transaction['id'];
$msp->transaction['items']         = $credits . ' credits';
$msp->transaction['gateway']		= $_POST['gateway'];


if(isset($_POST['issuer']))
{
	// returns a direct ideal payment url
	$msp->extravars = $_POST['issuer'];
	$url = $msp->startDirectXMLTransaction();
}else{
	// returns a payment url
	$url = $msp->startTransaction();
	
}



if ($msp->error){
  echo "Error " . $msp->error_code . ": " . $msp->error;
  exit();
}

// redirect
header('Location: ' . $url);

?>