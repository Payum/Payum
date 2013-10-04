<?php

namespace Payum\Paypal\Rest\Tests\Action;


use Payum\Paypal\Rest\Action\CaptureAction;

class CaptureActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new CaptureAction();
    }
}
