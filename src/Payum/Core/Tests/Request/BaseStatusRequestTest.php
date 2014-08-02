<?php
namespace Payum\Core\Tests\Request;

use Payum\Core\Request\GetBinaryStatus;

class BaseStatusRequestTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementStatusRequestInterface()
    {
        $rc = new \ReflectionClass('Payum\Core\Request\BaseStatusRequest');

        $this->assertTrue($rc->implementsInterface('Payum\Core\Request\StatusRequestInterface'));
    }

    /**
     * @test
     */
    public function shouldBeSubClassOfBaseModelInteractiveRequest()
    {
        $rc = new \ReflectionClass('Payum\Core\Request\BaseStatusRequest');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Request\BaseModelInteractiveRequest'));
    }

    /**
     * @test
     */
    public function shouldBeAbstract()
    {
        $rc = new \ReflectionClass('Payum\Core\Request\BaseStatusRequest');

        $this->assertTrue($rc->isAbstract());
    }
}