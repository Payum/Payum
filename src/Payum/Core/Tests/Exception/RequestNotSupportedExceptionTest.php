<?php

namespace Payum\Core\Tests\Exception;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\InvalidArgumentException;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Model\Identity;
use Payum\Core\Request\Capture;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use stdClass;

class RequestNotSupportedExceptionTest extends TestCase
{
    public function testShouldBeSubClassOfInvalidArgumentException()
    {
        $rc = new ReflectionClass(RequestNotSupportedException::class);

        $this->assertTrue($rc->isSubclassOf(InvalidArgumentException::class));
    }

    public function testShouldCreateWithNoneObjectRequest()
    {
        $exception = RequestNotSupportedException::create('anRequest');

        $this->assertInstanceOf(RequestNotSupportedException::class, $exception);
        $this->assertStringStartsWith('Request string is not supported.', $exception->getMessage());

        $this->assertSame('anRequest', $exception->getRequest());
        $this->assertNull($exception->getAction());
    }

    public function testShouldCreateWithObjectRequest()
    {
        $request = new stdClass();

        $exception = RequestNotSupportedException::create($request);

        $this->assertInstanceOf(RequestNotSupportedException::class, $exception);
        $this->assertStringStartsWith('Request stdClass is not supported.', $exception->getMessage());

        $this->assertSame($request, $exception->getRequest());
        $this->assertNull($exception->getAction());
    }

    public function testShouldCreateWithActionAndStringRequest()
    {
        $action = $this->createMock(ActionInterface::class);
        $actionClass = $action::class;

        $exception = RequestNotSupportedException::createActionNotSupported($action, 'anRequest');

        $this->assertInstanceOf(RequestNotSupportedException::class, $exception);
        $this->assertStringStartsWith(
            'Action ' . $actionClass . ' is not supported the request string.',
            $exception->getMessage()
        );

        $this->assertSame('anRequest', $exception->getRequest());
        $this->assertSame($action, $exception->getAction());
    }

    public function testShouldCreateWithActionAndObjectRequest()
    {
        $request = new stdClass();

        $action = $this->createMock(ActionInterface::class);
        $actionClass = $action::class;

        $exception = RequestNotSupportedException::createActionNotSupported($action, $request);

        $this->assertInstanceOf(RequestNotSupportedException::class, $exception);
        $this->assertStringStartsWith(
            'Action ' . $actionClass . ' is not supported the request stdClass.',
            $exception->getMessage()
        );

        $this->assertSame($request, $exception->getRequest());
        $this->assertSame($action, $exception->getAction());
    }

    public function testShouldCreateWithSuggestions()
    {
        $request = new stdClass();

        $exception = RequestNotSupportedException::create($request);

        $this->assertInstanceOf(RequestNotSupportedException::class, $exception);
        $this->assertSame(
            'Request stdClass is not supported. Make sure the gateway supports the requests and there is an action which supports this request (The method returns true). There may be a bug, so look for a related issue on the issue tracker.',
            $exception->getMessage()
        );
    }

    public function testShouldCreateWithSuggestionsOnIdentityAsModel()
    {
        $request = new Capture(new Identity('theId', stdClass::class));

        $exception = RequestNotSupportedException::create($request);

        $this->assertInstanceOf(RequestNotSupportedException::class, $exception);
        $this->assertSame(
            'Request Capture{model: Identity} is not supported. Make sure the storage extension for "stdClass" is registered to the gateway. Make sure the storage find method returns an instance by id "theId". Make sure the gateway supports the requests and there is an action which supports this request (The method returns true). There may be a bug, so look for a related issue on the issue tracker.',
            $exception->getMessage()
        );
    }

    /**
     * @return MockObject|ActionInterface
     */
    protected function createActionMock()
    {
        return $this->createMock(ActionInterface::class);
    }
}
