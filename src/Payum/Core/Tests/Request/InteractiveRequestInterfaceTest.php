<?php
namespace Payum\Core\Tests\Request;

class InteractiveRequestInterfaceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementExceptionInterface()
    {
        $rc = new \ReflectionClass('Payum\Core\Request\InteractiveRequestInterface');

        $this->assertTrue($rc->implementsInterface('Payum\Core\Exception\ExceptionInterface'));
    }
}