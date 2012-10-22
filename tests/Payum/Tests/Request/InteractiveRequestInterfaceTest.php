<?php
namespace Payum\Tests\Request;

class InteractiveRequestInterfaceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementExceptionInterface()
    {
        $rc = new \ReflectionClass('Payum\Request\InteractiveRequestInterface');

        $this->assertTrue($rc->implementsInterface('Payum\Exception\ExceptionInterface'));
    }
}