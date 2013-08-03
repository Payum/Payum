<?php
namespace Payum\Tests\Request;

class StatusRequestInterfaceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementInteractiveRequestInterface()
    {
        $rc = new \ReflectionClass('Payum\Request\StatusRequestInterface');

        $this->assertTrue($rc->implementsInterface('Payum\Request\InteractiveRequestInterface'));
    }

    /**
     * @test
     */
    public function shouldImplementModelRequestInterface()
    {
        $rc = new \ReflectionClass('Payum\Request\StatusRequestInterface');

        $this->assertTrue($rc->implementsInterface('Payum\Request\ModelRequestInterface'));
    }
}