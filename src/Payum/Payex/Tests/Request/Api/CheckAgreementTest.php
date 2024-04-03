<?php

namespace Payum\Payex\Tests\Request\Api;

use Payum\Core\Request\Generic;
use Payum\Payex\Request\Api\CheckAgreement;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class CheckAgreementTest extends TestCase
{
    public function testShouldBeSubClassOfGeneric(): void
    {
        $rc = new ReflectionClass(CheckAgreement::class);

        $this->assertTrue($rc->isSubclassOf(Generic::class));
    }
}
