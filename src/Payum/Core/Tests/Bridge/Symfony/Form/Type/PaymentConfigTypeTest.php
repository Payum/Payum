<?php
namespace Payum\Core\Tests\Bridge\Symfony\Form\Type;

use Payum\Core\Bridge\Symfony\Form\Type\PaymentConfigType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PaymentConfigTypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfAbstractType()
    {
        $rc = new \ReflectionClass('Payum\Core\Bridge\Symfony\Form\Type\PaymentConfigType');

        $this->assertTrue($rc->isSubclassOf('Symfony\Component\Form\AbstractType'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithPaymentFactoryRegistryAsFirstArgument()
    {
        new PaymentConfigType($this->getMock('Payum\Core\Registry\PaymentFactoryRegistryInterface'));
    }

    /**
     * @test
     */
    public function shouldExtendFormType()
    {
        $type = new PaymentConfigType($this->getMock('Payum\Core\Registry\PaymentFactoryRegistryInterface'));

        $this->assertEquals('form', $type->getParent());
    }

    /**
     * @test
     */
    public function shouldReturnExpectedName()
    {
        $type = new PaymentConfigType($this->getMock('Payum\Core\Registry\PaymentFactoryRegistryInterface'));

        $this->assertEquals('payum_payment_config', $type->getName());
    }

    /**
     * @test
     */
    public function shouldAllowResolveOptions()
    {
        $type = new PaymentConfigType($this->getMock('Payum\Core\Registry\PaymentFactoryRegistryInterface'));

        $resolver = new OptionsResolver();

        $type->setDefaultOptions($resolver);

        $options = $resolver->resolve();

        $this->assertArrayHasKey('data_class', $options);
        $this->assertEquals('Payum\Core\Model\PaymentConfigInterface', $options['data_class']);
    }
}
