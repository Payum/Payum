<?php
namespace Payum\PaymentBundle\DependencyInjection\Factory\Payment;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Reference;
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
        $loader->load('paypal-express-checkout-nvp.xml');

        $apiDefinition = new DefinitionDecorator('payum_payment.paypal.express_checkout_nvp.api');
        $apiDefinition->replaceArgument(1, $config['api']);
        $apiId = 'payum_payment.context.'.$contextName.'.api';
        $container->setDefinition($apiId, $apiDefinition);

        $authorizeTokenDefinition = new DefinitionDecorator('payum_payment.paypal.express_checkout_nvp.action.authorize_token');
        $authorizeTokenDefinition->replaceArgument(0, new Reference($apiId));
        $container->setDefinition('payum_payment.context.'.$contextName.'.action.authorize_token', $authorizeTokenDefinition);

        $doExpressCheckoutPaymentDefinition = new DefinitionDecorator('payum_payment.paypal.express_checkout_nvp.action.do_express_checkout_payment');
        $doExpressCheckoutPaymentDefinition->replaceArgument(0, new Reference($apiId));
        $container->setDefinition('payum_payment.context.'.$contextName.'.action.do_express_checkout_payment', $doExpressCheckoutPaymentDefinition);

        $getExpressCheckoutDetailsDefinition = new DefinitionDecorator('payum_payment.paypal.express_checkout_nvp.action.get_express_checkout_details');
        $getExpressCheckoutDetailsDefinition->replaceArgument(0, new Reference($apiId));
        $container->setDefinition('payum_payment.context.'.$contextName.'.action.get_express_checkout_details', $getExpressCheckoutDetailsDefinition);

        $getTransactionDetailsDefinition = new DefinitionDecorator('payum_payment.paypal.express_checkout_nvp.action.get_transaction_details');
        $getTransactionDetailsDefinition->replaceArgument(0, new Reference($apiId));
        $container->setDefinition('payum_payment.context.'.$contextName.'.action.get_transaction_details', $getTransactionDetailsDefinition);

        $setExpressCheckoutDefinition = new DefinitionDecorator('payum_payment.paypal.express_checkout_nvp.action.set_express_checkout');
        $setExpressCheckoutDefinition->replaceArgument(0, new Reference($apiId));
        $container->setDefinition('payum_payment.context.'.$contextName.'.action.set_express_checkout', $setExpressCheckoutDefinition);
        
        $paymentDefinition = new DefinitionDecorator('payum_payment.paypal.express_checkout_nvp.payment');
        $paymentDefinition->addMethodCall('addAction', new Reference('payum_payment.context.'.$contextName.'.action.authorize_token'));
        $paymentDefinition->addMethodCall('addAction', new Reference('payum_payment.context.'.$contextName.'.action.do_express_checkout_payment'));
        $paymentDefinition->addMethodCall('addAction', new Reference('payum_payment.context.'.$contextName.'.action.get_express_checkout_details'));
        $paymentDefinition->addMethodCall('addAction', new Reference('payum_payment.context.'.$contextName.'.action.get_transaction_details'));
        $paymentDefinition->addMethodCall('addAction', new Reference('payum_payment.context.'.$contextName.'.action.set_express_checkout'));
        $paymentDefinition->addMethodCall('addAction', new Reference('payum_payment.paypal.express_checkout_nvp.action.capture'));
        $paymentDefinition->addMethodCall('addAction', new Reference('payum_payment.paypal.express_checkout_nvp.action.status'));
        $paymentDefinition->addMethodCall('addAction', new Reference('payum_payment.paypal.express_checkout_nvp.action.sync'));

        $paymentId = 'payum_payment.context.'.$contextName.'.payment';
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
                ->scalarNode('username')->isRequired()->cannotBeEmpty()->end()
                ->scalarNode('password')->isRequired()->cannotBeEmpty()->end()
                ->scalarNode('signature')->isRequired()->cannotBeEmpty()->end()
                ->booleanNode('sandbox')->defaultTrue()->end()
            ->end()
        ->end();
    }
}