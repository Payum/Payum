<?php
namespace Payum\Core\Tests\Functional\Bridge\Symfony\Form\Type;

use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Gateway\GatewayFactoryInterface;
use Payum\Core\Bridge\Symfony\Form\Type\GatewayConfigType;
use Payum\Core\Bridge\Symfony\Form\Type\GatewayFactoriesChoiceType;
use Payum\Core\Model\GatewayConfig;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\Forms;

class GatewayConfigTypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var  FormFactory
     */
    protected $formFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|GatewayFactoryInterface
     */
    protected $fooGatewayFactoryMock;

    protected function setUp()
    {
        $this->fooGatewayFactoryMock = $this->getMock('Payum\Core\GatewayFactoryInterface');

        $registry = $this->getMock('Payum\Core\Registry\GatewayFactoryRegistryInterface');
        $registry
            ->expects($this->any())
            ->method('getGatewayFactory')
            ->with('foo')
            ->willReturn($this->fooGatewayFactoryMock)
        ;

        $this->formFactory = Forms::createFormFactoryBuilder()
            ->addType(new GatewayFactoriesChoiceType(array(
                'foo' => 'Foo Factory',
            )))
            ->addType(new GatewayConfigType($registry))
            ->getFormFactory()
        ;
    }

    /**
     * @test
     */
    public function shouldBeConstructedByFormFactory()
    {
        $form = $this->formFactory->create(GatewayConfigType::class);

        $this->assertInstanceOf('Symfony\Component\Form\Form', $form);
        $this->assertInstanceOf('Symfony\Component\Form\FormView', $form->createView());
    }

    /**
     * @test
     */
    public function shouldAddDefaultFieldsIfFactoryNameChosen()
    {
        $form = $this->formFactory->create(GatewayConfigType::class);

        $this->assertTrue($form->has('gatewayName'));
        $this->assertTrue($form->has('factoryName'));
        $this->assertFalse($form->has('config'));
    }

    /**
     * @test
     */
    public function shouldMarkFormInvalidAndAddConfigFields()
    {
        $this->fooGatewayFactoryMock
            ->expects($this->once())
            ->method('createConfig')
            ->with(array())
            ->willReturn(array(
                'payum.default_options' => array(
                    'username' => 'defaultName',
                    'password' => 'defaultPass',
                    'sandbox' => true,
                ),
                'payum.required_options' => array(),
            ))
        ;

        $form = $this->formFactory->create(GatewayConfigType::class);

        $form->submit(array(
            'gatewayName' => 'foo',
            'factoryName' => 'foo',
        ));

        $this->assertTrue($form->has('config'));

        $this->assertTrue($form->get('config')->has('username'));
        $this->assertEquals('defaultName', $form->get('config')->get('username')->getData());

        $this->assertTrue($form->get('config')->has('password'));
        $this->assertEquals('defaultPass', $form->get('config')->get('password')->getData());

        $this->assertTrue($form->get('config')->has('sandbox'));
        $this->assertEquals(true, $form->get('config')->get('sandbox')->getData());
    }

    /**
     * @test
     */
    public function shouldSubmitWholeGatewayConfig()
    {
        $this->fooGatewayFactoryMock
            ->expects($this->once())
            ->method('createConfig')
            ->with(array())
            ->willReturn(array(
                'payum.default_options' => array(
                    'username' => 'defaultName',
                    'password' => 'defaultPass',
                    'sandbox' => true,
                ),
                'payum.required_options' => array(),
            ))
        ;

        $form = $this->formFactory->create(GatewayConfigType::class);

        $form->submit(array(
            'gatewayName' => 'foo',
            'factoryName' => 'foo',
            'config' => array(
                'username' => 'submitName',
                'password' => 'submitPass',
                'sandbox' => false,
            )

        ));

        $this->assertTrue($form->has('config'));

        $this->assertTrue($form->get('config')->has('username'));
        $this->assertEquals('submitName', $form->get('config')->get('username')->getData());

        $this->assertTrue($form->get('config')->has('password'));
        $this->assertEquals('submitPass', $form->get('config')->get('password')->getData());

        $this->assertTrue($form->get('config')->has('sandbox'));
        $this->assertEquals(false, $form->get('config')->get('sandbox')->getData());
    }

    /**
     * @test
     */
    public function shouldSetSandboxToFalseIfCheckboxUnset()
    {
        $this->fooGatewayFactoryMock
            ->expects($this->once())
            ->method('createConfig')
            ->with(array())
            ->willReturn(array(
                'payum.default_options' => array(
                    'username' => 'defaultName',
                    'password' => 'defaultPass',
                    'sandbox' => true,
                ),
                'payum.required_options' => array(),
            ))
        ;

        $form = $this->formFactory->create(GatewayConfigType::class);

        $form->submit(array(
            'gatewayName' => 'foo',
            'factoryName' => 'foo',
            'config' => array(
                'username' => 'submitName',
                'password' => 'submitPass',
            )

        ));

        $this->assertTrue($form->has('config'));

        $this->assertTrue($form->get('config')->has('username'));
        $this->assertEquals('submitName', $form->get('config')->get('username')->getData());

        $this->assertTrue($form->get('config')->has('password'));
        $this->assertEquals('submitPass', $form->get('config')->get('password')->getData());

        $this->assertTrue($form->get('config')->has('sandbox'));
        $this->assertEquals(false, $form->get('config')->get('sandbox')->getData());
    }

    /**
     * @test
     */
    public function shouldAddConfigFieldsIfGatewayConfigHasFactorySet()
    {
        $this->fooGatewayFactoryMock
            ->expects($this->once())
            ->method('createConfig')
            ->with(array())
            ->willReturn(array(
                'payum.default_options' => array(
                    'username' => 'defaultName',
                    'password' => 'defaultPass',
                    'sandbox' => true,
                ),
                'payum.required_options' => array(),
            ))
        ;

        $gatewayConfig = new GatewayConfig();
        $gatewayConfig->setFactoryName('foo');
        $gatewayConfig->setGatewayName('theName');
        $gatewayConfig->setConfig(array(
            'username' => 'modelName',
            'password' => 'modelPass',
            'sandbox' => false,
        ));

        $form = $this->formFactory->create(GatewayConfigType::class, $gatewayConfig);


        $this->assertTrue($form->has('config'));

        $this->assertTrue($form->get('config')->has('username'));
        $this->assertEquals('modelName', $form->get('config')->get('username')->getData());

        $this->assertTrue($form->get('config')->has('password'));
        $this->assertEquals('modelPass', $form->get('config')->get('password')->getData());

        $this->assertTrue($form->get('config')->has('sandbox'));
        $this->assertEquals(false, $form->get('config')->get('sandbox')->getData());
    }
}
