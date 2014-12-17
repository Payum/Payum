<?php
namespace Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment;

use Payum\Core\Exception\RuntimeException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

class PayexPaymentFactory extends AbstractPaymentFactory
{
    /**
     * {@inheritDoc}
     */
    public function create(ContainerBuilder $container, $contextName, array $config)
    {
        if (false == class_exists('Payum\Payex\PaymentFactory')) {
            throw new RuntimeException('Cannot find payex payment factory class. Have you installed payum/payex package?');
        }

        return parent::create($container, $contextName, $config);
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'payex';
    }

    /**
     * {@inheritDoc}
     */
    public function addConfiguration(ArrayNodeDefinition $builder)
    {
        parent::addConfiguration($builder);
        
        $builder->children()
            ->scalarNode('encryption_key')->isRequired()->cannotBeEmpty()->end()
            ->scalarNode('account_number')->isRequired()->cannotBeEmpty()->end()
            ->booleanNode('sandbox')->defaultTrue()->end()
        ->end();
    }

    /**
     * {@inheritDoc}
     */
    protected function createPaymentDefinition(ContainerBuilder $container, $contextName, array $config)
    {
        $orderApi = new Definition('Payum\Payex\Api\OrderApi', array($config));
        $container->setDefinition('payum.context.'.$contextName.'.api.order', $orderApi);

        $agreementApi = new Definition('Payum\Payex\Api\AgreementApi', array($config));
        $container->setDefinition('payum.context.'.$contextName.'.api.agreement', $agreementApi);

        $recurringApi = new Definition('Payum\Payex\Api\RecurringApi', array($config));
        $container->setDefinition('payum.context.'.$contextName.'.api.recurring', $recurringApi);

        $factoryId = 'payum.payex.factory';
        $container->setDefinition($factoryId, new Definition('Payum\Payex\PaymentFactory'));

        $config['buzz.client'] = new Reference('payum.buzz.client');
        $config['twig.env'] = new Reference('twig');
        $config['payum.action.get_http_request'] = new Reference('payum.action.get_http_request');
        $config['payum.action.obtain_credit_card'] = new Reference('payum.action.obtain_credit_card');
        $config['payum.extension.log_executed_actions'] = new Reference('payum.extension.log_executed_actions');
        $config['payum.extension.logger'] = new Reference('payum.extension.logger');
    
        $payment = new Definition('Payum\Core\Payment', array($config));
        $payment->setFactoryService($factoryId);
        $payment->setFactoryMethod('create');
    
        return $payment;
    }
}