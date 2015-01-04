<?php
namespace Payum\Bundle\PayumBundle\Tests\Functional\DependencyInjection;

use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment\Be2BillOffsitePaymentFactory;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment\KlarnaCheckoutPaymentFactory;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment\KlarnaInvoicePaymentFactory;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment\OfflinePaymentFactory;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment\OmnipayOffsitePaymentFactory;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment\StripeCheckoutPaymentFactory;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment\StripeJsPaymentFactory;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Storage\FilesystemStorageFactory;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment\AuthorizeNetAimPaymentFactory;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment\Be2BillDirectPaymentFactory;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment\PayexPaymentFactory;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment\OmnipayDirectPaymentFactory;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment\PaypalExpressCheckoutNvpPaymentFactory;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment\PaypalProCheckoutNvpPaymentFactory;
use Payum\Bundle\PayumBundle\DependencyInjection\PayumExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class PayumExtensionTest extends  \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldLoadExtensionWithPaypalExpressCheckoutConfiguredPayment()
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
                'a_context' => array(
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
        $extension->addPaymentFactory(new PaypalExpressCheckoutNvpPaymentFactory);
        $extension->addStorageFactory(new FilesystemStorageFactory);
        
        $extension->load($configs, $containerBuilder);
        
        $this->assertTrue($containerBuilder->hasDefinition('payum.context.a_context.payment'));
    }

    /**
     * @test
     */
    public function shouldLoadExtensionWithPaypalProCheckoutConfiguredPayment()
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
                'a_context' => array(
                    'paypal_pro_checkout_nvp' => array(
                        'username' => 'a_username',
                        'password' => 'a_password',
                        'partner' => 'a_partner',
                        'vendor' => 'a_vendor',
                        'sandbox' => true
                    ),
                )
            )
        );

        $configs = array($config);

        $containerBuilder = new ContainerBuilder();
        $containerBuilder->setParameter('kernel.debug', false);

        $extension = new PayumExtension;
        $extension->addPaymentFactory(new PaypalProCheckoutNvpPaymentFactory);
        $extension->addStorageFactory(new FilesystemStorageFactory);

        $extension->load($configs, $containerBuilder);

        $this->assertTrue($containerBuilder->hasDefinition('payum.context.a_context.payment'));
    }

    /**
     * @test
     */
    public function shouldLoadExtensionWithBe2billDirectConfiguredPayment()
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
                'a_context' => array(
                    'be2bill_direct' => array(
                        'identifier' => 'a_identifier',
                        'password' => 'a_password',
                        'sandbox' => true
                    ),
                )
            )
        );

        $configs = array($config);

        $containerBuilder = new ContainerBuilder();
        $containerBuilder->setParameter('kernel.debug', false);

        $extension = new PayumExtension;
        $extension->addPaymentFactory(new Be2BillDirectPaymentFactory);
        $extension->addStorageFactory(new FilesystemStorageFactory);

        $extension->load($configs, $containerBuilder);

        $this->assertTrue($containerBuilder->hasDefinition('payum.context.a_context.payment'));
    }

    /**
     * @test
     */
    public function shouldLoadExtensionWithBe2billOffsiteConfiguredPayment()
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
                'a_context' => array(
                    'be2bill_offsite' => array(
                        'identifier' => 'a_identifier',
                        'password' => 'a_password',
                        'sandbox' => true
                    ),
                )
            )
        );

        $configs = array($config);

        $containerBuilder = new ContainerBuilder();
        $containerBuilder->setParameter('kernel.debug', false);

        $extension = new PayumExtension;
        $extension->addPaymentFactory(new Be2BillOffsitePaymentFactory);
        $extension->addStorageFactory(new FilesystemStorageFactory);

        $extension->load($configs, $containerBuilder);

        $this->assertTrue($containerBuilder->hasDefinition('payum.context.a_context.payment'));
    }

    /**
     * @test
     */
    public function shouldLoadExtensionWithOfflineConfiguredPayment()
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
                'a_context' => array(
                    'offline' => true
                )
            )
        );

        $configs = array($config);

        $containerBuilder = new ContainerBuilder();
        $containerBuilder->setParameter('kernel.debug', false);

        $extension = new PayumExtension;
        $extension->addPaymentFactory(new OfflinePaymentFactory);
        $extension->addStorageFactory(new FilesystemStorageFactory);

        $extension->load($configs, $containerBuilder);

        $this->assertTrue($containerBuilder->hasDefinition('payum.context.a_context.payment'));
    }

    /**
     * @test
     */
    public function shouldLoadExtensionWithAuthorizeNetAimConfiguredPayment()
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
                'a_context' => array(
                    'authorize_net_aim' => array(
                        'login_id' => 'a_login',
                        'transaction_key' => 'a_transaction_key',
                        'sandbox' => true
                    ),
                )
            )
        );

        $configs = array($config);

        $containerBuilder = new ContainerBuilder();
        $containerBuilder->setParameter('kernel.debug', false);

        $extension = new PayumExtension;
        $extension->addPaymentFactory(new AuthorizeNetAimPaymentFactory);
        $extension->addStorageFactory(new FilesystemStorageFactory);

        $extension->load($configs, $containerBuilder);

        $this->assertTrue($containerBuilder->hasDefinition('payum.context.a_context.payment'));
    }

    /**
     * @test
     */
    public function shouldLoadExtensionWithOmnipayDirectConfiguredPayment()
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
                'a_context' => array(
                    'omnipay_direct' => array(
                        'type' => 'Stripe',
                        'options' => array(
                            'apiKey' => 'abc123',
                        )
                    ),
                )
            )
        );

        $configs = array($config);

        $containerBuilder = new ContainerBuilder();
        $containerBuilder->setParameter('kernel.debug', false);

        $extension = new PayumExtension;
        $extension->addPaymentFactory(new OmnipayDirectPaymentFactory);
        $extension->addStorageFactory(new FilesystemStorageFactory);

        $extension->load($configs, $containerBuilder);

        $this->assertTrue($containerBuilder->hasDefinition('payum.context.a_context.payment'));
    }

    /**
     * @test
     */
    public function shouldLoadExtensionWithOmnipayOffsiteConfiguredPayment()
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
                'a_context' => array(
                    'omnipay_offsite' => array(
                        'type' => 'PayPal_Express',
                        'options' => array(
                            'username' => 'abc123',
                            'passowrd' => 'abc123',
                            'signature' => 'abc123',
                            'testMode' => true,
                        )
                    ),
                )
            )
        );

        $configs = array($config);

        $containerBuilder = new ContainerBuilder();
        $containerBuilder->setParameter('kernel.debug', false);

        $extension = new PayumExtension;
        $extension->addPaymentFactory(new OmnipayOffsitePaymentFactory);
        $extension->addStorageFactory(new FilesystemStorageFactory);

        $extension->load($configs, $containerBuilder);

        $this->assertTrue($containerBuilder->hasDefinition('payum.context.a_context.payment'));
    }

    /**
     * @test
     */
    public function shouldLoadExtensionWithPayexConfiguredPayment()
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
                'a_context' => array(
                    'payex' => array(
                        'encryption_key' => 'aKey',
                        'account_number' => 'aNum'
                    ),
                )
            )
        );

        $configs = array($config);

        $containerBuilder = new ContainerBuilder();
        $containerBuilder->setParameter('kernel.debug', false);

        $extension = new PayumExtension;
        $extension->addPaymentFactory(new PayexPaymentFactory);
        $extension->addStorageFactory(new FilesystemStorageFactory);

        $extension->load($configs, $containerBuilder);

        $this->assertTrue($containerBuilder->hasDefinition('payum.context.a_context.payment'));
    }

    /**
     * @test
     */
    public function shouldLoadExtensionWithKlarnaCheckoutConfiguredPayment()
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
                'a_context' => array(
                    'klarna_checkout' => array(
                        'secret' => 'aSecret',
                        'merchant_id' => 'anId'
                    ),
                )
            )
        );

        $configs = array($config);

        $containerBuilder = new ContainerBuilder();
        $containerBuilder->setParameter('kernel.debug', false);

        $extension = new PayumExtension;
        $extension->addPaymentFactory(new KlarnaCheckoutPaymentFactory);
        $extension->addStorageFactory(new FilesystemStorageFactory);

        $extension->load($configs, $containerBuilder);

        $this->assertTrue($containerBuilder->hasDefinition('payum.context.a_context.payment'));
    }

    /**
     * @test
     */
    public function shouldLoadExtensionWithKlarnaInvoiceConfiguredPayment()
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
                'a_context' => array(
                    'klarna_invoice' => array(
                        'secret' => 'aSecret',
                        'eid' => 'anId'
                    ),
                )
            )
        );

        $configs = array($config);

        $containerBuilder = new ContainerBuilder();
        $containerBuilder->setParameter('kernel.debug', false);

        $extension = new PayumExtension;
        $extension->addPaymentFactory(new KlarnaInvoicePaymentFactory);
        $extension->addStorageFactory(new FilesystemStorageFactory);

        $extension->load($configs, $containerBuilder);

        $this->assertTrue($containerBuilder->hasDefinition('payum.context.a_context.payment'));
    }

    /**
     * @test
     */
    public function shouldLoadExtensionWithStripeJsConfiguredPayment()
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
                'a_context' => array(
                    'stripe_js' => array(
                        'publishable_key' => 'aKey',
                        'secret_key' => 'aKey'
                    ),
                )
            )
        );

        $configs = array($config);

        $containerBuilder = new ContainerBuilder();
        $containerBuilder->setParameter('kernel.debug', false);

        $extension = new PayumExtension;
        $extension->addPaymentFactory(new StripeJsPaymentFactory());
        $extension->addStorageFactory(new FilesystemStorageFactory);

        $extension->load($configs, $containerBuilder);

        $this->assertTrue($containerBuilder->hasDefinition('payum.context.a_context.payment'));
    }

    /**
     * @test
     */
    public function shouldLoadExtensionWithStripeCheckoutConfiguredPayment()
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
                'a_context' => array(
                    'stripe_checkout' => array(
                        'publishable_key' => 'aKey',
                        'secret_key' => 'aKey'
                    ),
                )
            )
        );

        $configs = array($config);

        $containerBuilder = new ContainerBuilder();
        $containerBuilder->setParameter('kernel.debug', false);

        $extension = new PayumExtension;
        $extension->addPaymentFactory(new StripeCheckoutPaymentFactory());
        $extension->addStorageFactory(new FilesystemStorageFactory);

        $extension->load($configs, $containerBuilder);

        $this->assertTrue($containerBuilder->hasDefinition('payum.context.a_context.payment'));
    }

    /**
     * @test
     */
    public function shouldAddPaymentTagWithCorrectContextAndFactoryNamesSet()
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
                'the_paypal_context' => array(
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
        $extension->addPaymentFactory(new PaypalExpressCheckoutNvpPaymentFactory);
        $extension->addStorageFactory(new FilesystemStorageFactory);

        $extension->load($configs, $containerBuilder);

        $paymentDefinition = $containerBuilder->getDefinition('payum.context.the_paypal_context.payment');

        $tagAttributes = $paymentDefinition->getTag('payum.payment');

        $this->assertCount(1, $tagAttributes);

        $attributes = $tagAttributes[0];

        $this->assertArrayHasKey('factory', $attributes);
        $this->assertEquals('paypal_express_checkout_nvp', $attributes['factory']);

        $this->assertArrayHasKey('payment', $attributes);
        $this->assertEquals('the_paypal_context', $attributes['payment']);
    }

    protected function assertDefinitionContainsMethodCall(Definition $serviceDefinition, $expectedMethod, $expectedFirstArgument)
    {
        foreach ($serviceDefinition->getMethodCalls() as $methodCall) {
            if ($expectedMethod == $methodCall[0] && $expectedFirstArgument == $methodCall[1][0]) {
                return;
            }
        }
        
        $this->fail(sprintf(
            'Failed assert that service (Class: %s) has method %s been called with first argument %s',
            $serviceDefinition->getClass(),
            $expectedMethod,
            $expectedFirstArgument
        ));
    }
}