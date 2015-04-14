<?php
namespace Payum\Core\Tests\Request;

use Payum\Core\Request\GetCurrency;
use Payum\ISO4217\Currency;

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

        $this->assertEquals('theCode', $request->getCode());
    }

    public function shouldAllowGetPreviouslySetCurrency()
    {
        $currency = new Currency('aName', 'anAlpha3', 'aNumeric', 'anExp', 'aCountry');

        $request = new GetCurrency('aCode');
        $request->setCurrency($currency);

        $this->assertSame($currency, $request->getCurrency());
    }
}
