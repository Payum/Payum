<?php
namespace Payum\Bundle\PayumBundle\Tests\Request;

use Payum\Bundle\PayumBundle\Request\CaptureTokenizedDetailsRequest;
use Payum\Model\TokenizedDetails;
use Payum\Request\CaptureRequest;

class CaptureTokenizedDetailsRequestTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfCaptureRequest()
    {
        $rc = new \ReflectionClass('Payum\Bundle\PayumBundle\Request\CaptureTokenizedDetailsRequest');

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
}