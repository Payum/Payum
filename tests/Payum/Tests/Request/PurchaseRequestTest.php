<?php
namespace Payum\Tests\Request;

class PurchaseRequestTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfBaseModelRequest()
    {
        $rc = new \ReflectionClass('Payum\Request\PurchaseRequest');
        
        $this->assertTrue($rc->isSubclassOf('Payum\Request\BaseModelRequest'));
    }
}