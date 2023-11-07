<?php

namespace Payum\Core\Tests\Bridge\Symfony\Form\Type;

use Payum\Core\Bridge\Symfony\Form\Type\CreditCardType;
use Payum\Core\Model\CreditCard;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreditCardTypeTest extends TestCase
{
    public function testShouldBeSubClassOfAbstractType(): void
    {
        $rc = new ReflectionClass(CreditCardType::class);

        $this->assertTrue($rc->isSubclassOf(AbstractType::class));
    }

    public function testShouldExtendFormType(): void
    {
        $type = new CreditCardType();

        $this->assertSame(FormType::class, $type->getParent());
    }

    public function testShouldAllowResolveOptions(): void
    {
        $type = new CreditCardType();

        $resolver = new OptionsResolver();

        $type->configureOptions($resolver);

        $options = $resolver->resolve();

        $this->assertArrayHasKey('data_class', $options);
        $this->assertSame(CreditCard::class, $options['data_class']);

        $this->assertArrayHasKey('validation_groups', $options);
        $this->assertSame(['Payum'], $options['validation_groups']);

        $this->assertArrayHasKey('label', $options);
        $this->assertFalse($options['label']);
    }
}
