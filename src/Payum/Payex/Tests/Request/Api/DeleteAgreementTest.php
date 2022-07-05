<?php

namespace Payum\Payex\Tests\Request\Api;

use Payum\Core\Request\Generic;
use Payum\Payex\Request\Api\DeleteAgreement;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class DeleteAgreementTest extends TestCase
{
    public function testShouldBeSubClassOfGeneric()
    {
        $rc = new ReflectionClass(DeleteAgreement::class);

        $this->assertTrue($rc->isSubclassOf(Generic::class));
    }
}
