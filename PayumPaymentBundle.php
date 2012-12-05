<?php
namespace Payum\PaymentBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use Payum\PaymentBundle\DependencyInjection\Factory\Payment\PaypalExpressCheckoutNvpPaymentFactory;
use Payum\PaymentBundle\DependencyInjection\Factory\Storage\DoctrineStorageFactory;
use Payum\PaymentBundle\DependencyInjection\Factory\Storage\FilesystemStorageFactory;

class PayumPaymentBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        /** @var $extension \Payum\PaymentBundle\DependencyInjection\PayumPaymentExtension */
        $extension = $container->getExtension('payum_payment');
        
        $extension->addPaymentFactory(new PaypalExpressCheckoutNvpPaymentFactory());

        $extension->addStorageFactory(new FilesystemStorageFactory());
        $extension->addStorageFactory(new DoctrineStorageFactory());
    }
}