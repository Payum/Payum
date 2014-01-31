<?php
namespace Payum\Klarna\Checkout\Tests\Request\Api;

use Payum\Klarna\Checkout\Request\Api\ShowSnippetInteractiveRequest;

class ShowSnippetInteractiveRequestTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfBaseModelInteractiveRequest()
    {
        $rc = new \ReflectionClass('Payum\Klarna\Checkout\Request\Api\ShowSnippetInteractiveRequest');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Request\BaseModelInteractiveRequest'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithOrderAsArgument()
    {
        new ShowSnippetInteractiveRequest($this->createOrderMock());
    }

    /**
     * @test
     */
    public function shouldAllowGetOrderSetInConstructor()
    {
        $expectedOrder = $this->createOrderMock();

        $request = new ShowSnippetInteractiveRequest($expectedOrder);

        $this->assertSame($expectedOrder, $request->getModel());
    }

    /**
     * @test
     */
    public function shouldAllowGetSnippet()
    {
        $order = new \Klarna_Checkout_Order($this->getMock('Klarna_Checkout_ConnectorInterface'));
        $order->parse(array(
            'gui' => array('snippet' => 'expectedSnippet'),
        ));

        $request = new ShowSnippetInteractiveRequest($order);

        $this->assertEquals('expectedSnippet', $request->getSnippet());
    }

    /**
     * @test
     */
    public function shouldAllowGetLayout()
    {
        $order = new \Klarna_Checkout_Order($this->getMock('Klarna_Checkout_ConnectorInterface'));
        $order->parse(array(
            'gui' => array('layout' => 'expectedLayout'),
        ));

        $request = new ShowSnippetInteractiveRequest($order);

        $this->assertEquals('expectedLayout', $request->getLayout());
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Klarna_Checkout_Order
     */
    protected function createOrderMock()
    {
        return $this->getMock('Klarna_Checkout_Order', array(), array(), '', false);
    }
}