<?php

declare(strict_types=1);

namespace Payum\Klarna\Invoice\Tests\Request\Api;

use Payum\Core\Request\Generic;
use Payum\Klarna\Invoice\Request\Api\ActivateReservation;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class ActivateReservationTest extends TestCase
{
    public function testShouldBeSubClassOfBaseOrder(): void
    {
        $rc = new ReflectionClass(ActivateReservation::class);

        $this->assertTrue($rc->isSubclassOf(Generic::class));
    }
}
