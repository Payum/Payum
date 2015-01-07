<?php
namespace Payum\Bundle\PayumBundle\Tests\DependencyInjection\Factory\Payment;

use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment\KlarnaInvoicePaymentFactory;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class KlarnaInvoicePaymentFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfAbstractPaymentFactory()
    {
        $rc = new \ReflectionClass('Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment\KlarnaInvoicePaymentFactory');

        $this->assertTrue($rc->isSubclassOf('Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment\AbstractPaymentFactory'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new KlarnaInvoicePaymentFactory;
    }

    /**
     * @test
     */
    public function shouldAllowGetName()
    {
        $factory = new KlarnaInvoicePaymentFactory;

        $this->assertEquals('klarna_invoice', $factory->getName());
    }

    /**
     * @test
     */
    public function shouldAllowAddConfiguration()
    {
        $factory = new KlarnaInvoicePaymentFactory;

        $tb = new TreeBuilder();
        $rootNode = $tb->root('foo');
        
        $factory->addConfiguration($rootNode);

        $processor = new Processor();
        $config = $processor->process($tb->buildTree(), array(array(
            'eid' => 'aEid',
            'secret' => 'aSecret',
        )));

        $this->assertArrayHasKey('eid', $config);
        $this->assertEquals('aEid', $config['eid']);
        
        $this->assertArrayHasKey('secret', $config);
        $this->assertEquals('aSecret', $config['secret']);

        $this->assertArrayHasKey('country', $config);
        $this->assertEquals('SE', $config['country']);

        $this->assertArrayHasKey('language', $config);
        $this->assertEquals('SV', $config['language']);

        $this->assertArrayHasKey('currency', $config);
        $this->assertEquals('SEK', $config['currency']);

        $this->assertArrayHasKey('sandbox', $config);
        $this->assertTrue($config['sandbox']);

        //come from abstract payment factory
        $this->assertArrayHasKey('actions', $config);
        $this->assertArrayHasKey('apis', $config);
        $this->assertArrayHasKey('extensions', $config);
    }

    /**
     * @test
     */
    public function shouldAllowAddConfigurationAndOverwriteDefaults()
    {
        $factory = new KlarnaInvoicePaymentFactory;

        $tb = new TreeBuilder();
        $rootNode = $tb->root('foo');

        $factory->addConfiguration($rootNode);

        $processor = new Processor();
        $config = $processor->process($tb->buildTree(), array(array(
            'eid' => 'aEid',
            'secret' => 'aSecret',
            'country' => 'USA',
            'language' => 'ENG',
            'currency' => 'USD',
        )));

        $this->assertArrayHasKey('eid', $config);
        $this->assertEquals('aEid', $config['eid']);

        $this->assertArrayHasKey('secret', $config);
        $this->assertEquals('aSecret', $config['secret']);

        $this->assertArrayHasKey('country', $config);
        $this->assertEquals('USA', $config['country']);

        $this->assertArrayHasKey('language', $config);
        $this->assertEquals('ENG', $config['language']);

        $this->assertArrayHasKey('currency', $config);
        $this->assertEquals('USD', $config['currency']);

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
     * @expectedExceptionMessage The child node "eid" at path "foo" must be configured.
     */
    public function thrownIfEIDOptionNotSet()
    {
        $factory = new KlarnaInvoicePaymentFactory;

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
     * @expectedExceptionMessage The child node "secret" at path "foo" must be configured.
     */
    public function thrownIfSecretOptionNotSet()
    {
        $factory = new KlarnaInvoicePaymentFactory;

        $tb = new TreeBuilder();
        $rootNode = $tb->root('foo');

        $factory->addConfiguration($rootNode);

        $processor = new Processor();
        $processor->process($tb->buildTree(), array(array(
            'eid' => 'aEid',
        )));
    }

    /**
     * @test
     */
    public function shouldAllowCreatePaymentAndReturnItsId()
    {
        $factory = new KlarnaInvoicePaymentFactory;

        $container = new ContainerBuilder;

        $paymentId = $factory->create($container, 'aPaymentName', array(
            'eid' => 'aEid',
            'secret' => 'aSecret',
            'country' => 'SV',
            'language' => 'SE',
            'currency' => 'SEK',
            'sandbox' => true,
            'actions' => array(),
            'apis' => array(),
            'extensions' => array(),
        ));

        $this->assertEquals('payum.klarna_invoice.aPaymentName.payment', $paymentId);
        $this->assertTrue($container->hasDefinition($paymentId));
    }

    /**
     * @test
     */
    public function shouldLoadFactory()
    {
        $factory = new KlarnaInvoicePaymentFactory;

        $container = new ContainerBuilder;

        $factory->load($container);

        $this->assertTrue($container->hasDefinition('payum.klarna_invoice.factory'));

        $factoryService = $container->getDefinition('payum.klarna_invoice.factory');
        $this->assertEquals('Payum\Klarna\Invoice\PaymentFactory', $factoryService->getClass());
        $this->assertEquals(array(array('name' => 'klarna_invoice')), $factoryService->getTag('payum.payment_factory'));

        $this->assertInstanceOf('Symfony\Component\DependencyInjection\Reference', $factoryService->getArgument(0));
        $this->assertEquals('payum.payment_factory', (string) $factoryService->getArgument(0));
    }

    /**
     * @test
     */
    public function shouldCallParentsCreateMethod()
    {
        $factory = new KlarnaInvoicePaymentFactory;

        $container = new ContainerBuilder;

        $paymentId = $factory->create($container, 'aPaymentName', array(
            'eid' => 'aEid',
            'secret' => 'aSecret',
            'country' => 'SV',
            'language' => 'SE',
            'currency' => 'SEK',
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