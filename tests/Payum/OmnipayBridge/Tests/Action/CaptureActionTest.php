<?php
namespace Payum\OmnipayBridge\Action;

use Payum\OmnipayBridge\Action\CaptureAction;

class CaptureActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfBaseApiAwareAction()
    {
        $rc = new \ReflectionClass('Payum\OmnipayBridge\Action\CaptureAction');
        
        $this->assertTrue($rc->isSubclassOf('Payum\OmnipayBridge\Action\BaseActionApiAware'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new CaptureAction;
    }
}