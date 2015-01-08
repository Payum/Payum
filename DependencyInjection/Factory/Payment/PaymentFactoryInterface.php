<?php
namespace Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;

interface PaymentFactoryInterface
{
    /**
     * Method could be used to create a payment service. Must return the service id.
     *
     * @param ContainerBuilder $container
     * @param string $paymentName
     * @param array $config
     * 
     * @return string The payment serviceId
     */
    function create(ContainerBuilder $container, $paymentName, array $config);

    /**
     * Method could be used to load services which you need always, even if you do not create any payments with this factory riht now.
     * It could a payment factory service for example
     */
    function load(ContainerBuilder $container);

    /**
     * The payment name, 
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