<?php
namespace Payum\Core\Tests\Bridge\Symfony\Form\Type;

use Payum\Core\Bridge\Symfony\Form\Type\GatewayConfigType;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GatewayConfigTypeTest extends TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfAbstractType()
    {
        $rc = new \ReflectionClass('Payum\Core\Bridge\Symfony\Form\Type\GatewayConfigType');

        $this->assertTrue($rc->isSubclassOf('Symfony\Component\Form\AbstractType'));
    }

    /**
     * @test
     */
    public function shouldExtendFormType()
    {
        $type = new GatewayConfigType($this->createMock('Payum\Core\Registry\GatewayFactoryRegistryInterface'));

        $this->assertEquals(FormType::class, $type->getParent());
    }

    /**
     * @test
     */
    public function shouldAllowResolveOptions()
    {
        $type = new GatewayConfigType($this->createMock('Payum\Core\Registry\GatewayFactoryRegistryInterface'));

        $resolver = new OptionsResolver();

        $type->configureOptions($resolver);

        $options = $resolver->resolve();

        $this->assertArrayHasKey('data_class', $options);
        $this->assertEquals('Payum\Core\Model\GatewayConfig', $options['data_class']);
    }
}
