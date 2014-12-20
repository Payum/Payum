<?php
namespace Payum\Core\Tests\Exception;

use Payum\Core\Exception\RequestNotSupportedException;

class RequestNotSupportedExceptionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfInvalidArgumentException()
    {
        $rc = new \ReflectionClass('Payum\Core\Exception\RequestNotSupportedException');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Exception\InvalidArgumentException'));
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

        $this->assertInstanceOf('Payum\Core\Exception\RequestNotSupportedException', $exception);
        $this->assertEquals('Request string is not supported.', $exception->getMessage());
    }

    /**
     * @test
     */
    public function shouldCreateWithObjectRequest()
    {
        $exception = RequestNotSupportedException::create(new \stdClass());

        $this->assertInstanceOf('Payum\Core\Exception\RequestNotSupportedException', $exception);
        $this->assertEquals('Request stdClass is not supported.', $exception->getMessage());
    }

    /**
     * @test
     */
    public function shouldCreateWithActionAndStringRequest()
    {
        $action = $this->getMock('Payum\Core\Action\ActionInterface', array(), array(), 'Mock_Action12');

        $exception = RequestNotSupportedException::createActionNotSupported($action, 'anRequest');

        $this->assertInstanceOf('Payum\Core\Exception\RequestNotSupportedException', $exception);
        $this->assertEquals(
            'Action Mock_Action12 is not supported the request string.',
            $exception->getMessage()
        );
    }

    /**
     * @test
     */
    public function shouldCreateWithActionAndObjectRequest()
    {
        $action = $this->getMock('Payum\Core\Action\ActionInterface', array(), array(), 'Mock_Action24');

        $exception = RequestNotSupportedException::createActionNotSupported($action, new \stdClass());

        $this->assertInstanceOf('Payum\Core\Exception\RequestNotSupportedException', $exception);
        $this->assertEquals(
            'Action Mock_Action24 is not supported the request stdClass.',
            $exception->getMessage()
        );
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Payum\Core\Action\ActionInterface
     */
    protected function createActionMock()
    {
        return $this->getMock('Payum\Core\Action\ActionInterface');
    }
}
