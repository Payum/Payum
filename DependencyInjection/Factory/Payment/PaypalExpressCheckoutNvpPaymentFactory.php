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

class PaypalExpressCheckoutNvpPaymentFactory extends AbstractPaymentFactory
{
    /**
     * {@inheritdoc}
     */
    public function create(ContainerBuilder $container, $contextName, array $config)
    {
        if (false == class_exists('Payum\Paypal\ExpressCheckout\Nvp\PaymentFactory')) {
            throw new RuntimeException('Cannot find paypal express checkout payment factory class. Have you installed payum/paypal-express-checkout-nvp package?');
        }

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../../../Resources/config/payment'));
        $loader->load('paypal_express_checkout_nvp.xml');

        return parent::create($container, $contextName, $config);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'paypal_express_checkout_nvp';
    }

    /**
     * {@inheritdoc}
     */
    public function addConfiguration(ArrayNodeDefinition $builder)
    {
        parent::addConfiguration($builder);
        
        $builder->children()
            ->arrayNode('api')->isRequired()->children()
                ->scalarNode('client')->defaultValue('payum.buzz.client')->cannotBeEmpty()->end()
                ->arrayNode('options')->isRequired()->children()
                    ->scalarNode('username')->isRequired()->cannotBeEmpty()->end()
                    ->scalarNode('password')->isRequired()->cannotBeEmpty()->end()
                    ->scalarNode('signature')->isRequired()->cannotBeEmpty()->end()
                    ->booleanNode('sandbox')->defaultTrue()->end()
                ->end()
            ->end()
        ->end();
    }

    /**
     * {@inheritDoc}
     */
    protected function addApis(Definition $paymentDefinition, ContainerBuilder $container, $contextName, array $config)
    {
        $apiDefinition = new DefinitionDecorator('payum.paypal.express_checkout_nvp.api');
        $apiDefinition->replaceArgument(0, new Reference($config['api']['client']));
        $apiDefinition->replaceArgument(1, $config['api']['options']);
        $apiDefinition->setPublic(true);
        $apiId = 'payum.context.'.$contextName.'.api';
        $container->setDefinition($apiId, $apiDefinition);
        $paymentDefinition->addMethodCall('addApi', array(new Reference($apiId)));
    }

    /**
     * {@inheritDoc}
     */
    protected function addActions(Definition $paymentDefinition, ContainerBuilder $container, $contextName, array $config)
    {
        $authorizeTokenDefinition = new DefinitionDecorator('payum.paypal.express_checkout_nvp.action.api.authorize_token');
        $authorizeTokenId = 'payum.context.'.$contextName.'.action.api.authorize_token';
        $container->setDefinition($authorizeTokenId, $authorizeTokenDefinition);
        $paymentDefinition->addMethodCall('addAction', array(new Reference($authorizeTokenId)));

        $doExpressCheckoutPaymentDefinition = new DefinitionDecorator('payum.paypal.express_checkout_nvp.action.api.do_express_checkout_payment');
        $doExpressCheckoutPaymentId = 'payum.context.'.$contextName.'.action.api.do_express_checkout_payment';
        $container->setDefinition($doExpressCheckoutPaymentId, $doExpressCheckoutPaymentDefinition);
        $paymentDefinition->addMethodCall('addAction', array(new Reference($doExpressCheckoutPaymentId)));

        $getExpressCheckoutDetailsDefinition = new DefinitionDecorator('payum.paypal.express_checkout_nvp.action.api.get_express_checkout_details');
        $getExpressCheckoutDetailsId = 'payum.context.'.$contextName.'.action.api.get_express_checkout_details';
        $container->setDefinition($getExpressCheckoutDetailsId, $getExpressCheckoutDetailsDefinition);
        $paymentDefinition->addMethodCall('addAction', array(new Reference($getExpressCheckoutDetailsId)));

        $getTransactionDetailsDefinition = new DefinitionDecorator('payum.paypal.express_checkout_nvp.action.api.get_transaction_details');
        $getTransactionDetailsId = 'payum.context.'.$contextName.'.action.api.get_transaction_details';
        $container->setDefinition($getTransactionDetailsId, $getTransactionDetailsDefinition);
        $paymentDefinition->addMethodCall('addAction', array(new Reference($getTransactionDetailsId)));

        $setExpressCheckoutDefinition = new DefinitionDecorator('payum.paypal.express_checkout_nvp.action.api.set_express_checkout');
        $setExpressCheckoutId = 'payum.context.' . $contextName . '.action.api.set_express_checkout';
        $container->setDefinition($setExpressCheckoutId, $setExpressCheckoutDefinition);
        $paymentDefinition->addMethodCall('addAction', array(new Reference($setExpressCheckoutId)));

        $createRecurringPaymentProfileDefinition = new DefinitionDecorator('payum.paypal.express_checkout_nvp.action.api.create_recurring_payment_profile');
        $createRecurringPaymentProfileId = 'payum.context.' . $contextName . '.action.api.create_recurring_payment_profile';
        $container->setDefinition($createRecurringPaymentProfileId, $createRecurringPaymentProfileDefinition);
        $paymentDefinition->addMethodCall('addAction', array(new Reference($createRecurringPaymentProfileId)));

        $getRecurringPaymentsProfileDetailsDefinition = new DefinitionDecorator('payum.paypal.express_checkout_nvp.action.api.get_recurring_payments_profile_details');
        $getRecurringPaymentsProfileDetailsId = 'payum.context.' . $contextName . '.action.api.get_recurring_payments_profile_details';
        $container->setDefinition($getRecurringPaymentsProfileDetailsId, $getRecurringPaymentsProfileDetailsDefinition);
        $paymentDefinition->addMethodCall('addAction', array(new Reference($getRecurringPaymentsProfileDetailsId)));

        $manageRecurringPaymentsProfileStatusDefinition = new DefinitionDecorator('payum.paypal.express_checkout_nvp.action.api.manage_recurring_payments_profile_status');
        $manageRecurringPaymentsProfileStatusId = 'payum.context.' . $contextName . '.action.api.manage_recurring_payments_profile_status';
        $container->setDefinition($manageRecurringPaymentsProfileStatusId, $manageRecurringPaymentsProfileStatusDefinition);
        $paymentDefinition->addMethodCall('addAction', array(new Reference($manageRecurringPaymentsProfileStatusId)));

        $captureDefinition = new DefinitionDecorator('payum.paypal.express_checkout_nvp.action.capture');
        $captureId = 'payum.context.' . $contextName . '.action.capture';
        $container->setDefinition($captureId, $captureDefinition);
        $paymentDefinition->addMethodCall('addAction', array(new Reference($captureId)));

        $paymentDetailsStatusDefinition = new DefinitionDecorator('payum.paypal.express_checkout_nvp.action.payment_details_status');
        $paymentDetailsStatusId = 'payum.context.' . $contextName . '.action.payment_details_status';
        $container->setDefinition($paymentDetailsStatusId, $paymentDetailsStatusDefinition);
        $paymentDefinition->addMethodCall('addAction', array(new Reference($paymentDetailsStatusId)));

        $syncDefinition = new DefinitionDecorator('payum.paypal.express_checkout_nvp.action.payment_details_sync');
        $syncId = 'payum.context.' . $contextName . '.action.payment_details_sync';
        $container->setDefinition($syncId, $syncDefinition);
        $paymentDefinition->addMethodCall('addAction', array(new Reference($syncId)));

        $recurringPaymentDetailsStatusDefinition = new DefinitionDecorator('payum.paypal.express_checkout_nvp.action.recurring_payment_details_status');
        $recurringPaymentDetailsStatusId = 'payum.context.' . $contextName . '.action.recurring_payment_details_status';
        $container->setDefinition($recurringPaymentDetailsStatusId, $recurringPaymentDetailsStatusDefinition);
        $paymentDefinition->addMethodCall('addAction', array(new Reference($recurringPaymentDetailsStatusId)));

        $recurringPaymentDetailsSyncDefinition = new DefinitionDecorator('payum.paypal.express_checkout_nvp.action.recurring_payment_details_sync');
        $recurringPaymentDetailsSyncsId = 'payum.context.' . $contextName . '.action.recurring_payment_details_sync';
        $container->setDefinition($recurringPaymentDetailsSyncsId, $recurringPaymentDetailsSyncDefinition);
        $paymentDefinition->addMethodCall('addAction', array(new Reference($recurringPaymentDetailsSyncsId)));
    }
}