# Payum Bundle. Console commands

Payum Bundle provides a set of CLI commands.

In the following examples, we will show you how to [get a payment status](#get-payment-status), [create a notify token](#create-notify-token) and [create a purchase token](#create-purchase-token). We will use `paypal` as the payment name.

## Get Payment Status

```bash
$ php bin/console payum:status paypal --model-class=Acme\PaymentBundle\Entity\Payment --model-id=1

> Status: success
```

## Create Notify Token

Some payment gateways do not allow you to set a callback URL per model. You can set only one URL in their admin area.
This command allows you to generate a secure URL. Optionally, you can associate a model with this token.

```bash
$ php bin/console payum:security:create-notify-token paypal --model-class=Acme\PaymentBundle\Entity\Payment --model-id=1

> Hash: oTA0w-SRaVY8U1pRr6MVshAtdjiogRENTlnJit6lYLg
> Url: http://localhost/payment/notify/oTA0w-SRaVY8U1pRr6MVshAtdjiogRENTlnJit6lYLg
> Details: Acme\PaymentBundle\Entity\Payment#1
```

## Create Purchase Token

This could be helpful when you want to send user a purchase link (via email) manually, or when user is lost in the middle of a payment and asking for a new link.

```bash
$ php bin/console payum:security:create-capture-token paypal \
 --model-class=Acme\PaymentBundle\Entity\Payment \
 --model-id=1 \
 --after-url="url-or-route-to-go-after-purchase"

> Hash: oTA0w-SRaVY8U1pRr6MVshAtdjiogRENTlnJit6lYLg
> Url: http://localhost/payment/capture/oTA0w-SRaVY8U1pRr6MVshAtdjiogRENTlnJit6lYLg
> After Url: url-or-route-to-go-after-purchase
> Details: Acme\PaymentBundle\Entity\Payment#1
```

## Debug payment

This could be helpful when you want to find out what actions were added to payment and in which order. 
Also it will show extensions and apis added too.  

```bash
$ php bin/console payum:gateway:debug

Found 1 gateways

> fooGateway (Payum\Core\Gateway):
>	Actions:
>	Payum\Core\Action\CapturePaymentAction
>	Payum\Core\Action\NotifyOrderAction
>	Payum\Core\Action\ExecuteSameRequestWithModelDetailsAction
>	Payum\Core\Bridge\Symfony\Action\GetHttpRequestAction
>	Payum\Bundle\PayumBundle\Action\ObtainCreditCardAction
>	Payum\Core\Bridge\Twig\Action\RenderTemplateAction
>	Payum\Offline\Action\CaptureAction
>	Payum\Offline\Action\ConvertPaymentAction
>	Payum\Offline\Action\StatusAction
>
>	Extensions:
>	Payum\Core\Extension\EndlessCycleDetectorExtension
>	Payum\Core\Bridge\Psr\Log\LogExecutedActionsExtension
>	Payum\Core\Bridge\Psr\Log\LoggerExtension
>	Payum\Core\Extension\StorageExtension
>		Payum\Core\Storage\FilesystemStorage
>		Payum\Core\Model\ArrayObject
>
>	Apis:
```

## Next Step

* [Back to index](../index.md).
