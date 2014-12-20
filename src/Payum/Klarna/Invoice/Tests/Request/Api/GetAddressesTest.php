<?php
namespace Payum\Klarna\Invoice\Tests\Request\Api;

use Payum\Klarna\Invoice\Request\Api\GetAddresses;

class GetAddressesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function couldBeConstructedWithPnoAsArgument()
    {
        new GetAddresses('pno');
    }

    /**
     * @test
     */
    public function shouldAllowGetPnoSetInConstructor()
    {
        $request = new GetAddresses($pno = 'thePno');

        $this->assertSame($pno, $request->getPno());
    }

    /**
     * @test
     */
    public function shouldAllowAddKLarnaAddress()
    {
        $request = new GetAddresses('aPno');

        $request->addAddress(new \KlarnaAddr());
    }

    /**
     * @test
     */
    public function shouldAllowGetPreviouslyAddedKLarnaAddresses()
    {
        $request = new GetAddresses('aPno');

        $request->addAddress($first = new \KlarnaAddr());
        $request->addAddress($second = new \KlarnaAddr());

        $addresses = $request->getAddresses();

        $this->assertCount(2, $addresses);
        $this->assertContains($first, $addresses);
        $this->assertContains($second, $addresses);
    }

    /**
     * @test
     */
    public function shouldReturnNullIfAnyAddressAddedOnGetFirstAddress()
    {
        $request = new GetAddresses('aPno');

        $this->assertNull($request->getFirstAddress());
    }

    /**
     * @test
     */
    public function shouldAllowGetFirstAddress()
    {
        $request = new GetAddresses('aPno');

        $request->addAddress($first = new \KlarnaAddr());
        $request->addAddress($second = new \KlarnaAddr());

        $this->assertSame($first, $request->getFirstAddress());
    }
}
