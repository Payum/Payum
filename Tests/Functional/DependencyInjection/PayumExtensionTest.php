<?php
namespace Payum\Bundle\PayumBundle\Tests\Functional\DependencyInjection;

use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Gateway\Be2BillOffsiteGatewayFactory;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Gateway\CustomGatewayFactory;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Gateway\KlarnaCheckoutGatewayFactory;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Gateway\KlarnaInvoiceGatewayFactory;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Gateway\OfflineGatewayFactory;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Gateway\OmnipayOffsiteGatewayFactory;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Gateway\GatewayFactoryInterface;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Gateway\StripeCheckoutGatewayFactory;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Gateway\StripeJsGatewayFactory;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Storage\FilesystemStorageFactory;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Gateway\AuthorizeNetAimGatewayFactory;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Gateway\Be2BillDirectGatewayFactory;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Gateway\PayexGatewayFactory;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Gateway\OmnipayDirectGatewayFactory;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Gateway\PaypalExpressCheckoutNvpGatewayFactory;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Gateway\PaypalProCheckoutNvpGatewayFactory;
use Payum\Bundle\PayumBundle\DependencyInjection\PayumExtension;
use Payum\Core\Model\GatewayConfigInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class PayumExtensionTest extends  \PHPUnit_Framework_TestCase
{
    public function providePayments()
    {
        return array(
            'papyla express checkout' => array(
                array(
                    'username' => 'a_username',
                    'password' => 'a_password',
                    'signature' => 'a_signature',
                    'sandbox' => true
                ),
                new PaypalExpressCheckoutNvpGatewayFactory(),
            ),
            'paypal pro checkout' => array(
                array(
                    'username' => 'a_username',
                    'password' => 'a_password',
                    'partner' => 'a_partner',
                    'vendor' => 'a_vendor',
                    'sandbox' => true
                ),
                new PaypalProCheckoutNvpGatewayFactory(),
            ),
            'be2bill direct' => array(
                array(
                    'identifier' => 'a_identifier',
                    'password' => 'a_password',
                    'sandbox' => true
                ),
                new Be2BillDirectGatewayFactory(),
            ),
            'be2bill offsite' => array(
                array(
                    'identifier' => 'a_identifier',
                    'password' => 'a_password',
                    'sandbox' => true
                ),
                new Be2BillOffsiteGatewayFactory(),
            ),
            'offline' => array(
                array(),
                new OfflineGatewayFactory(),
            ),
            'stripe js' => array(
                array(
                    'publishable_key' => 'a_key',
                    'secret_key' => 'a_key'
                ),
                new StripeJsGatewayFactory(),
            ),
            'stripe checkout' => array(
                array(
                    'publishable_key' => 'a_key',
                    'secret_key' => 'a_key'
                ),
                new StripeCheckoutGatewayFactory(),
            ),
            'authorize net aim' => array(
                array(
                    'login_id' => 'a_login',
                    'transaction_key' => 'a_transaction_key',
                    'sandbox' => true
                ),
                new AuthorizeNetAimGatewayFactory(),
            ),
            'omnipay direct' => array(
                array(
                    'type' => 'Stripe',
                    'options' => array(
                        'apiKey' => 'abc123',
                    )
                ),
                new OmnipayDirectGatewayFactory(),
            ),
            'omnipay offsite' => array(
                array(
                    'type' => 'PayPal_Express',
                    'options' => array(
                        'username' => 'abc123',
                        'passowrd' => 'abc123',
                        'signature' => 'abc123',
                        'testMode' => true,
                    ),
                ),
                new OmnipayOffsiteGatewayFactory(),
            ),
            'payex' => array(
                array(
                    'encryption_key' => 'aKey',
                    'account_number' => 'aNum'
                ),
                new PayexGatewayFactory(),
            ),
            'klarna checkout' => array(
                array(
                    'secret' => 'aSecret',
                    'merchant_id' => 'anId'
                ),
                new KlarnaCheckoutGatewayFactory(),
            ),
            'klarna invoice' => array(
                array(
                    'eid' => 'anId',
                    'secret' => 'aSecret',
                ),
                new KlarnaInvoiceGatewayFactory(),
            ),
        );
    }

    /**
     * @test
     *
     * @dataProvider providePayments
     */
    public function shouldLoadExtensionWithPayment($config, GatewayFactoryInterface $paymentFactory)
    {
        $config = array(
            'security' => array(
                'token_storage' => array(
                    'Payum\Core\Model\Token' => array(
                        'filesystem' => array(
                            'storage_dir' => sys_get_temp_dir(),
                            'id_property' => 'hash'
                        )
                    )
                )
            ),
            'payments' => array(
                'a_payment' => array(
                    $paymentFactory->getName() => $config,
                )
            )
        );

        $configs = array($config);

        $container = new ContainerBuilder();
        $container->setParameter('kernel.debug', false);
        
        $extension = new PayumExtension;
        $extension->addGatewayFactory($paymentFactory);
        $extension->addStorageFactory(new FilesystemStorageFactory);
        
        $extension->load($configs, $container);

        $this->assertTrue($container->hasDefinition('payum.'.$paymentFactory->getName().'.factory'));
        $this->assertTrue($container->hasDefinition('payum.'.$paymentFactory->getName().'.a_payment.payment'));
        $this->assertEquals(
            'payum.'.$paymentFactory->getName().'.factory',
            $container->getDefinition('payum.'.$paymentFactory->getName().'.a_payment.payment')->getFactoryService()
        );
        $this->assertEquals('create', $container->getDefinition('payum.'.$paymentFactory->getName().'.a_payment.payment')->getFactoryMethod());
    }

    /**
     * @test
     */
    public function shouldLoadExtensionWithCustomPayment()
    {
        $config = array(
            'security' => array(
                'token_storage' => array(
                    'Payum\Core\Model\Token' => array(
                        'filesystem' => array(
                            'storage_dir' => sys_get_temp_dir(),
                            'id_property' => 'hash'
                        )
                    )
                )
            ),
            'payments' => array(
                'a_payment' => array(
                    'custom' => array(
                        'service' => 'aServiceId',
                    ),
                )
            )
        );

        $configs = array($config);

        $container = new ContainerBuilder();
        $container->setParameter('kernel.debug', false);

        $extension = new PayumExtension;
        $extension->addGatewayFactory(new CustomGatewayFactory());
        $extension->addStorageFactory(new FilesystemStorageFactory);

        $extension->load($configs, $container);

        $this->assertTrue($container->hasDefinition('payum.custom.a_payment.payment'));
    }

    /**
     * @test
     */
    public function shouldAddPaymentTagWithCorrectPaymentAndFactoryNamesSet()
    {
        $config = array(
            'security' => array(
                'token_storage' => array(
                    'Payum\Core\Model\Token' => array(
                        'filesystem' => array(
                            'storage_dir' => sys_get_temp_dir(),
                            'id_property' => 'hash'
                        )
                    )
                )
            ),
            'payments' => array(
                'the_paypal_payment' => array(
                    'paypal_express_checkout_nvp' => array(
                        'username' => 'a_username',
                        'password' => 'a_password',
                        'signature' => 'a_signature',
                        'sandbox' => true
                    ),
                )
            )
        );

        $configs = array($config);

        $containerBuilder = new ContainerBuilder();
        $containerBuilder->setParameter('kernel.debug', false);

        $extension = new PayumExtension;
        $extension->addGatewayFactory(new PaypalExpressCheckoutNvpGatewayFactory);
        $extension->addStorageFactory(new FilesystemStorageFactory);

        $extension->load($configs, $containerBuilder);

        $paymentDefinition = $containerBuilder->getDefinition('payum.paypal_express_checkout_nvp.the_paypal_payment.payment');

        $tagAttributes = $paymentDefinition->getTag('payum.payment');

        $this->assertCount(1, $tagAttributes);

        $attributes = $tagAttributes[0];

        $this->assertArrayHasKey('factory', $attributes);
        $this->assertEquals('paypal_express_checkout_nvp', $attributes['factory']);

        $this->assertArrayHasKey('payment', $attributes);
        $this->assertEquals('the_paypal_payment', $attributes['payment']);
    }

    /**
     * @test
     */
    public function shouldSetPayumAsAliasToStaticRegistryIfDynamicPaymentsNotConfigured()
    {
        $config = array(
            // 'dynamic_payments' => array()
            'security' => array(
                'token_storage' => array(
                    'Payum\Core\Model\Token' => array(
                        'filesystem' => array(
                            'storage_dir' => sys_get_temp_dir(),
                            'id_property' => 'hash'
                        )
                    )
                )
            ),
            'payments' => array(),
        );

        $configs = array($config);

        $containerBuilder = new ContainerBuilder();
        $containerBuilder->setParameter('kernel.debug', false);

        $extension = new PayumExtension;
        $extension->addStorageFactory(new FilesystemStorageFactory);

        $extension->load($configs, $containerBuilder);

        $this->assertEquals('payum.static_registry', $containerBuilder->getAlias('payum'));
    }

    /**
     * @test
     */
    public function shouldSetPayumAsAliasToDynamicRegistryAndPassStatisOneToDynamicOne()
    {
        $config = array(
            'dynamic_payments' => array(
                'config_storage' => array(
                    'Payum\Core\Model\PaymentConfig' => array(
                        'filesystem' => array(
                            'storage_dir' => sys_get_temp_dir(),
                            'id_property' => 'hash'
                        )
                    )
                )
            ),
            'security' => array(
                'token_storage' => array(
                    'Payum\Core\Model\Token' => array(
                        'filesystem' => array(
                            'storage_dir' => sys_get_temp_dir(),
                            'id_property' => 'hash'
                        )
                    )
                )
            ),
            'payments' => array(),
        );

        $configs = array($config);

        $containerBuilder = new ContainerBuilder();
        $containerBuilder->setParameter('kernel.debug', false);

        $extension = new PayumExtension;
        $extension->addStorageFactory(new FilesystemStorageFactory);

        $extension->load($configs, $containerBuilder);

        $this->assertEquals('payum.dynamic_registry', $containerBuilder->getAlias('payum'));

        $registry = $containerBuilder->getDefinition('payum.dynamic_registry');
        $this->assertEquals('Payum\Core\Registry\DynamicRegistry', $registry->getClass());
        $this->assertEquals('payum.dynamic_payments.config_storage', (string) $registry->getArgument(0));
        $this->assertEquals('payum.static_registry', (string) $registry->getArgument(1));
    }

    /**
     * @test
     */
    public function shouldConfigureSonataAdminClassForPaymentConfigModelSetInStorageSection()
    {
        $config = array(
            'dynamic_payments' => array(
                'sonata_admin' => true,
                'config_storage' => array(
                    'Payum\Bundle\PayumBundle\Tests\Functional\DependencyInjection\TestPaymentConfig' => array(
                        'filesystem' => array(
                            'storage_dir' => sys_get_temp_dir(),
                            'id_property' => 'hash'
                        )
                    )
                )
            ),
            'security' => array(
                'token_storage' => array(
                    'Payum\Core\Model\Token' => array(
                        'filesystem' => array(
                            'storage_dir' => sys_get_temp_dir(),
                            'id_property' => 'hash'
                        )
                    )
                )
            ),
            'payments' => array(),
        );

        $configs = array($config);

        $containerBuilder = new ContainerBuilder();
        $containerBuilder->setParameter('kernel.debug', false);

        $extension = new PayumExtension;
        $extension->addStorageFactory(new FilesystemStorageFactory);

        $extension->load($configs, $containerBuilder);

        $configAdmin = $containerBuilder->getDefinition('payum.dynamic_payments.payment_config_admin');

        $this->assertEquals('Payum\Bundle\PayumBundle\Sonata\PaymentConfigAdmin', $configAdmin->getClass());
        $this->assertEquals('Payum\Bundle\PayumBundle\Tests\Functional\DependencyInjection\TestPaymentConfig', $configAdmin->getArgument(1));

        $this->assertEquals(
            array(array('manager_type' => 'orm', 'group' => 'Payments', 'label' => 'Configs')),
            $configAdmin->getTag('sonata.admin')
        );

    }
}

class TestPaymentConfig implements GatewayConfigInterface
{
    public function getGatewayName()
    {
    }

    public function setGatewayName($gatewayName)
    {
    }

    public function getFactoryName()
    {
    }

    public function setFactoryName($name)
    {
    }

    public function setConfig(array $config)
    {
    }

    public function getConfig()
    {
    }
}