<?php
namespace Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment;

use Payum\Core\Exception\RuntimeException;
use Payum\Paypal\ProCheckout\Nvp\Api;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

/**
 * @author Ton Sharp <Forma-PRO@66ton99.org.ua>
 */
class PaypalProCheckoutNvpPaymentFactory extends AbstractPaymentFactory
{
    /**
     * {@inheritdoc}
     */
    public function create(ContainerBuilder $container, $contextName, array $config)
    {
        if (false == class_exists('Payum\Paypal\ProCheckout\Nvp\PaymentFactory')) {
            throw new RuntimeException('Cannot find paypal pro checkout payment class. Have you installed payum/paypal-pro-checkout-nvp package?');
        }

        return parent::create($container, $contextName, $config);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'paypal_pro_checkout_nvp';
    }

    /**
     * {@inheritdoc}
     */
    public function addConfiguration(ArrayNodeDefinition $builder)
    {
        parent::addConfiguration($builder);
        
        $builder->children()
            ->scalarNode('username')->isRequired()->cannotBeEmpty()->end()
            ->scalarNode('password')->isRequired()->cannotBeEmpty()->end()
            ->scalarNode('partner')->isRequired()->cannotBeEmpty()->end()
            ->scalarNode('vendor')->isRequired()->cannotBeEmpty()->end()
            ->scalarNode('tender')->defaultValue('C')->cannotBeEmpty()->end()
            ->booleanNode('sandbox')->defaultTrue()->end()
        ->end();
    }

    /**
     * {@inheritDoc}
     */
    protected function createPaymentDefinition(ContainerBuilder $container, $contextName, array $config)
    {
        $factoryId = 'payum.paypal.pro_checkout.factory';
        $container->setDefinition($factoryId, new Definition('Payum\Paypal\ProCheckout\Nvp\PaymentFactory', array(
            new Reference('payum.payment_factory'),
        )));

        $payment = new Definition('Payum\Core\Payment', array($config));
        $payment->setFactoryService($factoryId);
        $payment->setFactoryMethod('create');

        return $payment;
    }
}