<?php
namespace Payum\OmnipayBridge\Action;

use Payum\OmnipayBridge\Action\OnsiteCaptureAction;

class OnsiteCaptureActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfBaseApiAwareAction()
    {
        $rc = new \ReflectionClass('Payum\OmnipayBridge\Action\OnsiteCaptureAction');
        
        $this->assertTrue($rc->isSubclassOf('Payum\OmnipayBridge\Action\BaseApiAwareAction'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new OnsiteCaptureAction;
    }
}