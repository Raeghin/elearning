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
$msp->merchant['notification_url'] = BASE_URL . 'notify.php';
$msp->merchant['cancel_url']       = BASE_URL . 'cancel.php';
$msp->merchant['redirect_url']     = BASE_URL . 'return.php';

$price = $_POST['price'] * 100;

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
$msp->transaction['id']            = $POST["uid"] . date("dmdYGiB", time());// generally the shop's order ID is used here
$msp->transaction['currency']      = 'EUR';

$msp->transaction['amount']        = $price; // cents
$msp->transaction['description']   = 'Order #' . $msp->transaction['id'];
$msp->transaction['items']         = $_POST['creditamount'];
$msp->transaction['gateway']		= $_POST['gateway'];


$msp->extravars = $_POST['issuer'];
$url = $msp->startDirectXMLTransaction();

if ($msp->error){
  echo "Error " . $msp->error_code . ": " . $msp->error;
  exit();
}

header('Location: ' . $url);
?>