<?php

namespace Payum\Paypal\ProHosted\Nvp\Tests\Request;

use Payum\Core\Request\Generic;
use Payum\Paypal\ProHosted\Nvp\Request\Api\GetTransactionDetails;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class GetTransactionDetailsTest extends TestCase
{
    public function testShouldBeSubClassOfGeneric(): void
    {
        $rc = new ReflectionClass(GetTransactionDetails::class);

        $this->assertTrue($rc->isSubclassOf(Generic::class));
    }
}
