# Configuration Reference

## Payum extension:

```yaml
payum:
    security:
        token_storage:
            A\Model\TokenClass:
                # storage specific options

    storages:
        A\Model\Class:
            payment:
                all: true 
                factories: []
                contexts: []

            # storage specific options
        Another\Model\Class:
            payment:
                all: true 
                factories: []
                contexts: []

            # storage specific options

    contexts:
        foo_payment_context:
            xxx_payment:
                # payment specific options
                
        bar_payment_context:
            xxx_payment:
                # payment specific options
                
                actions:
                    - action.foo
                    - action.bar
                   
                apis:
                    - api.foo
                    - api.bar
                
                extensions:
                    - extension.foo
                    - extension.bar
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

## Paypal express checkout payment

```yaml
payum:
    contexts:
        aName:
            paypal_express_checkout_nvp:
                username:  'required'
                password:  'required'
                signature: 'required'
                sandbox: true
```

## Paypal pro checkout payment

```yaml
payum:
    contexts:
        aName:
            paypal_express_checkout_nvp:
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
    contexts:
        aName:
            stripe_js:
                publishable_key: 'required'
                secret_key: 'required'
```

## Stripe Checkout

```yaml
payum:
    contexts:
        aName:
            stripe_checkout:
                publishable_key: 'required'
                secret_key: 'required'
```

## Authorize.Net AIM payment

```yaml
payum:
    contexts:
        aName:
            login_id: 'required'
            transaction_key: 'required'
            sandbox: true
```

## Be2Bill payment

```yml
payum:
    contexts:
        aName:
            be2bill:
                identifier: 'required'
                password: 'required'
                sandbox: true
```

## Be2Bill onsite payment

```yml
payum:
    contexts:
        aName:
            be2bill_onsite:
                identifier: 'required'
                password: 'required'
                sandbox: true
```

## Payex payment

```yml
payum:
    contexts:
        aName:
            payex:
                encryption_key: 'required'
                account_number: 'required'
                sandbox: true
```

## Klarna checkout payment

```yml
payum:
    contexts:
        aName:
            klarna_checkout:
                secret:  'required'
                merchant_id: 'required'
                sandbox: true
```

## Klarna invoice payment

```yml
payum:
    contexts:
        aName:
            klarna_invoice:
                secret: 'required'
                eid: 'required'
                country: SE
                language: SV
                currency: SEK
                sandbox: true
```

## Omnipay payment

```yml
payum:
    contexts:
        aName:
            omnipay:
                type: 'required'
                options:
                    foo: fooOpt
                    bar: barOpt
```

## Custom payment

```yaml
payum:
    contexts:
        aName:
            custom:
                # if service not set an empty payment will be created.
                service: ~ 
```

## Next Step

* [Back to index](index.md).