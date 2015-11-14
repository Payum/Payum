# payum-sofort
Payum extension to provide a Sofort (Sofortüberweisung) gateway

## Symfony2 Bridge
### Register factory inside your bundle

```php
<?php
namespace Acme\Bundle\DemoBundle;

use Invit\PayumSofort\Bridge\SymfonySofortGatewayFactory;

class AcmeDemoBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        
        $extension = $container->getExtension('payum');
        $extension->addGatewayFactory(new SymfonySofortGatewayFactory());
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
```
