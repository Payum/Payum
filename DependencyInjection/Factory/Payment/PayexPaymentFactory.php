<?php
namespace Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Parameter;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\FileLocator;

use Payum\Exception\RuntimeException;

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

        $paymentId = parent::create($container, $contextName, $config);
        $paymentDefinition = $container->getDefinition($paymentId);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../../../Resources/config/payment'));
        $loader->load('payex.xml');

        $orderApiDefinition = new DefinitionDecorator('payum.payex.api.order.prototype');
        $orderApiDefinition->replaceArgument(1, array(
            'encryptionKey' => $config['api']['options']['encryption_key'],
            'accountNumber' => $config['api']['options']['account_number'],
            'sandbox' => $config['api']['options']['sandbox']
        ));
        $orderApiDefinition->setPublic(true);
        $orderApiId = 'payum.context.'.$contextName.'.api.order';
        $container->setDefinition($orderApiId, $orderApiDefinition);
        $paymentDefinition->addMethodCall('addApi', array(new Reference($orderApiId)));

        $agreementApiDefinition = new DefinitionDecorator('payum.payex.api.agreement.prototype');
        $agreementApiDefinition->replaceArgument(1, array(
            'encryptionKey' => $config['api']['options']['encryption_key'],
            'accountNumber' => $config['api']['options']['account_number'],
            'sandbox' => $config['api']['options']['sandbox']
        ));
        $agreementApiDefinition->setPublic(true);
        $agreementApiId = 'payum.context.'.$contextName.'.api.agreement';
        $container->setDefinition($agreementApiId, $agreementApiDefinition);
        $paymentDefinition->addMethodCall('addApi', array(new Reference($agreementApiId)));

        $initializeOrderActionDefinition = new DefinitionDecorator('payum.payex.action.api.initialize_order');
        $initializeOrderActionId = 'payum.context.'.$contextName.'.action.api.initialize_order';
        $container->setDefinition($initializeOrderActionId, $initializeOrderActionDefinition);
        $paymentDefinition->addMethodCall('addAction', array(new Reference($initializeOrderActionId)));

        $completeOrderActionDefinition = new DefinitionDecorator('payum.payex.action.api.complete_order');
        $completeOrderActionId = 'payum.context.'.$contextName.'.action.api.complete_order';
        $container->setDefinition($completeOrderActionId, $completeOrderActionDefinition);
        $paymentDefinition->addMethodCall('addAction', array(new Reference($completeOrderActionId)));

        $createAgreementActionDefinition = new DefinitionDecorator('payum.payex.action.api.create_agreement');
        $createAgreementActionId = 'payum.context.'.$contextName.'.action.api.create_agreement';
        $container->setDefinition($createAgreementActionId, $createAgreementActionDefinition);
        $paymentDefinition->addMethodCall('addAction', array(new Reference($createAgreementActionId)));

        $deleteAgreementActionDefinition = new DefinitionDecorator('payum.payex.action.api.delete_agreement');
        $deleteAgreementActionId = 'payum.context.'.$contextName.'.action.api.delete_agreement';
        $container->setDefinition($deleteAgreementActionId, $deleteAgreementActionDefinition);
        $paymentDefinition->addMethodCall('addAction', array(new Reference($deleteAgreementActionId)));

        $checkAgreementActionDefinition = new DefinitionDecorator('payum.payex.action.api.check_agreement');
        $checkAgreementActionId = 'payum.context.'.$contextName.'.action.api.check_agreement';
        $container->setDefinition($checkAgreementActionId, $checkAgreementActionDefinition);
        $paymentDefinition->addMethodCall('addAction', array(new Reference($checkAgreementActionId)));

        $autoPayAgreementActionDefinition = new DefinitionDecorator('payum.payex.action.api.autopay_agreement');
        $autoPayAgreementActionId = 'payum.context.'.$contextName.'.action.api.autopay_agreement';
        $container->setDefinition($autoPayAgreementActionId, $autoPayAgreementActionDefinition);
        $paymentDefinition->addMethodCall('addAction', array(new Reference($autoPayAgreementActionId)));
        
        $captureActionDefinition = new DefinitionDecorator('payum.payex.action.capture');
        $captureActionId = 'payum.context.'.$contextName.'.action.capture';
        $container->setDefinition($captureActionId, $captureActionDefinition);
        $paymentDefinition->addMethodCall('addAction', array(new Reference($captureActionId)));

        $paymentDetailsStatusActionDefinition = new DefinitionDecorator('payum.payex.action.payment_details_status');
        $paymentDetailsStatusActionActionId = 'payum.context.'.$contextName.'.action.payment_details_status';
        $container->setDefinition($paymentDetailsStatusActionActionId, $paymentDetailsStatusActionDefinition);
        $paymentDefinition->addMethodCall('addAction', array(new Reference($paymentDetailsStatusActionActionId)));

        $agreementDetailsStatusActionDefinition = new DefinitionDecorator('payum.payex.action.agreement_details_status');
        $agreementDetailsStatusActionActionId = 'payum.context.'.$contextName.'.action.agreement_details_status';
        $container->setDefinition($agreementDetailsStatusActionActionId, $agreementDetailsStatusActionDefinition);
        $paymentDefinition->addMethodCall('addAction', array(new Reference($agreementDetailsStatusActionActionId)));

        return $paymentId;
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
            ->arrayNode('api')->isRequired()->children()
                ->arrayNode('options')->isRequired()->children()
                    ->scalarNode('encryption_key')->isRequired()->cannotBeEmpty()->end()
                    ->scalarNode('account_number')->isRequired()->cannotBeEmpty()->end()
                    ->booleanNode('sandbox')->defaultTrue()->end()
                ->end()
            ->end()
        ->end();
    }
}