Payum
=====

The lib tends to provide very abstract interface to handle any possible payments. 

Basic concepts
--------------

* **Action** - it executes some logic depending on information from a request.
* **Request** - it contains information needed to run particular action.
* **ActionPaymentAware** - the action can ask payment to execute a request required for its job. 
* **InteractiveRequest** - The request requires an input from the user (display form, redirect somewhere, confirmation etc) It has to be thrown by an action.
* **Instruction** - Payment specific information. The request may aggregate or\and be aware of instruction.
* **Payment** - Contains payment specific actions. On request tries to find action responsible for the request. Pass the request to the found action.

Key principles:
--------------

* **Storage unaware** - It is up to you where to store all info.
* **Flexible** - It provides complete solution but you are free to change any part of it (Thanks to the "action" idea).
* **Domain logic friendly** - You can develop your domain logic without any limitations from the lib side. The "request" does not require any interface to be implemented
* **Decomposed** - The whole logic exploded into several actions.
* **Heavily tested** - As a result the lib is pretty stable.

Big Picture
===========

```php
<?php
//Source: Payum\Examples\ReadmeTest::bigPicture()

//Populate payment with actions.
$payment = new \Payum\Payment;
$payment->addAction(new \Payum\Examples\SellAction());
$payment->addAction(new \Payum\Examples\AuthorizeAction());
$payment->addAction(new \Payum\Examples\StatusAction());

//Create request object. It could be anything supported by an action.
$sell = new \Payum\Request\SimpleSellRequest;
$sell->setPrice(100.05);
$sell->setCurrency('EUR');

//Execute request
if (null === $payment->execute($sell)) {
    echo 'We are done!';
}
```

Interactive requests
====================

```php
<?php
//Source: Payum\Examples\ReadmeTest::interactiveRequests()

//...

//Create authorize required request.
$sell = new \Payum\Examples\AuthorizeRequiredSellRequest();
$sell->setPrice(100.05);
$sell->setCurrency('EUR');

if ($interactiveRequest = $payment->execute($sell)) {    
    if ($interactiveRequest instanceof \Payum\Request\RedirectUrlInteractiveRequest) {
        echo 'User must be redirected to '.$interactiveRequest->getUrl();
    } 
    
    //...
}
```

Getting Request Status
======================

```php
//Source: Payum\Examples\ReadmeTest::gettingRequestStatus()

//...

$statusRequest = new \Payum\Request\BinaryMaskStatusRequest($sell);
$payment->execute($statusRequest);

//Or there is a status which require our attention.
if ($statusRequest->isSuccess()) {
    echo 'We are done!';
} else if ($statusRequest->isCanceled()) {
    echo 'Canceled!';
} elseif ($statusRequest->isFailed()) {
    echo 'Failed!';
} elseif ($statusRequest->isInProgress()) {
    echo 'In progress!';
}
```