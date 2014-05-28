<?php
namespace Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment;

use Payum\Core\Exception\RuntimeException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\FileLocator;

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

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../../../Resources/config/payment'));
        $loader->load('payex.xml');

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
    protected function addApis(Definition $paymentDefinition, ContainerBuilder $container, $contextName, array $config)
    {
        $orderApiDefinition = new DefinitionDecorator('payum.payex.api.order.prototype');
        $orderApiDefinition->replaceArgument(1, array(
            'encryptionKey' => $config['encryption_key'],
            'accountNumber' => $config['account_number'],
            'sandbox' => $config['sandbox']
        ));
        $orderApiDefinition->setPublic(true);
        $orderApiId = 'payum.context.'.$contextName.'.api.order';
        $container->setDefinition($orderApiId, $orderApiDefinition);
        $paymentDefinition->addMethodCall('addApi', array(new Reference($orderApiId)));

        $agreementApiDefinition = new DefinitionDecorator('payum.payex.api.agreement.prototype');
        $agreementApiDefinition->replaceArgument(1, array(
            'encryptionKey' => $config['encryption_key'],
            'accountNumber' => $config['account_number'],
            'sandbox' => $config['sandbox']
        ));
        $agreementApiDefinition->setPublic(true);
        $agreementApiId = 'payum.context.'.$contextName.'.api.agreement';
        $container->setDefinition($agreementApiId, $agreementApiDefinition);
        $paymentDefinition->addMethodCall('addApi', array(new Reference($agreementApiId)));

        $recurringApiDefinition = new DefinitionDecorator('payum.payex.api.recurring.prototype');
        $recurringApiDefinition->replaceArgument(1, array(
            'encryptionKey' => $config['encryption_key'],
            'accountNumber' => $config['account_number'],
            'sandbox' => $config['sandbox']
        ));
        $recurringApiDefinition->setPublic(true);
        $recurringApiId = 'payum.context.'.$contextName.'.api.recurring';
        $container->setDefinition($recurringApiId, $recurringApiDefinition);
        $paymentDefinition->addMethodCall('addApi', array(new Reference($recurringApiId)));
    }
}