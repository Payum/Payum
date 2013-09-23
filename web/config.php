<?php
//config.php

use Buzz\Client\Curl;
use Payum\Extension\StorageExtension;
use Payum\Paypal\ExpressCheckout\Nvp\PaymentFactory;
use Payum\Paypal\ExpressCheckout\Nvp\Api;
use Payum\Registry\SimpleRegistry;
use Payum\Storage\FilesystemStorage;
use Payum\Security\PlainHttpRequestVerifier;

$tokenStorage = new FilesystemStorage('storages/', 'Payum/Model/Token', 'hash');
$requestVerifier = new PlainHttpRequestVerifier($tokenStorage);

$paypalPaymentDetailsClass = 'PayumStart/Model/PaypalPaymentDetails';
$storages = array(
    'paypal' => array(
        $paypalPaymentDetailsClass => new FilesystemStorage('storages/', $paypalPaymentDetailsClass, 'id')
    )
);

$payments = array(
    'paypal' => PaymentFactory::create(new Api(new Curl, array(
            'username' => 'poljakov.ws-facilitator_api1.gmail.com',
            'password' => '1379924240',
            'signature' => 'ASzjWrCiwL7ehuXZv-A7NnZMOYstAS5vaZDeqCN0V2cTaIVDCMirUNbn',
            'sandbox' => true,
        )
    )));

$payments['paypal']->addExtension(new StorageExtension($storages['paypal'][$paypalPaymentDetailsClass]));

$registry = new SimpleRegistry($payments, $storages, null, null);