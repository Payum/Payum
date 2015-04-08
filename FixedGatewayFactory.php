<?php
namespace Payum\Bundle\PayumBundle;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory as CoreGatewayFactory;
use Payum\Core\GatewayFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @deprecated it is a tmp fix.
 */
class FixedGatewayFactory implements GatewayFactoryInterface
{
    /**
     * @var GatewayFactoryInterface
     */
    protected $coreGatewayFactory;

    /**
     * @var array
     */
    private $defaultConfig;

    /**
     * @param array $defaultConfig
     * @param GatewayFactoryInterface $coreGatewayFactory
     */
    public function __construct(array $defaultConfig = array(), GatewayFactoryInterface $coreGatewayFactory = null)
    {
        $this->coreGatewayFactory = $coreGatewayFactory ?: new CoreGatewayFactory();
        $this->defaultConfig = $defaultConfig;
    }

    /**
     * {@inheritDoc}
     */
    public function create(array $config = array())
    {
        return $this->coreGatewayFactory->create($this->createConfig($config));
    }

    /**
     * {@inheritDoc}
     */
    public function createConfig(array $config = array())
    {
        $config = ArrayObject::ensureArrayObject($config);
        $config->defaults($this->defaultConfig);
        $config->defaults($this->coreGatewayFactory->createConfig((array)$config));

        return (array) $config;
    }
}