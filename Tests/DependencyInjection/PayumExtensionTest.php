<?php
namespace Payum\Bundle\PayumBundle\Tests\DependencyInjection;

use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment\AuthorizeNetAimPaymentFactory;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment\Be2BillPaymentFactory;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment\OmnipayPaymentFactory;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment\PaypalExpressCheckoutNvpPaymentFactory;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment\PaypalProCheckoutNvpPaymentFactory;
use Payum\Bundle\PayumBundle\DependencyInjection\PayumExtension;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

class PayumExtensionTest extends  \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldLoadExtensionWithPaypalExpressCheckoutConfiguredPayment()
    {
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
    }

    /**
     * @test
     */
    public function shouldLoadExtensionWithPaypalProCheckoutConfiguredPayment()
    {
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
    }

    /**
     * @test
     */
    public function shouldLoadExtensionWithAuthorizeNetAimConfiguredPayment()
    {
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
    }

    /**
     * @test
     */
    public function shouldLoadExtensionWithOmnipayConfiguredPayment()
    {
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
    }
}