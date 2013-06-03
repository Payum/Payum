PayumBundle [![Build Status](https://travis-ci.org/Payum/PayumBundle.png?branch=master)](https://travis-ci.org/Payum/PayumBundle)
===========

The PayumBundle adds support of [payum](https://github.com/Payum/Payum) lib to [symfony](symfony.com) framework.

The bundle allows you easy configure payments, add storages, custom actions/extensions/apis. 
Nothing is hardcoded: all payments and storages are added via _factories_ ([payment factories][payment-factories], [storage factories][storage-factories]) in the bundle [build method][payum-bundle].
You can add your payment this way too!

Also, it provides a nice secured [capture controller][capture-controller]. It's extremely reusable. Check the [sandbox][sandbox-online] ([code][sandbox-code]) for more details.

Supported Payments:
-------------------

- [Paypal express checkout](https://github.com/Payum/PaypalExpressCheckoutNvp).
- [Authorize.Net AIM](https://github.com/Payum/AuthorizeNetAim).
- [Be2Bill](https://github.com/Payum/Be2Bill).
- [Omnipay](https://github.com/adrianmacneil/omnipay) gateways via [bridge](https://github.com/Payum/OmnipayBridge). 

Documentation
-------------

The bulk of the documentation is stored in the `Resources/doc/index.md` file in this bundle:

[Read the Documentation for master](Resources/doc/index.md)

[Read the Documentation of payum ](https://github.com/Payum/Payum#payum-)

Look at sandbox: [online](http://sandbox.payum.forma-dev.com), [code](https://github.com/Payum/PayumBundleSandbox).


Installation
------------

All the installation instructions are located in [documentation](Resources/doc/index.md).

Like it? Spread the world!
--------------------------

You can star the lib on [github](https://github.com/Payum/PayumBundle) or [packagist](https://packagist.org/packages/payum/payum-bundle). You may also drop a message on Twitter.  

Need support?
-------------

If you are having general issues with [bundle](https://github.com/Payum/PayumBundle) or [payum](https://github.com/Payum/Payum), we suggest posting your issue on [stackoverflow](http://stackoverflow.com/). Feel free to ping @maksim_ka2 on Twitter if you can't find a solution.

If you believe you have found a bug, please report it using the GitHub issue tracker: [bundle](https://github.com/Payum/PayumBundle/issues) or [payum](https://github.com/Payum/Payum/issues), or better yet, fork the library and submit a pull request.

License
-------

PayumBundle is released under the MIT License. For more information, see [License](Resources/meta/LICENSE).

[capture-controller]: https://github.com/Payum/PayumBundle/blob/master/Controller/CaptureController.php
[payment-factories]: https://github.com/Payum/PayumBundle/tree/master/DependencyInjection/Factory/Payment
[storage-factories]: https://github.com/Payum/PayumBundle/tree/master/DependencyInjection/Factory/Storage
[sandbox-online]: http://sandbox.payum.forma-dev.com
[sandbox-code]: https://github.com/Payum/PayumBundleSandbox
