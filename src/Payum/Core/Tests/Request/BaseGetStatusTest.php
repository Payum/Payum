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
    public function shouldBeSubClassOfBaseModelAware()
    {
        $rc = new \ReflectionClass('Payum\Core\Request\BaseGetStatus');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Request\BaseModelAware'));
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