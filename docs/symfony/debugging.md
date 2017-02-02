# Payum Bundle. Debugging

## Debug payment

This could be helpful when you want to find out what actions were added to payment and in which order. 
Also it will show extensions and apis added too.  

```bash
$ php bin/console payum:gateway:debug
```

## Logs

Whenever you have problems, or want to understand Payum internals: _check the log file_.
It contains the whole stack of called actions including details about a request and a model.

_**Note:** The profiler allows to choose from up to 10 last http requests. So find desired one and check the logs section._

_**Note:** This log is available since symfony 2.2 because it is where psr-3 logger support was added._

Here's an example of paypal execution before you are redirected to paypal side.

```
DEBUG - [Payum] 1# Payum\Core\Action\ExecuteSameRequestWithModelDetailsAction::execute(Capture{model: Token})
DEBUG - [Payum] 2# Payum\Paypal\ExpressCheckout\Nvp\Action\CaptureAction::execute(Capture{model: PaymentDetails})
DEBUG - [Payum] 3# Payum\Paypal\ExpressCheckout\Nvp\Action\Api\SetExpressCheckoutAction::execute(SetExpressCheckout{model: ArrayObject})
DEBUG - [Payum] 3# Payum\Paypal\ExpressCheckout\Nvp\Action\Api\AuthorizeTokenAction::execute(AuthorizeToken{model: ArrayObject})
DEBUG - [Payum] 3# AuthorizeTokenAction::execute(AuthorizeTokenRequest{model: ArrayObject}) throws reply HttpRedirect{url: https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token=EC-86848505A5250314X}
DEBUG - [Payum] 2# CaptureAction::execute(Capture{model: PaymentDetails}) throws reply HttpRedirect{url: https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token=EC-86848505A5250314X}
DEBUG - [Payum] 1# ExecuteSameRequestWithModelDetailsAction::execute(SecuredCaptur{model: PaymentDetails}) throws reply HttpRedirect{url: https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token=EC-86848505A5250314X}
```

This stack of executed action when you come back from paypal side and finish the capture.

```
DEBUG - [Payum] 1# Payum\Core\Action\ExecuteSameRequestWithModelDetailsAction::execute(Capture{model: Token})
DEBUG - [Payum] 2# Payum\Paypal\ExpressCheckout\Nvp\Action\CaptureAction::execute(Capture{model: PaymentDetails})
DEBUG - [Payum] 3# Payum\Paypal\ExpressCheckout\Nvp\Action\PaymentDetailsSyncAction::execute(Sync{model: ArrayObject})
DEBUG - [Payum] 4# Payum\Paypal\ExpressCheckout\Nvp\Action\Api\GetExpressCheckoutDetailsAction::execute(GetExpressCheckoutDetails{model: ArrayObject})
DEBUG - [Payum] 3# Payum\Paypal\ExpressCheckout\Nvp\Action\Api\DoExpressCheckoutPaymentAction::execute(DoExpressCheckoutPayment{model: ArrayObject})
DEBUG - [Payum] 3# Payum\Paypal\ExpressCheckout\Nvp\Action\PaymentDetailsSyncAction::execute(Sync{model: ArrayObject})
DEBUG - [Payum] 4# Payum\Paypal\ExpressCheckout\Nvp\Action\Api\GetExpressCheckoutDetailsAction::execute(GetExpressCheckoutDetails{model: ArrayObject})
DEBUG - [Payum] 4# Payum\Paypal\ExpressCheckout\Nvp\Action\Api\GetTransactionDetailsAction::execute(GetTransactionDetails{model: ArrayObject})
```

* [Back to index](../index.md).