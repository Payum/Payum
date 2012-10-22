<?php
namespace Payum\Tests\Request;

class InteractiveRequestTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementInteractiveRequestInterface()
    {
        $rc = new \ReflectionClass('Payum\Request\InteractiveRequest');
        
        $this->assertTrue($rc->implementsInterface('Payum\Request\InteractiveRequestInterface'));
    }

    /**
     * @test
     */
    public function shouldBeSubClassOfLogicException()
    {
        $rc = new \ReflectionClass('Payum\Request\InteractiveRequest');

        $this->assertTrue($rc->isSubclassOf('Payum\Exception\LogicException'));
    }
}