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
    public static function provideTaggedActions()
    {
        return array(
            'api.initialize_order' => array('payum.payex.action.api.initialize_order'),
            'api.complete_order' => array('payum.payex.action.api.complete_order'),
            'api.check_order' => array('payum.payex.action.api.check_order'),
            'api.create_agreement' => array('payum.payex.action.api.create_agreement'),
            'api.delete_agreement' => array('payum.payex.action.api.delete_agreement'),
            'api.check_agreement' => array('payum.payex.action.api.check_agreement'),
            'api.autopay_agreement' => array('payum.payex.action.api.autopay_agreement'),
            'api.start_recurring_payment' => array('payum.payex.action.api.start_recurring_payment'),
            'api.stop_recurring_payment' => array('payum.payex.action.api.stop_recurring_payment'),
            'api.check_recurring_payment' => array('payum.payex.action.api.check_recurring_payment'),

            'payment_details_capture' => array('payum.payex.action.payment_details_capture'),
            'fill_order_details' => array('payum.payex.action.fill_order_details'),
            'payment_details_status' => array('payum.payex.action.payment_details_status'),
            'payment_details_sync' => array('payum.payex.action.payment_details_sync'),
            'autopay_payment_details_capture' => array('payum.payex.action.autopay_payment_details_capture'),
            'autopay_payment_details_status' => array('payum.payex.action.autopay_payment_details_status'),
            'agreement_details_status' => array('payum.payex.action.agreement_details_status'),
            'agreement_details_sync' => array('payum.payex.action.agreement_details_sync'),
        );
    }
    
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
            'obtain_credit_card' => false,
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
    public function thrownIfApiOptionEncryptionKeySectionMissing()
    {
        $factory = new PayexPaymentFactory;

        $tb = new TreeBuilder();
        $rootNode = $tb->root('foo');

        $factory->addConfiguration($rootNode);

        $processor = new Processor();
        $processor->process($tb->buildTree(), array(array(
            'obtain_credit_card' => false,
        )));
    }

    /**
     * @test
     *
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage The child node "account_number" at path "foo" must be configured.
     */
    public function thrownIfApiOptionAccountNumberSectionMissing()
    {
        $factory = new PayexPaymentFactory;

        $tb = new TreeBuilder();
        $rootNode = $tb->root('foo');

        $factory->addConfiguration($rootNode);

        $processor = new Processor();
        $processor->process($tb->buildTree(), array(array(
            'obtain_credit_card' => false,
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

        $paymentId = $factory->create($container, 'aContextName', array(
            'obtain_credit_card' => false,
            'encryption_key' => 'aKey',
            'account_number' => 'aNum',
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
        $factory = new PayexPaymentFactory;

        $container = new ContainerBuilder;

        $paymentId = $factory->create($container, 'aContextName', array(
            'obtain_credit_card' => false,
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

    /**
     * @test
     */
    public function shouldAddExpectedApisToPayment()
    {
        $factory = new PayexPaymentFactory;

        $container = new ContainerBuilder;

        $paymentId = $factory->create($container, 'aContextName', array(
            'obtain_credit_card' => false,
            'encryption_key' => 'aKey',
            'account_number' => 'aNum',
            'sandbox' => true,
            'actions' => array(),
            'apis' => array(),
            'extensions' => array(),
        ));

        $this->assertDefinitionContainsMethodCall(
            $container->getDefinition($paymentId),
            'addApi',
            new Reference('payum.context.aContextName.api.order')
        );

        $this->assertDefinitionContainsMethodCall(
            $container->getDefinition($paymentId),
            'addApi',
            new Reference('payum.context.aContextName.api.agreement')
        );

        $this->assertDefinitionContainsMethodCall(
            $container->getDefinition($paymentId),
            'addApi',
            new Reference('payum.context.aContextName.api.recurring')
        );
    }

    /**
     * @test
     * 
     * @dataProvider provideTaggedActions
     */
    public function shouldAddPayumActionTagToActions($actionId)
    {
        $factory = new PayexPaymentFactory;

        $container = new ContainerBuilder;

        $factory->create($container, 'aContextName', array(
            'obtain_credit_card' => false,
            'encryption_key' => 'aKey',
            'account_number' => 'aNum',
            'sandbox' => true,
            'actions' => array(),
            'apis' => array(),
            'extensions' => array(),
        ));

        $actionDefinition = $container->getDefinition($actionId);

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