<?php
namespace Payum\Bridge\Omnipay\Action;

use Payum\Bridge\Omnipay\Action\CaptureAction;

class CaptureActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfBaseApiAwareAction()
    {
        $rc = new \ReflectionClass('Payum\Bridge\Omnipay\Action\CaptureAction');
        
        $this->assertTrue($rc->isSubclassOf('Payum\Bridge\Omnipay\Action\BaseApiAwareAction'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new CaptureAction;
    }
}