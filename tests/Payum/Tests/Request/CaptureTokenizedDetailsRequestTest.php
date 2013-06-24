<?php
namespace Payum\Tests\Request;

use Payum\Request\CaptureTokenizedDetailsRequest;
use Payum\Model\TokenizedDetails;

class CaptureTokenizedDetailsRequestTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfCaptureRequest()
    {
        $rc = new \ReflectionClass('Payum\Request\CaptureTokenizedDetailsRequest');

        $this->assertTrue($rc->isSubclassOf('Payum\Request\CaptureRequest'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithTokenizedDetailsAsFirstArgument()
    {
        new CaptureTokenizedDetailsRequest(new TokenizedDetails);
    }

    /**
     * @test
     */
    public function shouldAllowGetTokenizedDetailsSetInConstructor()
    {
        $expectedTokenizedDetails = new TokenizedDetails;
        
        $request = new CaptureTokenizedDetailsRequest($expectedTokenizedDetails);
        
        $this->assertSame($expectedTokenizedDetails, $request->getTokenizedDetails());
        $this->assertSame($expectedTokenizedDetails, $request->getModel());
    }

    /**
     * @test
     */
    public function shouldAllowSetModelAndKeepTokenizedDetailsSame()
    {
        $tokenizedDetails = new TokenizedDetails;

        $request = new CaptureTokenizedDetailsRequest($tokenizedDetails);

        //guard
        $this->assertSame($tokenizedDetails, $request->getTokenizedDetails());
        $this->assertSame($tokenizedDetails, $request->getModel());

        $newModel = new \stdClass;
            
        $request->setModel($newModel);

        $this->assertSame($tokenizedDetails, $request->getTokenizedDetails());
        $this->assertSame($newModel, $request->getModel());
    }
}