<?php
namespace Payum\Bundle\PayumBundle\Tests\DependencyInjection\Factory\Payment;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment\AuthorizeNetAimPaymentFactory;

class AuthorizeNetAimPaymentFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfAbstractPaymentFactory()
    {
        $rc = new \ReflectionClass('Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment\AuthorizeNetAimPaymentFactory');

        $this->assertTrue($rc->isSubclassOf('Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment\AbstractPaymentFactory'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new AuthorizeNetAimPaymentFactory;
    }

    /**
     * @test
     */
    public function shouldAllowGetName()
    {
        $factory = new AuthorizeNetAimPaymentFactory;

        $this->assertEquals('authorize_net_aim', $factory->getName());
    }

    /**
     * @test
     */
    public function shouldAllowAddConfiguration()
    {
        $factory = new AuthorizeNetAimPaymentFactory;

        $tb = new TreeBuilder();
        $rootNode = $tb->root('foo');
        
        $factory->addConfiguration($rootNode);

        $processor = new Processor();
        $config = $processor->process($tb->buildTree(), array(array(
            'login_id' => 'aLoginId',
            'transaction_key' => 'aTransactionKey',
        )));

        $this->assertArrayHasKey('login_id', $config);
        $this->assertEquals('aLoginId', $config['login_id']);
        
        $this->assertArrayHasKey('transaction_key', $config);
        $this->assertEquals('aTransactionKey', $config['transaction_key']);

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
     * @expectedExceptionMessage The child node "login_id" at path "foo" must be configured.
     */
    public function thrownIfLoginIdOptionNotSet()
    {
        $factory = new AuthorizeNetAimPaymentFactory;

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
     * @expectedExceptionMessage The child node "transaction_key" at path "foo" must be configured.
     */
    public function thrownIfTransactionKeyOptionNotSet()
    {
        $factory = new AuthorizeNetAimPaymentFactory;

        $tb = new TreeBuilder();
        $rootNode = $tb->root('foo');

        $factory->addConfiguration($rootNode);

        $processor = new Processor();
        $processor->process($tb->buildTree(), array(array(
            'login_id' => 'aLoginId'
        )));
    }

    /**
     * @test
     */
    public function shouldAllowCreatePaymentAndReturnItsId()
    {
        $factory = new AuthorizeNetAimPaymentFactory;

        $container = new ContainerBuilder;

        $paymentId = $factory->create($container, 'aPaymentName', array(
            'login_id' => 'aLoginId',
            'transaction_key' => 'aTransactionKey',
            'sandbox' => true,
            'actions' => array(),
            'apis' => array(),
            'extensions' => array(),
        ));
        
        $this->assertEquals('payum.authorize_net_aim.aPaymentName.payment', $paymentId);
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
        $factory = new AuthorizeNetAimPaymentFactory;

        $container = new ContainerBuilder;

        $factory->load($container);

        $this->assertTrue($container->hasDefinition('payum.authorize_net_aim.factory'));

        $factoryService = $container->getDefinition('payum.authorize_net_aim.factory');
        $this->assertEquals('Payum\AuthorizeNet\Aim\PaymentFactory', $factoryService->getClass());
        $this->assertEquals(
            array(array('name' => 'authorize_net_aim', 'human_name' => 'Authorize Net Aim')),
            $factoryService->getTag('payum.payment_factory')
        );

        $factoryConfig = $factoryService->getArgument(0);
        $this->assertEquals('authorize_net_aim', $factoryConfig['payum.factory_name']);
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
        $factory = new AuthorizeNetAimPaymentFactory;

        $container = new ContainerBuilder;

        $paymentId = $factory->create($container, 'aPaymentName', array(
            'login_id' => 'aLoginId',
            'transaction_key' => 'aTransactionKey',
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