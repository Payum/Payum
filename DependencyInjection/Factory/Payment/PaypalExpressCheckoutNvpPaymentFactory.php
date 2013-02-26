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
        if (false == class_exists('Payum\Paypal\ExpressCheckout\Nvp\PaymentFactory')) {
            throw new RuntimeException('Cannot find paypal express checkout payment class. Have you installed payum/paypal-express-checkout-nvp package?');
        }

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../../../Resources/config/payment'));
        $loader->load('paypal_express_checkout_nvp.xml');

        $apiDefinition = new DefinitionDecorator('payum.paypal.express_checkout_nvp.api');
        $apiDefinition->replaceArgument(0, new Reference($config['api']['client']));
        $apiDefinition->replaceArgument(1, $config['api']['options']);
        $apiId = 'payum.context.'.$contextName.'.api';
        $container->setDefinition($apiId, $apiDefinition);

        $paymentDefinition = new Definition();
        $paymentDefinition->setClass(new Parameter('payum.paypal.express_checkout_nvp.payment.class'));
        $paymentDefinition->setPublic('false');
        $paymentDefinition->addMethodCall('addApi', array(new Reference($apiId)));
        $paymentId = 'payum.context.'.$contextName.'.payment';
        $container->setDefinition($paymentId, $paymentDefinition);

        $authorizeTokenDefinition = new DefinitionDecorator('payum.paypal.express_checkout_nvp.action.authorize_token');
        $authorizeTokenId = 'payum.context.'.$contextName.'.action.authorize_token';
        $container->setDefinition($authorizeTokenId, $authorizeTokenDefinition);
        $paymentDefinition->addMethodCall('addAction', array(new Reference($authorizeTokenId)));
        
        $doExpressCheckoutPaymentDefinition = new DefinitionDecorator('payum.paypal.express_checkout_nvp.action.do_express_checkout_payment');
        $doExpressCheckoutPaymentId = 'payum.context.'.$contextName.'.action.do_express_checkout_payment';
        $container->setDefinition($doExpressCheckoutPaymentId, $doExpressCheckoutPaymentDefinition);
        $paymentDefinition->addMethodCall('addAction', array(new Reference($doExpressCheckoutPaymentId)));

        $getExpressCheckoutDetailsDefinition = new DefinitionDecorator('payum.paypal.express_checkout_nvp.action.get_express_checkout_details');
        $getExpressCheckoutDetailsId = 'payum.context.'.$contextName.'.action.get_express_checkout_details';
        $container->setDefinition($getExpressCheckoutDetailsId, $getExpressCheckoutDetailsDefinition);
        $paymentDefinition->addMethodCall('addAction', array(new Reference($getExpressCheckoutDetailsId)));

        $getTransactionDetailsDefinition = new DefinitionDecorator('payum.paypal.express_checkout_nvp.action.get_transaction_details');
        $getTransactionDetailsId = 'payum.context.'.$contextName.'.action.get_transaction_details';
        $container->setDefinition($getTransactionDetailsId, $getTransactionDetailsDefinition);
        $paymentDefinition->addMethodCall('addAction', array(new Reference($getTransactionDetailsId)));

        $setExpressCheckoutDefinition = new DefinitionDecorator('payum.paypal.express_checkout_nvp.action.set_express_checkout');
        $setExpressCheckoutId = 'payum.context.' . $contextName . '.action.set_express_checkout';
        $container->setDefinition($setExpressCheckoutId, $setExpressCheckoutDefinition);
        $paymentDefinition->addMethodCall('addAction', array(new Reference($setExpressCheckoutId)));

        $captureDefinition = new DefinitionDecorator('payum.paypal.express_checkout_nvp.action.capture');
        $captureId = 'payum.context.' . $contextName . '.action.capture';
        $container->setDefinition($captureId, $captureDefinition);
        $paymentDefinition->addMethodCall('addAction', array(new Reference($captureId)));

        $statusDefinition = new DefinitionDecorator('payum.paypal.express_checkout_nvp.action.status');
        $statusId = 'payum.context.' . $contextName . '.action.status';
        $container->setDefinition($statusId, $statusDefinition);
        $paymentDefinition->addMethodCall('addAction', array(new Reference($statusId)));

        $syncDefinition = new DefinitionDecorator('payum.paypal.express_checkout_nvp.action.sync');
        $syncId = 'payum.context.' . $contextName . '.action.sync';
        $container->setDefinition($syncId, $syncDefinition);
        $paymentDefinition->addMethodCall('addAction', array(new Reference($syncId)));

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