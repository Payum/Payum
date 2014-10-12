<?php
namespace Payum\Bundle\PayumBundle\Tests\DependencyInjection\Factory\Payment;

use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment\OfflinePaymentFactory;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class OfflinePaymentFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfAbstractPaymentFactory()
    {
        $rc = new \ReflectionClass('Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment\OfflinePaymentFactory');

        $this->assertTrue($rc->isSubclassOf('Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment\AbstractPaymentFactory'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new OfflinePaymentFactory;
    }

    /**
     * @test
     */
    public function shouldAllowGetName()
    {
        $factory = new OfflinePaymentFactory;

        $this->assertEquals('offline', $factory->getName());
    }

    /**
     * @test
     */
    public function shouldNotRequireAnyConfiguration()
    {
        $factory = new OfflinePaymentFactory;

        $tb = new TreeBuilder();
        $rootNode = $tb->root('foo');
        
        $factory->addConfiguration($rootNode);

        $processor = new Processor();
        $config = $processor->process($tb->buildTree(), array(array(
            'obtain_credit_card' => false,
        )));

        //come from abstract payment factory
        $this->assertArrayHasKey('actions', $config);
        $this->assertArrayHasKey('apis', $config);
        $this->assertArrayHasKey('extensions', $config);
    }

    /**
     * @test
     */
    public function shouldAllowCreatePaymentAndReturnItsId()
    {
        $factory = new OfflinePaymentFactory;

        $container = new ContainerBuilder;

        $paymentId = $factory->create($container, 'aContextName', array(
            'obtain_credit_card' => false,
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
        $factory = new OfflinePaymentFactory;

        $container = new ContainerBuilder;

        $paymentId = $factory->create($container, 'aContextName', array(
            'obtain_credit_card' => false,
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
    public function shouldAddPayumActionTagCaptureAction()
    {
        $factory = new OfflinePaymentFactory;

        $container = new ContainerBuilder;

         $factory->create($container, 'aContextName', array(
             'obtain_credit_card' => false,
             'actions' => array(),
             'apis' => array(),
             'extensions' => array(),
        ));

        $actionDefinition = $container->getDefinition('payum.offline.action.capture');

        $tagAttributes = $actionDefinition->getTag('payum.action');
        $this->assertCount(1, $tagAttributes);
        $this->assertEquals($factory->getName(), $tagAttributes[0]['factory']);
    }

    /**
     * @test
     */
    public function shouldAddPayumActionTagStatusAction()
    {
        $factory = new OfflinePaymentFactory;

        $container = new ContainerBuilder;

        $factory->create($container, 'aContextName', array(
            'obtain_credit_card' => false,
            'actions' => array(),
            'apis' => array(),
            'extensions' => array(),
        ));

        $actionDefinition = $container->getDefinition('payum.offline.action.status');

        $tagAttributes = $actionDefinition->getTag('payum.action');
        $this->assertCount(1, $tagAttributes);
        $this->assertEquals($factory->getName(), $tagAttributes[0]['factory']);
    }

    /**
     * @test
     */
    public function shouldAddPayumActionTagToFillOrderDetailsAction()
    {
        $factory = new OfflinePaymentFactory;

        $container = new ContainerBuilder;

        $factory->create($container, 'aContextName', array(
            'obtain_credit_card' => false,
            'actions' => array(),
            'apis' => array(),
            'extensions' => array(),
        ));

        $actionDefinition = $container->getDefinition('payum.offline.action.fill_order_details');

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