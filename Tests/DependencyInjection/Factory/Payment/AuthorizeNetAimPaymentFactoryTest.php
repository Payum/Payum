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
            'api' => array(
                'options' => array(
                    'login_id' => 'aLoginId',
                    'transaction_key' => 'aTransactionKey',
                )
            )
        )));

        $this->assertArrayHasKey('api', $config);
        $this->assertArrayHasKey('options', $config['api']);
        
        $this->assertArrayHasKey('login_id', $config['api']['options']);
        $this->assertEquals('aLoginId', $config['api']['options']['login_id']);
        
        $this->assertArrayHasKey('transaction_key', $config['api']['options']);
        $this->assertEquals('aTransactionKey', $config['api']['options']['transaction_key']);

        $this->assertArrayHasKey('sandbox', $config['api']['options']);
        $this->assertTrue($config['api']['options']['sandbox']);

        //come from abstract payment factory
        $this->assertArrayHasKey('actions', $config);
        $this->assertArrayHasKey('apis', $config);
        $this->assertArrayHasKey('extensions', $config);
    }

    /**
     * @test
     * 
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage The child node "api" at path "foo" must be configured.
     */
    public function thrownIfApiSectionMissing()
    {
        $factory = new AuthorizeNetAimPaymentFactory;

        $tb = new TreeBuilder();
        $rootNode = $tb->root('foo');

        $factory->addConfiguration($rootNode);

        $processor = new Processor();
        $processor->process($tb->buildTree(), array());
    }

    /**
     * @test
     *
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage The child node "options" at path "foo.api" must be configured.
     */
    public function thrownIfApiOptionsSectionMissing()
    {
        $factory = new AuthorizeNetAimPaymentFactory;

        $tb = new TreeBuilder();
        $rootNode = $tb->root('foo');

        $factory->addConfiguration($rootNode);

        $processor = new Processor();
        $processor->process($tb->buildTree(), array(array(
            'api' => array()
        )));
    }

    /**
     * @test
     *
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage The child node "login_id" at path "foo.api.options" must be configured.
     */
    public function thrownIfApiOptionsLoginIdSectionMissing()
    {
        $factory = new AuthorizeNetAimPaymentFactory;

        $tb = new TreeBuilder();
        $rootNode = $tb->root('foo');

        $factory->addConfiguration($rootNode);

        $processor = new Processor();
        $processor->process($tb->buildTree(), array(array(
            'api' => array(
                'options' => array()
            )
        )));
    }

    /**
     * @test
     *
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage The child node "transaction_key" at path "foo.api.options" must be configured.
     */
    public function thrownIfApiOptionsTransactionKeySectionMissing()
    {
        $factory = new AuthorizeNetAimPaymentFactory;

        $tb = new TreeBuilder();
        $rootNode = $tb->root('foo');

        $factory->addConfiguration($rootNode);

        $processor = new Processor();
        $processor->process($tb->buildTree(), array(array(
                'api' => array(
                    'options' => array(
                        'login_id' => 'aLoginId'
                    )
                )
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
            'api' => array(
                'options' => array(
                    'login_id' => 'aLoginId',
                    'transaction_key' => 'aTransactionKey',
                    'sandbox' => true,
                )
            ),
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
            'api' => array(
                'options' => array(
                    'login_id' => 'aLoginId',
                    'transaction_key' => 'aTransactionKey',
                    'sandbox' => true,
                )
            ),
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
    public function shouldDecorateBasicApiDefinitionAndAddItToPayment()
    {
        $factory = new AuthorizeNetAimPaymentFactory;

        $container = new ContainerBuilder;

        $paymentId = $factory->create($container, 'aContextName', array(
            'api' => array(
                'options' => array(
                    'login_id' => 'aLoginId',
                    'transaction_key' => 'aTransactionKey',
                    'sandbox' => true,
                )
            ),
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
    public function shouldDecorateBasicCaptureActionDefinitionAndAddItToPayment()
    {
        $factory = new AuthorizeNetAimPaymentFactory;

        $container = new ContainerBuilder;

        $paymentId = $factory->create($container, 'aContextName', array(
            'api' => array(
                'options' => array(
                    'login_id' => 'aLoginId',
                    'transaction_key' => 'aTransactionKey',
                    'sandbox' => true,
                )
            ),
            'actions' => array(),
            'apis' => array(),
            'extensions' => array(),
        ));

        $this->assertTrue($container->hasDefinition('payum.context.aContextName.action.capture'));

        $this->assertDefinitionContainsMethodCall(
            $container->getDefinition($paymentId),
            'addAction',
            new Reference('payum.context.aContextName.action.capture')
        );
    }

    /**
     * @test
     */
    public function shouldDecorateBasicStatusActionDefinitionAndAddItToPayment()
    {
        $factory = new AuthorizeNetAimPaymentFactory;

        $container = new ContainerBuilder;

        $paymentId = $factory->create($container, 'aContextName', array(
            'api' => array(
                'options' => array(
                    'login_id' => 'aLoginId',
                    'transaction_key' => 'aTransactionKey',
                    'sandbox' => true,
                )
            ),
            'actions' => array(),
            'apis' => array(),
            'extensions' => array(),
        ));

        $this->assertTrue($container->hasDefinition('payum.context.aContextName.action.status'));

        $this->assertDefinitionContainsMethodCall(
            $container->getDefinition($paymentId),
            'addAction',
            new Reference('payum.context.aContextName.action.status')
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