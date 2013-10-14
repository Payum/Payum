#### The architecture

_**Note**: The code snippets presented below are only for demonstration purposes (pseudo code). Their goal is to illustrate the general approach to various tasks. To see real life examples please follow the links provided when appropriate._

_**Note**: If you'd like to see real world examples we have provided you with a sandbox: [online][sandbox-online], [code][sandbox-code]._

In general, you have to create a _[request][base-request]_ , implement _[action][action-interface]_ in order to know what to do with such request. And use _[payment] that implements [payment-interface]_. This is where things get processed. This interface forces us to specify route to possible actions and can execute the request. So, payment is the place where a request and an action meet together.

```php
<?php
$payment = new Payment;
$payment->addAction(new CaptureAction));

//CaptureAction does its job.
$payment->execute($capture = new CaptureRequest(array(
    'amount' => 100,
    'currency' => 'USD'
));

var_export($capture->getModel());
```

```php
<?php
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
        return $request instanceof CaptureRequest;
    }
}
```

Here's a real world [example][capture-controller].

That's the big picture. Now let's talk about the details:

* An action does not want to do all the job alone, so it delegates some responsibilities to other actions. In order to achieve this the action must be a _payment aware_ action. Only then, it can create a sub request and pass it to the payment.

    ```php
    <?php
    class FooAction extends PaymentAwareAction
    {
        public function execute($request)
        {
            //do its jobs

            // delegate some job to bar action.
            $this->payment->execute(new BarRequest);
        }
    }
    ```

    See paypal [capture action][paypal-capture-action].

* What about redirects? Some payments, like paypal express for instance, require authorization on their side. Payum can handle such cases and for that we use something called _[interactive requests][base-interactive-request]_. It is a special request object, which extends an exception. You can throw an interactive redirect request at any time and catch it at a top level.

    ```php
    <?php
    class FooAction implements ActionInterface
    {
        public function execute($request)
        {
            throw new RedirectUrlInteractiveRequest('http://example.com/auth');
        }
    }
    ```
    ```php
    <?php
    try {
        $payment->addAction(new FooAction);

        $payment->execute(new FooRequest);
    } catch (RedirectUrlInteractiveRequest $redirectUrlInteractiveRequest) {
        header( 'Location: '.$redirectUrlInteractiveRequest->getUrl());
        exit;
    }
    ```

    See paypal [authorize token action][paypal-authorize-token-action].

* Good status handling is very important. Statuses must not be hard coded and should be easy to reuse, hence we use the _[interface][status-request-interface]_ to hanle this. The [Status request][status-request] is provided by default by our library, however you are free to use your own and you can do so by implementing the status interface.

    ```php
    <?php
    class FooAction implements ActionInterface
    {
        public function execute($request)
        {
            if ('success condition') {
               $request->markSuccess();
            } else if ('pending condition') {
               $request->markPending();
            } else {
               $request->markUnknown();
            }
        }

        public function supports($request)
        {
            return $request instanceof StatusRequestInterface;
        }
    }
    ```
    ```php
    <?php

    $payment->addAction(new FooAction);

    $payment->execute($status = new BinaryMaskStatusRequest);

    $status->isSuccess();
    $status->isPending();

    // or

    $status->getStatus();
    ```

    The status logic could be a bit [complicated][paypal-status-action] or pretty [simple][authorize-status-action].

* There must be a way to extend the payment with custom logic. _[Extension][extension-interface]_ to the rescue. Let's look at the example below. Imagine you want to check permissions before a user can capture the payment:

    ```php
    <?php
    class PermissionExtension implements ExtensionInterface
    {
        public function onPreExecute($request)
        {
            if (false == in_array('ROLE_CUSTOMER', $request->getModel()->getRoles())) {
                throw new Exception('The user does not have the required roles.');
            }

            // congrats, user has enough rights.
        }
    }
    ```
    ```php
    <?php
    $payment->addExtension(new PermissionExtension);

    // here is the place where the exception may be thrown.
    $payment->execute(new FooRequest);
    ```

    The [storage extension][storage-extension-interface] may be a good extension example.

* Before you are redirected to the gateway side, you may want to store data somewhere, right? We take care of that too. This is handled by _[storage][storage-interface]_ and its _[storage extension][storage-extension-interface]_ for payment. The extension can solve two tasks. First it can save a model after the request is processed. Second, it can find a model by its id before the request is processed. Currently [Doctrine][doctrine-storage] and [filesystem][filesystem-storage] (use it for tests only!) storages are supported.

    ```php
    <?php
    $storage = new FooStorage;

    $payment = new Payment;
    $payment->addExtension(new StorageExtension($storage));
    ```
* The payment API has different versions? No problem! A payment may contain a set of APIs. When _API aware action_ is added to a payment it tries to set an API, one by one, to the action until the action accepts one.

    ```php
    <?php
    class FooAction implements ActionInterface, ApiAwareInterface
    {
        public function setApi($api)
        {
            if (false == $api instanceof FirstApi) {
                throw new UnsupportedApiException('Not supported.');
            }

            $this->api = $api;
        }
    }

    class BarAction implements ActionInterface, ApiAwareInterface
    {
        public function setApi($api)
        {
            if (false == $api instanceof SecondApi) {
                throw new UnsupportedApiException('Not supported.');
            }

            $this->api = $api;
        }
    }
    ```
    ```php
    <?php
    $payment = new Payment;
    $payment->addApi(new FirstApi);
    $payment->addApi(new SecondApi);

    // here the ApiVersionOne will be injected to FooAction
    $payment->addAction(new FooAction);

    // here the ApiVersionTwo will be injected to BarAction
    $payment->addAction(new BarAction);
    ```

    See authorize.net [capture action][authorize-capture-action].

As a result of the architecture described above we end up with a well decoupled, easy to extend and reusable library. For example, you can add your domain specific actions or a logger extension. Thanks to its flexibility any task could be achieved.

### The bundle architecture

_**Note:** There is a [doc][bundle-doc] on how to setup and use payum bundle with the supported payment gateways._

The bundle allows you easy configure payments, add storages, and custom actions/extensions/apis. Nothing is hardcoded: all payments and storages are added via _factories_ ([payment factories][payment-factories], [storage factories][storage-factories]) in the bundle [build method][payum-bundle]. You can add your payment this way too!

Also, it provides a nice secure [capture controller][capture-controller]. It's extremely reusable. Check the [sandbox][sandbox-online] ([code][sandbox-code]) for more details.

The bundle supports [omnipay][omnipay] gateways (up to 25) out of the box. They could be configured the same way as native payments. The capture controller works [here][omnipay-example] too.

Back to [index](index.md).

[sandbox-online]: http://sandbox.payum.forma-dev.com
[sandbox-code]: https://github.com/Payum/PayumBundleSandbox
[base-request]: https://github.com/Payum/Payum/blob/master/src/Payum/Request/BaseModelRequest.php
[status-request-interface]: https://github.com/Payum/Payum/blob/master/src/Payum/Request/StatusRequestInterface.php
[status-request]: https://github.com/Payum/Payum/blob/master/src/Payum/Request/BinaryMaskStatusRequest.php
[base-interactive-request]: https://github.com/Payum/Payum/blob/master/src/Payum/Request/BaseInteractiveRequest.php
[action-interface]: https://github.com/Payum/Payum/blob/master/src/Payum/Action/ActionInterface.php
[extension-interface]: https://github.com/Payum/Payum/blob/master/src/Payum/Extension/ExtensionInterface.php
[storage-extension-interface]: https://github.com/Payum/Payum/blob/master/src/Payum/Extension/StorageExtension.php
[storage-interface]: https://github.com/Payum/Payum/blob/master/src/Payum/Storage/StorageInterface.php
[doctrine-storage]: https://github.com/Payum/Payum/blob/master/src/Payum/Bridge/Doctrine/Storage/DoctrineStorage.php
[filesystem-storage]: https://github.com/Payum/Payum/blob/master/src/Payum/Storage/FilesystemStorage.php
[payment-interface]: https://github.com/Payum/Payum/blob/master/src/Payum/PaymentInterface.php
[capture-controller]: https://github.com/Payum/PayumBundle/blob/master/Controller/CaptureController.php
[paypal-capture-action]: https://github.com/Payum/PaypalExpressCheckoutNvp/blob/master/src/Payum/Paypal/ExpressCheckout/Nvp/Action/CaptureAction.php
[paypal-authorize-token-action]: https://github.com/Payum/PaypalExpressCheckoutNvp/blob/master/src/Payum/Paypal/ExpressCheckout/Nvp/Action/Api/AuthorizeTokenAction.php
[paypal-status-action]: https://github.com/Payum/PaypalExpressCheckoutNvp/blob/master/src/Payum/Paypal/ExpressCheckout/Nvp/Action/PaymentDetailsStatusAction.php
[authorize-capture-action]: https://github.com/Payum/AuthorizeNetAim/blob/master/src/Payum/AuthorizeNet/Aim/Action/CaptureAction.php
[authorize-status-action]: https://github.com/Payum/AuthorizeNetAim/blob/master/src/Payum/AuthorizeNet/Aim/Action/StatusAction.php
[omnipay]: https://github.com/adrianmacneil/omnipay
[omnipay-example]: https://github.com/Payum/PayumBundleSandbox/blob/master/src/Acme/PaymentBundle/Controller/SimplePurchasePaypalExpressViaOmnipayController.php
[bundle-doc]: https://github.com/Payum/PayumBundle/blob/master/Resources/doc/index.md
[payment-factories]: https://github.com/Payum/PayumBundle/tree/master/DependencyInjection/Factory/Payment
[storage-factories]: https://github.com/Payum/PayumBundle/tree/master/DependencyInjection/Factory/Storage
[payum-bundle]: https://github.com/Payum/PayumBundle/blob/master/PayumBundle.php
