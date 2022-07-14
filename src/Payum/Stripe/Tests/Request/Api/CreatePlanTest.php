<?php

namespace Payum\Stripe\Tests\Request\Api;

use Payum\Core\Request\Generic;
use Payum\Stripe\Request\Api\CreatePlan;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class CreatePlanTest extends TestCase
{
    public function testShouldBeSubClassOfGeneric(): void
    {
        $rc = new ReflectionClass(CreatePlan::class);

        $this->assertTrue($rc->isSubclassOf(Generic::class));
    }
}
