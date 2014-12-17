<?php
namespace Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment;

use Payum\Core\Exception\RuntimeException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

class AuthorizeNetAimPaymentFactory extends AbstractPaymentFactory
{
    /**
     * {@inheritDoc}
     */
    public function create(ContainerBuilder $container, $contextName, array $config)
    {
        if (false == class_exists('Payum\AuthorizeNet\Aim\PaymentFactory')) {
            throw new RuntimeException('Cannot find Authorize.net payment factory class. Have you installed payum/authorize-net-aim package?');
        }

        return parent::create($container, $contextName, $config);
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'authorize_net_aim';
    }

    /**
     * {@inheritDoc}
     */
    public function addConfiguration(ArrayNodeDefinition $builder)
    {
        parent::addConfiguration($builder);
        
        $builder->children()
            ->scalarNode('login_id')->isRequired()->cannotBeEmpty()->end()
            ->scalarNode('transaction_key')->isRequired()->cannotBeEmpty()->end()
            ->booleanNode('sandbox')->defaultTrue()->end()
        ->end();
    }

    /**
     * {@inheritDoc}
     */
    protected function createPaymentDefinition(ContainerBuilder $container, $contextName, array $config)
    {
        $api = new Definition('Payum\AuthorizeNet\Aim\Bridge\AuthorizeNet\AuthorizeNetAIM', array(
            $config['login_id'],
            $config['transaction_key']
        ));
        $api->addMethodCall('setSandbox', array($config['sandbox']));
        $container->setDefinition('payum.context.'.$contextName.'.api', $api);

        $factoryId = 'payum.authorize_net_aim.factory';
        $container->setDefinition($factoryId, new Definition('Payum\AuthorizeNet\Aim\PaymentFactory'));

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