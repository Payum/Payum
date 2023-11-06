<?php

namespace Payum\Core\Tests\Bridge\Symfony\Form\Type;

use Payum\Core\Bridge\Symfony\Form\Type\GatewayFactoriesChoiceType;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GatewayFactoriesChoiceTypeTest extends TestCase
{
    public function testShouldBeSubClassOfAbstractType()
    {
        $rc = new ReflectionClass(GatewayFactoriesChoiceType::class);

        $this->assertTrue($rc->isSubclassOf(AbstractType::class));
    }

    public function testShouldExtendChoice()
    {
        $type = new GatewayFactoriesChoiceType([]);

        $this->assertSame(ChoiceType::class, $type->getParent());
    }

    public function testShouldAllowResolveOptions()
    {
        $expectedChoices = [
            'foo' => 'Foo Factory',
            'bar' => 'Bar Factory',
        ];

        $type = new GatewayFactoriesChoiceType($expectedChoices);

        $resolver = new OptionsResolver();

        $type->configureOptions($resolver);

        $options = $resolver->resolve();

        $this->assertArrayHasKey('choices', $options);
        $this->assertSame($expectedChoices, $options['choices']);
    }
}
