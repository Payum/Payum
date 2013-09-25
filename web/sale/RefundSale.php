<?php

// # Sale Refund Sample
// This sample code demonstrate how you can 
// process a refund on a sale transaction created 
// using the Payments API.
// API used: /v1/payments/sale/{sale-id}/refund

require __DIR__ . '/../bootstrap.php';
use PayPal\Api\Amount;
use PayPal\Api\Refund;
use PayPal\Api\Sale;
use PayPal\Rest\ApiContext;

// ### Refund
// Create a refund object indicating 
// refund amount
$amt = new Amount();
$amt->setCurrency('USD');
$amt->setTotal('0.01');

$refund = new Refund();
$refund->setAmount($amt);

$saleId = '3RM92092UW5126232';

// ###Sale
// A sale transaction.
// Create a Sale object with the
// given sale transaction id.
$sale = new Sale();
$sale->setId($saleId);

// ### Api Context
// Pass in a `ApiContext` object to authenticate 
// the call and to send a unique request id 
// (that ensures idempotency). The SDK generates
// a request id if you do not pass one explicitly. 
$apiContext = new ApiContext($cred, 'Request' . time());
try {	
	// Refund the sale
	$sale->refund($refund, $apiContext);
} catch (\PPConnectionException $ex) {
	echo "Exception:" . $ex->getMessage() . PHP_EOL;
	var_dump($ex->getData());
	exit(1);
}
?>
<html>
<body>
	<div>Refunding sale id: <?php echo $saleId;?></div>
	<pre><?php var_dump($sale);?></pre>
	<a href='../index.html'>Back</a>
</body>
</html>