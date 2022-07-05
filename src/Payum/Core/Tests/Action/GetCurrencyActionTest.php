<?php

namespace Payum\Core\Tests\Action;

use Payum\Core\Action\GetCurrencyAction;
use Payum\Core\Request\GetCurrency;
use Payum\Core\Tests\GenericActionTest;

class GetCurrencyActionTest extends GenericActionTest
{
    protected $requestClass = 'Payum\Core\Request\GetCurrency';

    protected $actionClass = 'Payum\Core\Action\GetCurrencyAction';

    public function provideSupportedRequests(): \Iterator
    {
        yield [new $this->requestClass('USD')];
        yield [new $this->requestClass('EUR')];
    }

    public function provideNotSupportedRequests(): \Iterator
    {
        yield ['foo'];
        yield [['foo']];
        yield [new \stdClass()];
        yield [$this->getMockForAbstractClass('Payum\Core\Request\Generic', [[]])];
    }

    public function testShouldSetCurrencyByAlpha3()
    {
        $action = new GetCurrencyAction();

        $action->execute($getCurrency = new GetCurrency('USD'));

        $this->assertSame('USD', $getCurrency->alpha3);
    }

    public function testShouldSetCurrencyByNumeric()
    {
        $action = new GetCurrencyAction();

        $action->execute($getCurrency = new GetCurrency($euro = 978));

        $this->assertSame('EUR', $getCurrency->alpha3);
    }

    public function testThrowsIfCurrencyNotSupported()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('ISO 4217 does not contain: 000');
        $action = new GetCurrencyAction();

        $action->execute($getCurrency = new GetCurrency('000'));
    }
}
