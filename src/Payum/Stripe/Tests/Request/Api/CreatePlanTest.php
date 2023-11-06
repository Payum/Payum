<?php
namespace Payum\Stripe\Tests\Request\Api;

use Payum\Core\Request\Generic;
use Payum\Stripe\Request\Api\CreatePlan;

class CreatePlanTest extends \PHPUnit\Framework\TestCase
{
    public function testShouldBeSubClassOfGeneric()
    {
        $rc = new \ReflectionClass(CreatePlan::class);

        $this->assertTrue($rc->isSubclassOf(Generic::class));
    }
}
