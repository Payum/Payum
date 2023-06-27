<?php

namespace Payum\Klarna\Invoice\Tests\Request\Api;

use KlarnaAddr;
use Payum\Klarna\Invoice\Request\Api\GetAddresses;
use PHPUnit\Framework\TestCase;

class GetAddressesTest extends TestCase
{
    public function testShouldAllowGetPnoSetInConstructor(): void
    {
        $request = new GetAddresses($pno = 'thePno');

        $this->assertSame($pno, $request->getPno());
    }

    public function testShouldAllowAddKLarnaAddress(): void
    {
        $request = new GetAddresses('aPno');

        $address = new KlarnaAddr();
        $request->addAddress($address);
        $this->assertSame([$address], $request->getAddresses());
    }

    public function testShouldAllowGetPreviouslyAddedKLarnaAddresses(): void
    {
        $request = new GetAddresses('aPno');

        $request->addAddress($first = new KlarnaAddr());
        $request->addAddress($second = new KlarnaAddr());

        $addresses = $request->getAddresses();

        $this->assertCount(2, $addresses);
        $this->assertContains($first, $addresses);
        $this->assertContains($second, $addresses);
    }

    public function testShouldReturnNullIfAnyAddressAddedOnGetFirstAddress(): void
    {
        $request = new GetAddresses('aPno');

        $this->assertNull($request->getFirstAddress());
    }

    public function testShouldAllowGetFirstAddress(): void
    {
        $request = new GetAddresses('aPno');

        $request->addAddress($first = new KlarnaAddr());
        $request->addAddress($second = new KlarnaAddr());

        $this->assertSame($first, $request->getFirstAddress());
    }
}
