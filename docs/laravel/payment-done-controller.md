<h2 align="center">Supporting Payum</h2>

Payum is an MIT-licensed open source project with its ongoing development made possible entirely by the support of community and our customers. If you'd like to join them, please consider:

- [Become a sponsor](https://www.patreon.com/makasim)
- [Become our client](http://forma-pro.com/)

---

# Payum Laravel Package. Payment done controller

First we have to validate the request. 
If it is valid the verifier returns a token. 
We can use it later to get payment status, details and any other information. 

```php
<?php

use Payum\Core\Request\GetHumanStatus;
use Payum\LaravelPackage\Controller\PayumController;
use Symfony\Component\HttpFoundation\Request;

class PaymentController extends PayumController
{
    public function done($payum_token)
    {
        /** @var Request $request */
        $request = \App::make('request');
        $request->attributes->set('payum_token', $payum_token);

        $token = $this->getPayum()->getHttpRequestVerifier()->verify($request);
        $gateway = $this->getPayum()->getGateway($token->getGatewayName());

        $gateway->execute($status = new GetHumanStatus($token));

        return \Response::json(array(
            'status' => $status->getValue(),
            'details' => iterator_to_array($status->getFirstModel())
        ));
    }
}
```

Back to [index](../index.md).