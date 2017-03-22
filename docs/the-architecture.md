# The architecture

The code snippets presented below are only for demonstration purposes (pseudo code). Their goal is to illustrate the general approach to various tasks. To see real life examples please follow the links provided when appropriate.
In general, you have to create a _[request][base-request]_ , implement _[action][action-interface]_ in order to know what to do with such request.
And use _gateway_ that implements _[gateway interface][gateway-interface]_.
This is where things get processed.
This interface forces us to specify route to possible actions and can execute the request.
So, gateway is the place where a request and an action meet together.

_**Note**: If you'd like to see real world examples we have provided you with a sandbox: [online][sandbox-online], [code][sandbox-code]._

```php
<?php
use Payum\Core\Gateway;
use Payum\Core\Request\Capture;

$gateway = new Gateway;
$gateway->addAction(new CaptureAction);

//CaptureAction does its job.
$gateway->execute($capture = new Capture(array(
    'amount' => 100,
    'currency' => 'USD'
));

var_export($capture->getModel());
```

```php
<?php
use Payum\Core\Action\ActionInterface;
use Payum\Core\Request\Capture;

class CaptureAction implements ActionInterface
{
    public function execute($request)
    {
       $model = $request->getModel();

       //capture payment logic here

       $model['status'] = 'success';
       $model['transaction_id'] = 'an_id';
    }

    public function supports($request)
    {
        return $request instanceof Capture;
    }
}
```

That's the big picture. Now let's talk about the details:

_**Link**: See a real world example: [CaptureController][capture-controller]._

## Sub Requests

An action does not want to do all the job alone, so it delegates some responsibilities to other actions. In order to achieve this the action must be a _gateway aware_ action.
Only then, it can create a sub request and pass it to the gateway.

```php
<?php
use Payum\Core\Action\ActionInterface;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;

class FooAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;
    
    public function execute($request)
    {
        //do its jobs

        // delegate some job to bar action.
        $this->gateway->execute(new BarRequest);
    }
}
```

_**Link**: See paypal [CaptureAction][paypal-capture-action]._

## Replys

What about redirects or a credit card form? Some gateways, 
like Paypal ExpressCheckout for instance, require authorization on their side. 
Payum can handle such cases and for that we use something called _[replys][base-reply]_.
It is a special object which extends an exception hence could be thrown.
You can throw a http redirect reply for example at any time and catch it at a top level.

```php
<?php
use Payum\Core\Action\ActionInterface;
use Payum\Core\Reply\HttpRedirect;

class FooAction implements ActionInterface
{
    public function execute($request)
    {
        throw new HttpRedirect('http://example.com/auth');
    }
}
```

Above we see an action which throws a reply.
The reply is about redirecting a user to another url.
Next code example demonstrate how you catch and process it.

```php
<?php

use Payum\Core\Reply\HttpRedirect;

try {
    /** @var \Payum\Core\Gateway $gateway */
    $gateway->addAction(new FooAction);

    $gateway->execute(new FooRequest);
} catch (HttpRedirect $reply) {
    header( 'Location: '.$reply->getUrl());
    exit;
}
```

_**Link**: See real world example: [AuthorizeTokenAction][paypal-authorize-token-action]._

## Managing status

Good status handling is very important.
Statuses must not be hard coded and should be easy to reuse, hence we use the _[interface][status-request-interface]_ to handle this.
The [Status request][status-request] is provided by default by our library, however you are free to use your own and you can do so by implementing the status interface.

```php
<?php
use Payum\Core\Action\ActionInterface;
use Payum\Core\Request\GetStatusInterface;

class FooAction implements ActionInterface
{
    public function execute($request)
    {
        if ('success condition') {
           $request->markCaptured();
        } else if ('pending condition') {
           $request->markPending();
        } else {
           $request->markUnknown();
        }
    }

    public function supports($request)
    {
        return $request instanceof GetStatusInterface;
    }
}
```

```php
<?php

use Payum\Core\Request\GetHumanStatus;

/** @var \Payum\Core\Gateway $gateway */
$gateway->addAction(new FooAction);

$gateway->execute($status = new GetHumanStatus);

$status->isCaptured();
$status->isPending();

// or

$status->getValue();
```

_**Link**: The status logic could be a bit complicated [as paypal one][paypal-status-action] or pretty simple as [authorize.net one][authorize-status-action]._

## Extensions

There must be a way to extend the gateway with custom logic.
_[Extension][extension-interface]_ to the rescue.
Let's look at the example below.
Imagine you want to check permissions before a user can capture the payment:

```php
<?php
use Payum\Core\Extension\ExtensionInterface;
use Payum\Core\Extension\Context;

class PermissionExtension implements ExtensionInterface
{
    public function onPreExecute(Context $context)
    {
        $request = $context->getRequest();
        
        if (false == in_array('ROLE_CUSTOMER', $request->getModel()->getRoles())) {
            throw new Exception('The user does not have the required roles.');
        }

        // congrats, user has enough rights.
    }
}
```

```php
<?php

/** @var \Payum\Core\Gateway $gateway */
$gateway->addExtension(new PermissionExtension);

// here is the place where the exception may be thrown.
$gateway->execute(new FooRequest);
```

_**Link**: The [storage extension][storage-extension-interface] is a built-in extension._

## Persisting models

Before you are redirected to the gateway side, you may want to store data somewhere, right?
We take care of that too.
This is handled by _[storage][storage-interface]_ and its _[storage extension][storage-extension-interface]_ for gateway.
The extension can solve two tasks.
First it can save a model after the request is processed.
Second, it can find a model by its id before the request is processed.
Currently [Doctrine][doctrine-storage] [Zend Table Gateway][zend-table-gateway] and [filesystem][filesystem-storage] (use it for tests only!) storages are supported.

```php
<?php
use Payum\Core\Gateway;
use Payum\Core\Extension\StorageExtension;

/** @var \Payum\Core\Storage\StorageInterface $storage */
$storage = new FooStorage;

$gateway = new Gateway;
$gateway->addExtension(new StorageExtension($storage));
```

## All about API

The gateway API has different versions? Or, a gateway provide official sdk?
We already thought about these problems and you know what?

Let's say gateway have different versions: first and second.
And in the `FooAction` we want to use first api and `BarAction` second one.
To solve this problem we have to implement _API aware action_ to the actions.
When such api aware action is added to a gateway it tries to set an API, one by one, to the action until the action accepts one.

```php
<?php
use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\UnsupportedApiException;

class FooAction implements ActionInterface, ApiAwareInterface
{
    use ApiAwareTrait;
    
    public function __construct() 
    {
        $this->apiClass = Api::class;    
    }    
    
    
    public function execute($request) 
    {
        $this->api; // Api::class 
    }
}

class BarAction implements ActionInterface, ApiAwareInterface
{
    use ApiAwareTrait;
    
    public function __construct() 
    {
        $this->apiClass = AnotherApi::class;    
    }    
    
    
    public function execute($request) 
    {
        $this->api; // AnotherApi::class 
    }
}
```

```php
<?php
use Payum\Core\Gateway;

$gateway = new Gateway;
$gateway->addApi(new FirstApi);
$gateway->addApi(new SecondApi);

// here the ApiVersionOne will be injected to FooAction
$gateway->addAction(new FooAction);

// here the ApiVersionTwo will be injected to BarAction
$gateway->addAction(new BarAction);
```

_**Link**: See authorize.net [capture action][authorize-capture-action]._

## Conclusion

As a result of the architecture described above we end up with a well decoupled, easy to extend and reusable library.
For example, you can add your domain specific actions or a logger extension.
Thanks to its flexibility any task could be achieved.

Next [Your order integration](your-order-integration.md).

Back to [index](index.md).

[sandbox-online]: http://sandbox.payum.forma-dev.com
[sandbox-code]: https://github.com/Payum/PayumBundleSandbox
[base-request]: https://github.com/Payum/Payum/blob/master/src/Payum/Core/Request/Generic.php
[status-request-interface]: https://github.com/Payum/Payum/blob/master/src/Payum/Core/Request/GetStatusInterface.php
[status-request]: https://github.com/Payum/Payum/blob/master/src/Payum/Core/Request/GetHumanStatus.php
[base-reply]: https://github.com/Payum/Payum/blob/master/src/Payum/Core/Reply/Base.php
[action-interface]: https://github.com/Payum/Payum/blob/master/src/Payum/Core/Action/ActionInterface.php
[extension-interface]: https://github.com/Payum/Payum/blob/master/src/Payum/Core/Extension/ExtensionInterface.php
[storage-extension-interface]: https://github.com/Payum/Payum/blob/master/src/Payum/Core/Extension/StorageExtension.php
[storage-interface]: https://github.com/Payum/Payum/blob/master/src/Payum/Core/Storage/StorageInterface.php
[doctrine-storage]: https://github.com/Payum/Payum/blob/master/src/Payum/Core/Bridge/Doctrine/Storage/DoctrineStorage.php
[zend-table-gateway]: https://github.com/Payum/Payum/blob/master/src/Payum/Core/Bridge/Zend/Storage/TableGatewayStorage.php
[filesystem-storage]: https://github.com/Payum/Payum/blob/master/src/Payum/Core/Storage/FilesystemStorage.php
[gateway-interface]: https://github.com/Payum/Payum/blob/master/src/Payum/Core/GatewayInterface.php
[capture-controller]: https://github.com/Payum/PayumBundle/blob/master/Controller/CaptureController.php
[paypal-capture-action]:https://github.com/Payum/PaypalExpressCheckoutNvp/blob/master/Action/CaptureAction.php
[paypal-authorize-token-action]: https://github.com/Payum/Payum/blob/master/src/Payum/Paypal/ExpressCheckout/Nvp/Action/Api/AuthorizeTokenAction.php
[paypal-status-action]: https://github.com/Payum/Payum/blob/master/src/Payum/Paypal/ExpressCheckout/Nvp/Action/PaymentDetailsStatusAction.php
[authorize-capture-action]: https://github.com/Payum/Payum/blob/master/src/Payum/AuthorizeNet/Aim/Action/CaptureAction.php
[authorize-status-action]: https://github.com/Payum/Payum/blob/master/src/Payum/AuthorizeNet/Aim/Action/StatusAction.php
[omnipay]: https://github.com/adrianmacneil/omnipay
[omnipay-example]: https://github.com/Payum/PayumBundleSandbox/blob/master/src/Acme/PaymentBundle/Controller/SimplePurchasePaypalExpressViaOmnipayController.php
[bundle-doc]: index.md#symfony-payum-bundle
[payum-bundle]: https://github.com/Payum/PayumBundle/blob/master/PayumBundle.php
