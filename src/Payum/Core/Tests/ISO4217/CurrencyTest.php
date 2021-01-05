<?php
namespace Payum\Core\Tests\ISO4217;

use Payum\Core\ISO4217\Currency;
use PHPUnit\Framework\TestCase;

class CurrencyTest extends TestCase
{
    public function testShouldAllowGetInfoSetInConstructor()
    {
        $currency = new Currency('theName', 'theAlpha3', 'theNumeric', 2, 'theCountry');

        $this->assertSame('theName', $currency->getName());
        $this->assertSame('theAlpha3', $currency->getAlpha3());
        $this->assertSame('theNumeric', $currency->getNumeric());
        $this->assertSame(2, $currency->getExp());
        $this->assertSame('theCountry', $currency->getCountry());
    }

    public function testItShouldCreateCurrencyByAlpha3Code()
    {
        $currency = Currency::createFromIso4217Alpha3('USD');

        $this->assertInstanceOf(Currency::class, $currency);
        $this->assertSame('US Dollar', $currency->getName());
        $this->assertSame('USD', $currency->getAlpha3());
        $this->assertSame('840', $currency->getNumeric());
        $this->assertSame(2, $currency->getExp());
        $this->assertSame(['AS', 'BQ', 'EC', 'FM', 'GU', 'MF', 'MH', 'MP', 'PR', 'PW', 'SV', 'TC', 'TL', 'UM', 'US', 'VG', 'VI', 'ZW',], $currency->getCountry());
    }

    public function testItShouldCreateCurrencyByNumeric()
    {
        $currency = Currency::createFromIso4217Numeric('840');

        $this->assertInstanceOf(Currency::class, $currency);
        $this->assertSame('US Dollar', $currency->getName());
        $this->assertSame('USD', $currency->getAlpha3());
        $this->assertSame('840', $currency->getNumeric());
        $this->assertSame(2, $currency->getExp());
        $this->assertSame(['AS', 'BQ', 'EC', 'FM', 'GU', 'MF', 'MH', 'MP', 'PR', 'PW', 'SV', 'TC', 'TL', 'UM', 'US', 'VG', 'VI', 'ZW',], $currency->getCountry());
    }
}
