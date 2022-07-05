<?php

namespace Payum\Paypal\ProHosted\Nvp\Tests\Request;

use Payum\Core\Request\Generic;
use Payum\Paypal\ProHosted\Nvp\Request\Api\CreateButtonPayment;
use PHPUnit\Framework\TestCase;

class CreateButtonPaymentTest extends TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfGeneric()
    {
        $rc = new \ReflectionClass(CreateButtonPayment::class);

        $this->assertTrue($rc->isSubclassOf(Generic::class));
    }
}
