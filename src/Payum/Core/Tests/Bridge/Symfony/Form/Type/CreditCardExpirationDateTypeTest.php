<?php
namespace Payum\Core\Tests\Bridge\Symfony\Form\Type;

use Payum\Core\Bridge\Symfony\Form\Type\CreditCardExpirationDateType;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreditCardExpirationDateTypeTest extends TestCase
{
    public function testShouldBeSubClassOfAbstractType()
    {
        $rc = new \ReflectionClass('Payum\Core\Bridge\Symfony\Form\Type\CreditCardExpirationDateType');

        $this->assertTrue($rc->isSubclassOf('Symfony\Component\Form\AbstractType'));
    }

    public function testShouldExtendDateType()
    {
        $type = new CreditCardExpirationDateType();

        $this->assertSame(DateType::class, $type->getParent());
    }

    public function testShouldAllowResolveOptions()
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

    public function testShouldTakeMinAndMaxExpirationYearsWhileCalcYearsRange()
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
        $this->assertSame(2000, $options['min_expiration_year']);

        $this->assertArrayHasKey('max_expiration_year', $options);
        $this->assertSame(2002, $options['max_expiration_year']);
    }
}
