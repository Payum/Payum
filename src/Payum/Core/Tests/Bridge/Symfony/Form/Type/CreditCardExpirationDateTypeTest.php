<?php
namespace Payum\Core\Tests\Bridge\Symfony\Form\Type;

use Payum\Core\Bridge\Symfony\Form\Type\CreditCardExpirationDateType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
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

        $this->assertEquals(DateType::class, $type->getParent());
    }

    /**
     * @test
     */
    public function shouldAllowResolveOptions()
    {
        $type = new CreditCardExpirationDateType();

        $resolver = new OptionsResolver();

        $type->configureOptions($resolver);

        $options = $resolver->resolve();

        $this->assertArrayHasKey('years', $options);
        $this->assertCount(11, $options['years']);

        $this->assertArrayHasKey('min_expiration_year', $options);
        $this->assertEquals(date('Y'), $options['min_expiration_year']);

        $this->assertArrayHasKey('max_expiration_year', $options);
        $this->assertEquals(date('Y') + 10, $options['max_expiration_year']);
    }

    /**
     * @test
     */
    public function shouldTakeMinAndMaxExpirationYearsWhileCalcYearsRange()
    {
        $type = new CreditCardExpirationDateType();

        $resolver = new OptionsResolver();

        $type->configureOptions($resolver);

        $options = $resolver->resolve(array(
            'min_expiration_year' => 2000,
            'max_expiration_year' => 2002,
        ));

        $this->assertArrayHasKey('years', $options);
        $this->assertCount(3, $options['years']);

        $this->assertArrayHasKey('min_expiration_year', $options);
        $this->assertEquals(2000, $options['min_expiration_year']);

        $this->assertArrayHasKey('max_expiration_year', $options);
        $this->assertEquals(2002, $options['max_expiration_year']);
    }
}
