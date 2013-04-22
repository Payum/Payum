<?php
namespace Payum\Bundle\PayumBundle\Tests\DependencyInjection\Factory\Payment;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment\PaypalProCheckoutNvpPaymentFactory;

class PaypalProCheckoutNvpPaymentFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfAbstractPaymentFactory()
    {
        $rc = new \ReflectionClass('Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment\PaypalProCheckoutNvpPaymentFactory');

        $this->assertTrue($rc->isSubclassOf('Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment\AbstractPaymentFactory'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new PaypalProCheckoutNvpPaymentFactory;
    }

    /**
     * @test
     */
    public function shouldAllowGetName()
    {
        $factory = new PaypalProCheckoutNvpPaymentFactory;

        $this->assertEquals('paypal_pro_checkout_nvp', $factory->getName());
    }

    /**
     * @test
     */
    public function shouldAllowAddConfiguration()
    {
        $factory = new PaypalProCheckoutNvpPaymentFactory;

        $tb = new TreeBuilder();
        $rootNode = $tb->root('foo');
        
        $factory->addConfiguration($rootNode);

        $processor = new Processor();
        $config = $processor->process($tb->buildTree(), array(array(
            'api' => array( 
                'options' => array(
                    'username' => 'aUsername',
                    'password' => 'aPassword',
                    'partner' => 'aPartner',
                    'vendor' => 'aVendor'
                )
            )
        )));
        
        $this->assertArrayHasKey('api', $config);
        
        $this->assertArrayHasKey('options', $config['api']);
        $this->assertArrayHasKey('client', $config['api']);
        
        $this->assertArrayHasKey('username', $config['api']['options']);
        $this->assertEquals('aUsername', $config['api']['options']['username']);

        $this->assertArrayHasKey('password', $config['api']['options']);
        $this->assertEquals('aPassword', $config['api']['options']['password']);

        $this->assertArrayHasKey('partner', $config['api']['options']);
        $this->assertEquals('aPartner', $config['api']['options']['partner']);

        $this->assertArrayHasKey('vendor', $config['api']['options']);
        $this->assertEquals('aVendor', $config['api']['options']['vendor']);

        $this->assertArrayHasKey('tender', $config['api']['options']);
        $this->assertArrayHasKey('trxtype', $config['api']['options']);
        $this->assertArrayHasKey('sandbox', $config['api']['options']);

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
        $factory = new PaypalProCheckoutNvpPaymentFactory;

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
        $factory = new PaypalProCheckoutNvpPaymentFactory;

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
     * @expectedExceptionMessage The child node "username" at path "foo.api.options" must be configured.
     */
    public function thrownIfApiOptionUsernameSectionMissing()
    {
        $factory = new PaypalProCheckoutNvpPaymentFactory;

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
     * @expectedExceptionMessage The child node "password" at path "foo.api.options" must be configured.
     */
    public function thrownIfApiOptionPasswordSectionMissing()
    {
        $factory = new PaypalProCheckoutNvpPaymentFactory;

        $tb = new TreeBuilder();
        $rootNode = $tb->root('foo');

        $factory->addConfiguration($rootNode);

        $processor = new Processor();
        $processor->process($tb->buildTree(), array(array(
            'api' => array(
                'options' => array(
                    'username' => 'aUsername'
                )
            )
        )));
    }

    /**
     * @test
     *
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage The child node "partner" at path "foo.api.options" must be configured.
     */
    public function thrownIfApiOptionPartnerSectionMissing()
    {
        $factory = new PaypalProCheckoutNvpPaymentFactory;

        $tb = new TreeBuilder();
        $rootNode = $tb->root('foo');

        $factory->addConfiguration($rootNode);

        $processor = new Processor();
        $processor->process($tb->buildTree(), array(array(
            'api' => array(
                'options' => array(
                    'username' => 'aUsername',
                    'password' => 'aPassword',
                )
            )
        )));
    }

    /**
     * @test
     *
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage The child node "vendor" at path "foo.api.options" must be configured.
     */
    public function thrownIfApiOptionVendorSectionMissing()
    {
        $factory = new PaypalProCheckoutNvpPaymentFactory;

        $tb = new TreeBuilder();
        $rootNode = $tb->root('foo');

        $factory->addConfiguration($rootNode);

        $processor = new Processor();
        $processor->process($tb->buildTree(), array(array(
            'api' => array(
                'options' => array(
                    'username' => 'aUsername',
                    'password' => 'aPassword',
                    'partner' => 'aPartner',
                )
            )
        )));
    }

    /**
     * @test
     */
    public function shouldAllowCreatePaymentAndReturnItsId()
    {
        $factory = new PaypalProCheckoutNvpPaymentFactory;

        $container = new ContainerBuilder;

        $paymentId = $factory->create($container, 'aContextName', array(
            'api' => array(
                'client' => 'foo',
                'options' => array(
                    'username' => 'aUsername',
                    'password' => 'aPassword',
                    'partner' => 'aPartner',
                    'vendor' => 'aVendor'
                ),
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
        $factory = new PaypalProCheckoutNvpPaymentFactory;

        $container = new ContainerBuilder;

        $paymentId = $factory->create($container, 'aContextName', array(
            'api' => array(
                'client' => 'foo',
                'options' => array(
                    'username' => 'aUsername',
                    'password' => 'aPassword',
                    'partner' => 'aPartner',
                    'vendor' => 'aVendor'
                ),
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
        $factory = new PaypalProCheckoutNvpPaymentFactory;

        $container = new ContainerBuilder;

        $paymentId = $factory->create($container, 'aContextName', array(
            'api' => array(
                'client' => 'foo',
                'options' => array(
                    'username' => 'aUsername',
                    'password' => 'aPassword',
                    'partner' => 'aPartner',
                    'vendor' => 'aVendor'
                ),
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
        $factory = new PaypalProCheckoutNvpPaymentFactory;

        $container = new ContainerBuilder;

        $paymentId = $factory->create($container, 'aContextName', array(
            'api' => array(
                'client' => 'foo',
                'options' => array(
                    'username' => 'aUsername',
                    'password' => 'aPassword',
                    'partner' => 'aPartner',
                    'vendor' => 'aVendor'
                ),
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
        $factory = new PaypalProCheckoutNvpPaymentFactory;

        $container = new ContainerBuilder;

        $paymentId = $factory->create($container, 'aContextName', array(
            'api' => array(
                'client' => 'foo',
                'options' => array(
                    'username' => 'aUsername',
                    'password' => 'aPassword',
                    'partner' => 'aPartner',
                    'vendor' => 'aVendor'
                ),
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