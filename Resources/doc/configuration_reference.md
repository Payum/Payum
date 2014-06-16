# Configuration Reference

## Payum extension:

```yaml
payum:
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
    security:
        token_storage:
            A\Model\TokenClass:
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
doctrine: 'driver required'

# or 
doctrine:
    driver: 'required' # orm mongodb only supported atm
```

## Filesystem storage:

```yaml
filesystem:
    storage_dir: 'required'
    id_property: 'required'
```

## Paypal express checkout payment

```yaml
paypal_express_checkout_nvp:
    username:  'required'
    password:  'required'
    signature: 'required'
    sandbox: true
```

## Paypal pro checkout payment

```yaml
paypal_express_checkout_nvp:
    username:  'required'
    password:  'required'
    partner: 'required'
    vendor: 'required'
    tender: C
    trxtype: S
    sandbox: true
```

## Authorize.Net AIM payment

```yaml
authorize_net_aim:
    login_id: 'required'
    transaction_key: 'required'
    sandbox: true
```

## Be2Bill payment

```yml
be2bill:
    identifier: 'required'
    password: 'required'
    sandbox: true
```

## Be2Bill onsite payment

```yml
be2bill_onsite:
    identifier: 'required'
    password: 'required'
    sandbox: true
```

## Payex payment

```yml
payex:
    encryption_key: 'required'
    account_number: 'required'
    sandbox: true
```

## Klarna checkout payment

```yml
klarna_checkout:
    secret:  'required'
    merchant_id: 'required'
    sandbox: true
```

## Omnipay payment

```yml
omnipay:
    type: 'required'
    options:
        foo: fooOpt
        bar: barOpt
```

## Custom payment

```yaml
custom:
    # if service not set an empty payment will be created.
    service: ~ 
```

## Next Step

* [Back to index](index.md).