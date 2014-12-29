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
    public function couldBeConstructedWithoutAnyArguments()
    {
        new PaymentConfigType();
    }

    /**
     * @test
     */
    public function shouldExtendDateType()
    {
        $type = new PaymentConfigType();

        $this->assertEquals('form', $type->getParent());
    }

    /**
     * @test
     */
    public function shouldReturnExpectedName()
    {
        $type = new PaymentConfigType();

        $this->assertEquals('payum_payment_config', $type->getName());
    }

    /**
     * @test
     */
    public function shouldAllowResolveOptions()
    {
        $type = new PaymentConfigType();

        $resolver = new OptionsResolver();

        $type->setDefaultOptions($resolver);

        $options = $resolver->resolve();

        $this->assertArrayHasKey('data_class', $options);
        $this->assertEquals('Payum\Core\Model\PaymentConfigInterface', $options['data_class']);
    }
}
