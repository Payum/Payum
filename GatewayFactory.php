<?php
namespace Payum\Bundle\PayumBundle;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\CoreGatewayFactory;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class GatewayFactory extends CoreGatewayFactory implements ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var array
     */
    private $actionsTags;

    /**
     * @var array
     */
    private $extensionsTags;

    /**
     * @var array
     */
    private $apisTags;

    /**
     * @param array $actionsTags
     * @param array $extensionsTags
     * @param array $apisTags
     */
    public function __construct(array $actionsTags, array $extensionsTags, array $apisTags)
    {
        $this->actionsTags = $actionsTags;
        $this->extensionsTags = $extensionsTags;
        $this->apisTags = $apisTags;

        parent::__construct([]);
    }

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    public function createConfig(array $config = array())
    {
        $config = ArrayObject::ensureArrayObject($config);
        $config->defaults(parent::createConfig((array) $config));

        $prependActions = array();
        foreach ($this->actionsTags as $id => $tagAttributes) {
            foreach ($tagAttributes as $attributes) {
                $name = isset($attributes['alias']) ? $attributes['alias'] : $id;

                if (isset($attributes['all']) && $attributes['all']) {
                    $config["payum.action.$name"] = $this->container->get($id);
                }

                if (
                    isset($attributes['factory']) &&
                    isset($config['payum.factory_name']) &&
                    $config['payum.factory_name'] === $attributes['factory']
                ) {
                    $config["payum.action.$name"] = $this->container->get($id);
                }

                if (
                    isset($attributes['gateway']) &&
                    isset($config['payum.gateway_name']) &&
                    $config['payum.gateway_name'] === $attributes['gateway']
                ) {
                    $config["payum.action.$name"] = $this->container->get($id);
                }

                if (isset($attributes['prepend'])) {
                    $prependActions[] = "payum.action.$name";
                }
            }
        }
        $config['payum.prepend_actions'] = $prependActions;


        $prependExtensions = array();
        foreach ($this->extensionsTags as $id => $tagAttributes) {
            foreach ($tagAttributes as $attributes) {
                $name = isset($attributes['alias']) ? $attributes['alias'] : $id;

                if (isset($attributes['all']) && $attributes['all']) {
                    $config["payum.extension.$name"] = $this->container->get($id);
                }

                if (
                    isset($attributes['factory']) &&
                    isset($config['payum.factory_name']) &&
                    $config['payum.factory_name'] === $attributes['factory']
                ) {
                    $config["payum.extension.$name"] = $this->container->get($id);
                }

                if (
                    isset($attributes['gateway']) &&
                    isset($config['payum.gateway_name']) &&
                    $config['payum.gateway_name'] === $attributes['gateway']
                ) {
                    $config["payum.extension.$name"] = $this->container->get($id);
                }

                if (isset($attributes['prepend'])) {
                    $prependExtensions[] = "payum.extension.$name";
                }
            }
        }
        $config['payum.prepend_extensions'] = $prependExtensions;

        $prependApis = array();
        foreach ($this->apisTags as $id => $tagAttributes) {
            foreach ($tagAttributes as $attributes) {
                $name = isset($attributes['alias']) ? $attributes['alias'] : $id;

                if (isset($attributes['all']) && $attributes['all']) {
                    $config["payum.api.$name"] = $this->container->get($id);
                }

                if (
                    isset($attributes['factory']) &&
                    isset($config['payum.factory_name']) &&
                    $config['payum.factory_name'] === $attributes['factory']
                ) {
                    $config["payum.api.$name"] = $this->container->get($id);
                }

                if (
                    isset($attributes['gateway']) &&
                    isset($config['payum.gateway_name']) &&
                    $config['payum.gateway_name'] === $attributes['gateway']
                ) {
                    $config["payum.api.$name"] = $this->container->get($id);
                }

                if (isset($attributes['prepend'])) {
                    $prependApis[] = "payum.api.$name";
                }
            }
        }
        $config['payum.prepend_apis'] = $prependApis;

        return (array) $config;
    }
}