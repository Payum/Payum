<?php
namespace Payum\Core\Tests\Bridge\Symfony\Form\Type;

use Payum\Core\Bridge\Symfony\Form\Type\GatewayConfigType;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GatewayConfigTypeTest extends TestCase
{
    public function testShouldBeSubClassOfAbstractType()
    {
        $rc = new \ReflectionClass('Payum\Core\Bridge\Symfony\Form\Type\GatewayConfigType');

        $this->assertTrue($rc->isSubclassOf('Symfony\Component\Form\AbstractType'));
    }

    public function testShouldExtendFormType()
    {
        $type = new GatewayConfigType($this->createMock('Payum\Core\Registry\GatewayFactoryRegistryInterface'));

        $this->assertSame(FormType::class, $type->getParent());
    }

    public function testShouldAllowResolveOptions()
    {
        $type = new GatewayConfigType($this->createMock('Payum\Core\Registry\GatewayFactoryRegistryInterface'));

        $resolver = new OptionsResolver();

        $type->configureOptions($resolver);

        $options = $resolver->resolve();

        $this->assertArrayHasKey('data_class', $options);
        $this->assertSame('Payum\Core\Model\GatewayConfig', $options['data_class']);
    }
}
