<?php

namespace Payum\Core\Tests\Bridge\Symfony\Form\Type;

use Payum\Core\Bridge\Symfony\Form\Type\GatewayConfigType;
use Payum\Core\Model\GatewayConfig;
use Payum\Core\Registry\GatewayFactoryRegistryInterface;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GatewayConfigTypeTest extends TestCase
{
    public function testShouldBeSubClassOfAbstractType(): void
    {
        $rc = new ReflectionClass(GatewayConfigType::class);

        $this->assertTrue($rc->isSubclassOf(AbstractType::class));
    }

    public function testShouldExtendFormType(): void
    {
        $type = new GatewayConfigType($this->createMock(GatewayFactoryRegistryInterface::class));

        $this->assertSame(FormType::class, $type->getParent());
    }

    public function testShouldAllowResolveOptions(): void
    {
        $type = new GatewayConfigType($this->createMock(GatewayFactoryRegistryInterface::class));

        $resolver = new OptionsResolver();

        $type->configureOptions($resolver);

        $options = $resolver->resolve();

        $this->assertArrayHasKey('data_class', $options);
        $this->assertSame(GatewayConfig::class, $options['data_class']);
    }
}
