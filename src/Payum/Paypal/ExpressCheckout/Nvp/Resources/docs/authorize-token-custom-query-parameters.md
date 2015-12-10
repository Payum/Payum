# Authorize token custom query parameters.

It often required to customize authorize token ulr parameters.
For example you may want a mobile version of paypal pages.
In this case you would like to change `cmd` from `_express-checkout` to `_express-checkout-mobile`.
Or to change "Continue" button to "Pay" on the last paypal's page. To do so you have to send an extra query parameter `useraction=commit`.
So here we would describe ways you can use this.

## Static usage.

You can pass these query parameters as api options:

```php
<?php

$api = new Api(array(
    // ...
    'useraction' => Api::USERACTION_COMMIT,
    'cmd' => Api::CMD_EXPRESS_CHECKOUT_MOBILE,
));

echo $api->getAuthorizeTokenUrl('aToken');
// https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_express-checkout-mobile&useraction=commit&token=aToken
```

## Runtime usage.

You could also pass these parameters as a second argument of `getAuthorizeTokenUrl` method.
These parameters will overwrite values passed as options.
You could also use second variant to pass any other parameters.

```php
<?php

$api = new Api($options= array());

echo $api->getAuthorizeTokenUrl('aToken', array(
    'useraction' => Api::USERACTION_COMMIT,
    'cmd' => Api::CMD_EXPRESS_CHECKOUT_MOBILE,
));
// https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_express-checkout-mobile&token=aToken
```

To pass these values using your PaymentDetails models set next fields:

```php
<?php
use Payum\Core\Model\ArrayObject;

$model = new ArrayObject;
$model['AUTHORIZE_TOKEN_CMD'] = Api::CMD_EXPRESS_CHECKOUT_MOBILE;
$model['AUTHORIZE_TOKEN_USERACTION'] = Api::USERACTION_COMMIT;

$gateway->execute(new Capture($model));
```

Back to [index](index.md).