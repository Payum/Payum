<?php
namespace Payum\Bundle\PayumBundle\Tests\DependencyInjection\Factory\Payment;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment\PaypalExpressCheckoutNvpPaymentFactory;

class PaypalExpressCheckoutNvpPaymentFactoryTest extends \PHPUnit_Framework_TestCase
{
    public static function provideTaggedActions()
    {
        return array(
            'api.authorize_token' => array('payum.paypal.express_checkout_nvp.action.api.authorize_token'),
            'api.do_express_checkout_payment' => array('payum.paypal.express_checkout_nvp.action.api.do_express_checkout_payment'),
            'api.get_express_checkout_details' => array('payum.paypal.express_checkout_nvp.action.api.get_express_checkout_details'),
            'api.get_transaction_details' => array('payum.paypal.express_checkout_nvp.action.api.get_transaction_details'),
            'api.set_express_checkout' => array('payum.paypal.express_checkout_nvp.action.api.set_express_checkout'),
            'api.create_recurring_payment_profile' => array('payum.paypal.express_checkout_nvp.action.api.create_recurring_payment_profile'),
            'api.get_recurring_payments_profile_details' => array('payum.paypal.express_checkout_nvp.action.api.get_recurring_payments_profile_details'),
            'api.create_billing_agreement' => array('payum.paypal.express_checkout_nvp.action.api.create_billing_agreement'),
            'api.do_reference_transaction' => array('payum.paypal.express_checkout_nvp.action.api.do_reference_transaction'),

            'capture' => array('payum.paypal.express_checkout_nvp.action.capture'),
            'fill_order_details' => array('payum.paypal.express_checkout_nvp.action.fill_order_details'),
            'notify' => array('payum.paypal.express_checkout_nvp.action.notify'),
            'payment_details_status' => array('payum.paypal.express_checkout_nvp.action.payment_details_status'),
            'payment_details_sync' => array('payum.paypal.express_checkout_nvp.action.payment_details_sync'),
            'recurring_payment_details_status' => array('payum.paypal.express_checkout_nvp.action.recurring_payment_details_status'),
            'recurring_payment_details_sync' => array('payum.paypal.express_checkout_nvp.action.recurring_payment_details_sync'),
        );
    }
    
    /**
     * @test
     */
    public function shouldBeSubClassOfAbstractPaymentFactory()
    {
        $rc = new \ReflectionClass('Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment\PaypalExpressCheckoutNvpPaymentFactory');

        $this->assertTrue($rc->isSubclassOf('Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment\AbstractPaymentFactory'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new PaypalExpressCheckoutNvpPaymentFactory;
    }

    /**
     * @test
     */
    public function shouldAllowGetName()
    {
        $factory = new PaypalExpressCheckoutNvpPaymentFactory;

        $this->assertEquals('paypal_express_checkout_nvp', $factory->getName());
    }

    /**
     * @test
     */
    public function shouldAllowAddConfiguration()
    {
        $factory = new PaypalExpressCheckoutNvpPaymentFactory;

        $tb = new TreeBuilder();
        $rootNode = $tb->root('foo');
        
        $factory->addConfiguration($rootNode);

        $processor = new Processor();
        $config = $processor->process($tb->buildTree(), array(array(
            'username' => 'aUsername',
            'password' => 'aPassword',
            'signature' => 'aSignature',
        )));
        

        $this->assertArrayHasKey('username', $config);
        $this->assertEquals('aUsername', $config['username']);

        $this->assertArrayHasKey('password', $config);
        $this->assertEquals('aPassword', $config['password']);

        $this->assertArrayHasKey('signature', $config);
        $this->assertEquals('aSignature', $config['signature']);

        //come from abstract payment factory
        $this->assertArrayHasKey('actions', $config);
        $this->assertArrayHasKey('apis', $config);
        $this->assertArrayHasKey('extensions', $config);
    }

    /**
     * @test
     *
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage The child node "username" at path "foo" must be configured.
     */
    public function thrownIfApiOptionUsernameSectionMissing()
    {
        $factory = new PaypalExpressCheckoutNvpPaymentFactory;

        $tb = new TreeBuilder();
        $rootNode = $tb->root('foo');

        $factory->addConfiguration($rootNode);

        $processor = new Processor();
        $processor->process($tb->buildTree(), array(array()));
    }

    /**
     * @test
     *
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage The child node "password" at path "foo" must be configured.
     */
    public function thrownIfApiOptionPasswordSectionMissing()
    {
        $factory = new PaypalExpressCheckoutNvpPaymentFactory;

        $tb = new TreeBuilder();
        $rootNode = $tb->root('foo');

        $factory->addConfiguration($rootNode);

        $processor = new Processor();
        $processor->process($tb->buildTree(), array(array(
            'obtain_credit_card' => false,
            'username' => 'aUsername'
        )));
    }

    /**
     * @test
     *
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage The child node "signature" at path "foo" must be configured.
     */
    public function thrownIfApiOptionSignatureSectionMissing()
    {
        $factory = new PaypalExpressCheckoutNvpPaymentFactory;

        $tb = new TreeBuilder();
        $rootNode = $tb->root('foo');

        $factory->addConfiguration($rootNode);

        $processor = new Processor();
        $processor->process($tb->buildTree(), array(array(
            'obtain_credit_card' => false,
            'username' => 'aUsername',
            'password' => 'aPassword',
        )));
    }

    /**
     * @test
     */
    public function shouldAllowCreatePaymentAndReturnItsId()
    {
        $factory = new PaypalExpressCheckoutNvpPaymentFactory;

        $container = new ContainerBuilder;

        $paymentId = $factory->create($container, 'aContextName', array(
            'obtain_credit_card' => false,
            'username' => 'aUsername',
            'password' => 'aPassword',
            'signature' => 'aSignature',
            'sandbox' => true,
            'actions' => array(),
            'apis' => array(),
            'extensions' => array(),
        ));
        
        $this->assertEquals('payum.context.aContextName.payment', $paymentId);
        $this->assertTrue($container->hasDefinition($paymentId));
    }

    /**
     * @test
     */
    public function shouldCallParentsCreateMethod()
    {
        $factory = new PaypalExpressCheckoutNvpPaymentFactory;

        $container = new ContainerBuilder;

        $paymentId = $factory->create($container, 'aContextName', array(
            'obtain_credit_card' => false,
            'username' => 'aUsername',
            'password' => 'aPassword',
            'signature' => 'aSignature',
            'sandbox' => true,
            'actions' => array('payum.action.foo'),
            'apis' => array('payum.api.bar'),
            'extensions' => array('payum.extension.ololo'),
        ));

        $this->assertDefinitionContainsMethodCall(
            $container->getDefinition($paymentId), 
            'addAction', 
            new Reference('payum.action.foo')
        );
        $this->assertDefinitionContainsMethodCall(
            $container->getDefinition($paymentId),
            'addApi',
            new Reference('payum.api.bar')
        );
        $this->assertDefinitionContainsMethodCall(
            $container->getDefinition($paymentId),
            'addExtension',
            new Reference('payum.extension.ololo')
        );
    }

    /**
     * @test
     * 
     * @dataProvider provideTaggedActions
     */
    public function shouldAddPayumActionTagToActions($actionId)
    {
        $factory = new PaypalExpressCheckoutNvpPaymentFactory;

        $container = new ContainerBuilder;

        $factory->create($container, 'aContextName', array(
            'obtain_credit_card' => false,
            'username' => 'aUsername',
            'password' => 'aPassword',
            'signature' => 'aSignature',
            'sandbox' => true,
            'actions' => array(),
            'apis' => array(),
            'extensions' => array(),
        ));

        $actionDefinition = $container->getDefinition($actionId);

        $tagAttributes = $actionDefinition->getTag('payum.action');
        $this->assertCount(1, $tagAttributes);
        $this->assertEquals($factory->getName(), $tagAttributes[0]['factory']);
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