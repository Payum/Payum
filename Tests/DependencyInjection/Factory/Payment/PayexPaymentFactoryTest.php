<?php
namespace Payum\Bundle\PayumBundle\Tests\DependencyInjection\Factory\Payment;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment\PayexPaymentFactory;

class PayexPaymentFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfAbstractPaymentFactory()
    {
        $rc = new \ReflectionClass('Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment\PayexPaymentFactory');

        $this->assertTrue($rc->isSubclassOf('Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment\AbstractPaymentFactory'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new PayexPaymentFactory;
    }

    /**
     * @test
     */
    public function shouldAllowGetName()
    {
        $factory = new PayexPaymentFactory;

        $this->assertEquals('payex', $factory->getName());
    }

    /**
     * @test
     */
    public function shouldAllowAddConfiguration()
    {
        $factory = new PayexPaymentFactory;

        $tb = new TreeBuilder();
        $rootNode = $tb->root('foo');
        
        $factory->addConfiguration($rootNode);

        $processor = new Processor();
        $config = $processor->process($tb->buildTree(), array(array(
            'encryption_key' => 'aKey',
            'account_number' => 'aNum',
            'sandbox' => true,
        )));
        
        $this->assertArrayHasKey('encryption_key', $config);
        $this->assertEquals('aKey', $config['encryption_key']);

        $this->assertArrayHasKey('account_number', $config);
        $this->assertEquals('aNum', $config['account_number']);

        //come from abstract payment factory
        $this->assertArrayHasKey('actions', $config);
        $this->assertArrayHasKey('apis', $config);
        $this->assertArrayHasKey('extensions', $config);
    }

    /**
     * @test
     *
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage The child node "encryption_key" at path "foo" must be configured.
     */
    public function thrownIfEncryptionKeyOptionNotSet()
    {
        $factory = new PayexPaymentFactory;

        $tb = new TreeBuilder();
        $rootNode = $tb->root('foo');

        $factory->addConfiguration($rootNode);

        $processor = new Processor();
        $processor->process($tb->buildTree(), array(array(
        )));
    }

    /**
     * @test
     *
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage The child node "account_number" at path "foo" must be configured.
     */
    public function thrownIfAccountNumberOptionNotSet()
    {
        $factory = new PayexPaymentFactory;

        $tb = new TreeBuilder();
        $rootNode = $tb->root('foo');

        $factory->addConfiguration($rootNode);

        $processor = new Processor();
        $processor->process($tb->buildTree(), array(array(
            'encryption_key' => 'aKey'
        )));
    }

    /**
     * @test
     */
    public function shouldAllowCreatePaymentAndReturnItsId()
    {
        $factory = new PayexPaymentFactory;

        $container = new ContainerBuilder;

        $paymentId = $factory->create($container, 'aPaymentName', array(
            'encryption_key' => 'aKey',
            'account_number' => 'aNum',
            'sandbox' => true,
            'actions' => array(),
            'apis' => array(),
            'extensions' => array(),
        ));
        
        $this->assertEquals('payum.payex.aPaymentName.payment', $paymentId);
        $this->assertTrue($container->hasDefinition($paymentId));
    }

    /**
     * @test
     */
    public function shouldLoadFactory()
    {
        $factory = new PayexPaymentFactory;

        $container = new ContainerBuilder;

        $factory->load($container);

        $this->assertTrue($container->hasDefinition('payum.payex.factory'));

        $factoryService = $container->getDefinition('payum.payex.factory');
        $this->assertEquals('Payum\Payex\PaymentFactory', $factoryService->getClass());
        $this->assertEquals(array(array('name' => 'payex')), $factoryService->getTag('payum.payment_factory'));

        $this->assertInstanceOf('Symfony\Component\DependencyInjection\Reference', $factoryService->getArgument(0));
        $this->assertEquals('payum.payment_factory', (string) $factoryService->getArgument(0));
    }

    /**
     * @test
     */
    public function shouldCallParentsCreateMethod()
    {
        $factory = new PayexPaymentFactory;

        $container = new ContainerBuilder;

        $paymentId = $factory->create($container, 'aPaymentName', array(
            'encryption_key' => 'aKey',
            'account_number' => 'aNum',
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