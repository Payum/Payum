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
    public function thrownIfApiOptionsLoginIdSectionMissing()
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
    public function thrownIfApiOptionsTransactionKeySectionMissing()
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

        $paymentId = $factory->create($container, 'aContextName', array(
            'obtain_credit_card' => false,
            'login_id' => 'aLoginId',
            'transaction_key' => 'aTransactionKey',
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
        $factory = new AuthorizeNetAimPaymentFactory;

        $container = new ContainerBuilder;

        $paymentId = $factory->create($container, 'aContextName', array(
            'obtain_credit_card' => false,
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

    /**
     * @test
     */
    public function shouldAddPayumActionTagApiDefinitionAndAddItToPayment()
    {
        $factory = new AuthorizeNetAimPaymentFactory;

        $container = new ContainerBuilder;

        $paymentId = $factory->create($container, 'aContextName', array(
            'obtain_credit_card' => false,
            'login_id' => 'aLoginId',
            'transaction_key' => 'aTransactionKey',
            'sandbox' => true,
            'actions' => array(),
            'apis' => array(),
            'extensions' => array(),
        ));

        $this->assertTrue($container->hasDefinition('payum.context.aContextName.api'));

        $this->assertDefinitionContainsMethodCall(
            $container->getDefinition($paymentId),
            'addApi',
            new Reference('payum.context.aContextName.api')
        );
    }

    /**
     * @test
     */
    public function shouldAddPayumActionTagToCaptureAction()
    {
        $factory = new AuthorizeNetAimPaymentFactory;

        $container = new ContainerBuilder;

        $factory->create($container, 'aContextName', array(
            'obtain_credit_card' => false,
            'login_id' => 'aLoginId',
            'transaction_key' => 'aTransactionKey',
            'sandbox' => true,
            'actions' => array(),
            'apis' => array(),
            'extensions' => array(),
        ));

        $actionDefinition = $container->getDefinition('payum.authorize_net_aim.action.capture');

        $tagAttributes = $actionDefinition->getTag('payum.action');
        $this->assertCount(1, $tagAttributes);
        $this->assertEquals($factory->getName(), $tagAttributes[0]['factory']);
    }

    /**
     * @test
     */
    public function shouldAddPayumActionTagToStatusAction()
    {
        $factory = new AuthorizeNetAimPaymentFactory;

        $container = new ContainerBuilder;

        $factory->create($container, 'aContextName', array(
            'obtain_credit_card' => false,
            'login_id' => 'aLoginId',
            'transaction_key' => 'aTransactionKey',
            'sandbox' => true,
            'actions' => array(),
            'apis' => array(),
            'extensions' => array(),
        ));

        $actionDefinition = $container->getDefinition('payum.authorize_net_aim.action.status');

        $tagAttributes = $actionDefinition->getTag('payum.action');
        $this->assertCount(1, $tagAttributes);
        $this->assertEquals($factory->getName(), $tagAttributes[0]['factory']);
    }

    /**
     * @test
     */
    public function shouldAddPayumActionTagToFillOrderDetailsAction()
    {
        $factory = new AuthorizeNetAimPaymentFactory;

        $container = new ContainerBuilder;

        $factory->create($container, 'aContextName', array(
            'obtain_credit_card' => false,
            'login_id' => 'aLoginId',
            'transaction_key' => 'aTransactionKey',
            'sandbox' => true,
            'actions' => array(),
            'apis' => array(),
            'extensions' => array(),
        ));

        $actionDefinition = $container->getDefinition('payum.authorize_net_aim.action.fill_order_details');

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