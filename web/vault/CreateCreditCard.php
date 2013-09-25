<?php

// # Create Credit Card Sample
// Using the 'vault' API, you can store a 
// Credit Card securely on PayPal. You can
// use a saved Credit Card to process
// a payment in the future.
// The following code demonstrates how 
// can save a Credit Card on PayPal using 
// the Vault API.
// API used: POST /v1/vault/credit-card

use PayPal\Rest\ApiContext;

require __DIR__ . '/../bootstrap.php';
use PayPal\Api\CreditCard;
use PayPal\Api\Address;

// ### CreditCard
// A resource representing a credit card that can be
// used to fund a payment.
$card = new CreditCard();
$card->setType("visa");
$card->setNumber("4417119669820331");
$card->setExpire_month("11");
$card->setExpire_year("2019");
$card->setCvv2("012");
$card->setFirst_name("Joe");
$card->setLast_name("Shopper");

// ### Api Context
// Pass in a `ApiContext` object to authenticate 
// the call and to send a unique request id 
// (that ensures idempotency). The SDK generates
// a request id if you do not pass one explicitly. 
$apiContext = new ApiContext($cred, 'Request' . time());

// ### Save card
// Creates the credit card as a resource
// in the PayPal vault. The response contains
// an 'id' that you can use to refer to it
// in the future payments.
try {
	$card->create();	
} catch (\PPConnectionException $ex) {
	echo "Exception:" . $ex->getMessage() . PHP_EOL;
	var_dump($ex->getData());
	exit(1);
}
?>
<html>
<body>
	<div>Saved a new credit card with id: <?php echo $card->getId();?></div>
	<pre><?php var_dump($card);?></pre>
	<a href='../index.html'>Back</a>
</body>
</html>