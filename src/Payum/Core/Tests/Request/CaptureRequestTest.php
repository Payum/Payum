<?php
namespace Payum\Tests\Request;

class CaptureRequestTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfBaseModelRequest()
    {
        $rc = new \ReflectionClass('Payum\Core\Request\CaptureRequest');
        
        $this->assertTrue($rc->isSubclassOf('Payum\Core\Request\BaseModelRequest'));
    }
}