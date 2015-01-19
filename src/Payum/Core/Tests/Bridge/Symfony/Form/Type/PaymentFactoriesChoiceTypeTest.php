<?php
namespace Payum\Core\Tests\Bridge\Symfony\Form\Type;

use Payum\Core\Bridge\Symfony\Form\Type\PaymentFactoriesChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PaymentFactoriesChoiceTypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfAbstractType()
    {
        $rc = new \ReflectionClass('Payum\Core\Bridge\Symfony\Form\Type\PaymentFactoriesChoiceType');

        $this->assertTrue($rc->isSubclassOf('Symfony\Component\Form\AbstractType'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithDefaultFactoriesAsFirstArgument()
    {
        new PaymentFactoriesChoiceType(array('foo' => 'Foo Factory'));
    }

    /**
     * @test
     */
    public function shouldExtendChoice()
    {
        $type = new PaymentFactoriesChoiceType(array());

        $this->assertEquals('choice', $type->getParent());
    }

    /**
     * @test
     */
    public function shouldReturnExpectedName()
    {
        $type = new PaymentFactoriesChoiceType(array());

        $this->assertEquals('payum_payment_factories_choice', $type->getName());
    }

    /**
     * @test
     */
    public function shouldAllowResolveOptions()
    {
        $expectedChoices = array(
            'foo' => 'Foo Factory',
            'bar' => 'Bar Factory',
        );

        $type = new PaymentFactoriesChoiceType($expectedChoices);

        $resolver = new OptionsResolver();

        $type->setDefaultOptions($resolver);

        $options = $resolver->resolve();

        $this->assertArrayHasKey('choices', $options);
        $this->assertEquals($expectedChoices, $options['choices']);
    }
}
