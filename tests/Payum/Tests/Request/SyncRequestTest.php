<?php
namespace Payum\Tests\Request;

use Payum\Request\SyncRequest;

class SyncRequestTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function couldBeConstructedWithModel()
    {
        new SyncRequest(new \stdClass());
    }

    /**
     * @test
     */
    public function shouldAllowGetModelSetInConstructor()
    {
        $request = new SyncRequest($expectedModel = new \stdClass());
        
        $this->assertSame($expectedModel, $request->getModel());
    }

    /**
     * @test
     */
    public function shouldAllowSetModel()
    {
        $request = new SyncRequest('model');

        $request->setModel(new \stdClass());
    }

    /**
     * @test
     */
    public function shouldAllowGetPreviouslySetModel()
    {
        $expectedModel = new \stdClass();

        $request = new SyncRequest('model');

        $request->setModel($expectedModel);

        $this->assertSame($expectedModel, $request->getModel());
    }
}

