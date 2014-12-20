<?php
namespace Payum\Core\Tests\Bridge\Symfony\Form\Type;

use Payum\Core\Bridge\Symfony\Form\Type\CreditCardExpirationDateType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreditCardExpirationDateTypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfAbstractType()
    {
        $rc = new \ReflectionClass('Payum\Core\Bridge\Symfony\Form\Type\CreditCardExpirationDateType');

        $this->assertTrue($rc->isSubclassOf('Symfony\Component\Form\AbstractType'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new CreditCardExpirationDateType();
    }

    /**
     * @test
     */
    public function shouldExtendDateType()
    {
        $type = new CreditCardExpirationDateType();

        $this->assertEquals('date', $type->getParent());
    }

    /**
     * @test
     */
    public function shouldReturnExpectedName()
    {
        $type = new CreditCardExpirationDateType();

        $this->assertEquals('payum_credit_card_expiration_date', $type->getName());
    }

    /**
     * @test
     */
    public function shouldAllowResolveOptions()
    {
        $type = new CreditCardExpirationDateType();

        $resolver = new OptionsResolver();

        $type->setDefaultOptions($resolver);

        $options = $resolver->resolve();

        $this->assertArrayHasKey('years', $options);
        $this->assertCount(10, $options['years']);
    }
}
