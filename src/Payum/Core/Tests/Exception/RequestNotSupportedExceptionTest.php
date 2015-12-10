<?php
namespace Payum\Core\Tests\Exception;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\InvalidArgumentException;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Model\Identity;
use Payum\Core\Request\Capture;

class RequestNotSupportedExceptionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfInvalidArgumentException()
    {
        $rc = new \ReflectionClass(RequestNotSupportedException::class);

        $this->assertTrue($rc->isSubclassOf(InvalidArgumentException::class));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new RequestNotSupportedException();
    }

    /**
     * @test
     */
    public function shouldCreateWithNoneObjectRequest()
    {
        $exception = RequestNotSupportedException::create('anRequest');

        $this->assertInstanceOf(RequestNotSupportedException::class, $exception);
        $this->assertStringStartsWith('Request string is not supported.', $exception->getMessage());

        $this->assertSame('anRequest', $exception->getRequest());
        $this->assertNull($exception->getAction());
    }

    /**
     * @test
     */
    public function shouldCreateWithObjectRequest()
    {
        $request = new \stdClass();

        $exception = RequestNotSupportedException::create($request);

        $this->assertInstanceOf(RequestNotSupportedException::class, $exception);
        $this->assertStringStartsWith('Request stdClass is not supported.', $exception->getMessage());

        $this->assertSame($request, $exception->getRequest());
        $this->assertNull($exception->getAction());
    }

    /**
     * @test
     */
    public function shouldCreateWithActionAndStringRequest()
    {
        $action = $this->getMock(ActionInterface::class, array(), array(), 'Mock_Action12');

        $exception = RequestNotSupportedException::createActionNotSupported($action, 'anRequest');

        $this->assertInstanceOf(RequestNotSupportedException::class, $exception);
        $this->assertStringStartsWith(
            'Action Mock_Action12 is not supported the request string.',
            $exception->getMessage()
        );

        $this->assertSame('anRequest', $exception->getRequest());
        $this->assertSame($action, $exception->getAction());
    }

    /**
     * @test
     */
    public function shouldCreateWithActionAndObjectRequest()
    {
        $request = new \stdClass();

        $action = $this->getMock(ActionInterface::class, array(), array(), 'Mock_Action24');

        $exception = RequestNotSupportedException::createActionNotSupported($action, $request);

        $this->assertInstanceOf(RequestNotSupportedException::class, $exception);
        $this->assertStringStartsWith(
            'Action Mock_Action24 is not supported the request stdClass.',
            $exception->getMessage()
        );

        $this->assertSame($request, $exception->getRequest());
        $this->assertSame($action, $exception->getAction());
    }

    /**
     * @test
     */
    public function shouldCreateWithSuggestions()
    {
        $request = new \stdClass();

        $exception = RequestNotSupportedException::create($request);

        $this->assertInstanceOf(RequestNotSupportedException::class, $exception);
        $this->assertEquals(
            'Request stdClass is not supported. Make sure the gateway supports the requests and there is an action which supports this request (The method returns true). There may be a bug, so look for a related issue on the issue tracker.',
            $exception->getMessage()
        );
    }

    /**
     * @test
     */
    public function shouldCreateWithSuggestionsOnIdentityAsModel()
    {
        $request = new Capture(new Identity('theId', \stdClass::class));

        $exception = RequestNotSupportedException::create($request);

        $this->assertInstanceOf(RequestNotSupportedException::class, $exception);
        $this->assertEquals(
            'Request Capture{model: Identity} is not supported. Make sure the storage extension for "stdClass" is registered to the gateway. Make sure the storage find method returns an instance by id "theId". Make sure the gateway supports the requests and there is an action which supports this request (The method returns true). There may be a bug, so look for a related issue on the issue tracker.',
            $exception->getMessage()
        );
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Payum\Core\Action\ActionInterface
     */
    protected function createActionMock()
    {
        return $this->getMock(ActionInterface::class);
    }
}
