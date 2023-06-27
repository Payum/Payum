<?php

namespace Payum\Core\Tests\Action;

use Iterator;
use Payum\Core\Action\GetCurrencyAction;
use Payum\Core\Request\Generic;
use Payum\Core\Request\GetCurrency;
use Payum\Core\Tests\GenericActionTest;
use RuntimeException;
use stdClass;

class GetCurrencyActionTest extends GenericActionTest
{
    /**
     * @var class-string<GetCurrency>
     */
    protected $requestClass = GetCurrency::class;

    /**
     * @var class-string<GetCurrencyAction>
     */
    protected $actionClass = GetCurrencyAction::class;

    /**
     * @return \Iterator<GetCurrency[]>
     */
    public function provideSupportedRequests(): Iterator
    {
        yield [new $this->requestClass('USD')];
        yield [new $this->requestClass('EUR')];
    }

    public function provideNotSupportedRequests(): Iterator
    {
        yield ['foo'];
        yield [['foo']];
        yield [new stdClass()];
        yield [$this->getMockForAbstractClass(Generic::class, [[]])];
    }

    public function testShouldSetCurrencyByAlpha3(): void
    {
        $action = new GetCurrencyAction();

        $action->execute($getCurrency = new GetCurrency('USD'));

        $this->assertSame('USD', $getCurrency->alpha3);
    }

    public function testShouldSetCurrencyByNumeric(): void
    {
        $action = new GetCurrencyAction();

        $action->execute($getCurrency = new GetCurrency($euro = 978));

        $this->assertSame('EUR', $getCurrency->alpha3);
    }

    public function testThrowsIfCurrencyNotSupported(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('ISO 4217 does not contain: 000');
        $action = new GetCurrencyAction();

        $action->execute($getCurrency = new GetCurrency('000'));
    }
}
