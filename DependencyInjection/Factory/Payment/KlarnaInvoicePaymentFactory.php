<?php
namespace Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment;

use Payum\Core\Exception\RuntimeException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

class KlarnaInvoicePaymentFactory extends AbstractPaymentFactory
{
    /**
     * {@inheritDoc}
     */
    public function create(ContainerBuilder $container, $paymentName, array $config)
    {
        if (false == class_exists('Payum\Klarna\Invoice\PaymentFactory')) {
            throw new RuntimeException('Cannot find Klarna Invoice payment factory class. Have you installed payum/klarna-invoice package?');
        }

        //autoload Klarna
        \Klarna::BETA;

        return parent::create($container, $paymentName, $config);
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
    protected function getPayumPaymentFactoryClass()
    {
        return 'Payum\Klarna\Invoice\PaymentFactory';
    }

    /**
     * {@inheritDoc}
     */
    protected function getComposerPackage()
    {
        return 'payum/klarna-invoice';
    }
}