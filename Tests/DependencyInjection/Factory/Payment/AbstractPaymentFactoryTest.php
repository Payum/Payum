<?php
namespace Payum\Bundle\PayumBundle\Tests\DependencyInjection\Factory\Payment;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment\AbstractPaymentFactory;

class AbstractPaymentFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementPaymentFactoryInterface()
    {
        $rc = new \ReflectionClass('Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment\AbstractPaymentFactory');
        
        $this->assertTrue($rc->implementsInterface('Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment\PaymentFactoryInterface'));
    }

    /**
     * @test
     */
    public function shouldBeAbstract()
    {
        $rc = new \ReflectionClass('Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment\AbstractPaymentFactory');

        $this->assertTrue($rc->isAbstract());
    }

    /**
     * @test
     */
    public function shouldAllowAddConfiguration()
    {
        $factory = $this->createAbstractPaymentFactory();

        $tb = new TreeBuilder();
        $rootNode = $tb->root('foo');
        
        $factory->addConfiguration($rootNode);

        $processor = new Processor();
        $processor->process($tb->buildTree(), array());
    }

    /**
     * @test
     */
    public function shouldAllowConfigureCustomActions()
    {
        $factory = $this->createAbstractPaymentFactory();

        $tb = new TreeBuilder();
        $rootNode = $tb->root('foo');

        $factory->addConfiguration($rootNode);

        $processor = new Processor();
        
        $config = $processor->process($tb->buildTree(), array());        
        $this->assertArrayHasKey('actions', $config);
        $this->assertEmpty($config['actions']);

        $config = $processor->process($tb->buildTree(), array(array(
            'actions' => array(
                'payum.action.foo',
                'payum.action.bar'
            ))
        ));
        $this->assertArrayHasKey('actions', $config);
        $this->assertContains('payum.action.foo', $config['actions']);
        $this->assertContains('payum.action.bar', $config['actions']);
    }

    /**
     * @test
     */
    public function shouldAllowConfigureCustomApis()
    {
        $factory = $this->createAbstractPaymentFactory();

        $tb = new TreeBuilder();
        $rootNode = $tb->root('foo');

        $factory->addConfiguration($rootNode);

        $processor = new Processor();

        $config = $processor->process($tb->buildTree(), array());
        $this->assertArrayHasKey('apis', $config);
        $this->assertEmpty($config['apis']);

        $config = $processor->process($tb->buildTree(), array(array(
            'apis' => array(
                'payum.api.foo',
                'payum.api.bar'
            ))
        ));
        $this->assertArrayHasKey('apis', $config);
        $this->assertContains('payum.api.foo', $config['apis']);
        $this->assertContains('payum.api.bar', $config['apis']);
    }

    /**
     * @test
     */
    public function shouldAllowConfigureCustomExtensions()
    {
        $factory = $this->createAbstractPaymentFactory();

        $tb = new TreeBuilder();
        $rootNode = $tb->root('foo');

        $factory->addConfiguration($rootNode);

        $processor = new Processor();

        $config = $processor->process($tb->buildTree(), array());
        $this->assertArrayHasKey('extensions', $config);
        $this->assertEmpty($config['extensions']);

        $config = $processor->process($tb->buildTree(), array(array(
            'extensions' => array(
                'payum.extension.foo',
                'payum.extension.bar'
            ))
        ));
        $this->assertArrayHasKey('extensions', $config);
        $this->assertContains('payum.extension.foo', $config['extensions']);
        $this->assertContains('payum.extension.bar', $config['extensions']);
    }

    /**
     * @test
     */
    public function shouldAllowCreatePaymentAndReturnItsId()
    {
        $factory = $this->createAbstractPaymentFactory();
        $factory
            ->expects($this->any())
            ->method('getName')
            ->willReturn('foo')
        ;

        $container = new ContainerBuilder;

        $paymentId = $factory->create($container, 'aPaymentName', array(
            'actions' => array(),
            'apis' => array(),
            'extensions' => array(),
        ));
        
        $this->assertEquals('payum.foo.aPaymentName.payment', $paymentId);
        $this->assertTrue($container->hasDefinition($paymentId));
    }

    /**
     * @test
     */
    public function shouldAllowCreatePaymentWithExpectedConfig()
    {
        $factory = $this->createAbstractPaymentFactory();
        $factory
            ->expects($this->any())
            ->method('getName')
            ->willReturn('foo')
        ;

        $container = new ContainerBuilder;

        $paymentId = $factory->create($container, 'aPaymentName', array(
            'actions' => array(),
            'apis' => array(),
            'extensions' => array(),
        ));

        $this->assertEquals('payum.foo.aPaymentName.payment', $paymentId);

        $payment = $container->getDefinition($paymentId);

        //guard
        $this->assertNotEmpty($payment->getFactoryMethod());
        $this->assertNotEmpty($payment->getFactoryService());
        $this->assertNotEmpty($payment->getArguments());

        $config = $payment->getArgument(0);

        $this->assertEquals('aPaymentName', $config['payum.payment_name']);

    }

    /**
     * @test
     */
    public function shouldLoadFactoryAndTemplateParameters()
    {
        $factory = $this->createAbstractPaymentFactory();
        $factory
            ->expects($this->any())
            ->method('getName')
            ->willReturn('foo')
        ;

        $container = new ContainerBuilder;

        $factory->load($container);

        $this->assertTrue($container->hasDefinition('payum.foo.factory'));

        $factoryService = $container->getDefinition('payum.foo.factory');
        $this->assertEquals('Payum\Core\PaymentFactory', $factoryService->getClass());
        $this->assertEquals(array(array('name' => 'foo', 'human_name' => 'Foo')), $factoryService->getTag('payum.payment_factory'));

        $this->assertInstanceOf('Symfony\Component\DependencyInjection\Reference', $factoryService->getArgument(1));
        $this->assertEquals('payum.payment_factory', (string) $factoryService->getArgument(1));

        $this->assertEquals('@PayumCore\layout.html.twig', $container->getParameter('payum.template.layout'));
        $this->assertEquals('@PayumSymfonyBridge\obtainCreditCard.html.twig', $container->getParameter('payum.template.obtain_credit_card'));
    }

    /**
     * @test
     */
    public function shouldAddCustomActions()
    {
        $factory = $this->createAbstractPaymentFactory();

        $container = new ContainerBuilder;

        $paymentId = $factory->create($container, 'aPaymentName', array(
            'actions' => array(
                'payum.action.foo',
                'payum.action.bar',
            ),
            'apis' => array(),
            'extensions' => array(),
        ));

        $this->assertDefinitionContainsMethodCall(
            $container->getDefinition($paymentId), 
            'addAction', 
            new Reference('payum.action.foo')
        );
        $this->assertDefinitionContainsMethodCall(
            $container->getDefinition($paymentId),
            'addAction',
            new Reference('payum.action.bar')
        );
    }

    /**
     * @test
     */
    public function shouldAddCustomApis()
    {
        $factory = $this->createAbstractPaymentFactory();

        $container = new ContainerBuilder;

        $paymentId = $factory->create($container, 'aPaymentName', array(
            'actions' => array(),
            'apis' => array(
                'payum.api.foo',
                'payum.api.bar',
            ),
            'extensions' => array(),
        ));

        $this->assertDefinitionContainsMethodCall(
            $container->getDefinition($paymentId),
            'addApi',
            new Reference('payum.api.foo')
        );
        $this->assertDefinitionContainsMethodCall(
            $container->getDefinition($paymentId),
            'addApi',
            new Reference('payum.api.bar')
        );
    }

    /**
     * @test
     */
    public function shouldAddCustomExtensions()
    {
        $factory = $this->createAbstractPaymentFactory();

        $container = new ContainerBuilder;

        $paymentId = $factory->create($container, 'aPaymentName', array(
            'actions' => array(),
            'apis' => array(),
            'extensions' => array(
                'payum.extension.foo',
                'payum.extension.bar',
            ),
        ));

        $this->assertDefinitionContainsMethodCall(
            $container->getDefinition($paymentId),
            'addExtension',
            new Reference('payum.extension.foo')
        );
        $this->assertDefinitionContainsMethodCall(
            $container->getDefinition($paymentId),
            'addExtension',
            new Reference('payum.extension.bar')
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

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|AbstractPaymentFactory
     */
    protected function createAbstractPaymentFactory()
    {
        return $this->getMockForAbstractClass('Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment\AbstractPaymentFactory');
    }
}