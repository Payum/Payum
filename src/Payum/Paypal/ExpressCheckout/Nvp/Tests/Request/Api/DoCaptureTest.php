<?php

namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Request\Api;

use Payum\Core\Request\Generic;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\DoCapture;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use stdClass;

class DoCaptureTest extends TestCase
{
    public function testShouldBeSubClassOfGeneric()
    {
        $rc = new ReflectionClass(DoCapture::class);

        $this->assertTrue($rc->isSubclassOf(Generic::class));
    }

    public function testShouldAllowGetPaymentRequestNSetInConstructor()
    {
        $expectedPaymentRequestN = 7;

        $request = new DoCapture(new stdClass(), $expectedPaymentRequestN);

        $this->assertSame($expectedPaymentRequestN, $request->getPaymentRequestN());
    }
}
