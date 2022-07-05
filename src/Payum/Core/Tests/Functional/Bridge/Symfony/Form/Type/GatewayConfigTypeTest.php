<?php

namespace Payum\Core\Tests\Functional\Bridge\Symfony\Form\Type;

use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Gateway\GatewayFactoryInterface;
use Payum\Core\Bridge\Symfony\Form\Type\GatewayConfigType;
use Payum\Core\Bridge\Symfony\Form\Type\GatewayFactoriesChoiceType;
use Payum\Core\Model\GatewayConfig;
use Payum\Core\Registry\GatewayFactoryRegistryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\Forms;
use Symfony\Component\Form\FormView;

class GatewayConfigTypeTest extends TestCase
{
    /**
     * @var  FormFactory
     */
    protected $formFactory;

    /**
     * @var MockObject|GatewayFactoryInterface
     */
    protected $fooGatewayFactoryMock;

    protected function setUp(): void
    {
        $this->fooGatewayFactoryMock = $this->createMock(\Payum\Core\GatewayFactoryInterface::class);

        $registry = $this->createMock(GatewayFactoryRegistryInterface::class);
        $registry
            ->method('getGatewayFactory')
            ->with('foo')
            ->willReturn($this->fooGatewayFactoryMock)
        ;

        $this->formFactory = Forms::createFormFactoryBuilder()
            ->addType(new GatewayFactoriesChoiceType([
                'foo' => 'Foo Factory',
            ]))
            ->addType(new GatewayConfigType($registry))
            ->getFormFactory()
        ;
    }

    public function testShouldBeConstructedByFormFactory()
    {
        $form = $this->formFactory->create(GatewayConfigType::class);

        $this->assertInstanceOf(Form::class, $form);
        $this->assertInstanceOf(FormView::class, $form->createView());
    }

    public function testShouldAddDefaultFieldsIfFactoryNameChosen()
    {
        $form = $this->formFactory->create(GatewayConfigType::class);

        $this->assertTrue($form->has('gatewayName'));
        $this->assertTrue($form->has('factoryName'));
        $this->assertFalse($form->has('config'));
    }

    public function testShouldMarkFormInvalidAndAddConfigFields()
    {
        $this->fooGatewayFactoryMock
            ->expects($this->once())
            ->method('createConfig')
            ->with([])
            ->willReturn([
                'payum.default_options' => [
                    'username' => 'defaultName',
                    'password' => 'defaultPass',
                    'sandbox' => true,
                ],
                'payum.required_options' => [],
            ])
        ;

        $form = $this->formFactory->create(GatewayConfigType::class);

        $form->submit([
            'gatewayName' => 'foo',
            'factoryName' => 'foo',
        ]);

        $this->assertTrue($form->has('config'));

        $this->assertTrue($form->get('config')->has('username'));
        $this->assertSame('defaultName', $form->get('config')->get('username')->getData());

        $this->assertTrue($form->get('config')->has('password'));
        $this->assertSame('defaultPass', $form->get('config')->get('password')->getData());

        $this->assertTrue($form->get('config')->has('sandbox'));
        $this->assertEquals(true, $form->get('config')->get('sandbox')->getData());
    }

    public function testShouldSubmitWholeGatewayConfig()
    {
        $this->fooGatewayFactoryMock
            ->expects($this->once())
            ->method('createConfig')
            ->with([])
            ->willReturn([
                'payum.default_options' => [
                    'username' => 'defaultName',
                    'password' => 'defaultPass',
                    'sandbox' => true,
                ],
                'payum.required_options' => [],
            ])
        ;

        $form = $this->formFactory->create(GatewayConfigType::class);

        $form->submit([
            'gatewayName' => 'foo',
            'factoryName' => 'foo',
            'config' => [
                'username' => 'submitName',
                'password' => 'submitPass',
                'sandbox' => false,
            ],

        ]);

        $this->assertTrue($form->has('config'));

        $this->assertTrue($form->get('config')->has('username'));
        $this->assertSame('submitName', $form->get('config')->get('username')->getData());

        $this->assertTrue($form->get('config')->has('password'));
        $this->assertSame('submitPass', $form->get('config')->get('password')->getData());

        $this->assertTrue($form->get('config')->has('sandbox'));
        $this->assertEquals(false, $form->get('config')->get('sandbox')->getData());
    }

    public function testShouldSetSandboxToFalseIfCheckboxUnset()
    {
        $this->fooGatewayFactoryMock
            ->expects($this->once())
            ->method('createConfig')
            ->with([])
            ->willReturn([
                'payum.default_options' => [
                    'username' => 'defaultName',
                    'password' => 'defaultPass',
                    'sandbox' => true,
                ],
                'payum.required_options' => [],
            ])
        ;

        $form = $this->formFactory->create(GatewayConfigType::class);

        $form->submit([
            'gatewayName' => 'foo',
            'factoryName' => 'foo',
            'config' => [
                'username' => 'submitName',
                'password' => 'submitPass',
            ],

        ]);

        $this->assertTrue($form->has('config'));

        $this->assertTrue($form->get('config')->has('username'));
        $this->assertSame('submitName', $form->get('config')->get('username')->getData());

        $this->assertTrue($form->get('config')->has('password'));
        $this->assertSame('submitPass', $form->get('config')->get('password')->getData());

        $this->assertTrue($form->get('config')->has('sandbox'));
        $this->assertEquals(false, $form->get('config')->get('sandbox')->getData());
    }

    public function testShouldAddConfigFieldsIfGatewayConfigHasFactorySet()
    {
        $this->fooGatewayFactoryMock
            ->expects($this->once())
            ->method('createConfig')
            ->with([])
            ->willReturn([
                'payum.default_options' => [
                    'username' => 'defaultName',
                    'password' => 'defaultPass',
                    'sandbox' => true,
                ],
                'payum.required_options' => [],
            ])
        ;

        $gatewayConfig = new GatewayConfig();
        $gatewayConfig->setFactoryName('foo');
        $gatewayConfig->setGatewayName('theName');
        $gatewayConfig->setConfig([
            'username' => 'modelName',
            'password' => 'modelPass',
            'sandbox' => false,
        ]);

        $form = $this->formFactory->create(GatewayConfigType::class, $gatewayConfig);

        $this->assertTrue($form->has('config'));

        $this->assertTrue($form->get('config')->has('username'));
        $this->assertSame('modelName', $form->get('config')->get('username')->getData());

        $this->assertTrue($form->get('config')->has('password'));
        $this->assertSame('modelPass', $form->get('config')->get('password')->getData());

        $this->assertTrue($form->get('config')->has('sandbox'));
        $this->assertEquals(false, $form->get('config')->get('sandbox')->getData());
    }
}
