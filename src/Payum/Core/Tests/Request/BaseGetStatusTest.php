<?php
namespace Payum\Core\Tests\Request;

class BaseGetStatusTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementGetStatusInterface()
    {
        $rc = new \ReflectionClass('Payum\Core\Request\BaseGetStatus');

        $this->assertTrue($rc->implementsInterface('Payum\Core\Request\GetStatusInterface'));
    }

    /**
     * @test
     */
    public function shouldBeSubClassOfGeneric()
    {
        $rc = new \ReflectionClass('Payum\Core\Request\BaseGetStatus');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Request\Generic'));
    }

    /**
     * @test
     */
    public function shouldBeAbstract()
    {
        $rc = new \ReflectionClass('Payum\Core\Request\BaseGetStatus');

        $this->assertTrue($rc->isAbstract());
    }
}
