<?php
namespace Payum\Bundle\PayumBundle\Tests\DependencyInjection\Factory\Gateway;

use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Gateway\KlarnaInvoiceGatewayFactory;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\Kernel;

class KlarnaInvoiceGatewayFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfAbstractGatewayFactory()
    {
        $rc = new \ReflectionClass('Payum\Bundle\PayumBundle\DependencyInjection\Factory\Gateway\KlarnaInvoiceGatewayFactory');

        $this->assertTrue($rc->isSubclassOf('Payum\Bundle\PayumBundle\DependencyInjection\Factory\Gateway\AbstractGatewayFactory'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new KlarnaInvoiceGatewayFactory;
    }

    /**
     * @test
     */
    public function shouldAllowGetName()
    {
        $factory = new KlarnaInvoiceGatewayFactory;

        $this->assertEquals('klarna_invoice', $factory->getName());
    }

    /**
     * @test
     */
    public function shouldAllowAddConfiguration()
    {
        $factory = new KlarnaInvoiceGatewayFactory;

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

        //come from abstract gateway factory
        $this->assertArrayHasKey('actions', $config);
        $this->assertArrayHasKey('apis', $config);
        $this->assertArrayHasKey('extensions', $config);
    }

    /**
     * @test
     */
    public function shouldAllowAddConfigurationAndOverwriteDefaults()
    {
        $factory = new KlarnaInvoiceGatewayFactory;

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

        //come from abstract gateway factory
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
        $factory = new KlarnaInvoiceGatewayFactory;

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
        $factory = new KlarnaInvoiceGatewayFactory;

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
    public function shouldAllowCreateGatewayAndReturnItsId()
    {
        $factory = new KlarnaInvoiceGatewayFactory;

        $container = new ContainerBuilder;

        $gatewayId = $factory->create($container, 'aGatewayName', array(
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

        $this->assertEquals('payum.klarna_invoice.aGatewayName.gateway', $gatewayId);
        $this->assertTrue($container->hasDefinition($gatewayId));

        $gateway = $container->getDefinition($gatewayId);

        //guard
        $this->assertNotEmpty($gateway->getFactory());
        $this->assertNotEmpty($gateway->getArguments());

        $config = $gateway->getArgument(0);

        $this->assertEquals('aGatewayName', $config['payum.gateway_name']);
    }

    /**
     * @test
     */
    public function shouldLoadFactory()
    {
        $factory = new KlarnaInvoiceGatewayFactory;

        $container = new ContainerBuilder;

        $factory->load($container);

        $this->assertTrue($container->hasDefinition('payum.klarna_invoice.factory'));

        $factoryService = $container->getDefinition('payum.klarna_invoice.factory');
        $this->assertEquals('Payum\Klarna\Invoice\KlarnaInvoiceGatewayFactory', $factoryService->getClass());
        $this->assertEquals(
            array(array('factory_name' => 'klarna_invoice', 'human_name' => 'Klarna Invoice')),
            $factoryService->getTag('payum.gateway_factory')
        );

        $factoryConfig = $factoryService->getArgument(0);
        $this->assertEquals('klarna_invoice', $factoryConfig['payum.factory_name']);
        $this->assertArrayHasKey('payum.http_client', $factoryConfig);
        $this->assertArrayHasKey('twig.env', $factoryConfig);
        $this->assertArrayHasKey('payum.iso4217', $factoryConfig);
        $this->assertArrayHasKey('payum.template.layout', $factoryConfig);
        $this->assertArrayHasKey('payum.template.obtain_credit_card', $factoryConfig);

        $this->assertInstanceOf('Symfony\Component\DependencyInjection\Reference', $factoryService->getArgument(1));
        $this->assertEquals('payum.gateway_factory', (string) $factoryService->getArgument(1));
    }

    /**
     * @test
     */
    public function shouldCallParentsCreateMethod()
    {
        $factory = new KlarnaInvoiceGatewayFactory;

        $container = new ContainerBuilder;

        $gatewayId = $factory->create($container, 'aGatewayName', array(
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
            $container->getDefinition($gatewayId),
            'addAction',
            new Reference('payum.action.foo')
        );
        $this->assertDefinitionContainsMethodCall(
            $container->getDefinition($gatewayId),
            'addApi',
            new Reference('payum.api.bar')
        );
        $this->assertDefinitionContainsMethodCall(
            $container->getDefinition($gatewayId),
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