<?php
namespace Payum\Core\Tests\Model;

use Payum\Core\Model\DetailsAggregateInterface;
use Payum\Core\Model\DetailsAwareInterface;
use Payum\Core\Model\PayoutInterface;
use PHPUnit\Framework\TestCase;

class PayoutInterfaceTest extends TestCase
{
    /**
     * @test
     */
    public function shouldExtendDetailsAwareInterface()
    {
        $rc = new \ReflectionClass(PayoutInterface::class);

        $this->assertTrue($rc->implementsInterface(DetailsAwareInterface::class));
    }

    /**
     * @test
     */
    public function shouldExtendDetailsAggregateInterface()
    {
        $rc = new \ReflectionClass(PayoutInterface::class);

        $this->assertTrue($rc->implementsInterface(DetailsAggregateInterface::class));
    }
}
