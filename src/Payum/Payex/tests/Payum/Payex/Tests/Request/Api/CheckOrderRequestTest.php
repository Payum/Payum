<?php
namespace Payum\Payex\Tests\Request\Api;

class CheckOrderRequestTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfBaseModelRequest()
    {
        $rc = new \ReflectionClass('Payum\Payex\Request\Api\CheckOrderRequest');

        $this->assertTrue($rc->isSubclassOf('Payum\Request\BaseModelRequest'));
    }
}