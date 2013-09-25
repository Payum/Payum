<?php

include '../src/Start/config.php';

$storage = $registry->getStorageForClass($paypalPaymentDetailsClass, 'paypal');

$paymentDetails = $storage->createModel();
$paymentDetails['PAYMENTREQUEST_0_CURRENCYCODE'] = 'EUR';
$paymentDetails['PAYMENTREQUEST_0_AMT'] = 1.23;
$storage->updateModel($paymentDetails);

$doneToken = $tokenStorage->createModel();
$doneToken->setPaymentName('paypal');
$doneToken->setDetails($storage->getIdentificator($paymentDetails));
$doneToken->setTargetUrl('http://'.$_SERVER['HTTP_HOST'].'/done.php?payum_token='.$doneToken->getHash());
$tokenStorage->updateModel($doneToken);

$captureToken = $tokenStorage->createModel();
$captureToken->setPaymentName('paypal');
$captureToken->setDetails($storage->getIdentificator($paymentDetails));
$captureToken->setTargetUrl('http://'.$_SERVER['HTTP_HOST'].'/capture.php?payum_token='.$captureToken->getHash());
$captureToken->setAfterUrl($doneToken->getTargetUrl());
$tokenStorage->updateModel($captureToken);

$paymentDetails['RETURNURL'] = $captureToken->getTargetUrl();
$paymentDetails['CANCELURL'] = $captureToken->getTargetUrl();
$storage->updateModel($paymentDetails);

header("Location: ".$captureToken->getTargetUrl());