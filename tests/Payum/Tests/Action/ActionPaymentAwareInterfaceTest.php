<?php
namespace Payum\Tests\Action;

class ActionPaymentAwareInterfaceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementActionInterface()
    {
        $rc = new \ReflectionClass('Payum\Action\ActionPaymentAwareInterface');
        
        $this->assertTrue($rc->implementsInterface('Payum\Action\ActionInterface'));
    }
}