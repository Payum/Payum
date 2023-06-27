<?php

namespace Payum\Payex\Tests\Request\Api;

use Payum\Core\Request\Generic;
use Payum\Payex\Request\Api\AutoPayAgreement;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class AutoPayAgreementTest extends TestCase
{
    public function testShouldBeSubClassOfGeneric(): void
    {
        $rc = new ReflectionClass(AutoPayAgreement::class);

        $this->assertTrue($rc->isSubclassOf(Generic::class));
    }
}
