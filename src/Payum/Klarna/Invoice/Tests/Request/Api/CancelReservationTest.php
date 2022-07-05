<?php

namespace Payum\Klarna\Invoice\Tests\Request\Api;

use Payum\Core\Request\Generic;
use Payum\Klarna\Invoice\Request\Api\CancelReservation;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class CancelReservationTest extends TestCase
{
    public function testShouldBeSubClassOfBaseOrder()
    {
        $rc = new ReflectionClass(CancelReservation::class);

        $this->assertTrue($rc->isSubclassOf(Generic::class));
    }
}
