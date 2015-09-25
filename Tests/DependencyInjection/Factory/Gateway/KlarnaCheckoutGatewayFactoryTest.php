<?php
namespace Payum\Bundle\PayumBundle\Tests\DependencyInjection\Factory\Gateway;

use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Gateway\KlarnaCheckoutGatewayFactory;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\Kernel;

class KlarnaCheckoutGatewayFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfAbstractGatewayFactory()
    {
        $rc = new \ReflectionClass('Payum\Bundle\PayumBundle\DependencyInjection\Factory\Gateway\KlarnaCheckoutGatewayFactory');

        $this->assertTrue($rc->isSubclassOf('Payum\Bundle\PayumBundle\DependencyInjection\Factory\Gateway\AbstractGatewayFactory'));
    }

    /**
     * @test
     */
    public function shouldImplementPrependExtensionInterface()
    {
        $rc = new \ReflectionClass('Payum\Bundle\PayumBundle\DependencyInjection\Factory\Gateway\KlarnaCheckoutGatewayFactory');

        $this->assertTrue($rc->implementsInterface('Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new KlarnaCheckoutGatewayFactory;
    }

    /**
     * @test
     */
    public function shouldAllowGetName()
    {
        $factory = new KlarnaCheckoutGatewayFactory;

        $this->assertEquals('klarna_checkout', $factory->getName());
    }

    /**
     * @test
     */
    public function shouldAllowAddConfiguration()
    {
        $factory = new KlarnaCheckoutGatewayFactory;

        $tb = new TreeBuilder();
        $rootNode = $tb->root('foo');
        
        $factory->addConfiguration($rootNode);

        $processor = new Processor();
        $config = $processor->process($tb->buildTree(), array(array(
            'secret' => 'aSecret',
            'merchant_id' => 'aMerchantId',
        )));

        $this->assertArrayHasKey('secret', $config);
        $this->assertEquals('aSecret', $config['secret']);
        
        $this->assertArrayHasKey('merchant_id', $config);
        $this->assertEquals('aMerchantId', $config['merchant_id']);

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
     * @expectedExceptionMessage The child node "secret" at path "foo" must be configured.
     */
    public function thrownIfIdentifierOptionNotSet()
    {
        $factory = new KlarnaCheckoutGatewayFactory;

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
     * @expectedExceptionMessage The child node "merchant_id" at path "foo" must be configured.
     */
    public function thrownIfPasswordOptionNotSet()
    {
        $factory = new KlarnaCheckoutGatewayFactory;

        $tb = new TreeBuilder();
        $rootNode = $tb->root('foo');

        $factory->addConfiguration($rootNode);

        $processor = new Processor();
        $processor->process($tb->buildTree(), array(array(
            'secret' => 'aSecret'
        )));
    }

    /**
     * @test
     */
    public function shouldAllowCreateGatewayAndReturnItsId()
    {
        $factory = new KlarnaCheckoutGatewayFactory;

        $container = new ContainerBuilder;

        $gatewayId = $factory->create($container, 'aGatewayName', array(
            'secret' => 'aSecret',
            'merchant_id' => 'aMerchantId',
            'sandbox' => true,
            'actions' => array(),
            'apis' => array(),
            'extensions' => array(),
        ));
        
        $this->assertEquals('payum.klarna_checkout.aGatewayName.gateway', $gatewayId);
        $this->assertTrue($container->hasDefinition($gatewayId));
    }

    /**
     * @test
     */
    public function shouldAllowCreateGatewayWithExpectedConfig()
    {
        $factory = new KlarnaCheckoutGatewayFactory;

        $container = new ContainerBuilder;

        $gatewayId = $factory->create($container, 'aGatewayName', array(
            'actions' => array(),
            'apis' => array(),
            'extensions' => array(),
        ));

        $this->assertEquals('payum.klarna_checkout.aGatewayName.gateway', $gatewayId);

        $gateway = $container->getDefinition($gatewayId);

        //guard
        $this->assertNotEmpty($gateway->getFactory());
        $this->assertNotEmpty($gateway->getArguments());

        $config = $gateway->getArgument(0);

        $this->assertEquals('aGatewayName', $config['payum.gateway_name']);

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
    public function shouldLoadFactoryAndTemplateParameters()
    {
        $factory = new KlarnaCheckoutGatewayFactory;

        $container = new ContainerBuilder;

        $factory->load($container);

        $this->assertTrue($container->hasDefinition('payum.klarna_checkout.factory'));

        $factoryService = $container->getDefinition('payum.klarna_checkout.factory');
        $this->assertEquals('Payum\Klarna\Checkout\KlarnaCheckoutGatewayFactory', $factoryService->getClass());
        $this->assertEquals(
            array(array('factory_name' => 'klarna_checkout', 'human_name' => 'Klarna Checkout')),
            $factoryService->getTag('payum.gateway_factory')
        );

        $factoryConfig = $factoryService->getArgument(0);
        $this->assertEquals('klarna_checkout', $factoryConfig['payum.factory_name']);
        $this->assertArrayHasKey('payum.http_client', $factoryConfig);
        $this->assertArrayHasKey('twig.env', $factoryConfig);
        $this->assertArrayHasKey('payum.iso4217', $factoryConfig);
        $this->assertArrayHasKey('payum.template.authorize', $factoryConfig);
        $this->assertArrayHasKey('payum.template.layout', $factoryConfig);
        $this->assertArrayHasKey('payum.template.obtain_credit_card', $factoryConfig);

        $this->assertInstanceOf('Symfony\Component\DependencyInjection\Reference', $factoryService->getArgument(1));
        $this->assertEquals('payum.gateway_factory', (string) $factoryService->getArgument(1));

        $this->assertEquals('@PayumKlarnaCheckout/Action/capture.html.twig', $container->getParameter('payum.klarna_checkout.template.capture'));
    }

    /**
     * @test
     */
    public function shouldCallParentsCreateMethod()
    {
        $factory = new KlarnaCheckoutGatewayFactory;

        $container = new ContainerBuilder;

        $gatewayId = $factory->create($container, 'aGatewayName', array(
            'secret' => 'aSecret',
            'merchant_id' => 'aMerchantId',
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

    /**
     * @test
     */
    public function shouldPrependTwigsExtensionConfig()
    {
        $factory = new KlarnaCheckoutGatewayFactory;

        $container = new ContainerBuilder;

        $factory->prepend($container);

        $twigConfig = $container->getExtensionConfig('twig');

        //guard
        $this->assertTrue(isset($twigConfig[0]['paths']));

        $paths = $twigConfig[0]['paths'];

        $key = array_search('PayumCore', $paths);
        $this->assertFileExists($key);

        $key = array_search('PayumKlarnaCheckout', $paths);
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