<?php
namespace Payum\Core\Tests\Request;

class StatusRequestInterfaceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementInteractiveRequestInterface()
    {
        $rc = new \ReflectionClass('Payum\Core\Request\StatusRequestInterface');

        $this->assertTrue($rc->implementsInterface('Payum\Core\Request\InteractiveRequestInterface'));
    }

    /**
     * @test
     */
    public function shouldImplementModelRequestInterface()
    {
        $rc = new \ReflectionClass('Payum\Core\Request\StatusRequestInterface');

        $this->assertTrue($rc->implementsInterface('Payum\Core\Request\ModelRequestInterface'));
    }
}