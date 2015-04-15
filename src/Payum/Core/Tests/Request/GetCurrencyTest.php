<?php
namespace Payum\Core\Tests\Request;

use Payum\Core\Request\GetCurrency;

class GetCurrencyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function couldBeConstructedWithCurrencyCode()
    {
        new GetCurrency('aCode');
    }

    /**
     * @test
     */
    public function shouldAllowGetCodeSetInConstructor()
    {
        $request = new GetCurrency('theCode');

        $this->assertEquals('theCode', $request->code);
    }

    public function shouldAllowGetPreviouslySetCurrency()
    {
        $request = new GetCurrency('aCode');
        $request->numeric = 'aNumeric';
        $request->name = 'aName';
        $request->exp = 'anExp';
        $request->country = 'aCountry';
        $request->code = 'aCode';

        $this->assertEquals('aNumeric', $request->numeric);
        $this->assertEquals('aName', $request->name);
        $this->assertEquals('anExp', $request->exp);
        $this->assertEquals('aCountry', $request->country);
        $this->assertEquals('aCode', $request->code);
    }
}
