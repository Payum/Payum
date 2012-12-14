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

class PaypalExpressCheckoutNvpPaymentFactory implements PaymentFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function create(ContainerBuilder $container, $contextName, array $config)
    {
        if (false == class_exists('Payum\Paypal\ExpressCheckout\Nvp\Payment')) {
            throw new RuntimeException('Cannot find paypal express checkout payment class. Have you installed payum/paypal-express-checkout-nvp package?');
        }

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../../../Resources/config/payment'));
        $loader->load('paypal_express_checkout_nvp.xml');

        $apiDefinition = new DefinitionDecorator('payum.paypal.express_checkout_nvp.api');
        $apiDefinition->replaceArgument(0, new Reference($config['api']['client']));
        $apiDefinition->replaceArgument(1, $config['api']['options']);
        $apiId = 'payum.context.'.$contextName.'.api';
        $container->setDefinition($apiId, $apiDefinition);

        $authorizeTokenDefinition = new DefinitionDecorator('payum.paypal.express_checkout_nvp.action.authorize_token');
        $authorizeTokenId = 'payum.context.'.$contextName.'.action.authorize_token';
        $container->setDefinition($authorizeTokenId, $authorizeTokenDefinition);

        $doExpressCheckoutPaymentDefinition = new DefinitionDecorator('payum.paypal.express_checkout_nvp.action.do_express_checkout_payment');
        $doExpressCheckoutPaymentId = 'payum.context.'.$contextName.'.action.do_express_checkout_payment';
        $container->setDefinition($doExpressCheckoutPaymentId, $doExpressCheckoutPaymentDefinition);

        $getExpressCheckoutDetailsDefinition = new DefinitionDecorator('payum.paypal.express_checkout_nvp.action.get_express_checkout_details');
        $getExpressCheckoutDetailsId = 'payum.context.'.$contextName.'.action.get_express_checkout_details';
        $container->setDefinition($getExpressCheckoutDetailsId, $getExpressCheckoutDetailsDefinition);

        $getTransactionDetailsDefinition = new DefinitionDecorator('payum.paypal.express_checkout_nvp.action.get_transaction_details');
        $getTransactionDetailsId = 'payum.context.'.$contextName.'.action.get_transaction_details';
        $container->setDefinition($getTransactionDetailsId, $getTransactionDetailsDefinition);

        $setExpressCheckoutDefinition = new DefinitionDecorator('payum.paypal.express_checkout_nvp.action.set_express_checkout');
        $setExpressCheckoutId = 'payum.context.' . $contextName . '.action.set_express_checkout';
        $container->setDefinition($setExpressCheckoutId, $setExpressCheckoutDefinition);

        $captureDefinition = new DefinitionDecorator('payum.paypal.express_checkout_nvp.action.capture');
        $captureId = 'payum.context.' . $contextName . '.action.capture';
        $container->setDefinition($captureId, $captureDefinition);

        $statusDefinition = new DefinitionDecorator('payum.paypal.express_checkout_nvp.action.status');
        $statusId = 'payum.context.' . $contextName . '.action.status';
        $container->setDefinition($statusId, $statusDefinition);

        $syncDefinition = new DefinitionDecorator('payum.paypal.express_checkout_nvp.action.sync');
        $syncId = 'payum.context.' . $contextName . '.action.sync';
        $container->setDefinition($syncId, $syncDefinition);

        $createInstructionActionDefinition = new DefinitionDecorator($config['create_instruction_from_model_action']);
        $createInstructionActionId = 'payum.context.'.$contextName.'.action.create_instruction';
        $container->setDefinition($createInstructionActionId, $createInstructionActionDefinition);

        $paymentDefinition = new Definition();
        $paymentDefinition->setClass(new Parameter('payum.paypal.express_checkout_nvp.payment.class'));
        $paymentDefinition->setPublic('false');
        $paymentDefinition->setArguments(array(new Reference($apiId)));
        $paymentDefinition->addMethodCall('addAction', array(new Reference($authorizeTokenId)));
        $paymentDefinition->addMethodCall('addAction', array(new Reference($getExpressCheckoutDetailsId)));
        $paymentDefinition->addMethodCall('addAction', array(new Reference($getTransactionDetailsId)));
        $paymentDefinition->addMethodCall('addAction', array(new Reference($doExpressCheckoutPaymentId)));
        $paymentDefinition->addMethodCall('addAction', array(new Reference($setExpressCheckoutId)));
        $paymentDefinition->addMethodCall('addAction', array(new Reference($captureId)));
        $paymentDefinition->addMethodCall('addAction', array(new Reference($statusId)));
        $paymentDefinition->addMethodCall('addAction', array(new Reference($syncId)));
        $paymentDefinition->addMethodCall('addAction', array(new Reference($createInstructionActionId)));

        $paymentId = 'payum.context.'.$contextName.'.payment';
        $container->setDefinition($paymentId, $paymentDefinition);

        return $paymentId;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'paypal_express_checkout_nvp_payment';
    }

    /**
     * {@inheritdoc}
     */
    public function addConfiguration(ArrayNodeDefinition $builder)
    {
        $builder->children()
            ->scalarNode('create_instruction_from_model_action')->isRequired()->cannotBeEmpty()->end()
            ->arrayNode('api')->children()
                ->scalarNode('client')->defaultValue('payum.buzz.client')->cannotBeEmpty()->end()
                ->arrayNode('options')->children()
                    ->scalarNode('username')->isRequired()->cannotBeEmpty()->end()
                    ->scalarNode('password')->isRequired()->cannotBeEmpty()->end()
                    ->scalarNode('signature')->isRequired()->cannotBeEmpty()->end()
                    ->booleanNode('sandbox')->defaultTrue()->end()
                ->end()
            ->end()
        ->end();
    }
}