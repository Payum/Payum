<?php

declare(strict_types=1);

namespace Payum\Core\Tests\ISO4217;

use Payum\Core\ISO4217\Currency;
use PHPUnit\Framework\TestCase;

final class CurrencyTest extends TestCase
{
    public function testShouldAllowGetInfoSetInConstructor(): void
    {
        $currency = new Currency('theName', 'theAlpha3', 'theNumeric', 2, 'theCountry');

        $this->assertSame('theName', $currency->getName());
        $this->assertSame('theAlpha3', $currency->getAlpha3());
        $this->assertSame('theNumeric', $currency->getNumeric());
        $this->assertSame(2, $currency->getExp());
        $this->assertSame('theCountry', $currency->getCountry());
    }

    public function testItShouldCreateCurrencyByAlpha3Code(): void
    {
        $currency = Currency::createFromIso4217Alpha3('USD');

        $this->assertInstanceOf(Currency::class, $currency);
        $this->assertSame('US Dollar', $currency->getName());
        $this->assertSame('USD', $currency->getAlpha3());
        $this->assertSame('840', $currency->getNumeric());
        $this->assertSame(2, $currency->getExp());
        // The country list is alcohol/iso4217's data and grows between its releases.
        $this->assertContains('US', $currency->getCountry());
        $this->assertContains('EC', $currency->getCountry());
    }

    public function testItShouldCreateCurrencyByNumeric(): void
    {
        $currency = Currency::createFromIso4217Numeric('840');

        $this->assertInstanceOf(Currency::class, $currency);
        $this->assertSame('US Dollar', $currency->getName());
        $this->assertSame('USD', $currency->getAlpha3());
        $this->assertSame('840', $currency->getNumeric());
        $this->assertSame(2, $currency->getExp());
        // The country list is alcohol/iso4217's data and grows between its releases.
        $this->assertContains('US', $currency->getCountry());
        $this->assertContains('EC', $currency->getCountry());
    }
}
