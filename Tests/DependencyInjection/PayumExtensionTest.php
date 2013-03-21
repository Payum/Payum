<?php
namespace Payum\Bundle\PayumBundle\Tests\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment\AuthorizeNetAimPaymentFactory;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment\Be2BillPaymentFactory;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment\OmnipayPaymentFactory;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment\PaypalExpressCheckoutNvpPaymentFactory;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment\PaypalProCheckoutNvpPaymentFactory;
use Payum\Bundle\PayumBundle\DependencyInjection\PayumExtension;
use Symfony\Component\DependencyInjection\Reference;

class PayumExtensionTest extends  \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldLoadExtensionWithPaypalExpressCheckoutConfiguredPayment()
    {
        if (false == class_exists('Payum\Paypal\ExpressCheckout\Nvp\PaymentFactory')) {
            $this->markTestSkipped('Skipped because payment library is not installed.');
        }
        
        $config = array(
            'contexts' => array(
                'a_context' => array(
                    'paypal_express_checkout_nvp_payment' => array(
                        'api' => array(
                            'options' => array(
                                'username' => 'a_username',
                                'password' => 'a_password',
                                'signature' => 'a_signature',
                                'sandbox' => true
                            )
                        )
                    ),
                )
            )
        );

        $configs = array($config);

        $containerBuilder = new ContainerBuilder(new ParameterBag);
        
        $extension = new PayumExtension;
        $extension->addPaymentFactory(new PaypalExpressCheckoutNvpPaymentFactory);
        
        $extension->load($configs, $containerBuilder);
        
        $this->assertTrue($containerBuilder->hasDefinition('payum.context.a_context'));
        $this->assertTrue($containerBuilder->hasDefinition('payum.context.a_context.api'));
        $this->assertTrue($containerBuilder->hasDefinition('payum.context.a_context.payment'));

        $this->assertDefinitionContainsMethodCall(
            $containerBuilder->getDefinition('payum.context.a_context.payment'),
            'addApi',
            new Reference('payum.context.a_context.api')
        );
    }

    /**
     * @test
     */
    public function shouldLoadExtensionWithPaypalProCheckoutConfiguredPayment()
    {
        if (false == class_exists('Payum\Paypal\ProCheckout\Nvp\PaymentFactory')) {
            $this->markTestSkipped('Skipped because payment library is not installed.');
        }
        
        $config = array(
            'contexts' => array(
                'a_context' => array(
                    'paypal_pro_checkout_nvp_payment' => array(
                        'api' => array(
                            'options' => array(
                                'username' => 'a_username',
                                'password' => 'a_password',
                                'partner' => 'a_partner',
                                'vendor' => 'a_vendor',
                                'sandbox' => true
                            )
                        )
                    ),
                )
            )
        );

        $configs = array($config);

        $containerBuilder = new ContainerBuilder(new ParameterBag);

        $extension = new PayumExtension;
        $extension->addPaymentFactory(new PaypalProCheckoutNvpPaymentFactory);

        $extension->load($configs, $containerBuilder);

        $this->assertTrue($containerBuilder->hasDefinition('payum.context.a_context'));
        $this->assertTrue($containerBuilder->hasDefinition('payum.context.a_context.api'));
        $this->assertTrue($containerBuilder->hasDefinition('payum.context.a_context.payment'));
    }

    /**
     * @test
     */
    public function shouldLoadExtensionWithBe2billConfiguredPayment()
    {
        if (false == class_exists('Payum\Be2Bill\PaymentFactory')) {
            $this->markTestSkipped('Skipped because payment library is not installed.');
        }
        
        $config = array(
            'contexts' => array(
                'a_context' => array(
                    'be2bill_payment' => array(
                        'api' => array(
                            'options' => array(
                                'identifier' => 'a_identifier',
                                'password' => 'a_password',
                                'sandbox' => true
                            )
                        )
                    ),
                )
            )
        );

        $configs = array($config);

        $containerBuilder = new ContainerBuilder(new ParameterBag);

        $extension = new PayumExtension;
        $extension->addPaymentFactory(new Be2BillPaymentFactory);

        $extension->load($configs, $containerBuilder);

        $this->assertTrue($containerBuilder->hasDefinition('payum.context.a_context'));
        $this->assertTrue($containerBuilder->hasDefinition('payum.context.a_context.api'));
        $this->assertTrue($containerBuilder->hasDefinition('payum.context.a_context.payment'));

        $this->assertDefinitionContainsMethodCall(
            $containerBuilder->getDefinition('payum.context.a_context.payment'),
            'addApi',
            new Reference('payum.context.a_context.api')
        );
    }

    /**
     * @test
     */
    public function shouldLoadExtensionWithAuthorizeNetAimConfiguredPayment()
    {
        if (false == class_exists('Payum\AuthorizeNet\Aim\PaymentFactory')) {
            $this->markTestSkipped('Skipped because payment library is not installed.');
        }
        
        $config = array(
            'contexts' => array(
                'a_context' => array(
                    'authorize_net_aim_payment' => array(
                        'api' => array(
                            'options' => array(
                                'login_id' => 'a_login',
                                'transaction_key' => 'a_transaction_key',
                                'sandbox' => true
                            )
                        )
                    ),
                )
            )
        );

        $configs = array($config);

        $containerBuilder = new ContainerBuilder(new ParameterBag);

        $extension = new PayumExtension;
        $extension->addPaymentFactory(new AuthorizeNetAimPaymentFactory);

        $extension->load($configs, $containerBuilder);

        $this->assertTrue($containerBuilder->hasDefinition('payum.context.a_context'));
        $this->assertTrue($containerBuilder->hasDefinition('payum.context.a_context.api'));
        $this->assertTrue($containerBuilder->hasDefinition('payum.context.a_context.payment'));

        $this->assertDefinitionContainsMethodCall(
            $containerBuilder->getDefinition('payum.context.a_context.payment'),
            'addApi',
            new Reference('payum.context.a_context.api')
        );
    }

    /**
     * @test
     */
    public function shouldLoadExtensionWithOmnipayConfiguredPayment()
    {
        if (false == class_exists('Payum\Bridge\Omnipay\PaymentFactory')) {
            $this->markTestSkipped('Skipped because payment library is not installed.');
        }
        
        $config = array(
            'contexts' => array(
                'a_context' => array(
                    'omnipay_payment' => array(
                        'type' => 'Stripe',
                        'options' => array(
                            'apiKey' => 'abc123',
                        )
                    ),
                )
            )
        );

        $configs = array($config);

        $containerBuilder = new ContainerBuilder(new ParameterBag);

        $extension = new PayumExtension;
        $extension->addPaymentFactory(new OmnipayPaymentFactory);

        $extension->load($configs, $containerBuilder);

        $this->assertTrue($containerBuilder->hasDefinition('payum.context.a_context'));
        $this->assertTrue($containerBuilder->hasDefinition('payum.context.a_context.gateway'));
        $this->assertTrue($containerBuilder->hasDefinition('payum.context.a_context.payment'));

        $this->assertDefinitionContainsMethodCall(
            $containerBuilder->getDefinition('payum.context.a_context.payment'),
            'addApi',
            new Reference('payum.context.a_context.gateway')
        );
    }

    /**
     * @test
     */
    public function shouldAllowAddCustomActions()
    {
        if (false == class_exists('Payum\Paypal\ExpressCheckout\Nvp\PaymentFactory')) {
            $this->markTestSkipped('Skipped because payment library is not installed.');
        }
        
        $config = array(
            'contexts' => array(
                'a_context' => array(
                    'paypal_express_checkout_nvp_payment' => array(
                        'api' => array(
                            'options' => array(
                                'username' => 'a_username',
                                'password' => 'a_password',
                                'signature' => 'a_signature',
                                'sandbox' => true
                            )
                        ),
                        'actions' => array(
                            'action.foo',
                            'action.bar'
                        )
                    ),
                )
            )
        );

        $configs = array($config);

        $containerBuilder = new ContainerBuilder(new ParameterBag);

        $extension = new PayumExtension;
        $extension->addPaymentFactory(new PaypalExpressCheckoutNvpPaymentFactory);

        $extension->load($configs, $containerBuilder);

        $this->assertTrue($containerBuilder->hasDefinition('payum.context.a_context.payment'));
        
        $this->assertDefinitionContainsMethodCall(
            $containerBuilder->getDefinition('payum.context.a_context.payment'), 
            'addAction', 
            new Reference('action.foo')
        );

        $this->assertDefinitionContainsMethodCall(
            $containerBuilder->getDefinition('payum.context.a_context.payment'),
            'addAction',
            new Reference('action.bar')
        );
    }

    /**
     * @test
     */
    public function shouldAllowAddCustomApis()
    {
        if (false == class_exists('Payum\Paypal\ExpressCheckout\Nvp\PaymentFactory')) {
            $this->markTestSkipped('Skipped because payment library is not installed.');
        }
        
        $config = array(
            'contexts' => array(
                'a_context' => array(
                    'paypal_express_checkout_nvp_payment' => array(
                        'api' => array(
                            'options' => array(
                                'username' => 'a_username',
                                'password' => 'a_password',
                                'signature' => 'a_signature',
                                'sandbox' => true
                            )
                        ),
                        'apis' => array(
                            'api.foo',
                            'api.bar'
                        )
                    ),
                )
            )
        );

        $configs = array($config);

        $containerBuilder = new ContainerBuilder(new ParameterBag);

        $extension = new PayumExtension;
        $extension->addPaymentFactory(new PaypalExpressCheckoutNvpPaymentFactory);

        $extension->load($configs, $containerBuilder);

        $this->assertTrue($containerBuilder->hasDefinition('payum.context.a_context.payment'));

        $this->assertDefinitionContainsMethodCall(
            $containerBuilder->getDefinition('payum.context.a_context.payment'),
            'addApi',
            new Reference('api.foo')
        );

        $this->assertDefinitionContainsMethodCall(
            $containerBuilder->getDefinition('payum.context.a_context.payment'),
            'addApi',
            new Reference('api.bar')
        );
    }

    /**
     * @test
     */
    public function shouldAllowAddCustomExtensions()
    {
        if (false == class_exists('Payum\Paypal\ExpressCheckout\Nvp\PaymentFactory')) {
            $this->markTestSkipped('Skipped because payment library is not installed.');
        }
        
        $config = array(
            'contexts' => array(
                'a_context' => array(
                    'paypal_express_checkout_nvp_payment' => array(
                        'api' => array(
                            'options' => array(
                                'username' => 'a_username',
                                'password' => 'a_password',
                                'signature' => 'a_signature',
                                'sandbox' => true
                            )
                        ),
                        'extensions' => array(
                            'extension.foo',
                            'extension.bar'
                        )
                    ),
                )
            )
        );

        $configs = array($config);

        $containerBuilder = new ContainerBuilder(new ParameterBag);

        $extension = new PayumExtension;
        $extension->addPaymentFactory(new PaypalExpressCheckoutNvpPaymentFactory);

        $extension->load($configs, $containerBuilder);

        $this->assertTrue($containerBuilder->hasDefinition('payum.context.a_context.payment'));

        $this->assertDefinitionContainsMethodCall(
            $containerBuilder->getDefinition('payum.context.a_context.payment'),
            'addExtension',
            new Reference('extension.foo')
        );

        $this->assertDefinitionContainsMethodCall(
            $containerBuilder->getDefinition('payum.context.a_context.payment'),
            'addExtension',
            new Reference('extension.bar')
        );
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