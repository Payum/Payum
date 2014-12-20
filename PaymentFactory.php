<?php
namespace Payum\Bundle\PayumBundle;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\PaymentFactory as CorePaymentFactory;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class PaymentFactory extends CorePaymentFactory implements ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    public function createConfig(array $config = array())
    {
        $config = ArrayObject::ensureArrayObject($config);
        $config->defaults(array(
            'payum.template.layout' => $this->container->getParameter('payum.template.layout'),
            'payum.template.obtain_credit_card' => $this->container->getParameter('payum.template.obtain_credit_card'),

            'buzz.client' => $this->container->get('payum.buzz.client'),
            'twig.env' => $this->container->get('twig'),

            'payum.action.get_http_request' => $this->container->get('payum.action.get_http_request'),
            'payum.action.obtain_credit_card' => $this->container->get('payum.action.obtain_credit_card'),

            'payum.extension.logger' => $this->container->get('payum.extension.logger'),
        ));

        if ($this->container->getParameter('kernel.debug')) {
            $config->defaults(array(
                'payum.extension.log_executed_actions' => $this->container->get('payum.extension.log_executed_actions'),
            ));
        }

        return (array) $config;
    }
}