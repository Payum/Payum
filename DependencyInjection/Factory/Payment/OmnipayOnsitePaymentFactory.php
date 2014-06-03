<?php
namespace Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment;

use Omnipay\Common\GatewayFactory;
use Payum\Core\Exception\RuntimeException;
use Payum\Core\Exception\LogicException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

class OmnipayOnsitePaymentFactory extends OmnipayPaymentFactory
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'omnipay_onsite';
    }
}
