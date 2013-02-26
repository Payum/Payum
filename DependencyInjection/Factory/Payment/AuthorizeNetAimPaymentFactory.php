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

class AuthorizeNetAimPaymentFactory implements PaymentFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function create(ContainerBuilder $container, $contextName, array $config)
    {
        if (false == class_exists('Payum\AuthorizeNet\Aim\PaymentFactory')) {
            throw new RuntimeException('Cannot find Authorize.net payment class. Have you installed payum/authorize-net-aim package?');
        }

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../../../Resources/config/payment'));
        $loader->load('authorize_net_aim.xml');

        $apiDefinition = new DefinitionDecorator('payum.authorize_net_aim.api');
        $apiDefinition->replaceArgument(0, $config['api']['options']['login_id']);
        $apiDefinition->replaceArgument(1, $config['api']['options']['transaction_key']);
        $apiDefinition->addMethodCall('setSandbox', array($config['api']['options']['sandbox']));
        $apiId = 'payum.context.'.$contextName.'.api';
        $container->setDefinition($apiId, $apiDefinition);

        $paymentDefinition = new Definition();
        $paymentDefinition->setClass(new Parameter('payum.authorize_net_aim.payment.class'));
        $paymentDefinition->setPublic('false');
        $paymentDefinition->addMethodCall('addApi', array(new Reference($apiId)));
        $paymentId = 'payum.context.'.$contextName.'.payment';
        $container->setDefinition($paymentId, $paymentDefinition);
        
        $captureActionDefinition = new DefinitionDecorator('payum.authorize_net_aim.action.capture');
        $captureActionId = 'payum.context.'.$contextName.'.action.capture';
        $container->setDefinition($captureActionId, $captureActionDefinition);
        $paymentDefinition->addMethodCall('addAction', array(new Reference($captureActionId)));

        $statusActionDefinition = new DefinitionDecorator('payum.authorize_net_aim.action.status');
        $statusActionId = 'payum.context.'.$contextName.'.action.status';
        $container->setDefinition($statusActionId, $statusActionDefinition);
        $paymentDefinition->addMethodCall('addAction', array(new Reference($statusActionId)));
        
        return $paymentId;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'authorize_net_aim_payment';
    }

    /**
     * {@inheritdoc}
     */
    public function addConfiguration(ArrayNodeDefinition $builder)
    {
        $builder->children()
            ->arrayNode('api')->children()
                ->arrayNode('options')->children()
                    ->scalarNode('login_id')->isRequired()->cannotBeEmpty()->end()
                    ->scalarNode('transaction_key')->isRequired()->cannotBeEmpty()->end()
                    ->booleanNode('sandbox')->defaultTrue()->end()
                ->end()
            ->end()
        ->end();
    }
}