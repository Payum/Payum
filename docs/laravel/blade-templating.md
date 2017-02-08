# Payum Laravel Package. Templating

Some gateways require authorizations in one way or another. Some of these are to be included as a javascript
or iframe or anything else on your page. By default, payum solves this with twix templates. With Laravel
we are used to work with blade, and the laravel-package includes a simple way to use blade templates
with payum instead of the default twix.

## Configuration

All you have to do is change the configuration of payum on the gateway you want to apply the blade templating.
This is a example of the klarna_checkout-gateway config. The important part for changing the templateing is
`payum.action.render_template` and `payum.template.authorize`.

```php
<?php
/** @var Payum $payum */
$payum = (new PayumBuilder())
    ->addDefaultStorages()
    ->addGateway('aGateway', [
        'factory' => 'klarna_checkout'
        'merchant_id' => '',
        'secret' => '',
        'payum.action.render_template' => new \Payum\LaravelPackage\Action\RenderTemplateAction(), // Activates blade templating
        'payum.template.authorize' => 'page.klarna-checkout-authorize', // Your custom blade-template
    ])
    ->getPayum()
;
```

Back to [index](../index.md).