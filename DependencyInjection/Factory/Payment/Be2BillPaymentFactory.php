<?php
namespace Payum\PaymentBundle\DependencyInjection\Factory\Payment;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\FileLocator;

use Payum\Exception\RuntimeException;

class Be2BillPaymentFactory implements PaymentFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function create(ContainerBuilder $container, $contextName, array $config)
    {
        if (false == class_exists('Payum\Be2Bill\Payment')) {
            throw new RuntimeException('Cannot find be2bill payment class. Have you installed payum/be2bill package?');
        }

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../../../Resources/config/payment'));
        $loader->load('be2bill.xml');

        $apiDefinition = new DefinitionDecorator('payum_payment.be2bill.api');
        $apiDefinition->replaceArgument(1, $config['api']);
        $apiId = 'payum_payment.context.'.$contextName.'.api';
        $container->setDefinition($apiId, $apiDefinition);

        $captureActionDefinition = new DefinitionDecorator('payum_payment.be2bill.action.capture');
        $captureActionDefinition->replaceArgument(0, new Reference($apiId));
        $container->setDefinition('payum_payment.context.'.$contextName.'.action.capture', $captureActionDefinition);

        
        $paymentDefinition = new DefinitionDecorator('payum_payment.be2bill.payment');
        $paymentDefinition->addMethodCall('addAction', new Reference('payum_payment.context.'.$contextName.'.action.capture'));
        $paymentDefinition->addMethodCall('addAction', new Reference('payum_payment.be2bill.action.status'));

        $paymentId = 'payum_payment.context.'.$contextName.'.payment';
        $container->setDefinition($paymentId, $paymentDefinition);

        return $paymentId;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'be2bill_payment';
    }

    /**
     * {@inheritdoc}
     */
    public function addConfiguration(ArrayNodeDefinition $builder)
    {
        $builder->children()
            ->scalarNode('create_instruction_from_model_action')->isRequired()->cannotBeEmpty()->end()
            ->arrayNode('api')->children()
                ->scalarNode('identifier')->isRequired()->cannotBeEmpty()->end()
                ->scalarNode('password')->isRequired()->cannotBeEmpty()->end()
                ->booleanNode('sandbox')->defaultTrue()->end()
            ->end()
        ->end();
    }
}