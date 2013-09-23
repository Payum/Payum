<?php

use Payum\Request\BinaryMaskStatusRequest;

include 'config.php';

$token = $requestVerifier->verify($_REQUEST);
$payment = $registry->getPayment($token->getPaymentName());

$payment->execute($status = new BinaryMaskStatusRequest($token));
if ($status->isSuccess()) {
    echo 'payment captured successfully';
} else {
    echo 'payment captured not successfully';
}