<?php
namespace Payum\Core\Tests\Model;

use Payum\Core\Model\PaymentConfig;

class PaymentConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldExtendDetailsAwareInterface()
    {
        $rc = new \ReflectionClass('Payum\Core\Model\PaymentConfig');

        $this->assertTrue($rc->implementsInterface('Payum\Core\Model\PaymentConfigInterface'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new PaymentConfig();
    }

    /**
     * @test
     */
    public function shouldAllowGetPreviouslySetFactoryName()
    {
        $config = new PaymentConfig();

        $config->setFactoryName('theName');

        $this->assertEquals('theName', $config->getFactoryName());
    }

    /**
     * @test
     */
    public function shouldAllowGetPreviouslySetPaymentName()
    {
        $config = new PaymentConfig();

        $config->setPaymentName('theName');

        $this->assertEquals('theName', $config->getPaymentName());
    }

    /**
     * @test
     */
    public function shouldAllowGetDefaultConfigSetInConstructor()
    {
        $config = new PaymentConfig();

        $this->assertEquals(array(), $config->getConfig());
    }

    /**
     * @test
     */
    public function shouldAllowGetPreviouslySetConfig()
    {
        $config = new PaymentConfig();

        $config->setConfig(array('foo' => 'fooVal'));

        $this->assertEquals(array('foo' => 'fooVal'), $config->getConfig());
    }
}
