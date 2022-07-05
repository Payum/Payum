<?php

namespace Payum\Core\Tests\Bridge\Symfony\Form\Type;

use Payum\Core\Bridge\Symfony\Form\Type\CreditCardType;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreditCardTypeTest extends TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfAbstractType()
    {
        $rc = new \ReflectionClass('Payum\Core\Bridge\Symfony\Form\Type\CreditCardType');

        $this->assertTrue($rc->isSubclassOf('Symfony\Component\Form\AbstractType'));
    }

    /**
     * @test
     */
    public function shouldExtendFormType()
    {
        $type = new CreditCardType();

        $this->assertSame(FormType::class, $type->getParent());
    }

    /**
     * @test
     */
    public function shouldAllowResolveOptions()
    {
        $type = new CreditCardType();

        $resolver = new OptionsResolver();

        $type->configureOptions($resolver);

        $options = $resolver->resolve();

        $this->assertArrayHasKey('data_class', $options);
        $this->assertSame('Payum\Core\Model\CreditCard', $options['data_class']);

        $this->assertArrayHasKey('validation_groups', $options);
        $this->assertEquals(array('Payum'), $options['validation_groups']);

        $this->assertArrayHasKey('label', $options);
        $this->assertFalse($options['label']);
    }
}
