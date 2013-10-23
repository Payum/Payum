# Debugging

Whenever you have problems, want understand payum internals: _check the log file_.
It contains the whole stack of called actions including details about a request and a model.

_**Note:** The profiler allows to choose from up to 10 last http requests. So find desired one and check the logs section._

_**Note:** This log is available since symfony 2.2 because it is where psr-3 logger support was added._

Here's an example of paypal execution before you are redirected to paypal side.

```
DEBUG - [Payum] 1# Payum\Action\StatusDetailsAggregatedModelAction::execute(BinaryMaskStatusRequest{model: TokenizedDetails})
DEBUG - [Payum] 2# Payum\Paypal\ExpressCheckout\Nvp\Action\PaymentDetailsStatusAction::execute(BinaryMaskStatusRequest{model: PaymentDetails})
DEBUG - [Payum] 1# Payum\Action\CaptureDetailsAggregatedModelAction::execute(CaptureTokenizedDetailsRequest{model: TokenizedDetails})
DEBUG - [Payum] 2# Payum\Paypal\ExpressCheckout\Nvp\Action\CaptureAction::execute(CaptureTokenizedDetailsRequest{model: PaymentDetails})
DEBUG - [Payum] 3# Payum\Paypal\ExpressCheckout\Nvp\Action\Api\SetExpressCheckoutAction::execute(SetExpressCheckoutRequest{model: ArrayObject})
DEBUG - [Payum] 3# Payum\Paypal\ExpressCheckout\Nvp\Action\Api\AuthorizeTokenAction::execute(AuthorizeTokenRequest{model: ArrayObject})
DEBUG - [Payum] 3# AuthorizeTokenAction::execute(AuthorizeTokenRequest{model: ArrayObject}) throws interactive RedirectUrlInteractiveRequest{url: https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token=EC-86848505A5250314X}
DEBUG - [Payum] 2# CaptureAction::execute(CaptureTokenizedDetailsRequest{model: PaymentDetails}) throws interactive RedirectUrlInteractiveRequest{url: https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token=EC-86848505A5250314X}
DEBUG - [Payum] 1# CaptureDetailsAggregatedModelAction::execute(CaptureTokenizedDetailsRequest{model: PaymentDetails}) throws interactive RedirectUrlInteractiveRequest{url: https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token=EC-86848505A5250314X}
```

This stack of executed action when you come back from paypal side and finish the capture.

```
DEBUG - [Payum] 1# Payum\Action\StatusDetailsAggregatedModelAction::execute(BinaryMaskStatusRequest{model: TokenizedDetails})
DEBUG - [Payum] 2# Payum\Paypal\ExpressCheckout\Nvp\Action\PaymentDetailsStatusAction::execute(BinaryMaskStatusRequest{model: PaymentDetails})
DEBUG - [Payum] 1# Payum\Action\CaptureDetailsAggregatedModelAction::execute(CaptureTokenizedDetailsRequest{model: TokenizedDetails})
DEBUG - [Payum] 2# Payum\Paypal\ExpressCheckout\Nvp\Action\CaptureAction::execute(CaptureTokenizedDetailsRequest{model: PaymentDetails})
DEBUG - [Payum] 3# Payum\Paypal\ExpressCheckout\Nvp\Action\PaymentDetailsSyncAction::execute(SyncRequest{model: ArrayObject})
DEBUG - [Payum] 4# Payum\Paypal\ExpressCheckout\Nvp\Action\Api\GetExpressCheckoutDetailsAction::execute(GetExpressCheckoutDetailsRequest{model: ArrayObject})
DEBUG - [Payum] 3# Payum\Paypal\ExpressCheckout\Nvp\Action\Api\DoExpressCheckoutPaymentAction::execute(DoExpressCheckoutPaymentRequest{model: ArrayObject})
DEBUG - [Payum] 3# Payum\Paypal\ExpressCheckout\Nvp\Action\PaymentDetailsSyncAction::execute(SyncRequest{model: ArrayObject})
DEBUG - [Payum] 4# Payum\Paypal\ExpressCheckout\Nvp\Action\Api\GetExpressCheckoutDetailsAction::execute(GetExpressCheckoutDetailsRequest{model: ArrayObject})
DEBUG - [Payum] 4# Payum\Paypal\ExpressCheckout\Nvp\Action\Api\GetTransactionDetailsAction::execute(GetTransactionDetailsRequest{model: ArrayObject})
```

## Next Step

* [Back to index](index.md).