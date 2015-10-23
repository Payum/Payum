<?php
namespace Payum\Bundle\PayumBundle;

use Payum\Bundle\PayumBundle\DependencyInjection\Compiler\BuildGatewayFactoryPass;
use Payum\Bundle\PayumBundle\DependencyInjection\Compiler\BuildRegistryPass;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Gateway\Be2BillOffsiteGatewayFactory;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Gateway\Be2BillDirectGatewayFactory;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Gateway\KlarnaCheckoutGatewayFactory;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Gateway\KlarnaInvoiceGatewayFactory;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Gateway\OfflineGatewayFactory;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Gateway\CustomGatewayFactory;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Gateway\OmnipayGatewayFactory;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Gateway\OmnipayOffsiteGatewayFactory;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Gateway\OmnipayDirectGatewayFactory;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Gateway\PaypalExpressCheckoutNvpGatewayFactory;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Gateway\PaypalProCheckoutNvpGatewayFactory;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Gateway\PayexGatewayFactory;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Gateway\AuthorizeNetAimGatewayFactory;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Gateway\StripeCheckoutGatewayFactory;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Gateway\StripeJsGatewayFactory;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Storage\CustomStorageFactory;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Storage\Propel1StorageFactory;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Storage\Propel2StorageFactory;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Storage\DoctrineStorageFactory;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Storage\FilesystemStorageFactory;
use Payum\Bundle\PayumBundle\DependencyInjection\PayumExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class PayumBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        /** @var $extension PayumExtension */
        $extension = $container->getExtension('payum');

        $extension->addGatewayFactory(new PaypalExpressCheckoutNvpGatewayFactory);
        $extension->addGatewayFactory(new PaypalProCheckoutNvpGatewayFactory);
        $extension->addGatewayFactory(new Be2BillDirectGatewayFactory);
        $extension->addGatewayFactory(new Be2BillOffsiteGatewayFactory);
        $extension->addGatewayFactory(new AuthorizeNetAimGatewayFactory);
        $extension->addGatewayFactory(new PayexGatewayFactory);
        $extension->addGatewayFactory(new OmnipayDirectGatewayFactory);
        $extension->addGatewayFactory(new OmnipayOffsiteGatewayFactory);
        $extension->addGatewayFactory(new OmnipayGatewayFactory);
        $extension->addGatewayFactory(new CustomGatewayFactory);
        $extension->addGatewayFactory(new OfflineGatewayFactory);
        $extension->addGatewayFactory(new KlarnaCheckoutGatewayFactory);
        $extension->addGatewayFactory(new KlarnaInvoiceGatewayFactory);
        $extension->addGatewayFactory(new StripeJsGatewayFactory);
        $extension->addGatewayFactory(new StripeCheckoutGatewayFactory);

        $extension->addStorageFactory(new FilesystemStorageFactory);
        $extension->addStorageFactory(new DoctrineStorageFactory);
        $extension->addStorageFactory(new CustomStorageFactory);
        $extension->addStorageFactory(new Propel1StorageFactory);
        $extension->addStorageFactory(new Propel2StorageFactory);

        $container->addCompilerPass(new BuildRegistryPass());
        $container->addCompilerPass(new BuildGatewayFactoryPass);
    }
}
