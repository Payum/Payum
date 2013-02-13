<?php
namespace Payum\Tests\Request;

use Payum\Request\CaptureRequest;

class CaptureRequestTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function couldBeConstructedWithModel()
    {
        new CaptureRequest(new \stdClass());
    }

    /**
     * @test
     */
    public function shouldAllowGetModelSetInConstructor()
    {
        $request = new CaptureRequest($expectedModel = new \stdClass());
        
        $this->assertSame($expectedModel, $request->getModel());
    }
}

