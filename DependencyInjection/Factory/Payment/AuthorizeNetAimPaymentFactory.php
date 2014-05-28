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

class AuthorizeNetAimPaymentFactory extends AbstractPaymentFactory
{
    /**
     * {@inheritDoc}
     */
    public function create(ContainerBuilder $container, $contextName, array $config)
    {
        if (false == class_exists('Payum\AuthorizeNet\Aim\PaymentFactory')) {
            throw new RuntimeException('Cannot find Authorize.net payment factory class. Have you installed payum/authorize-net-aim package?');
        }

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../../../Resources/config/payment'));
        $loader->load('authorize_net_aim.xml');
        
        return parent::create($container, $contextName, $config);
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'authorize_net_aim';
    }

    /**
     * {@inheritDoc}
     */
    public function addConfiguration(ArrayNodeDefinition $builder)
    {
        parent::addConfiguration($builder);
        
        $builder->children()
            ->scalarNode('login_id')->isRequired()->cannotBeEmpty()->end()
            ->scalarNode('transaction_key')->isRequired()->cannotBeEmpty()->end()
            ->booleanNode('sandbox')->defaultTrue()->end()
        ->end();
    }

    /**
     * {@inheritDoc}
     */
    protected function addApis(Definition $paymentDefinition, ContainerBuilder $container, $contextName, array $config)
    {
        $apiDefinition = new DefinitionDecorator('payum.authorize_net_aim.api.prototype');
        $apiDefinition->replaceArgument(0, $config['login_id']);
        $apiDefinition->replaceArgument(1, $config['transaction_key']);
        $apiDefinition->addMethodCall('setSandbox', array($config['sandbox']));
        $apiId = 'payum.context.'.$contextName.'.api';
        $container->setDefinition($apiId, $apiDefinition);
        $paymentDefinition->addMethodCall('addApi', array(new Reference($apiId)));
    }
}