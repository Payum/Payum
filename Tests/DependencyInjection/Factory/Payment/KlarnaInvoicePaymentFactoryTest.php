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
    public function thrownIfApiOptionsIdentifierSectionMissing()
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
    public function thrownIfApiOptionsPasswordSectionMissing()
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

        $paymentId = $factory->create($container, 'aContextName', array(
            'obtain_credit_card' => false,
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

        $this->assertEquals('payum.context.aContextName.payment', $paymentId);
        $this->assertTrue($container->hasDefinition($paymentId));
    }

    /**
     * @test
     */
    public function shouldCallParentsCreateMethod()
    {
        $factory = new KlarnaInvoicePaymentFactory;

        $container = new ContainerBuilder;

        $paymentId = $factory->create($container, 'aContextName', array(
            'obtain_credit_card' => false,
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

    /**
     * @test
     */
    public function shouldDecorateBasicApiDefinitionAndAddItToPayment()
    {
        $factory = new KlarnaInvoicePaymentFactory;

        $container = new ContainerBuilder;

        $paymentId = $factory->create($container, 'aContextName', array(
            'obtain_credit_card' => false,
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

        $this->assertTrue($container->hasDefinition('payum.context.aContextName.config'));

        $this->assertDefinitionContainsMethodCall(
            $container->getDefinition($paymentId),
            'addApi',
            new Reference('payum.context.aContextName.config')
        );
    }

    /**
     * @test
     */
    public function shouldAddPayumActionTagToCaptureAction()
    {
        $factory = new KlarnaInvoicePaymentFactory;

        $container = new ContainerBuilder;

        $factory->create($container, 'aContextName', array(
            'obtain_credit_card' => false,
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

        $actionDefinition = $container->getDefinition('payum.klarna.invoice.action.capture');

        $tagAttributes = $actionDefinition->getTag('payum.action');
        $this->assertCount(1, $tagAttributes);
        $this->assertEquals($factory->getName(), $tagAttributes[0]['factory']);
    }

    /**
     * @test
     */
    public function shouldAddPayumActionTagToStatusAction()
    {
        $factory = new KlarnaInvoicePaymentFactory;

        $container = new ContainerBuilder;

        $factory->create($container, 'aContextName', array(
            'obtain_credit_card' => false,
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

        $actionDefinition = $container->getDefinition('payum.klarna.invoice.action.status');

        $tagAttributes = $actionDefinition->getTag('payum.action');
        $this->assertCount(1, $tagAttributes);
        $this->assertEquals($factory->getName(), $tagAttributes[0]['factory']);
    }

    /**
     * @test
     */
    public function shouldAddPayumActionTagToSyncAction()
    {
        $factory = new KlarnaInvoicePaymentFactory;

        $container = new ContainerBuilder;

        $factory->create($container, 'aContextName', array(
            'obtain_credit_card' => false,
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

        $actionDefinition = $container->getDefinition('payum.klarna.invoice.action.sync');

        $tagAttributes = $actionDefinition->getTag('payum.action');
        $this->assertCount(1, $tagAttributes);
        $this->assertEquals($factory->getName(), $tagAttributes[0]['factory']);
    }

    /**
     * @test
     */
    public function shouldAddPayumActionTagToAuthorizeAction()
    {
        $factory = new KlarnaInvoicePaymentFactory;

        $container = new ContainerBuilder;

        $factory->create($container, 'aContextName', array(
            'obtain_credit_card' => false,
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

        $actionDefinition = $container->getDefinition('payum.klarna.invoice.action.authorize');

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