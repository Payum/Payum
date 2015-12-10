<?php
namespace Payum\Core\Tests\Action;

use Payum\Core\Action\GetCurrencyAction;
use Payum\Core\Request\GetCurrency;
use Payum\Core\Tests\GenericActionTest;

class GetCurrencyActionTest extends GenericActionTest
{
    protected $requestClass = 'Payum\Core\Request\GetCurrency';

    protected $actionClass = 'Payum\Core\Action\GetCurrencyAction';

    public function provideSupportedRequests()
    {
        return array(
            array(new $this->requestClass('USD')),
            array(new $this->requestClass('EUR')),
        );
    }

    public function provideNotSupportedRequests()
    {
        return array(
            array('foo'),
            array(array('foo')),
            array(new \stdClass()),
            array($this->getMockForAbstractClass('Payum\Core\Request\Generic', array(array()))),
        );
    }

    /**
     * @test
     */
    public function shouldSetCurrencyByAlpha3()
    {
        $action = new GetCurrencyAction();

        $action->execute($getCurrency = new GetCurrency('USD'));

        $this->assertEquals('USD', $getCurrency->alpha3);
    }

    /**
     * @test
     */
    public function shouldSetCurrencyByNumeric()
    {
        $action = new GetCurrencyAction();

        $action->execute($getCurrency = new GetCurrency($euro = 978));

        $this->assertEquals('EUR', $getCurrency->alpha3);
    }

    /**
     * @test
     *
     * @expectedException \RuntimeException
     * @expectedExceptionMessage ISO 4217 does not contain: 000
     */
    public function throwsIfCurrencyNotSupported()
    {
        $action = new GetCurrencyAction();

        $action->execute($getCurrency = new GetCurrency('000'));
    }
}
