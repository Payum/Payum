# Payum Bundle. Configuration Reference

## Payum extension:

```yaml
payum:
    security:
        token_storage:
            A\Model\TokenClass:
                # storage specific options

    storages:
        A\Model\Class:
            gateway:
                all: true 
                factories: []
                payments: []

            # storage specific options
        Another\Model\Class:
            payment:
                all: true 
                factories: []
                payments: []

            # storage specific options

    gateways:
        foo_gateway:
            # options
                
        bar_gateway:
            # options

            payum.template.layout: "AcmeDemoBundle::layout.html.twig"

            #use container parameter
            payum.template.foo: "%aParameterName%"

            # use service from container
            payum.action.foo: "@aServiceId"
            payum.api.foo: "@aServiceId"
            payum.extension.foo: "@aServiceId"
```

## Doctrine storage:

```yaml
payum:
    storages:
        A\Model\Class: { doctrine: orm } # orm or mongodb for now
```

## Filesystem storage:

```yaml
payum:
    storages:
        A\Model\Class:
            filesystem:
                storage_dir: 'required'
                id_property: 'required'
```

## Paypal express checkout gateway

```yaml
payum:
    gateways:
        aName:
            factory: paypal_express_checkout
            username:  'required'
            password:  'required'
            signature: 'required'
            sandbox: true
```

## Paypal pro checkout gateway

```yaml
payum:
    gateways:
        aName:
            factory: paypal_pro_checkout
            username:  'required'
            password:  'required'
            partner: 'required'
            vendor: 'required'
            tender: C
            trxtype: S
            sandbox: true
```

## Stripe.Js

```yaml
payum:
    gateways:
        aName:
            factory: stripe_js
            publishable_key: 'required'
            secret_key: 'required'
```

## Stripe Checkout

```yaml
payum:
    gateways:
        aName:
            factory: stripe_checkout
            publishable_key: 'required'
            secret_key: 'required'
```

## Authorize.Net AIM gateway

```yaml
payum:
    gateways:
        aName:
            factory: authorize_net_aim
            login_id: 'required'
            transaction_key: 'required'
            sandbox: true
```

## Be2Bill gateway

```yml
payum:
    gateways:
        aName:
            factory: be2bill
            identifier: 'required'
            password: 'required'
            sandbox: true
```

## Be2Bill onsite gateway

```yml
payum:
    gateways:
        aName:
            factory: be2bill_onsite
            identifier: 'required'
            password: 'required'
            sandbox: true
```

## Payex gateway

```yml
payum:
    gateways:
        aName:
            factory: payex
            encryption_key: 'required'
            account_number: 'required'
            sandbox: true
```

## Klarna checkout gateway

```yml
payum:
    gateways:
        aName:
            factory: klarna_checkout
            secret:  'required'
            merchant_id: 'required'
            sandbox: true
```

## Klarna invoice gateway

```yml
payum:
    gateways:
        aName:
            factory: klarna_invoice
            secret: 'required'
            eid: 'required'
            country: SE
            language: SV
            currency: SEK
            sandbox: true
```

## Omnipay gateway

```yml
payum:
    gateways:
        aName:
            factory: omnipay
            type: 'required'
            options:
                foo: fooOpt
                bar: barOpt
```

## Custom gateway

```yaml
payum:
    gateways:
        aName:
            factory: custom
            # if service not set an empty gateway will be created.
            service: ~
```

* [Back to index](../index.md).
