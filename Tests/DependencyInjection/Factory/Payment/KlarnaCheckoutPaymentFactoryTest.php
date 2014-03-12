<?php
namespace Payum\Bundle\PayumBundle\Tests\DependencyInjection\Factory\Payment;

use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment\KlarnaCheckoutPaymentFactory;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class KlarnaCheckoutPaymentFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfAbstractPaymentFactory()
    {
        $rc = new \ReflectionClass('Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment\KlarnaCheckoutPaymentFactory');

        $this->assertTrue($rc->isSubclassOf('Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment\AbstractPaymentFactory'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new KlarnaCheckoutPaymentFactory;
    }

    /**
     * @test
     */
    public function shouldAllowGetName()
    {
        $factory = new KlarnaCheckoutPaymentFactory;

        $this->assertEquals('klarna_checkout', $factory->getName());
    }

    /**
     * @test
     */
    public function shouldAllowAddConfiguration()
    {
        $factory = new KlarnaCheckoutPaymentFactory;

        $tb = new TreeBuilder();
        $rootNode = $tb->root('foo');
        
        $factory->addConfiguration($rootNode);

        $processor = new Processor();
        $config = $processor->process($tb->buildTree(), array(array(
            'api' => array(
                'options' => array(
                    'secret' => 'aSecret',
                    'merchant_id' => 'aMerchantId',
                )
            )
        )));

        $this->assertArrayHasKey('api', $config);
        $this->assertArrayHasKey('options', $config['api']);
        
        $this->assertArrayHasKey('secret', $config['api']['options']);
        $this->assertEquals('aSecret', $config['api']['options']['secret']);
        
        $this->assertArrayHasKey('merchant_id', $config['api']['options']);
        $this->assertEquals('aMerchantId', $config['api']['options']['merchant_id']);

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
        $factory = new KlarnaCheckoutPaymentFactory;

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
        $factory = new KlarnaCheckoutPaymentFactory;

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
     * @expectedExceptionMessage The child node "secret" at path "foo.api.options" must be configured.
     */
    public function thrownIfApiOptionsIdentifierSectionMissing()
    {
        $factory = new KlarnaCheckoutPaymentFactory;

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
     * @expectedExceptionMessage The child node "merchant_id" at path "foo.api.options" must be configured.
     */
    public function thrownIfApiOptionsPasswordSectionMissing()
    {
        $factory = new KlarnaCheckoutPaymentFactory;

        $tb = new TreeBuilder();
        $rootNode = $tb->root('foo');

        $factory->addConfiguration($rootNode);

        $processor = new Processor();
        $processor->process($tb->buildTree(), array(array(
            'api' => array(
                'options' => array(
                    'secret' => 'aSecret'
                )
            )
        )));
    }

    /**
     * @test
     */
    public function shouldAllowCreatePaymentAndReturnItsId()
    {
        $factory = new KlarnaCheckoutPaymentFactory;

        $container = new ContainerBuilder;

        $paymentId = $factory->create($container, 'aContextName', array(
            'api' => array(
                'client' => 'foo',
                'options' => array(
                    'secret' => 'aSecret',
                    'merchant_id' => 'aMerchantId',
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
        $factory = new KlarnaCheckoutPaymentFactory;

        $container = new ContainerBuilder;

        $paymentId = $factory->create($container, 'aContextName', array(
            'api' => array(
                'client' => 'foo',
                'options' => array(
                    'secret' => 'aSecret',
                    'merchant_id' => 'aMerchantId',
                    'sandbox' => true,                )
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
        $factory = new KlarnaCheckoutPaymentFactory;

        $container = new ContainerBuilder;

        $paymentId = $factory->create($container, 'aContextName', array(
            'api' => array(
                'client' => 'foo',
                'options' => array(
                    'secret' => 'aSecret',
                    'merchant_id' => 'aMerchantId',
                    'sandbox' => true,
                )
            ),
            'actions' => array(),
            'apis' => array(),
            'extensions' => array(),
        ));

        $this->assertTrue($container->hasDefinition('payum.context.aContextName.connector'));

        $this->assertDefinitionContainsMethodCall(
            $container->getDefinition($paymentId),
            'addApi',
            new Reference('payum.context.aContextName.connector')
        );
    }

    /**
     * @test
     */
    public function shouldDecorateBasicCaptureActionDefinitionAndAddItToPayment()
    {
        $factory = new KlarnaCheckoutPaymentFactory;

        $container = new ContainerBuilder;

        $paymentId = $factory->create($container, 'aContextName', array(
            'api' => array(
                'client' => 'foo',
                'options' => array(
                    'secret' => 'aSecret',
                    'merchant_id' => 'aMerchantId',
                    'sandbox' => true,
                )
            ),
            'actions' => array(),
            'apis' => array(),
            'extensions' => array(),
        ));

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
        $factory = new KlarnaCheckoutPaymentFactory;

        $container = new ContainerBuilder;

        $paymentId = $factory->create($container, 'aContextName', array(
            'api' => array(
                'client' => 'foo',
                'options' => array(
                    'secret' => 'aSecret',
                    'merchant_id' => 'aMerchantId',
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

    /**
     * @test
     */
    public function shouldDecorateBasicSyncActionDefinitionAndAddItToPayment()
    {
        $factory = new KlarnaCheckoutPaymentFactory;

        $container = new ContainerBuilder;

        $paymentId = $factory->create($container, 'aContextName', array(
            'api' => array(
                'client' => 'foo',
                'options' => array(
                    'secret' => 'aSecret',
                    'merchant_id' => 'aMerchantId',
                    'sandbox' => true,
                )
            ),
            'actions' => array(),
            'apis' => array(),
            'extensions' => array(),
        ));

        $this->assertTrue($container->hasDefinition('payum.context.aContextName.action.sync'));

        $this->assertDefinitionContainsMethodCall(
            $container->getDefinition($paymentId),
            'addAction',
            new Reference('payum.context.aContextName.action.sync')
        );
    }

    /**
     * @test
     */
    public function shouldDecorateBasicNotifyActionDefinitionAndAddItToPayment()
    {
        $factory = new KlarnaCheckoutPaymentFactory;

        $container = new ContainerBuilder;

        $paymentId = $factory->create($container, 'aContextName', array(
            'api' => array(
                'client' => 'foo',
                'options' => array(
                    'secret' => 'aSecret',
                    'merchant_id' => 'aMerchantId',
                    'sandbox' => true,
                )
            ),
            'actions' => array(),
            'apis' => array(),
            'extensions' => array(),
        ));

        $this->assertTrue($container->hasDefinition('payum.context.aContextName.action.notify'));

        $this->assertDefinitionContainsMethodCall(
            $container->getDefinition($paymentId),
            'addAction',
            new Reference('payum.context.aContextName.action.notify')
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