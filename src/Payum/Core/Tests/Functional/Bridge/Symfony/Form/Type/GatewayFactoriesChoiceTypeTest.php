<?php

namespace Payum\Core\Tests\Functional\Bridge\Symfony\Form\Type;

use Payum\Core\Bridge\Symfony\Form\Type\GatewayFactoriesChoiceType;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\Forms;
use Symfony\Component\Form\FormView;

class GatewayFactoriesChoiceTypeTest extends TestCase
{
    /**
     * @var  FormFactory
     */
    protected $formFactory;

    protected function setUp(): void
    {
        $this->formFactory = Forms::createFormFactoryBuilder()
            ->addType(new GatewayFactoriesChoiceType([
                'Foo Factory' => 'foo',
                'Bar Factory' => 'bar',
            ]))
            ->getFormFactory()
        ;
    }

    public function testShouldBeConstructedByFormFactory(): void
    {
        $form = $this->formFactory->create(GatewayFactoriesChoiceType::class);

        $this->assertInstanceOf(Form::class, $form);
        $this->assertInstanceOf(FormView::class, $form->createView());
    }

    public function testShouldCorrectlyBindValidValue(): void
    {
        $form = $this->formFactory->create(GatewayFactoriesChoiceType::class);

        $form->submit('foo');

        $this->assertTrue($form->isValid());

        $this->assertSame('foo', $form->getData());
    }

    public function testShouldNotBindInvalidValue(): void
    {
        $form = $this->formFactory->create(GatewayFactoriesChoiceType::class);

        $form->submit('invalid');

        $this->assertNull($form->getData());
    }
}
