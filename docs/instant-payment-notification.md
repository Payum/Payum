# Instant payment notification

A notification is a callback. A gateway sends it back to you to let you know about changes. It could be due a refund or pending payment acceptance. The diagram shows two examples where notification could be very handy:

![notification](http://www.websequencediagrams.com/cgi-bin/cdraw?lz=cGFydGljaXBhbnQgUGF5cGFsCgAHDGNhcHR1cmUucGhwAAsNbm90aWZ5ABIFCgAZCy0-KwA\_BjogYSBwdXJjYWhzZQoAUgYtPi0AQws6IHBlbmRpbmcAFggtPgBKCjogc3VjY2VzcwBiBmljYXRpb24AMTkARgcAVBZjYW5jZWxlZCAodXNlciB2b2lkIG9uIHAAggcFIHNpZGUp\&s=default)

If you follow [get it started](get-it-started.md) and used a payum builder to create paypal gateway, you do not have to care about notify url. Payum does it for you. You just have to make sure [notify script](examples/notify-script.md) is accessible from web.

The model will be updated automatically once the notification is sent. What you have to do is add an extension to detect payment status changes, and act accordingly.

Here's an example of the extension:

```php
<?php

use Payum\Core\Extension\Context;
use Payum\Core\Extension\ExtensionInterface;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Request\Generic;
use Payum\Core\Request\GetHumanStatus;
use Payum\Core\Request\GetStatusInterface;

class PaymentStatusExtension implements ExtensionInterface
{
    /**
     * {@inheritDoc}
     */
    public function onPostExecute(Context $context)
    {
        $request = $context->getRequest();
        if (! $request instanceof Generic) {
            return;
        }
        if ($request instanceof GetStatusInterface) {
            return;
        }

        $payment = $request->getModel();
        if (! $payment instanceof PaymentInterface) {
            return;
        }

        $context->getGateway()->execute($status = new GetHumanStatus($payment));

        // check the status and act accordingly
    }

    /**
     * {@inheritDoc}
     */
    public function onPreExecute(Context $context)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function onExecute(Context $context)
    {
    }
}
```

***

### Supporting Payum

Payum is an MIT-licensed open source project with its ongoing development made possible entirely by the support of community and our customers. If you'd like to join them, please consider:

* [Become a sponsor](https://github.com/sponsors/Payum)
