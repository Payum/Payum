# payum-sofortueberweisung
Payum extension to provide a Sofort (Sofortüberweisung) gateway

## Symfony2 Bridge
### Register factory inside your bundle

```php
<?php
namespace Acme\Bundle\DemoBundle;

use Invit\PayumSofortueberweisung\Bridge\SymfonySofortueberweisungGatewayFactory;

class AcmeDemoBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        
        $extension = $container->getExtension('payum');
        $extension->addGatewayFactory(new SymfonySofortueberweisungGatewayFactory());
    }
}

```
### Configure sofort gateway

```yaml
payum:
    gateways:
        sofort_xyz:
            sofort:
                config_key: 'xxx:yyy:zzzzzzzz'
                abort_url:  'https://www.nicewebshop.ch/get_me_back_there'
```
