<?php

$api = new \Payum\Paypal\ExpressCheckout\Nvp\NvpApi();

$payment = new Payum\Paypal\ExpressCheckout\Nvp\Payment();
$payment->addStrategy(new Payum\Paypal\ExpressCheckout\Nvp\Strategy\SimplePayStrategy());
$payment->addStrategy(new Payum\Paypal\ExpressCheckout\Nvp\Strategy\SetExpressCheckoutStrategy($api));
$payment->addStrategy(new Payum\Paypal\ExpressCheckout\Nvp\Strategy\DoExpressCheckoutPaymentStrategy($api));
$payment->addStrategy(new Payum\Paypal\ExpressCheckout\Nvp\Strategy\GetExpressCheckoutDetailsStrategy($api));

try {
    //create or get from storage
    $payAction = new SimplePayAction(100.05, 'EUR');

    if ($interactiveOperation = $payment->execute($payAction)) {
        //interactive mode.
        //redirect to authorize page for example.
    }
// save to storage.
// transaction stop
//we are done
} catch (Exception $e) {
    //rollback
}