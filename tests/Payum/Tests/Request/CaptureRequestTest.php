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

    /**
     * @test
     */
    public function shouldAllowSetModel()
    {
        $request = new CaptureRequest('model');

        $request->setModel(new \stdClass());
    }

    /**
     * @test
     */
    public function shouldAllowGetPreviouslySetModel()
    {
        $expectedModel = new \stdClass();

        $request = new CaptureRequest('model');

        $request->setModel($expectedModel);

        $this->assertSame($expectedModel, $request->getModel());
    }
}

