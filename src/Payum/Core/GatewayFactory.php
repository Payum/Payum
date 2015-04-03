<?php
namespace Payum\Core;

use Payum\Core\Bridge\Spl\ArrayObject;

class GatewayFactory extends PaymentFactory implements GatewayFactoryInterface
{
    /**
     * @var array
     */
    protected $defaultConfig;

    /**
     * @param array $defaultConfig
     */
    public function __construct(array $defaultConfig = array())
    {
        $this->defaultConfig = $defaultConfig;
    }

    /**
     * {@inheritDoc}
     */
    public function create(array $config = array())
    {
        $config = ArrayObject::ensureArrayObject($config);
        $config->defaults($this->createConfig());

        $gateway = new Gateway();

        $this->buildActions($gateway, $config);
        $this->buildApis($gateway, $config);
        $this->buildExtensions($gateway, $config);

        return $gateway;
    }
}
