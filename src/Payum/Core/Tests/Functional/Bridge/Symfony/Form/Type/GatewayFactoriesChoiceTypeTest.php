<?php
namespace Payum\Core\Tests\Functional\Bridge\Symfony\Form\Type;

use Payum\Core\Bridge\Symfony\Form\Type\GatewayFactoriesChoiceType;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\Forms;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GatewayFactoriesChoiceTypeTest extends TestCase
{
    /**
     * @var  FormFactory
     */
    protected $formFactory;

    protected function setUp(): void
    {
        $this->formFactory = Forms::createFormFactoryBuilder()
            ->addType(new GatewayFactoriesChoiceType(array(
                'Foo Factory' => 'foo',
                'Bar Factory' => 'bar'
            )))
            ->getFormFactory()
        ;
    }

    public function testShouldBeConstructedByFormFactory()
    {
        $form = $this->formFactory->create(GatewayFactoriesChoiceType::class);

        $this->assertInstanceOf('Symfony\Component\Form\Form', $form);
        $this->assertInstanceOf('Symfony\Component\Form\FormView', $form->createView());
    }

    public function testShouldCorrectlyBindValidValue()
    {
        $form = $this->formFactory->create(GatewayFactoriesChoiceType::class);

        $form->submit('foo');

        $this->assertTrue($form->isValid());

        $this->assertSame('foo', $form->getData());
    }

    public function testShouldNotBindInvalidValue()
    {
        $form = $this->formFactory->create(GatewayFactoriesChoiceType::class);

        $form->submit('invalid');

        $this->assertNull($form->getData());
    }
}
