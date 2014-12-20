<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Action\Api;

use Payum\Paypal\ExpressCheckout\Nvp\Action\Api\DoReferenceTransactionAction;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\DoReferenceTransaction;

class DoReferenceTransactionActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfBaseApiAwareAction()
    {
        $rc = new \ReflectionClass('Payum\Paypal\ExpressCheckout\Nvp\Action\Api\DoReferenceTransactionAction');

        $this->assertTrue($rc->isSubclassOf('Payum\Paypal\ExpressCheckout\Nvp\Action\Api\BaseApiAwareAction'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new DoReferenceTransactionAction();
    }

    /**
     * @test
     */
    public function shouldSupportDoReferenceTransactionRequestAndArrayAccessAsModel()
    {
        $action = new DoReferenceTransactionAction();

        $this->assertTrue($action->supports(new DoReferenceTransaction($this->getMock('ArrayAccess'))));
    }

    /**
     * @test
     */
    public function shouldNotSupportAnythingNotDoReferenceTransactionRequest()
    {
        $action = new DoReferenceTransactionAction();

        $this->assertFalse($action->supports(new \stdClass()));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\RequestNotSupportedException
     */
    public function throwIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $action = new DoReferenceTransactionAction();

        $action->execute(new \stdClass());
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\LogicException
     * @expectedExceptionMessage REFERENCEID must be set.
     */
    public function throwIfReferenceIdNotSetInModel()
    {
        $action = new DoReferenceTransactionAction();

        $action->execute(new DoReferenceTransaction(array()));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\LogicException
     * @expectedExceptionMessage PAYMENTACTION must be set.
     */
    public function throwIfPaymentActionNotSet()
    {
        $action = new DoReferenceTransactionAction();

        $request = new DoReferenceTransaction(array(
            'REFERENCEID' => 'aReferenceId',
        ));

        $action->execute($request);
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\LogicException
     * @expectedExceptionMessage AMT must be set.
     */
    public function throwIfAmtNotSet()
    {
        $action = new DoReferenceTransactionAction();

        $request = new DoReferenceTransaction(array(
            'REFERENCEID' => 'aReferenceId',
            'PAYMENTACTION' => 'anAction',
        ));

        $action->execute($request);
    }

    /**
     * @test
     */
    public function shouldCallApiDoReferenceTransactionMethodWithExpectedRequiredArguments()
    {
        $testCase = $this;

        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('doReferenceTransaction')
            ->will($this->returnCallback(function (array $fields) use ($testCase) {
                $testCase->assertArrayHasKey('REFERENCEID', $fields);
                $testCase->assertEquals('theReferenceId', $fields['REFERENCEID']);

                $testCase->assertArrayHasKey('AMT', $fields);
                $testCase->assertEquals('theAmt', $fields['AMT']);

                $testCase->assertArrayHasKey('PAYMENTACTION', $fields);
                $testCase->assertEquals('theAction', $fields['PAYMENTACTION']);

                return array();
            }))
        ;

        $action = new DoReferenceTransactionAction();
        $action->setApi($apiMock);

        $request = new DoReferenceTransaction(array(
            'REFERENCEID' => 'theReferenceId',
            'PAYMENTACTION' => 'theAction',
            'AMT' => 'theAmt',
        ));

        $action->execute($request);
    }

    /**
     * @test
     */
    public function shouldCallApiDoReferenceTransactionMethodAndUpdateModelFromResponseOnSuccess()
    {
        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('doReferenceTransaction')
            ->will($this->returnCallback(function () {
                return array(
                    'FIRSTNAME' => 'theFirstname',
                    'EMAIL' => 'the@example.com',
                );
            }))
        ;

        $action = new DoReferenceTransactionAction();
        $action->setApi($apiMock);

        $request = new DoReferenceTransaction(array(
            'REFERENCEID' => 'aReferenceId',
            'PAYMENTACTION' => 'anAction',
            'AMT' => 'anAmt',
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
