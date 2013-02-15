<?php
namespace Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Parameter;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\FileLocator;

use Payum\Exception\RuntimeException;

/**
 * @author Ton Sharp <Forma-PRO@66ton99.org.ua>
 */
class PaypalProCheckoutNvpPaymentFactory implements PaymentFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function create(ContainerBuilder $container, $contextName, array $config)
    {
        if (false == class_exists('Payum\Paypal\ProCheckout\Nvp\Payment')) {
            throw new RuntimeException(
              'Cannot find paypal pro checkout payment class. Have you installed payum/paypal-pro-checkout-nvp package?'
            );
        }

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../../../Resources/config/payment'));
        $loader->load('paypal_pro_checkout_nvp.xml');

        $apiDefinition = new DefinitionDecorator('payum.paypal.pro_checkout_nvp.api');
        $apiDefinition->replaceArgument(0, new Reference($config['api']['client']));
        $apiDefinition->replaceArgument(1, $config['api']['options']);
        $apiId = 'payum.context.'.$contextName.'.api';
        $container->setDefinition($apiId, $apiDefinition);

        $paymentDefinition = new Definition();
        $paymentDefinition->setClass(new Parameter('payum.paypal.pro_checkout_nvp.payment.class'));
        $paymentDefinition->setPublic('false');
        $paymentDefinition->setArguments(array(new Reference($apiId)));
        $paymentId = 'payum.context.'.$contextName.'.payment';
        $container->setDefinition($paymentId, $paymentDefinition);

        $captureDefinition = new DefinitionDecorator('payum.paypal.pro_checkout_nvp.action.capture');
        $captureId = 'payum.context.' . $contextName . '.action.capture';
        $container->setDefinition($captureId, $captureDefinition);
        $paymentDefinition->addMethodCall('addAction', array(new Reference($captureId)));

        $statusDefinition = new DefinitionDecorator('payum.paypal.pro_checkout_nvp.action.status');
        $statusId = 'payum.context.' . $contextName . '.action.status';
        $container->setDefinition($statusId, $statusDefinition);
        $paymentDefinition->addMethodCall('addAction', array(new Reference($statusId)));

        if ($config['create_instruction_from_model_action']) {
            $createInstructionActionDefinition = new DefinitionDecorator($config['create_instruction_from_model_action']);
            $createInstructionActionId = 'payum.context.'.$contextName.'.action.create_instruction';
            $container->setDefinition($createInstructionActionId, $createInstructionActionDefinition);
            $paymentDefinition->addMethodCall('addAction', array(new Reference($createInstructionActionId)));
        }

        return $paymentId;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'paypal_pro_checkout_nvp_payment';
    }

    /**
     * {@inheritdoc}
     */
    public function addConfiguration(ArrayNodeDefinition $builder)
    {
        $builder->children()
            ->scalarNode('create_instruction_from_model_action')->defaultNull()->end()
            ->arrayNode('api')->children()
                ->scalarNode('client')->defaultValue('payum.buzz.client')->cannotBeEmpty()->end()
                ->arrayNode('options')->children()
                    ->scalarNode('username')->isRequired()->cannotBeEmpty()->end()
                    ->scalarNode('password')->isRequired()->cannotBeEmpty()->end()
                    ->scalarNode('partner')->isRequired()->cannotBeEmpty()->end()
                    ->scalarNode('vendor')->isRequired()->cannotBeEmpty()->end()
                    ->scalarNode('tender')->defaultValue('C')->cannotBeEmpty()->end()
                    ->scalarNode('trxtype')->defaultValue('S')->cannotBeEmpty()->end()
                    ->booleanNode('sandbox')->defaultTrue()->end()
                ->end()
            ->end()
        ->end();
    }
}
