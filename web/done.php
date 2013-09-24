<?php

use Payum\Request\BinaryMaskStatusRequest;

include 'config.php';

$token = $requestVerifier->verify($_REQUEST);
$payment = $registry->getPayment($token->getPaymentName());

$payment->execute($status = new BinaryMaskStatusRequest($token));
var_dump(json_encode(array('status' => $status->getStatus()) + iterator_to_array($status->getModel()), JSON_PRETTY_PRINT));
die;
if ($status->isSuccess()) {
    echo 'payment captured successfully';
} else {
    echo 'payment captured not successfully';
}