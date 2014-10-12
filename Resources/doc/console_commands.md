# Console commands

Payum Bundle provides a set of CLI commands.

In the following examples, we will show you how to [get a payment status](#get-payment-status), [create a notify token](#create-notify-token) and [create a purchase token](#create-purchase-token). We will use `paypal` as the payment name.

## Get Payment Status

```bash
$ php app/console payum:status paypal --model-class=Acme\PaymentBundle\Entity\Order --model-id=1

> Status: success
```

## Create Notify Token

Some payment gateways do not allow you to set a callback URL per model. You can set only one URL in their admin area.
This command allows you to generate a secure URL. Optionally, you can associate a model with this token.

```bash
$ php app/console payum:security:create-notify-token paypal --model-class=Acme\PaymentBundle\Entity\Order --model-id=1

> Hash: oTA0w-SRaVY8U1pRr6MVshAtdjiogRENTlnJit6lYLg
> Url: http://localhost/payment/notify/oTA0w-SRaVY8U1pRr6MVshAtdjiogRENTlnJit6lYLg
> Details: Acme\PaymentBundle\Entity\Order#1
```

## Create Purchase Token

This could be helpful when you want to send user a purchase link (via email) manually, or when user is lost in the middle of a payment and asking for a new link.

```bash
$ php app/console payum:security:create-capture-token paypal \
 --model-class=Acme\PaymentBundle\Entity\Order \
 --model-id=1 \
 --after-url="url-or-route-to-go-after-purchase"

> Hash: oTA0w-SRaVY8U1pRr6MVshAtdjiogRENTlnJit6lYLg
> Url: http://localhost/payment/capture/oTA0w-SRaVY8U1pRr6MVshAtdjiogRENTlnJit6lYLg
> After Url: url-or-route-to-go-after-purchase
> Details: Acme\PaymentBundle\Entity\Order#1
```

## Next Step

* [Back to index](index.md).
