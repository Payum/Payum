<?php
namespace Payum\Core\Tests\Request;

use Payum\Core\Model\ModelAggregateInterface;
use Payum\Core\Model\ModelAwareInterface;
use Payum\Core\Request\GetStatusInterface;
use PHPUnit\Framework\TestCase;

class GetStatusInterfaceTest extends TestCase
{
    public function testShouldImplementModelAwareInterface()
    {
        $rc = new \ReflectionClass(GetStatusInterface::class);

        $this->assertTrue($rc->implementsInterface(ModelAwareInterface::class));
    }

    public function testShouldImplementModelAggregateInterface()
    {
        $rc = new \ReflectionClass(GetStatusInterface::class);

        $this->assertTrue($rc->implementsInterface(ModelAggregateInterface::class));
    }
}
