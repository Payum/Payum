<?php
namespace Payum\Bundle\PayumBundle\Tests;

use Payum\Bundle\PayumBundle\GatewayFactory;
use Payum\Core\HttpClientInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerInterface;

class GatewayFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    protected function setUp()
    {
        $this->container = new Container();
        $this->container->setParameter('payum.template.layout', 'theLayout');
        $this->container->setParameter('payum.template.obtain_credit_card', 'theObtainCreditCardTemplate');
        $this->container->set('payum.http_client', $this->getMock(HttpClientInterface::class));
        $this->container->set('twig', $this->getMock(\Twig_Environment::class, [], [], '', false));
    }

    /**
     * @test
     */
    public function shouldBeSubClassCoreGatewayFactory()
    {
        $rc = new \ReflectionClass('Payum\Bundle\PayumBundle\GatewayFactory');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\CoreGatewayFactory'));
    }

    /**
     * @test
     */
    public function shouldImplementsGatewayFactoryInterface()
    {
        $rc = new \ReflectionClass('Payum\Bundle\PayumBundle\GatewayFactory');

        $this->assertTrue($rc->implementsInterface('Payum\Core\GatewayFactoryInterface'));
    }

    /**
     * @test
     */
    public function shouldImplementContainerAwareInterface()
    {
        $rc = new \ReflectionClass('Payum\Bundle\PayumBundle\GatewayFactory');

        $this->assertTrue($rc->implementsInterface('Symfony\Component\DependencyInjection\ContainerAwareInterface'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithTagsAsArguments()
    {
        new GatewayFactory(array(), array(), array());
    }

    /**
     * @test
     */
    public function shouldAllowSetContainer()
    {
        $factory = new GatewayFactory(array(), array(), array());
        $factory->setContainer($container = new Container());

        $this->assertAttributeSame($container, 'container', $factory);
    }

    /**
     * @test
     */
    public function shouldAllowGetGatewayWithoutAnyAdditionalOptions()
    {
        $factory = new GatewayFactory(array(), array(), array());
        $factory->setContainer($this->container);

        $gateway = $factory->create();

        $this->assertInstanceOf('Payum\Core\GatewayInterface', $gateway);
    }

    /**
     * @test
     */
    public function shouldAddTaggedActionIfGatewayNameMatch()
    {
        $this->container->set('the_action', $this->getMock('Payum\Core\Action\ActionInterface'));

        $factory = new GatewayFactory(array('the_action' => array(
            array('gateway' => 'theGateway'),
        )), array(), array());
        $factory->setContainer($this->container);

        $config = $factory->createConfig(array(
            'payum.gateway_name' => 'theGateway'
        ));

        $this->assertArrayHasKey('payum.action.the_action', $config);
        $this->assertSame($this->container->get('the_action'), $config['payum.action.the_action']);
    }

    /**
     * @test
     */
    public function shouldAddTaggedActionIfFactoryNameMatch()
    {
        $this->container->set('the_action', $this->getMock('Payum\Core\Action\ActionInterface'));

        $factory = new GatewayFactory(array('the_action' => array(
            array('factory' => 'theFactory'),
        )), array(), array());
        $factory->setContainer($this->container);

        $config = $factory->createConfig(array(
            'payum.factory_name' => 'theFactory'
        ));

        $this->assertArrayHasKey('payum.action.the_action', $config);
        $this->assertSame($this->container->get('the_action'), $config['payum.action.the_action']);
    }

    /**
     * @test
     */
    public function shouldAddTaggedActionIfAllAttributeSet()
    {
        $this->container->set('the_action', $this->getMock('Payum\Core\Action\ActionInterface'));

        $factory = new GatewayFactory(array('the_action' => array(
            array('all' => true),
        )), array(), array());
        $factory->setContainer($this->container);

        $config = $factory->createConfig();

        $this->assertArrayHasKey('payum.action.the_action', $config);
        $this->assertSame($this->container->get('the_action'), $config['payum.action.the_action']);
    }

    /**
     * @test
     */
    public function shouldAddTaggedActionWithAlias()
    {
        $this->container->set('the_action', $this->getMock('Payum\Core\Action\ActionInterface'));

        $factory = new GatewayFactory(array('the_action' => array(
            array('all' => true, 'alias' => 'the_action_alias'),
        )), array(), array());
        $factory->setContainer($this->container);

        $config = $factory->createConfig();

        $this->assertArrayHasKey('payum.action.the_action_alias', $config);
        $this->assertSame($this->container->get('the_action'), $config['payum.action.the_action_alias']);
    }

    /**
     * @test
     */
    public function shouldAddTaggedActionWithPrepend()
    {
        $this->container->set('the_action', $this->getMock('Payum\Core\Action\ActionInterface'));

        $factory = new GatewayFactory(array('the_action' => array(
            array('all' => true, 'prepend' => true),
        )), array(), array());
        $factory->setContainer($this->container);

        $config = $factory->createConfig();

        //guard
        $this->assertArrayHasKey('payum.action.the_action', $config);

        $this->assertContains('payum.action.the_action', $config['payum.prepend_actions']);
    }

    /**
     * @test
     */
    public function shouldAddTaggedActionWithSeveralTags()
    {
        $this->container->set('the_action', $this->getMock('Payum\Core\Action\ActionInterface'));

        $factory = new GatewayFactory(array('the_action' => array(
            array('factory' => 'theFactory'),
            array('gateway' => 'theGateway'),
            array('all' => true, 'prepend' => true),
        )), array() , array());
        $factory->setContainer($this->container);

        $config = $factory->createConfig(array(
            'payum.gateway_name' => 'theGateway',
            'payum.factory_name' => 'theFactory',
        ));

        //guard
        $this->assertArrayHasKey('payum.action.the_action', $config);

        $this->assertContains('payum.action.the_action', $config['payum.prepend_actions']);
    }

    /**
     * @test
     */
    public function shouldAddTaggedExtensionIfFactoryNameMatch()
    {
        $this->container->set('the_extension', $this->getMock('Payum\Core\Extension\ExtensionInterface'));

        $factory = new GatewayFactory(array(), array('the_extension' => array(
            array('factory' => 'theFactory'),
        )), array());
        $factory->setContainer($this->container);

        $config = $factory->createConfig(array(
            'payum.factory_name' => 'theFactory'
        ));

        $this->assertArrayHasKey('payum.extension.the_extension', $config);
        $this->assertSame($this->container->get('the_extension'), $config['payum.extension.the_extension']);
    }

    /**
     * @test
     */
    public function shouldAddTaggedExtensionIfAllAttributeSet()
    {
        $this->container->set('the_extension', $this->getMock('Payum\Core\Extension\ExtensionInterface'));

        $factory = new GatewayFactory(array(), array('the_extension' => array(
            array('all' => true),
        )), array());
        $factory->setContainer($this->container);

        $config = $factory->createConfig();

        $this->assertArrayHasKey('payum.extension.the_extension', $config);
        $this->assertSame($this->container->get('the_extension'), $config['payum.extension.the_extension']);
    }

    /**
     * @test
     */
    public function shouldAddTaggedExtensionWithAlias()
    {
        $this->container->set('the_extension', $this->getMock('Payum\Core\Extension\ExtensionInterface'));

        $factory = new GatewayFactory(array(), array('the_extension' => array(
            array('all' => true, 'alias' => 'the_extension_alias'),
        )), array());
        $factory->setContainer($this->container);

        $config = $factory->createConfig();

        $this->assertArrayHasKey('payum.extension.the_extension_alias', $config);
        $this->assertSame($this->container->get('the_extension'), $config['payum.extension.the_extension_alias']);
    }

    /**
     * @test
     */
    public function shouldAddTaggedExtensionWithPrepend()
    {
        $this->container->set('the_extension', $this->getMock('Payum\Core\Extension\ExtensionInterface'));

        $factory = new GatewayFactory(array(), array('the_extension' => array(
            array('all' => true, 'prepend' => true),
        )), array());
        $factory->setContainer($this->container);

        $config = $factory->createConfig();

        //guard
        $this->assertArrayHasKey('payum.extension.the_extension', $config);

        $this->assertContains('payum.extension.the_extension', $config['payum.prepend_extensions']);
    }

    /**
     * @test
     */
    public function shouldAddTaggedExtensionWithSeveralTags()
    {
        $this->container->set('the_extension', $this->getMock('Payum\Core\Extension\ExtensionInterface'));

        $factory = new GatewayFactory(array(), array('the_extension' => array(
            array('factory' => 'theFactory'),
            array('gateway' => 'theGateway'),
            array('all' => true, 'prepend' => true),
        )), array());
        $factory->setContainer($this->container);

        $config = $factory->createConfig(array(
            'payum.gateway_name' => 'theGateway',
            'payum.factory_name' => 'theFactory',
        ));

        //guard
        $this->assertArrayHasKey('payum.extension.the_extension', $config);

        $this->assertContains('payum.extension.the_extension', $config['payum.prepend_extensions']);
    }

    /**
     * @test
     */
    public function shouldAddTaggedApiIfFactoryNameMatch()
    {
        $this->container->set('the_api', new \stdClass);

        $factory = new GatewayFactory(array(), array(), array('the_api' => array(
            array('factory' => 'theFactory'),
        )));
        $factory->setContainer($this->container);

        $config = $factory->createConfig(array(
            'payum.factory_name' => 'theFactory'
        ));

        $this->assertArrayHasKey('payum.api.the_api', $config);
        $this->assertSame($this->container->get('the_api'), $config['payum.api.the_api']);
    }

    /**
     * @test
     */
    public function shouldAddTaggedApiIfAllAttributeSet()
    {
        $this->container->set('the_api', new \stdClass);

        $factory = new GatewayFactory(array(), array(), array('the_api' => array(
            array('all' => true),
        )));
        $factory->setContainer($this->container);

        $config = $factory->createConfig();

        $this->assertArrayHasKey('payum.api.the_api', $config);
        $this->assertSame($this->container->get('the_api'), $config['payum.api.the_api']);
    }

    /**
     * @test
     */
    public function shouldAddTaggedApiWithAlias()
    {
        $this->container->set('the_api', new \stdClass);

        $factory = new GatewayFactory(array(), array(), array('the_api' => array(
            array('all' => true, 'alias' => 'the_api_alias'),
        )));
        $factory->setContainer($this->container);

        $config = $factory->createConfig();

        $this->assertArrayHasKey('payum.api.the_api_alias', $config);
        $this->assertSame($this->container->get('the_api'), $config['payum.api.the_api_alias']);
    }

    /**
     * @test
     */
    public function shouldAddTaggedApiWithPrepend()
    {
        $this->container->set('the_api', new \stdClass);

        $factory = new GatewayFactory(array(), array(), array('the_api' => array(
            array('all' => true, 'prepend' => true),
        )));
        $factory->setContainer($this->container);

        $config = $factory->createConfig();

        //guard
        $this->assertArrayHasKey('payum.api.the_api', $config);

        $this->assertContains('payum.api.the_api', $config['payum.prepend_apis']);
    }

    /**
     * @test
     */
    public function shouldAddTaggedApiWithSeveralTags()
    {
        $this->container->set('the_api', new \stdClass);

        $factory = new GatewayFactory(array(), array(), array('the_api' => array(
            array('factory' => 'theFactory'),
            array('gateway' => 'theGateway'),
            array('all' => true, 'prepend' => true),
        )));
        $factory->setContainer($this->container);

        $config = $factory->createConfig(array(
            'payum.gateway_name' => 'theGateway',
            'payum.factory_name' => 'theFactory',
        ));

        //guard
        $this->assertArrayHasKey('payum.api.the_api', $config);

        $this->assertContains('payum.api.the_api', $config['payum.prepend_apis']);
    }
}
