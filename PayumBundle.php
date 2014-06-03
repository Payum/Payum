<?php
namespace Payum\Bundle\PayumBundle;

use Payum\Bundle\PayumBundle\DependencyInjection\Compiler\PayumActionsPass;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment\Be2BillOnsitePaymentFactory;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment\Be2BillPaymentFactory;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment\KlarnaCheckoutPaymentFactory;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment\OfflinePaymentFactory;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment\CustomPaymentFactory;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment\OmnipayOnsitePaymentFactory;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment\OmnipayPaymentFactory;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment\PaypalExpressCheckoutNvpPaymentFactory;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment\PaypalProCheckoutNvpPaymentFactory;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment\PayexPaymentFactory;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment\AuthorizeNetAimPaymentFactory;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Storage\DoctrineStorageFactory;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Storage\FilesystemStorageFactory;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class PayumBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        /** @var $extension DependencyInjection\PayumExtension */
        $extension = $container->getExtension('payum');

        $extension->addPaymentFactory(new PaypalExpressCheckoutNvpPaymentFactory);
        $extension->addPaymentFactory(new PaypalProCheckoutNvpPaymentFactory);
        $extension->addPaymentFactory(new Be2BillPaymentFactory);
        $extension->addPaymentFactory(new Be2BillOnsitePaymentFactory);
        $extension->addPaymentFactory(new AuthorizeNetAimPaymentFactory);
        $extension->addPaymentFactory(new PayexPaymentFactory);
        $extension->addPaymentFactory(new OmnipayPaymentFactory);
        $extension->addPaymentFactory(new OmnipayOnsitePaymentFactory);
        $extension->addPaymentFactory(new CustomPaymentFactory);
        $extension->addPaymentFactory(new OfflinePaymentFactory);
        $extension->addPaymentFactory(new KlarnaCheckoutPaymentFactory);

        $extension->addStorageFactory(new FilesystemStorageFactory);
        $extension->addStorageFactory(new DoctrineStorageFactory);

        $container->addCompilerPass(new PayumActionsPass);
    }
}
