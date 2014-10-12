<?php
namespace Payum\Bundle\PayumBundle\Tests\DependencyInjection\Factory\Payment;

use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment\StripeCheckoutPaymentFactory;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class StripeCheckoutPaymentFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfAbstractPaymentFactory()
    {
        $rc = new \ReflectionClass('Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment\StripeCheckoutPaymentFactory');

        $this->assertTrue($rc->isSubclassOf('Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment\AbstractPaymentFactory'));
    }

    /**
     * @test
     */
    public function shouldImplementPrependExtensionInterface()
    {
        $rc = new \ReflectionClass('Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment\StripeCheckoutPaymentFactory');

        $this->assertTrue($rc->implementsInterface('Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new StripeCheckoutPaymentFactory;
    }

    /**
     * @test
     */
    public function shouldAllowGetName()
    {
        $factory = new StripeCheckoutPaymentFactory;

        $this->assertEquals('stripe_checkout', $factory->getName());
    }

    /**
     * @test
     */
    public function shouldAllowAddConfiguration()
    {
        $factory = new StripeCheckoutPaymentFactory;

        $tb = new TreeBuilder();
        $rootNode = $tb->root('foo');
        
        $factory->addConfiguration($rootNode);

        $processor = new Processor();
        $config = $processor->process($tb->buildTree(), array(array(
            'publishable_key' => 'thePubKey',
            'secret_key' => 'theSecretKey',
            'obtain_credit_card' => false,
        )));

        $this->assertArrayHasKey('publishable_key', $config);
        $this->assertEquals('thePubKey', $config['publishable_key']);
        
        $this->assertArrayHasKey('secret_key', $config);
        $this->assertEquals('theSecretKey', $config['secret_key']);

        //come from abstract payment factory
        $this->assertArrayHasKey('actions', $config);
        $this->assertArrayHasKey('apis', $config);
        $this->assertArrayHasKey('extensions', $config);
    }

    /**
     * @test
     *
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage The child node "publishable_key" at path "foo" must be configured.
     */
    public function thrownIfPublishableKeyOptionMissed()
    {
        $factory = new StripeCheckoutPaymentFactory;

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
     * @expectedExceptionMessage The child node "secret_key" at path "foo" must be configured.
     */
    public function thrownIfSecretKeyOptionMissed()
    {
        $factory = new StripeCheckoutPaymentFactory;

        $tb = new TreeBuilder();
        $rootNode = $tb->root('foo');

        $factory->addConfiguration($rootNode);

        $processor = new Processor();
        $processor->process($tb->buildTree(), array(array(
            'publishable_key' => 'aPubKey',
        )));
    }

    /**
     * @test
     */
    public function shouldAllowCreatePaymentAndReturnItsId()
    {
        $factory = new StripeCheckoutPaymentFactory;

        $container = new ContainerBuilder;

        $paymentId = $factory->create($container, 'aContextName', array(
            'publishable_key' => 'aPubKey',
            'secret_key' => 'aSecretKey',
            'obtain_credit_card' => false,
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
        $factory = new StripeCheckoutPaymentFactory;

        $container = new ContainerBuilder;

        $paymentId = $factory->create($container, 'aContextName', array(
            'publishable_key' => 'aPubKey',
            'secret_key' => 'aSecretKey',
            'obtain_credit_card' => false,
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
     */
    public function shouldDecorateBasicKeysDefinitionAndAddItToPaymentAsApi()
    {
        $factory = new StripeCheckoutPaymentFactory;

        $container = new ContainerBuilder;

        $paymentId = $factory->create($container, 'aContextName', array(
            'publishable_key' => 'aPubKey',
            'secret_key' => 'aSecretKey',
            'obtain_credit_card' => false,
            'actions' => array(),
            'apis' => array(),
            'extensions' => array(),
        ));

        $this->assertTrue($container->hasDefinition('payum.context.aContextName.keys'));

        $this->assertDefinitionContainsMethodCall(
            $container->getDefinition($paymentId),
            'addApi',
            new Reference('payum.context.aContextName.keys')
        );
    }

    /**
     * @test
     */
    public function shouldAddPayumActionTagToCaptureAction()
    {
        $factory = new StripeCheckoutPaymentFactory;

        $container = new ContainerBuilder;

        $factory->create($container, 'aContextName', array(
            'publishable_key' => 'aPubKey',
            'secret_key' => 'aSecretKey',
            'obtain_credit_card' => false,
            'actions' => array(),
            'apis' => array(),
            'extensions' => array(),
        ));

        $actionDefinition = $container->getDefinition('payum.stripe.action.capture');

        $tagAttributes = $actionDefinition->getTag('payum.action');
        $this->assertCount(2, $tagAttributes);
        $this->assertEquals($factory->getName(), $tagAttributes[1]['factory']);
    }

    /**
     * @test
     */
    public function shouldAddPayumActionTagToStatusAction()
    {
        $factory = new StripeCheckoutPaymentFactory;

        $container = new ContainerBuilder;

        $factory->create($container, 'aContextName', array(
            'publishable_key' => 'aPubKey',
            'secret_key' => 'aSecretKey',
            'obtain_credit_card' => false,
            'actions' => array(),
            'apis' => array(),
            'extensions' => array(),
        ));

        $actionDefinition = $container->getDefinition('payum.stripe.action.status');

        $tagAttributes = $actionDefinition->getTag('payum.action');
        $this->assertCount(2, $tagAttributes);
        $this->assertEquals($factory->getName(), $tagAttributes[1]['factory']);
    }

    /**
     * @test
     */
    public function shouldAddPayumActionTagToApiCreateChargeAction()
    {
        $factory = new StripeCheckoutPaymentFactory;

        $container = new ContainerBuilder;

        $factory->create($container, 'aContextName', array(
            'publishable_key' => 'aPubKey',
            'secret_key' => 'aSecretKey',
            'obtain_credit_card' => false,
            'actions' => array(),
            'apis' => array(),
            'extensions' => array(),
        ));

        $actionDefinition = $container->getDefinition('payum.stripe.action.api.create_charge');

        $tagAttributes = $actionDefinition->getTag('payum.action');
        $this->assertCount(2, $tagAttributes);
        $this->assertEquals($factory->getName(), $tagAttributes[1]['factory']);
    }

    /**
     * @test
     */
    public function shouldAddPayumActionTagToApiObtainCheckoutTokenAction()
    {
        $factory = new StripeCheckoutPaymentFactory;

        $container = new ContainerBuilder;

        $factory->create($container, 'aContextName', array(
            'publishable_key' => 'aPubKey',
            'secret_key' => 'aSecretKey',
            'obtain_credit_card' => false,
            'actions' => array(),
            'apis' => array(),
            'extensions' => array(),
        ));

        $actionDefinition = $container->getDefinition('payum.stripe.action.api.obtain_checkout_token');

        $tagAttributes = $actionDefinition->getTag('payum.action');
        $this->assertCount(1, $tagAttributes);
        $this->assertEquals($factory->getName(), $tagAttributes[0]['factory']);
    }

    /**
     * @test
     */
    public function shouldAddPayumActionTagToFillOrderDetailsAction()
    {
        $factory = new StripeCheckoutPaymentFactory;

        $container = new ContainerBuilder;

        $factory->create($container, 'aContextName', array(
            'publishable_key' => 'aPubKey',
            'secret_key' => 'aSecretKey',
            'obtain_credit_card' => false,
            'actions' => array(),
            'apis' => array(),
            'extensions' => array(),
        ));

        $actionDefinition = $container->getDefinition('payum.stripe.action.fill_order_details');

        $tagAttributes = $actionDefinition->getTag('payum.action');
        $this->assertCount(2, $tagAttributes);
        $this->assertEquals($factory->getName(), $tagAttributes[1]['factory']);
    }

    /**
     * @test
     */
    public function shouldPrependTwigsExtensionConfig()
    {
        $factory = new StripeCheckoutPaymentFactory;

        $container = new ContainerBuilder;

        $factory->prepend($container);

        $twigConfig = $container->getExtensionConfig('twig');

        //guard
        $this->assertTrue(isset($twigConfig[0]['paths']));

        $paths = $twigConfig[0]['paths'];

        $key = array_search('PayumCore', $paths);
        $this->assertFileExists($key);

        $key = array_search('PayumStripe', $paths);
        $this->assertFileExists($key);
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