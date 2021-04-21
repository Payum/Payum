<?php
namespace Payum\Core\Tests\Request;

use Payum\Core\Model\ModelAggregateInterface;
use Payum\Core\Model\ModelAwareInterface;
use Payum\Core\Request\GetStatusInterface;
use PHPUnit\Framework\TestCase;

class GetStatusInterfaceTest extends TestCase
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
