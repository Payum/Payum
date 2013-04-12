## Configuration Reference

### Payum extension:

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

### Doctrine storage:

```yaml
doctrine:
    driver: 'required' # orm only supported atm
    model_class: 'required'
```

### Filesystem storage:

```yaml
filesystem:
    model_class: 'required'
    storage_dir: 'required'
    id_property: 'required'
```

### Paypal express checkout payment

```yaml
paypal_express_checkout_nvp_payment:
    api:
        client: payum.buzz.client #default
        options:
            username:  'required'
            password:  'required'
            signature: 'required'
            sandbox: true
```

### Authorize.Net AIM payment

```yaml
authorize_net_aim_payment:
    api:
        options:
            login_id: 'required'
            transaction_key: 'required'
            sandbox: true
```

### Be2Bill payment

```yml
be2bill_payment:
    api:
        options:
            identifier: 'required'
            password: 'required'
            sandbox: true
```

### Omnipay payment

```yml
ominpay_payment:
    type: 'required'
    options:
        foo: fooOpt
        bar: barOpt
```