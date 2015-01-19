<?php
namespace Payum\Core\Tests\Functional\Bridge\Symfony\Form\Type;

use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment\PaymentFactoryInterface;
use Payum\Core\Bridge\Symfony\Form\Type\PaymentConfigType;
use Payum\Core\Bridge\Symfony\Form\Type\PaymentFactoriesChoiceType;
use Payum\Core\Model\PaymentConfig;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\Forms;

class PaymentConfigTypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var  FormFactory
     */
    protected $formFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|PaymentFactoryInterface
     */
    protected $fooPaymentFactoryMock;

    protected function setUp()
    {
        $this->fooPaymentFactoryMock = $this->getMock('Payum\Core\PaymentFactoryInterface');

        $registry = $this->getMock('Payum\Core\Registry\PaymentFactoryRegistryInterface');
        $registry
            ->expects($this->any())
            ->method('getPaymentFactory')
            ->with('foo')
            ->willReturn($this->fooPaymentFactoryMock)
        ;

        $this->formFactory = Forms::createFormFactoryBuilder()
            ->addType(new PaymentFactoriesChoiceType(array(
                'foo' => 'Foo Factory',
            )))
            ->addType(new PaymentConfigType($registry))
            ->getFormFactory()
        ;
    }

    /**
     * @test
     */
    public function shouldBeConstructedByFormFactory()
    {
        $form = $this->formFactory->create('payum_payment_config');

        $this->assertInstanceOf('Symfony\Component\Form\Form', $form);
        $this->assertInstanceOf('Symfony\Component\Form\FormView', $form->createView());
    }

    /**
     * @test
     */
    public function shouldAddDefaultFieldsIfFactoryNameChosen()
    {
        $form = $this->formFactory->create('payum_payment_config');

        $this->assertTrue($form->has('paymentName'));
        $this->assertTrue($form->has('factoryName'));
        $this->assertFalse($form->has('config'));
    }

    /**
     * @test
     */
    public function shouldMarkFormInvalidAndAddConfigFields()
    {
        $this->fooPaymentFactoryMock
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

        $form = $this->formFactory->create('payum_payment_config');

        $form->submit(array(
            'paymentName' => 'foo',
            'factoryName' => 'foo',
        ));

        $this->assertTrue($form->has('config'));

        $this->assertTrue($form->get('config')->has('username'));
        $this->assertEquals('defaultName', $form->get('config')->get('username')->getData());

        $this->assertTrue($form->get('config')->has('password'));
        $this->assertEquals('defaultPass', $form->get('config')->get('password')->getData());

        // TODO why it is null??
        $this->assertTrue($form->get('config')->has('sandbox'));
        $this->assertEquals(null, $form->get('config')->get('sandbox')->getData());
    }

    /**
     * @test
     */
    public function shouldSubmitWholePaymentConfig()
    {
        $this->fooPaymentFactoryMock
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

        $form = $this->formFactory->create('payum_payment_config');

        $form->submit(array(
            'paymentName' => 'foo',
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
    public function shouldAddConfigFieldsIfPaymentConfigHasFactorySet()
    {
        $this->fooPaymentFactoryMock
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

        $paymentConfig = new PaymentConfig();
        $paymentConfig->setFactoryName('foo');
        $paymentConfig->setPaymentName('theName');
        $paymentConfig->setConfig(array(
            'username' => 'modelName',
            'password' => 'modelPass',
            'sandbox' => false,
        ));

        $form = $this->formFactory->create('payum_payment_config', $paymentConfig);


        $this->assertTrue($form->has('config'));

        $this->assertTrue($form->get('config')->has('username'));
        $this->assertEquals('modelName', $form->get('config')->get('username')->getData());

        $this->assertTrue($form->get('config')->has('password'));
        $this->assertEquals('modelPass', $form->get('config')->get('password')->getData());

        $this->assertTrue($form->get('config')->has('sandbox'));
        $this->assertEquals(false, $form->get('config')->get('sandbox')->getData());
    }
}
