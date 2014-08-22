<?php
namespace Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment;

use Payum\Core\Bridge\Twig\TwigFactory;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RuntimeException;
use Payum\Klarna\Checkout\Constants;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\FileLocator;

class KlarnaInvoicePaymentFactory extends AbstractPaymentFactory
{
    /**
     * {@inheritDoc}
     */
    public function create(ContainerBuilder $container, $contextName, array $config)
    {
        if (false == class_exists('Payum\Klarna\Invoice\PaymentFactory')) {
            throw new RuntimeException('Cannot find Klarna Invoice payment factory class. Have you installed payum/klarna-invoice package?');
        }

        //autoload Klarna
        \Klarna::BETA;

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../../../Resources/config/payment'));
        $loader->load('klarna_invoice.xml');

        return parent::create($container, $contextName, $config);
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'klarna_invoice';
    }

    /**
     * {@inheritDoc}
     */
    public function addConfiguration(ArrayNodeDefinition $builder)
    {
        parent::addConfiguration($builder);
        
        $builder->children()
            ->scalarNode('eid')->isRequired()->cannotBeEmpty()->end()
            ->scalarNode('secret')->isRequired()->cannotBeEmpty()->end()
            ->scalarNode('country')->defaultValue('SE')->cannotBeEmpty()->end()
            ->scalarNode('language')->defaultValue('SV')->cannotBeEmpty()->end()
            ->scalarNode('currency')->defaultValue('SEK')->cannotBeEmpty()->end()
            ->booleanNode('sandbox')->defaultTrue()->end()
        ->end();
    }

    /**
     * {@inheritDoc}
     */
    protected function addApis(Definition $paymentDefinition, ContainerBuilder $container, $contextName, array $config)
    {
        if (null === $country = \KlarnaCountry::fromCode($config['country'])) {
            throw new LogicException(sprintf('Given %s country code is not valid. Klarna cannot recognize it.', $config['country']));
        }

        if (null === $language = \KlarnaLanguage::fromCode($config['language'])) {
            throw new LogicException(sprintf('Given %s language code is not valid. Klarna cannot recognize it.', $config['language']));
        }

        if (null === $currency = \KlarnaCurrency::fromCode($config['currency'])) {
            throw new LogicException(sprintf('Given %s currency code is not valid. Klarna cannot recognize it.', $config['currency']));
        }

        $klarnaConfig = new DefinitionDecorator('payum.klarna.invoice.config.prototype');
        $klarnaConfig->setProperty('eid', $config['eid']);
        $klarnaConfig->setProperty('secret', $config['secret']);
        $klarnaConfig->setProperty('country', $country);
        $klarnaConfig->setProperty('language', $language);
        $klarnaConfig->setProperty('currency', $currency);
        $klarnaConfig->setProperty('mode', $config['sandbox'] ? \Klarna::BETA : \Klarna::LIVE);

        $klarnaConfig->setPublic(true);
        $klarnaConfigId = 'payum.context.'.$contextName.'.config';

        $container->setDefinition($klarnaConfigId, $klarnaConfig);
        $paymentDefinition->addMethodCall('addApi', array(new Reference($klarnaConfigId)));
    }
}