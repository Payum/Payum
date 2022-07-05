<?php

namespace Payum\Klarna\Invoice\Tests\Request\Api;

use PHPUnit\Framework\TestCase;

class CancelReservationTest extends TestCase
{
    public function testShouldBeSubClassOfBaseOrder()
    {
        $rc = new \ReflectionClass(\Payum\Klarna\Invoice\Request\Api\CancelReservation::class);

        $this->assertTrue($rc->isSubclassOf(\Payum\Core\Request\Generic::class));
    }
}
