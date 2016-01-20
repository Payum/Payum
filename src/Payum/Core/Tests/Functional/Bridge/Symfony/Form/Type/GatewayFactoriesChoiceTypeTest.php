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
                'Foo Factory' => 'foo',
                'Bar Factory' => 'bar'
            )))
            ->getFormFactory()
        ;
    }

    /**
     * @test
     */
    public function shouldBeConstructedByFormFactory()
    {
        $form = $this->formFactory->create(GatewayFactoriesChoiceType::class);

        $this->assertInstanceOf('Symfony\Component\Form\Form', $form);
        $this->assertInstanceOf('Symfony\Component\Form\FormView', $form->createView());
    }

    /**
     * @test
     */
    public function shouldCorrectlyBindValidValue()
    {
        $form = $this->formFactory->create(GatewayFactoriesChoiceType::class);

        $form->submit('foo');

        $this->assertTrue($form->isValid());

        $this->assertEquals('foo', $form->getData());
    }

    /**
     * @test
     */
    public function shouldNotBindInvalidValue()
    {
        $form = $this->formFactory->create(GatewayFactoriesChoiceType::class);

        $form->submit('invalid');

        $this->assertNull($form->getData());
    }
}
