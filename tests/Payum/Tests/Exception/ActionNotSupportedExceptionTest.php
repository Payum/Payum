<?php
namespace Payum\Tests\Exception;

use Payum\Exception\RequestNotSupportedException;

class RequestNotSupportedExceptionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfInvalidArgumentException()
    {
        $rc = new \ReflectionClass('Payum\Exception\RequestNotSupportedException');
        
        $this->assertTrue($rc->isSubclassOf('Payum\Exception\InvalidArgumentException'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new RequestNotSupportedException;
    }

    /**
     * @test
     */
    public function shouldCreateWithNoneObjectRequest()
    {
        $exception = RequestNotSupportedException::create('anRequest');
        
        $this->assertInstanceOf('Payum\Exception\RequestNotSupportedException', $exception);
        $this->assertEquals('Request string is not supported.', $exception->getMessage());
    }

    /**
     * @test
     */
    public function shouldCreateWithObjectRequest()
    {
        $exception = RequestNotSupportedException::create(new \stdClass());

        $this->assertInstanceOf('Payum\Exception\RequestNotSupportedException', $exception);
        $this->assertEquals('Request stdClass is not supported.', $exception->getMessage());
    }

    /**
     * @test
     */
    public function shouldCreateWithActionAndStringRequest()
    {
        $action = $this->getMock('Payum\Action\ActionInterface', array(), array(), 'Mock_Action12');
        
        $exception = RequestNotSupportedException::createActionNotSupported($action, 'anRequest');

        $this->assertInstanceOf('Payum\Exception\RequestNotSupportedException', $exception);
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
        $action = $this->getMock('Payum\Action\ActionInterface', array(), array(), 'Mock_Action24');

        $exception = RequestNotSupportedException::createActionNotSupported($action, new \stdClass());

        $this->assertInstanceOf('Payum\Exception\RequestNotSupportedException', $exception);
        $this->assertEquals(
            'Action Mock_Action24 is not supported the request stdClass.',
            $exception->getMessage()
        );
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Payum\Action\ActionInterface
     */
    protected function createActionMock()
    {
        return $this->getMock('Payum\Action\ActionInterface');
    }
}
