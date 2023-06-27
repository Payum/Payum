<?php

namespace Payum\Core\Tests;

use Iterator;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Model\Payment;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Request\Convert;
use Payum\Core\Request\Generic;
use Payum\Core\Security\TokenInterface;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use stdClass;

abstract class BaseConvertPaymentActionTest extends TestCase
{
    /**
     * @var Generic
     */
    protected $requestClass = Convert::class;

    /**
     * @var ActionInterface
     */
    protected $actionClass;

    /**
     * @return \Iterator<Generic[]>
     */
    public function provideSupportedRequests(): Iterator
    {
        yield [new $this->requestClass(new Payment())];
        yield [new $this->requestClass($this->createMock(PaymentInterface::class))];
        yield [new $this->requestClass(new Payment(), $this->createMock(TokenInterface::class))];
    }

    public function provideNotSupportedRequests(): Iterator
    {
        yield ['foo'];
        yield [['foo']];
        yield [new stdClass()];
        yield [$this->getMockForAbstractClass(Generic::class, [[]])];
    }

    public function testShouldImplementActionInterface(): void
    {
        $rc = new ReflectionClass($this->actionClass);

        $this->assertTrue($rc->implementsInterface(ActionInterface::class));
    }

    /**
     * @dataProvider provideSupportedRequests
     */
    public function testShouldSupportRequest(Generic $request): void
    {
        $action = new $this->actionClass();

        $this->assertTrue($action->supports($request));
    }

    /**
     * @dataProvider provideNotSupportedRequests
     */
    public function testShouldNotSupportRequest($request): void
    {
        $action = new $this->actionClass();

        $this->assertFalse($action->supports($request));
    }

    /**
     * @dataProvider provideNotSupportedRequests
     */
    public function testThrowIfNotSupportedRequestGivenAsArgumentForExecute($request): void
    {
        $this->expectException(RequestNotSupportedException::class);
        $action = new $this->actionClass();

        $action->execute($request);
    }
}
