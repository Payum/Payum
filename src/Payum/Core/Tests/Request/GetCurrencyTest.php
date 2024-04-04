<?php
namespace Payum\Core\Tests\Request;

use Payum\Core\Request\GetCurrency;
use PHPUnit\Framework\TestCase;

class GetCurrencyTest extends TestCase
{
    public function testShouldAllowGetCodeSetInConstructor()
    {
        $request = new GetCurrency('theCode');

        $this->assertSame('theCode', $request->code);
    }

    public function shouldAllowGetPreviouslySetCurrency()
    {
        $request = new GetCurrency('aCode');
        $request->numeric = 'aNumeric';
        $request->name = 'aName';
        $request->exp = 'anExp';
        $request->country = 'aCountry';
        $request->code = 'aCode';

        $this->assertSame('aNumeric', $request->numeric);
        $this->assertSame('aName', $request->name);
        $this->assertSame('anExp', $request->exp);
        $this->assertSame('aCountry', $request->country);
        $this->assertSame('aCode', $request->code);
    }
}
