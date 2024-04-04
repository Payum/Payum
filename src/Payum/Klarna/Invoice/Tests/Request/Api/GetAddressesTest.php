<?php
namespace Payum\Klarna\Invoice\Tests\Request\Api;

use Payum\Klarna\Invoice\Request\Api\GetAddresses;

class GetAddressesTest extends \PHPUnit\Framework\TestCase
{
    public function testShouldAllowGetPnoSetInConstructor()
    {
        $request = new GetAddresses($pno = 'thePno');

        $this->assertSame($pno, $request->getPno());
    }

    public function testShouldAllowAddKLarnaAddress()
    {
        $request = new GetAddresses('aPno');

        $address = new \KlarnaAddr();
        $request->addAddress($address);
        $this->assertSame([$address], $request->getAddresses());
    }

    public function testShouldAllowGetPreviouslyAddedKLarnaAddresses()
    {
        $request = new GetAddresses('aPno');

        $request->addAddress($first = new \KlarnaAddr());
        $request->addAddress($second = new \KlarnaAddr());

        $addresses = $request->getAddresses();

        $this->assertCount(2, $addresses);
        $this->assertContains($first, $addresses);
        $this->assertContains($second, $addresses);
    }

    public function testShouldReturnNullIfAnyAddressAddedOnGetFirstAddress()
    {
        $request = new GetAddresses('aPno');

        $this->assertNull($request->getFirstAddress());
    }

    public function testShouldAllowGetFirstAddress()
    {
        $request = new GetAddresses('aPno');

        $request->addAddress($first = new \KlarnaAddr());
        $request->addAddress($second = new \KlarnaAddr());

        $this->assertSame($first, $request->getFirstAddress());
    }
}
