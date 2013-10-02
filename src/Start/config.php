<?php
//config.php

require '../../vendor/autoload.php';

use Buzz\Client\Curl;
use Payum\Extension\StorageExtension;
use Payum\Paypal\ExpressCheckout\Nvp\PaymentFactory;
use Payum\Paypal\ExpressCheckout\Nvp\Api;
use Payum\Registry\SimpleRegistry;
use Payum\Storage\FilesystemStorage;
use Payum\Security\PlainHttpRequestVerifier;

use Payum\Paypal\Rest\PaymentFactory as RestPaymentFactory;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Rest\ApiContext;

$tokenStorage = new FilesystemStorage(__DIR__.'/storage', 'Payum\Model\Token', 'hash');
$requestVerifier = new PlainHttpRequestVerifier($tokenStorage);

$paypalPaymentDetailsClass = 'Start\Model\PaypalPaymentDetails';
$paypalRestPaymentDetailsClass = 'Payum\Paypal\Rest\Model\PaymentDetails';
$storages = array(
    'paypal' => array(
        $paypalPaymentDetailsClass => new FilesystemStorage(__DIR__.'/storage', $paypalPaymentDetailsClass, 'id')
    ),
    'paypalRest' => array(
        $paypalRestPaymentDetailsClass => new FilesystemStorage(__DIR__.'/storage', $paypalRestPaymentDetailsClass, 'idStorage')
    )
);


define("PP_CONFIG_PATH", __DIR__);

$configManager = \PPConfigManager::getInstance();

$cred = new OAuthTokenCredential(
    $configManager->get('acct1.ClientId'),
    $configManager->get('acct1.ClientSecret'));


$payments = array(
    'paypal' => PaymentFactory::create(new Api(new Curl, array(
            'username' => 'poljakov.ws-facilitator_api1.gmail.com',
            'password' => '1379924240',
            'signature' => 'ASzjWrCiwL7ehuXZv-A7NnZMOYstAS5vaZDeqCN0V2cTaIVDCMirUNbn',
            'sandbox' => true,
        )
    )),
    'paypalRest' => RestPaymentFactory::create(new ApiContext($cred, 'Request' . time()))

);

$payments['paypal']->addExtension(new StorageExtension($storages['paypal'][$paypalPaymentDetailsClass]));
$payments['paypalRest']->addExtension(new StorageExtension($storages['paypalRest'][$paypalRestPaymentDetailsClass]));

$registry = new SimpleRegistry($payments, $storages, null, null);
