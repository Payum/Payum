<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Action\Api;

use Payum\Paypal\ExpressCheckout\Nvp\Action\Api\DoCaptureAction;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\DoCapture;

class DoCaptureActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfBaseApiAwareAction()
    {
        $rc = new \ReflectionClass('Payum\Paypal\ExpressCheckout\Nvp\Action\Api\DoCaptureAction');

        $this->assertTrue($rc->isSubclassOf('Payum\Paypal\ExpressCheckout\Nvp\Action\Api\BaseApiAwareAction'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new DoCaptureAction();
    }

    /**
     * @test
     */
    public function shouldSupportDoCaptureRequestAndArrayAccessAsModel()
    {
        $action = new DoCaptureAction();

        $this->assertTrue($action->supports(new DoCapture($this->getMock('ArrayAccess'))));
    }

    /**
     * @test
     */
    public function shouldNotSupportAnythingNotDoCaptureRequest()
    {
        $action = new DoCaptureAction();

        $this->assertFalse($action->supports(new \stdClass()));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\RequestNotSupportedException
     */
    public function throwIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $action = new DoCaptureAction();

        $action->execute(new \stdClass());
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\LogicException
     * @expectedExceptionMessage TRANSACTIONID or AUTHORIZATIONID must be set.
     */
    public function throwIfTransactionIdNorAuthorizationIdNotSetInModel()
    {
        $action = new DoCaptureAction();

        $action->execute(new DoCapture(array()));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\LogicException
     * @expectedExceptionMessage The COMPLETETYPE fields are required.
     */
    public function throwIfCompleteTypeNotSet()
    {
        $action = new DoCaptureAction();

        $request = new DoCapture(array(
            'TRANSACTIONID' => 'aTransactionId',
            'AMT' => 100,
        ));

        $action->execute($request);
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\LogicException
     * @expectedExceptionMessage The AMT fields are required.
     */
    public function throwIfAmtNotSet()
    {
        $action = new DoCaptureAction();

        $request = new DoCapture(array(
            'TRANSACTIONID' => 'aReferenceId',
            'COMPLETETYPE' => 'Complete',
        ));

        $action->execute($request);
    }

    /**
     * @test
     */
    public function shouldCallApiDoCaptureMethodWithExpectedRequiredArguments()
    {
        $testCase = $this;

        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('DoCapture')
            ->will($this->returnCallback(function (array $fields) use ($testCase) {
                $testCase->assertArrayHasKey('TRANSACTIONID', $fields);
                $testCase->assertEquals('theTransactionId', $fields['TRANSACTIONID']);

                $testCase->assertArrayHasKey('AMT', $fields);
                $testCase->assertEquals('theAmt', $fields['AMT']);

                $testCase->assertArrayHasKey('COMPLETETYPE', $fields);
                $testCase->assertEquals('Complete', $fields['COMPLETETYPE']);

                return array();
            }))
        ;

        $action = new DoCaptureAction();
        $action->setApi($apiMock);

        $request = new DoCapture(array(
            'TRANSACTIONID' => 'theTransactionId',
            'COMPLETETYPE' => 'Complete',
            'AMT' => 'theAmt',
        ));

        $action->execute($request);
    }

    /**
     * @test
     */
    public function shouldCallApiDoCaptureMethodAndUpdateModelFromResponseOnSuccess()
    {
        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('DoCapture')
            ->will($this->returnCallback(function () {
                return array(
                    'FIRSTNAME' => 'theFirstname',
                    'EMAIL' => 'the@example.com',
                );
            }))
        ;

        $action = new DoCaptureAction();
        $action->setApi($apiMock);

        $request = new DoCapture(array(
            'TRANSACTIONID' => 'theTransactionId',
            'COMPLETETYPE' => 'Complete',
            'AMT' => 'theAmt',
        ));

        $action->execute($request);

        $model = $request->getModel();

        $this->assertArrayHasKey('FIRSTNAME', $model);
        $this->assertEquals('theFirstname', $model['FIRSTNAME']);

        $this->assertArrayHasKey('EMAIL', $model);
        $this->assertEquals('the@example.com', $model['EMAIL']);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Payum\Paypal\ExpressCheckout\Nvp\Api
     */
    protected function createApiMock()
    {
        return $this->getMock('Payum\Paypal\ExpressCheckout\Nvp\Api', array(), array(), '', false);
    }
}
