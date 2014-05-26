# Console commands

The bundle provide a set of cli commands.

## Get payment status command.

```bash
$ ./app/console payum:status paypal --model-clsas=Acme\PaymentBundle\Entity\PaymentDetails --model-id=1
>Status: success
```

## Create notify token.

Some payment gateway does not allow you to set a callback url per model. You can set only one url in their admin area.
This command allows you to generate secure url. Optionally, you can associate a model with this token.

```bash
$ ./app/console payum:status paypal --model-clsas=Acme\PaymentBundle\Entity\PaymentDetails --model-id=1
>Hash: oTA0w-SRaVY8U1pRr6MVshAtdjiogRENTlnJit6lYLg
>Url: http://localhost/payment/notify/oTA0w-SRaVY8U1pRr6MVshAtdjiogRENTlnJit6lYLg
>Details: Acme\PaymentBundle\Entity\PaymentDetails#1
```

## Create purchase token.

This could be helpful when you want to send user a purchase link (via email) manually.
Or when user is lost in the middle and asking for the new link.

```bash
$ ./app/console payum:status paypal \
 --after-url="url-or-route-to-go-after-purchase" \
 --model-clsas=Acme\PaymentBundle\Entity\PaymentDetails \
 --model-id=1
>Hash: oTA0w-SRaVY8U1pRr6MVshAtdjiogRENTlnJit6lYLg
>Url: http://localhost/payment/capture/oTA0w-SRaVY8U1pRr6MVshAtdjiogRENTlnJit6lYLg
>After Url: url-or-route-to-go-after-purchase
>Details: Acme\PaymentBundle\Entity\PaymentDetails#1
```

## Next Step

* [Back to index](index.md).
