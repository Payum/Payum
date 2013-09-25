<?php

// # Create payment using a saved credit card
// This sample code demonstrates how you can process a 
// Payment using a previously saved credit card.
// API used: /v1/payments/payment

require __DIR__ . '/../bootstrap.php';
use PayPal\Api\Address;
use PayPal\Api\Amount;
use PayPal\Api\CreditCard;
use PayPal\Api\CreditCardToken;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\FundingInstrument;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Rest\ApiContext;

// ### Credit card token
// Saved credit card id from a previous call to
// CreateCreditCard.php
$creditCardId = 'CARD-5BT058015C739554AKE2GCEI';
$creditCardToken = new CreditCardToken();
$creditCardToken->setCredit_card_id($creditCardId);

// ### FundingInstrument
// A resource representing a Payer's funding instrument.
// Use a Payer ID (A unique identifier of the payer generated
// and provided by the facilitator. This is required when
// creating or using a tokenized funding instrument)
// and the `CreditCardDetails`
$fi = new FundingInstrument();
$fi->setCredit_card_token($creditCardToken);

// ### Payer
// A resource representing a Payer that funds a payment
// Use the List of `FundingInstrument` and the Payment Method
// as 'credit_card'
$payer = new Payer();
$payer->setPayment_method("credit_card");
$payer->setFunding_instruments(array($fi));

// ### Amount
// Let's you specify a payment amount.
$amount = new Amount();
$amount->setCurrency("USD");
$amount->setTotal("1.00");

// ### Transaction
// A transaction defines the contract of a
// payment - what is the payment for and who
// is fulfilling it. Transaction is created with
// a `Payee` and `Amount` types
$transaction = new Transaction();
$transaction->setAmount($amount);
$transaction->setDescription("This is the payment description.");

// ### Payment
// A Payment Resource; create one using
// the above types and intent as 'sale'
$payment = new Payment();
$payment->setIntent("sale");
$payment->setPayer($payer);
$payment->setTransactions(array($transaction));

// ### Api Context
// Pass in a `ApiContext` object to authenticate
// the call and to send a unique request id
// (that ensures idempotency). The SDK generates
// a request id if you do not pass one explicitly.
$apiContext = new ApiContext($cred, 'Request' . time());

// ###Create Payment
// Create a payment by posting to the APIService
// using a valid apiContext
// The return object contains the status;
try {
	$payment->create($apiContext);
} catch (\PPConnectionException $ex) {
	echo "Exception: " . $ex->getMessage() . PHP_EOL;
	var_dump($ex->getData());	
	exit(1);
}
?>
<html>
<body>
	<div>
		Created payment:
		<?php echo $payment->getId();?>
	</div>
	<pre><?php var_dump($payment->toArray());?></pre>
	<a href='../index.html'>Back</a>
</body>
</html>