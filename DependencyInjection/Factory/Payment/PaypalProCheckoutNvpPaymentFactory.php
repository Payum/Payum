<?php
namespace Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment;

use Payum\Core\Exception\RuntimeException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\FileLocator;

/**
 * @author Ton Sharp <Forma-PRO@66ton99.org.ua>
 */
class PaypalProCheckoutNvpPaymentFactory extends AbstractPaymentFactory
{
    /**
     * {@inheritdoc}
     */
    public function create(ContainerBuilder $container, $contextName, array $config)
    {
        if (false == class_exists('Payum\Paypal\ProCheckout\Nvp\PaymentFactory')) {
            throw new RuntimeException(
              'Cannot find paypal pro checkout payment class. Have you installed payum/paypal-pro-checkout-nvp package?'
            );
        }

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../../../Resources/config/payment'));
        $loader->load('paypal_pro_checkout_nvp.xml');

        return parent::create($container, $contextName, $config);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'paypal_pro_checkout_nvp';
    }

    /**
     * {@inheritdoc}
     */
    public function addConfiguration(ArrayNodeDefinition $builder)
    {
        parent::addConfiguration($builder);
        
        $builder->children()
            ->scalarNode('username')->isRequired()->cannotBeEmpty()->end()
            ->scalarNode('password')->isRequired()->cannotBeEmpty()->end()
            ->scalarNode('partner')->isRequired()->cannotBeEmpty()->end()
            ->scalarNode('vendor')->isRequired()->cannotBeEmpty()->end()
            ->scalarNode('tender')->defaultValue('C')->cannotBeEmpty()->end()
            ->booleanNode('sandbox')->defaultTrue()->end()
        ->end();
    }

    /**
     * {@inheritDoc}
     */
    protected function addApis(Definition $paymentDefinition, ContainerBuilder $container, $contextName, array $config)
    {
        $apiDefinition = new DefinitionDecorator('payum.paypal.pro_checkout_nvp.api.prototype');
        $apiDefinition->replaceArgument(0, array(
            'username' => $config['username'],
            'password' => $config['password'],
            'partner' => $config['partner'],
            'vendor' => $config['vendor'],
            'sandbox' => $config['sandbox'],
            'tender' => isset($config['tender']) ? $config['tender'] : 'C',
        ));
        $apiDefinition->setPublic(true);
        $apiId = 'payum.context.'.$contextName.'.api';
        $container->setDefinition($apiId, $apiDefinition);
        $paymentDefinition->addMethodCall('addApi', array(new Reference($apiId)));
    }
}