<?php
namespace Payum\Core\Tests\Request;

class BaseInteractiveRequestTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementInteractiveRequestInterface()
    {
        $rc = new \ReflectionClass('Payum\Core\Request\BaseInteractiveRequest');
        
        $this->assertTrue($rc->implementsInterface('Payum\Core\Request\InteractiveRequestInterface'));
    }

    /**
     * @test
     */
    public function shouldBeSubClassOfLogicException()
    {
        $rc = new \ReflectionClass('Payum\Core\Request\BaseInteractiveRequest');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Exception\LogicException'));
    }
}