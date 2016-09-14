<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Action\Api;

use Payum\Paypal\ExpressCheckout\Nvp\Action\Api\DoVoidAction;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\DoVoid;

class DoVoidActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfBaseApiAwareAction()
    {
        $rc = new \ReflectionClass('Payum\Paypal\ExpressCheckout\Nvp\Action\Api\DoVoidAction');

        $this->assertTrue($rc->isSubclassOf('Payum\Paypal\ExpressCheckout\Nvp\Action\Api\BaseApiAwareAction'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new DoVoidAction();
    }

    /**
     * @test
     */
    public function shouldSupportDoVoidRequestAndArrayAccessAsModel()
    {
        $action = new DoVoidAction();

        $this->assertTrue(
            $action->supports(new DoVoid($this->getMock('ArrayAccess')))
        );
    }

    /**
     * @test
     */
    public function shouldNotSupportAnythingNotDoVoidRequest()
    {
        $action = new DoVoidAction();

        $this->assertFalse($action->supports(new \stdClass()));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\RequestNotSupportedException
     */
    public function throwIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $action = new DoVoidAction();

        $action->execute(new \stdClass());
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\LogicException
     * @expectedExceptionMessage TRANSACTIONID must be set. Has user not authorized this transaction?
     */
    public function throwIfTransactionIdNotSetInModel()
    {
        $action = new DoVoidAction();

        $request = new DoVoid(array());

        $action->execute($request);
    }

    /**
     * @test
     */
    public function shouldCallApiDoVoidMethodWithExpectedRequiredArguments()
    {
        $testCase = $this;

        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('DoVoid')
            ->will($this->returnCallback(function (array $fields) use ($testCase) {
                $testCase->assertArrayHasKey('TRANSACTIONID', $fields);
                $testCase->assertEquals('theTransactionId', $fields['TRANSACTIONID']);

                return array();
            }))
        ;

        $action = new DoVoidAction();
        $action->setApi($apiMock);

        $request = new DoVoid(array(
            'TRANSACTIONID' => 'theTransactionId',
        ));

        $action->execute($request);
    }

    /**
     * @test
     */
    public function shouldCallApiDoVoidMethodAndUpdateModelFromResponseOnSuccess()
    {
        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('DoVoid')
            ->will($this->returnCallback(function () {
                return array(
                    'AUTHORIZATIONID' => 'theTransactionId',
                    'MSGSUBID' => 'aMessageId',
                );
            }))
        ;

        $action = new DoVoidAction();
        $action->setApi($apiMock);

        $request = new DoVoid(array(
            'TRANSACTIONID' => 'theTransactionId',
        ));

        $action->execute($request);

        $model = $request->getModel();

        $this->assertArrayHasKey('AUTHORIZATIONID', $model);
        $this->assertEquals('theTransactionId', $model['AUTHORIZATIONID']);

        $this->assertArrayHasKey('MSGSUBID', $model);
        $this->assertEquals('aMessageId', $model['MSGSUBID']);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Payum\Paypal\ExpressCheckout\Nvp\Api
     */
    protected function createApiMock()
    {
        return $this->getMock('Payum\Paypal\ExpressCheckout\Nvp\Api', array(), array(), '', false);
    }
}
