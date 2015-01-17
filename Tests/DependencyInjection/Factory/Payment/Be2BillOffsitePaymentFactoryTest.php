<?php
namespace Payum\Bundle\PayumBundle\Tests\DependencyInjection\Factory\Payment;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment\Be2BillOffsitePaymentFactory;

class Be2BillOffsitePaymentFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfAbstractPaymentFactory()
    {
        $rc = new \ReflectionClass('Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment\Be2BillOffsitePaymentFactory');

        $this->assertTrue($rc->isSubclassOf('Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment\AbstractPaymentFactory'));
    }

    /**
     * @test
     */
    public function shouldBeSubClassOfBe2BillDirectPaymentFactory()
    {
        $rc = new \ReflectionClass('Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment\Be2BillOffsitePaymentFactory');

        $this->assertTrue($rc->isSubclassOf('Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment\Be2BillDirectPaymentFactory'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new Be2BillOffsitePaymentFactory;
    }

    /**
     * @test
     */
    public function shouldAllowGetName()
    {
        $factory = new Be2BillOffsitePaymentFactory;

        $this->assertEquals('be2bill_offsite', $factory->getName());
    }

    /**
     * @test
     */
    public function shouldAllowAddConfiguration()
    {
        $factory = new Be2BillOffsitePaymentFactory;

        $tb = new TreeBuilder();
        $rootNode = $tb->root('foo');

        $factory->addConfiguration($rootNode);

        $processor = new Processor();
        $config = $processor->process($tb->buildTree(), array(array(
            'identifier' => 'anIdentifier',
            'password' => 'aPassword',
        )));

        $this->assertArrayHasKey('identifier', $config);
        $this->assertEquals('anIdentifier', $config['identifier']);

        $this->assertArrayHasKey('password', $config);
        $this->assertEquals('aPassword', $config['password']);

        $this->assertArrayHasKey('sandbox', $config);
        $this->assertTrue($config['sandbox']);

        //come from abstract payment factory
        $this->assertArrayHasKey('actions', $config);
        $this->assertArrayHasKey('apis', $config);
        $this->assertArrayHasKey('extensions', $config);
    }

    /**
     * @test
     *
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage The child node "identifier" at path "foo" must be configured.
     */
    public function thrownIfIdentifierOptionNotSet()
    {
        $factory = new Be2BillOffsitePaymentFactory;

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
    public function thrownIfPasswordOptionNotSet()
    {
        $factory = new Be2BillOffsitePaymentFactory;

        $tb = new TreeBuilder();
        $rootNode = $tb->root('foo');

        $factory->addConfiguration($rootNode);

        $processor = new Processor();
        $processor->process($tb->buildTree(), array(array(
            'identifier' => 'anIdentifier'
        )));
    }

    /**
     * @test
     */
    public function shouldAllowCreatePaymentAndReturnItsId()
    {
        $factory = new Be2BillOffsitePaymentFactory;

        $container = new ContainerBuilder;

        $paymentId = $factory->create($container, 'aPaymentName', array(
            'identifier' => 'anIdentifier',
            'password' => 'aPassword',
            'sandbox' => true,
            'actions' => array(),
            'apis' => array(),
            'extensions' => array(),
        ));

        $this->assertEquals('payum.be2bill_offsite.aPaymentName.payment', $paymentId);
        $this->assertTrue($container->hasDefinition($paymentId));

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
    public function shouldLoadFactory()
    {
        $factory = new Be2BillOffsitePaymentFactory;

        $container = new ContainerBuilder;

        $factory->load($container);

        $this->assertTrue($container->hasDefinition('payum.be2bill_offsite.factory'));

        $factoryService = $container->getDefinition('payum.be2bill_offsite.factory');
        $this->assertEquals('Payum\Be2Bill\OffsitePaymentFactory', $factoryService->getClass());
        $this->assertEquals(
            array(array('name' => 'be2bill_offsite', 'human_name' => 'Be2bill Offsite')),
            $factoryService->getTag('payum.payment_factory')
        );

        $factoryConfig = $factoryService->getArgument(0);
        $this->assertEquals('be2bill_offsite', $factoryConfig['payum.factory_name']);
        $this->assertArrayHasKey('buzz.client', $factoryConfig);
        $this->assertArrayHasKey('twig.env', $factoryConfig);
        $this->assertArrayHasKey('payum.template.layout', $factoryConfig);
        $this->assertArrayHasKey('payum.template.obtain_credit_card', $factoryConfig);

        $this->assertInstanceOf('Symfony\Component\DependencyInjection\Reference', $factoryService->getArgument(1));
        $this->assertEquals('payum.payment_factory', (string) $factoryService->getArgument(1));
    }

    /**
     * @test
     */
    public function shouldCallParentsCreateMethod()
    {
        $factory = new Be2BillOffsitePaymentFactory;

        $container = new ContainerBuilder;

        $paymentId = $factory->create($container, 'aPaymentName', array(
            'identifier' => 'anIdentifier',
            'password' => 'aPassword',
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