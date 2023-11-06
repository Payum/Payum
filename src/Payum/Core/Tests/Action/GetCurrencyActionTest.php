<?php
namespace Payum\Core\Tests\Action;

use Payum\Core\Action\GetCurrencyAction;
use Payum\Core\Request\GetCurrency;
use Payum\Core\Tests\GenericActionTest;
use Payum\ISO4217\Currency;
use Payum\ISO4217\ISO4217;

class GetCurrencyActionTest extends GenericActionTest
{
    protected $requestClass = 'Payum\Core\Request\GetCurrency';

    protected $actionClass = 'Payum\Core\Action\GetCurrencyAction';

    public function provideSupportedRequests(): \Iterator
    {
        yield array(new $this->requestClass('USD'));
        yield array(new $this->requestClass('EUR'));
    }

    public function provideNotSupportedRequests(): \Iterator
    {
        yield array('foo');
        yield array(array('foo'));
        yield array(new \stdClass());
        yield array($this->getMockForAbstractClass('Payum\Core\Request\Generic', array(array())));
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

    /**
     * @legacy
     */
    public function testPassingPayumIso4217IsDeprecated()
    {
        set_error_handler(function ($errorCode, $errorString) {
            $this->assertSame(E_USER_DEPRECATED, $errorCode);
            $this->assertSame('Passing an instance of Payum\ISO4217\ISO4217 in Payum\Core\Action\GetCurrencyAction::__construct is deprecated and won\'t be supported in version 2.', $errorString);

            restore_error_handler();
        });

        new GetCurrencyAction(new ISO4217());
    }

    /**
     * @group legacy
     */
    public function testItUsesPayumIso4217WhenItIsPassedThrough()
    {
        $mock = $this->createMock(ISO4217::class);
        $mock->expects($this->once())
            ->method('findByNumeric')
            ->with(978)
            ->willReturn(new Currency('Euro', 'EUR', 978, 2, []));

        $action = new GetCurrencyAction($mock);
        $action->execute($getCurrency = new GetCurrency($euro = 978));

        $this->assertSame('EUR', $getCurrency->alpha3);
    }

    public function testItDoesNotUsePayumIso4217ByDefault()
    {
        $mock = $this->createMock(ISO4217::class);

        $action = new GetCurrencyAction();

        $iso4217 = (new \ReflectionProperty($action, 'iso4217'));
        $iso4217->setAccessible(true);
        $iso4217->setValue($action, $mock);

        $mock->expects($this->never())
            ->method('findByNumeric');

        $action->execute($getCurrency = new GetCurrency($euro = 978));

        $this->assertSame('EUR', $getCurrency->alpha3);
    }
}
