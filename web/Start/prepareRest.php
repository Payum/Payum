<?php

include '../../src/Start/config.php';

use PayPal\Api\Amount;
use PayPal\Api\Payer;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;

$storage = $registry->getStorageForClass($paypalRestPaymentDetailsClass, 'paypalRest');

$paymentDetails = $storage->createModel();

$payer = new Payer();
$payer->setPayment_method("paypal");

$amount = new Amount();
$amount->setCurrency("USD");
$amount->setTotal("1.00");

$transaction = new Transaction();
$transaction->setAmount($amount);
$transaction->setDescription("This is the payment description.");

$doneToken = $tokenStorage->createModel();
$doneToken->setPaymentName('paypal');
$doneToken->setDetails($storage->getIdentificator($paymentDetails));
$doneToken->setTargetUrl('http://'.$_SERVER['HTTP_HOST'].'/Start/done.php?payum_token='.$doneToken->getHash());
$tokenStorage->updateModel($doneToken);

$captureToken = $tokenStorage->createModel();
$captureToken->setPaymentName('paypal');
$captureToken->setDetails($storage->getIdentificator($paymentDetails));
$captureToken->setTargetUrl('http://'.$_SERVER['HTTP_HOST'].'/Start/capture.php?payum_token='.$captureToken->getHash());
$captureToken->setAfterUrl($doneToken->getTargetUrl());
$tokenStorage->updateModel($captureToken);

$redirectUrls = new RedirectUrls();
$redirectUrls->setReturn_url($captureToken->getTargetUrl());
$redirectUrls->setCancel_url($captureToken->getTargetUrl());

$paymentDetails->setIntent("sale");
$paymentDetails->setPayer($payer);
$paymentDetails->setRedirect_urls($redirectUrls);
$paymentDetails->setTransactions(array($transaction));

$storage->updateModel($paymentDetails);

header("Location: ".$captureToken->getTargetUrl());
