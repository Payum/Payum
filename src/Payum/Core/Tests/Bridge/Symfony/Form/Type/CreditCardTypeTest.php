<?php
namespace Payum\Core\Tests\Bridge\Symfony\Form\Type;

use Payum\Core\Bridge\Symfony\Form\Type\CreditCardType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreditCardTypeTest extends \PHPUnit_Framework_TestCase
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
    public function couldBeConstructedWithoutAnyArguments()
    {
        new CreditCardType();
    }

    /**
     * @test
     */
    public function shouldExtendFormType()
    {
        $this->markTestSkipped('Undo mark skipp when minimum supported version of Symfony will be 2.8');

        $type = new CreditCardType();

        $this->assertEquals('form', $type->getParent());
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
        $this->assertEquals('Payum\Core\Model\CreditCard', $options['data_class']);

        $this->assertArrayHasKey('validation_groups', $options);
        $this->assertEquals(array('Payum'), $options['validation_groups']);

        $this->assertArrayHasKey('label', $options);
        $this->assertFalse($options['label']);
    }
}
