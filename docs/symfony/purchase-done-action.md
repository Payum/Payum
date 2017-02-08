# Payum Bundle. Purchase done action

We assume you already know how to prepare payment details and how to capture them.
The last thing in this store what to do after?
This chapter should cover these questions.

Well, let's assume you created capture token this way while preparing payment.

```php
<?php
$captureToken = $this->get('payum')->getTokenFactory()->createCaptureToken(
    $gatewayName,
    $details,
    'acme_payment_done'
);
```

Pay attention to third parameter `acme_payment_done`.
It is the route of url you will be redirected after capture done its job. Let's look at an example of how this action may look like:

```php
<?php
    use Payum\Core\Request\GetHumanStatus;

    public function captureDoneAction(Request $request)
    {
        $token = $this->get('payum')->getHttpRequestVerifier()->verify($request);
        
        $identity = $token->getDetails();
        $model = $this->get('payum')->getStorage($identity->getClass())->find($identity);

        $gateway = $this->get('payum')->getGateway($token->getGatewayName());

        // you can invalidate the token. The url could not be requested any more.
        // $this->get('payum')->getHttpRequestVerifier()->invalidate($token);
        
        // Once you have token you can get the model from the storage directly. 
        //$identity = $token->getDetails();
        //$details = $payum->getStorage($identity->getClass())->find($identity);
        
        // or Payum can fetch the model for you while executing a request (Preferred).
        $gateway->execute($status = new GetHumanStatus($token));
        $details = $status->getFirstModel();
        
        // you have order and payment status 
        // so you can do whatever you want for example you can just print status and payment details.
        
        return new JsonResponse(array(
            'status' => $status->getValue(),
            'details' => iterator_to_array($details),
        ));
    }
```

In general you have to check status of the payment and do whatever you want depending on it.
For example if payment success you  would add a user some credits or update expiration date.
If not you redirect him to homepage and show a flash message with a bad news.

* [Back to index](../index.md).
