<?php

// # Get Credit Card Sample
// The CreditCard resource allows you to
// retrieve previously saved CreditCards, 
// by sending a GET request to the URI
// '/v1/vault/credit-card'
// The following code takes you through
// the process of retrieving a saved CreditCard
require __DIR__ . '/../bootstrap.php';
use PayPal\Api\CreditCard;

// The cardId can be obtained from a previous save credit
// card operation. Use $card->getId()
$cardId = "CARD-5BT058015C739554AKE2GCEI";

// ### Authentication
// Pass in a `OAuthTokenCredential` object
// explicilty to authenticate the call.
// If you skip this step, the client id/secret
// set in the config file will be used.
CreditCard::setCredential($cred);
/// ### Retrieve card
try {
	$card = CreditCard::get($cardId);
} catch (\PPConnectionException $ex) {
	echo "Exception: " . $ex->getMessage() . PHP_EOL;
	var_dump($ex->getData());
	exit(1);
}
?>
<html>
<body>
	<div>Retrieving credit card: <?php echo $cardId;?></div>
	<pre><?php var_dump($card);?></pre>
	<a href='../index.html'>Back</a>
</body>
</html>