<?php

// # Get Sale sample 
// This sample code demonstrates how you can retrieve 
// details of completed Sale Transaction.
// API used: /v1/payments/sale/{sale-id}

require __DIR__ . '/../bootstrap.php';
use PayPal\Api\Sale;

$saleId = '3RM92092UW5126232';
// ### Authentication
// Pass in a `OAuthTokenCredential` object
// explicilty to authenticate the call. 
// If you skip this step, the client id/secret
// set in the config file will be used. 
Sale::setCredential($cred);
try {	
	// ### Retrieve the sale object
	// Pass the ID of the sale
	// transaction from your payment resource.
	$sale = Sale::get($saleId);
} catch (\PPConnectionException $ex) {
	echo "Exception:" . $ex->getMessage() . PHP_EOL;
	var_dump($ex->getData());
	exit(1);
}
?>
<html>
<body>
	<div>Retrieving sale id: <?php echo $saleId;?></div>
	<pre><?php var_dump($sale);?></pre>
	<a href='../index.html'>Back</a>
</body>
</html>