<?php
namespace Payum\Core\Tests\Functional\Bridge\Symfony\Form\Type;

use Payum\Core\Bridge\Symfony\Form\Type\GatewayFactoriesChoiceType;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\Forms;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GatewayFactoriesChoiceTypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var  FormFactory
     */
    protected $formFactory;

    protected function setUp()
    {
        $this->formFactory = Forms::createFormFactoryBuilder()
            ->addType(new GatewayFactoriesChoiceType(array(
                'foo' => 'Foo Factory',
                'bar' => 'Bar Factory'
            )))
            ->getFormFactory()
        ;
    }

    /**
     * @test
     */
    public function shouldBeConstructedByFormFactory()
    {
        $form = $this->formFactory->create('payum_gateway_factories_choice');

        $this->assertInstanceOf('Symfony\Component\Form\Form', $form);
        $this->assertInstanceOf('Symfony\Component\Form\FormView', $form->createView());
    }

    /**
     * @test
     */
    public function shouldCorrectlyBindValidValue()
    {
        $form = $this->formFactory->create('payum_gateway_factories_choice');

        $form->submit('foo');

        $this->assertTrue($form->isValid());

        $this->assertEquals('foo', $form->getData());
    }

    /**
     * @test
     */
    public function shouldNotBindInvalidValue()
    {
        $form = $this->formFactory->create('payum_gateway_factories_choice');

        $form->submit('invalid');

        $this->assertNull($form->getData());
    }
}
