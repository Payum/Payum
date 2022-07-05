<?php

namespace Payum\Klarna\Invoice\Tests\Request\Api;

use Payum\Core\Request\Generic;
use Payum\Klarna\Invoice\Request\Api\ActivateReservation;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class ActivateReservationTest extends TestCase
{
    public function testShouldBeSubClassOfBaseOrder()
    {
        $rc = new ReflectionClass(ActivateReservation::class);

        $this->assertTrue($rc->isSubclassOf(Generic::class));
    }
}
