<?php
namespace Payum\Core\Tests\Request;

class GetStatusInterfaceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementModelRequestInterface()
    {
        $rc = new \ReflectionClass('Payum\Core\Request\GetStatusInterface');

        $this->assertTrue($rc->implementsInterface('Payum\Core\Request\ModelAwareInterface'));
    }
}