<?php
namespace Payum\Tests\Action;

class ActionApiAwareInterfaceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementActionInterface()
    {
        $rc = new \ReflectionClass('Payum\Action\ActionApiAwareInterface');
        
        $this->assertTrue($rc->implementsInterface('Payum\Action\ActionInterface'));
    }
}