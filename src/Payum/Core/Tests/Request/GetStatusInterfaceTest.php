<?php
namespace Payum\Core\Tests\Request;

use Payum\Core\Model\ModelAggregateInterface;
use Payum\Core\Model\ModelAwareInterface;
use Payum\Core\Request\GetStatusInterface;

class GetStatusInterfaceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementModelAwareInterface()
    {
        $rc = new \ReflectionClass(GetStatusInterface::class);

        $this->assertTrue($rc->implementsInterface(ModelAwareInterface::class));
    }

    /**
     * @test
     */
    public function shouldImplementModelAggregateInterface()
    {
        $rc = new \ReflectionClass(GetStatusInterface::class);

        $this->assertTrue($rc->implementsInterface(ModelAggregateInterface::class));
    }
}
