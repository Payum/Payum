<?php
namespace Payum\Tests\Request;

use Payum\Request\BinaryMaskStatusRequest;

class BaseStatusRequestTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementStatusRequestInterface()
    {
        $rc = new \ReflectionClass('Payum\Request\BaseStatusRequest');

        $this->assertTrue($rc->implementsInterface('Payum\Request\StatusRequestInterface'));
    }

    /**
     * @test
     */
    public function shouldBeSubClassOfBaseModelInteractiveRequest()
    {
        $rc = new \ReflectionClass('Payum\Request\BaseStatusRequest');

        $this->assertTrue($rc->isSubclassOf('Payum\Request\BaseModelInteractiveRequest'));
    }

    /**
     * @test
     */
    public function shouldBeAbstract()
    {
        $rc = new \ReflectionClass('Payum\Request\BaseStatusRequest');

        $this->assertTrue($rc->isAbstract());
    }
}