<?php
namespace Payum\PaymentBundle\DependencyInjection\Factory\Payment;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Parameter;
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

        $apiDefinition = new Definition();
        $apiDefinition->setClass(new Parameter('payum_payment.be2bill.api.class'));
        $apiDefinition->setPublic(false);
        $apiDefinition->setArguments(array(
            new Reference($config['api']['client']),
            $config['api']['options']
        ));
        $apiId = 'payum_payment.context.'.$contextName.'.api';
        $container->setDefinition($apiId, $apiDefinition);
        
        $captureActionDefinition = new DefinitionDecorator('payum_payment.be2bill.action.capture');
        $captureActionId = 'payum_payment.context.'.$contextName.'.action.capture';
        $container->setDefinition($captureActionId, $captureActionDefinition);

        $statusActionDefinition = new DefinitionDecorator('payum_payment.be2bill.action.capture');
        $statusActionId = 'payum_payment.context.'.$contextName.'.action.capture';
        $container->setDefinition($statusActionId, $statusActionDefinition);

        $createInstructionActionDefinition = new DefinitionDecorator($config['create_instruction_from_model_action']);
        $createInstructionActionId = 'payum_payment.context.'.$contextName.'.action.create_instruction';
        $container->setDefinition($createInstructionActionId, $createInstructionActionDefinition);

        $paymentDefinition = new Definition('payum_payment.be2bill.payment');
        $paymentDefinition->setClass(new Parameter('payum_payment.be2bill.payment.class'));
        $paymentDefinition->setPublic('false');
        $paymentDefinition->setArguments(array(new Reference($apiId)));
        $paymentDefinition->addMethodCall('addAction', array(new Reference($captureActionId)));
        $paymentDefinition->addMethodCall('addAction', array(new Reference($statusActionId)));
        $paymentDefinition->addMethodCall('addAction', array(new Reference($createInstructionActionId)));

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
                ->scalarNode('client')->defaultValue('payum_payment.buzz.client')->cannotBeEmpty()->end()
                ->arrayNode('options')->children()
                    ->scalarNode('identifier')->isRequired()->cannotBeEmpty()->end()
                    ->scalarNode('password')->isRequired()->cannotBeEmpty()->end()
                    ->booleanNode('sandbox')->defaultTrue()->end()
                ->end()
            ->end()
        ->end();
    }
}