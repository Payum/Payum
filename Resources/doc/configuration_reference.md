# Configuration Reference

## Payum extension:

```yaml
payum:
    contexts:
        foo_payment_context:
            xxx_payment:
                # payment specific options

            storages:
                A\Model\Class:
                    # storage specific options
                Another\Model\Class:
                    # storage specific options
                
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
doctrine:
    driver: 'required' # orm only supported atm
    payment_extension:
        enabled: false
```

## Filesystem storage:

```yaml
filesystem:
    storage_dir: 'required'
    id_property: 'required'
    payment_extension:
        enabled: false
```

## Paypal express checkout payment

```yaml
paypal_express_checkout_nvp:
    api:
        client: payum.buzz.client #default
        options:
            username:  'required'
            password:  'required'
            signature: 'required'
            sandbox: true
```

## Authorize.Net AIM payment

```yaml
authorize_net_aim:
    api:
        options:
            login_id: 'required'
            transaction_key: 'required'
            sandbox: true
```

## Be2Bill payment

```yml
be2bill:
    api:
        options:
            identifier: 'required'
            password: 'required'
            sandbox: true
```

## Payex payment

```yml
payex:
    api:
        options:
            encryption_key: 'required'
            account_number: 'required'
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
    #if service not set an empty payment will be created. 
    service: ~ 
```

## Next Step

* [Back to index](index.md).