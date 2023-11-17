<?php

namespace Payum\Payex\Tests\Request\Api;

use Payum\Core\Request\Generic;
use Payum\Payex\Request\Api\CreateAgreement;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class CreateAgreementTest extends TestCase
{
    public function testShouldBeSubClassOfGeneric(): void
    {
        $rc = new ReflectionClass(CreateAgreement::class);

        $this->assertTrue($rc->isSubclassOf(Generic::class));
    }
}
