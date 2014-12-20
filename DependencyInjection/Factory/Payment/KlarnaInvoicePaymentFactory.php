<?php
namespace Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment;

use Payum\Core\Bridge\Twig\TwigFactory;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RuntimeException;
use Payum\Klarna\Checkout\Constants;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\FileLocator;

class KlarnaInvoicePaymentFactory extends AbstractPaymentFactory
{
    /**
     * {@inheritDoc}
     */
    public function create(ContainerBuilder $container, $contextName, array $config)
    {
        if (false == class_exists('Payum\Klarna\Invoice\PaymentFactory')) {
            throw new RuntimeException('Cannot find Klarna Invoice payment factory class. Have you installed payum/klarna-invoice package?');
        }

        //autoload Klarna
        \Klarna::BETA;

        return parent::create($container, $contextName, $config);
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'klarna_invoice';
    }

    /**
     * {@inheritDoc}
     */
    public function addConfiguration(ArrayNodeDefinition $builder)
    {
        parent::addConfiguration($builder);
        
        $builder->children()
            ->scalarNode('eid')->isRequired()->cannotBeEmpty()->end()
            ->scalarNode('secret')->isRequired()->cannotBeEmpty()->end()
            ->scalarNode('country')->defaultValue('SE')->cannotBeEmpty()->end()
            ->scalarNode('language')->defaultValue('SV')->cannotBeEmpty()->end()
            ->scalarNode('currency')->defaultValue('SEK')->cannotBeEmpty()->end()
            ->booleanNode('sandbox')->defaultTrue()->end()
        ->end();
    }

    /**
     * {@inheritDoc}
     */
    protected function createPaymentDefinition(ContainerBuilder $container, $contextName, array $config)
    {
        $factoryId = 'payum.klarna_invoice.factory';
        $container->setDefinition($factoryId, new Definition('Payum\Klarna\Invoice\PaymentFactory', array(
            new Reference('payum.payment_factory'),
        )));

        $payment = new Definition('Payum\Core\Payment', array($config));
        $payment->setFactoryService($factoryId);
        $payment->setFactoryMethod('create');

        return $payment;
    }
}