<?php
namespace Payum\Core\Tests\Request;

use Payum\Core\Request\BaseGetStatus;
use Payum\Core\Request\Generic;
use Payum\Core\Request\GetStatusInterface;

class BaseGetStatusTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementGetStatusInterface()
    {
        $rc = new \ReflectionClass(BaseGetStatus::class);

        $this->assertTrue($rc->implementsInterface(GetStatusInterface::class));
    }

    /**
     * @test
     */
    public function shouldBeSubClassOfGeneric()
    {
        $rc = new \ReflectionClass(BaseGetStatus::class);

        $this->assertTrue($rc->isSubclassOf(Generic::class));
    }

    /**
     * @test
     */
    public function shouldBeAbstract()
    {
        $rc = new \ReflectionClass(BaseGetStatus::class);

        $this->assertTrue($rc->isAbstract());
    }
}
