<?php
namespace Payum\Klarna\Checkout\Tests;

use Payum\Klarna\Checkout\GlobalStateSafeConnector;

class GlobalStateSafeConnectorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementsConnectorInterface()
    {
        $rc = new \ReflectionClass('Payum\Klarna\Checkout\GlobalStateSafeConnector');

        $this->assertTrue($rc->implementsInterface('Klarna_Checkout_ConnectorInterface'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithSecretAsArgument()
    {
        new GlobalStateSafeConnector($this->createConnectorMock());
    }

    /**
     * @test
     */
    public function shouldSetBaseUrlAndContentTypeBackAfterInternalConnectorCallOnApply()
    {
        \Klarna_Checkout_Order::$baseUri = 'theBaseUri';
        \Klarna_Checkout_Order::$contentType = 'theContentType';

        $connector = new GlobalStateSafeConnector(
            $this->createConnectorMock(),
            'theOtherBaseUri',
            'theOtherContentType'
        );

        $order = new \Klarna_Checkout_Order($connector);

        $connector->apply('GET', $order);

        $this->assertEquals('theBaseUri', \Klarna_Checkout_Order::$baseUri);
        $this->assertEquals('theContentType', \Klarna_Checkout_Order::$contentType);
    }

    /**
     * @test
     */
    public function shouldSetBaseUrlAndContentTypeBackAfterInternalConnectorThrowsExceptionOnApply()
    {
        \Klarna_Checkout_Order::$baseUri = 'theBaseUri';
        \Klarna_Checkout_Order::$contentType = 'theContentType';

        $internalConnectorMock = $this->createConnectorMock();
        $internalConnectorMock
            ->expects($this->once())
            ->method('apply')
            ->will($this->throwException(new \Exception))
        ;

        $connector = new GlobalStateSafeConnector(
            $internalConnectorMock,
            'theOtherBaseUri',
            'theOtherContentType'
        );

        $order = new \Klarna_Checkout_Order($connector);

        try {
            $connector->apply('GET', $order);
        } catch (\Exception $e) {
            $this->assertEquals('theBaseUri', \Klarna_Checkout_Order::$baseUri);
            $this->assertEquals('theContentType', \Klarna_Checkout_Order::$contentType);

            return;
        }

        $this->fail('Expect an exception to be thrown');
    }

    /**
     * @test
     */
    public function shouldProxyAllArgumentsAsIsToInternalConnectorOnApply()
    {
        $expectedMethod = 'theMethod';
        $expectedResourceMock = $this->getMock('Klarna_Checkout_ResourceInterface');
        $expectedOptions = array('foo', 'bar', 'baz', 'url' => 'foo');

        $internalConnectorMock = $this->createConnectorMock();
        $internalConnectorMock
            ->expects($this->once())
            ->method('apply')
            ->with(
                $expectedMethod,
                $this->identicalTo($expectedResourceMock),
                $expectedOptions
            )
        ;

        $connector = new GlobalStateSafeConnector(
            $internalConnectorMock,
            'theOtherBaseUri',
            'theOtherContentType'
        );

        $connector->apply($expectedMethod, $expectedResourceMock, $expectedOptions);
    }

    /**
     * @test
     */
    public function shouldProxyAllArgumentsAddingUriIfNotSetToInternalConnectorOnApply()
    {
        $expectedMethod = 'theMethod';
        $expectedResourceMock = $this->getMock('Klarna_Checkout_ResourceInterface');
        $expectedOptions = array('foo', 'bar', 'baz', 'url' => 'theOtherBaseUri');

        $internalConnectorMock = $this->createConnectorMock();
        $internalConnectorMock
            ->expects($this->once())
            ->method('apply')
            ->with(
                $expectedMethod,
                $this->identicalTo($expectedResourceMock),
                $expectedOptions
            )
        ;

        $connector = new GlobalStateSafeConnector(
            $internalConnectorMock,
            'theOtherBaseUri',
            'theOtherContentType'
        );

        $connector->apply($expectedMethod, $expectedResourceMock, $expectedOptions);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Klarna_Checkout_ConnectorInterface
     */
    protected function createConnectorMock()
    {
        return $this->getMock('Klarna_Checkout_ConnectorInterface');
    }
} 