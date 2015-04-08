<?php
namespace Payum\Bundle\PayumBundle\DependencyInjection\Factory\Gateway;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;

interface GatewayFactoryInterface
{
    /**
     * Method could be used to create a gateway service. Must return the service id.
     *
     * @param ContainerBuilder $container
     * @param string $gatewayName
     * @param array $config
     * 
     * @return string The gateway serviceId
     */
    function create(ContainerBuilder $container, $gatewayName, array $config);

    /**
     * Method could be used to load services which you need always, even if you do not create any gateways with this factory riht now.
     * It could a gateway factory service for example
     */
    function load(ContainerBuilder $container);

    /**
     * The gateway name,
     * For example paypal_express_checkout_nvp or authorize_net_aim
     * 
     * @return string
     */
    function getName();

    /**
     * @param ArrayNodeDefinition $builder
     * 
     * @return void
     */
    function addConfiguration(ArrayNodeDefinition $builder);
}