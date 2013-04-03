Payum Paypal Ipn
================

The lib implements [Paypal Instant payment notifications](https://www.x.com/developers/paypal/documentation-tools/ipn/integration-guide/IPNIntro) client.

## How to Api?

```
<?php
use Buzz\Client\Curl;
use Payum\Paypal\Ipn\Api;

$api = new Api(new Curl, array(
    'sandbox' => true
)));

if (Api::NOTIFY_VERIFIED === $api->notifyValidate($_POST)) {
    echo 'It is valid paypal notification. Let\'s do some additional checks';
}

echo 'Something wrong in notification';
```

**Warning:**

> Important: After you receive the VERIFIED message, there are several important checks you must perform before you can assume that the message is legitimate and not already processed.

## Like it? Spread the world!

You can star the lib on [github](https://github.com/Payum/PaypalIpn) or [packagist](https://packagist.org/packages/payum/paypal-ipn). You may also drop a message on Twitter.  

## Need support?

If you are having general issues with [paypal ipn](https://github.com/Payum/PaypalIpn) or [payum](https://github.com/Payum/Payum), we suggest posting your issue on [stackoverflow](http://stackoverflow.com/). Feel free to ping @maksim_ka2 on Twitter if you can't find a solution.

If you believe you have found a bug, please report it using the GitHub issue tracker: [paypal ipn](https://github.com/Payum/PaypalIpn/issues) or [payum](https://github.com/Payum/Payum/issues), or better yet, fork the library and submit a pull request.

## License

Paypal Ipn is released under the MIT License. For more information, see [License](LICENSE).