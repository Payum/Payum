<?php
namespace Payum\Payex\Tests\Api;

use Payum\Payex\Api\SoapClientFactory;

class BaseApiTest extends \PHPUnit_Framework_TestCase 
{
    /**
     * @test
     */
    public function shouldBeAbstract()
    {
        $rc = new \ReflectionClass('Payum\Payex\Api\BaseApi');
        
        $this->assertTrue($rc->isAbstract());
    }

    
    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\InvalidArgumentException
     * @expectedExceptionMessage The accountNumber option must be set.
     */
    public function throwIfAccountNumberOptionNotSet()
    {
        $this->getMockForAbstractClass('Payum\Payex\Api\BaseApi', array(
            new SoapClientFactory, 
            array()
        ));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\InvalidArgumentException
     * @expectedExceptionMessage The encryptionKey option must be set.
     */
    public function throwIfEncryptionKeyOptionNotSet()
    {
        $this->getMockForAbstractClass('Payum\Payex\Api\BaseApi', array(
            new SoapClientFactory,
            array(
                'accountNumber' => 'aNumber',
            )
        ));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\InvalidArgumentException
     * @expectedExceptionMessage The boolean sandbox option must be set.
     */
    public function throwIfNotBoolSandboxOptionGiven()
    {
        $this->getMockForAbstractClass('Payum\Payex\Api\BaseApi', array(
            new SoapClientFactory,
            array(
                'accountNumber' => 'aNumber',
                'encryptionKey' => 'aKey',
                'sandbox' => 'not a bool',
            )
        ));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithValidOptions()
    {
        $this->getMockForAbstractClass('Payum\Payex\Api\BaseApi', array(
            new SoapClientFactory,
            array(
                'accountNumber' => 'aNumber',
                'encryptionKey' => 'aKey',
                'sandbox' => true,
            )
        ));
    }
}