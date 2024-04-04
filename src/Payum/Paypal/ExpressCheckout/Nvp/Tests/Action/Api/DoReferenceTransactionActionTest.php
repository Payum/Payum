<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Action\Api;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Paypal\ExpressCheckout\Nvp\Action\Api\DoReferenceTransactionAction;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\DoReferenceTransaction;

class DoReferenceTransactionActionTest extends \PHPUnit\Framework\TestCase
{
    public function testShouldImplementActionInterface()
    {
        $rc = new \ReflectionClass(DoReferenceTransactionAction::class);

        $this->assertTrue($rc->implementsInterface(ActionInterface::class));
    }

    public function testShouldImplementApoAwareInterface()
    {
        $rc = new \ReflectionClass(DoReferenceTransactionAction::class);

        $this->assertTrue($rc->implementsInterface(ApiAwareInterface::class));
    }

    public function testShouldSupportDoReferenceTransactionRequestAndArrayAccessAsModel()
    {
        $action = new DoReferenceTransactionAction();

        $this->assertTrue($action->supports(new DoReferenceTransaction($this->createMock('ArrayAccess'))));
    }

    public function testShouldNotSupportAnythingNotDoReferenceTransactionRequest()
    {
        $action = new DoReferenceTransactionAction();

        $this->assertFalse($action->supports(new \stdClass()));
    }

    public function testThrowIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $this->expectException(\Payum\Core\Exception\RequestNotSupportedException::class);
        $action = new DoReferenceTransactionAction();

        $action->execute(new \stdClass());
    }

    public function testThrowIfReferenceIdNotSetInModel()
    {
        $this->expectException(\Payum\Core\Exception\LogicException::class);
        $this->expectExceptionMessage('REFERENCEID must be set.');
        $action = new DoReferenceTransactionAction();

        $action->execute(new DoReferenceTransaction(array()));
    }

    public function testThrowIfPaymentActionNotSet()
    {
        $this->expectException(\Payum\Core\Exception\LogicException::class);
        $this->expectExceptionMessage('PAYMENTACTION must be set.');
        $action = new DoReferenceTransactionAction();

        $request = new DoReferenceTransaction(array(
            'REFERENCEID' => 'aReferenceId',
        ));

        $action->execute($request);
    }

    public function testThrowIfAmtNotSet()
    {
        $this->expectException(\Payum\Core\Exception\LogicException::class);
        $this->expectExceptionMessage('AMT must be set.');
        $action = new DoReferenceTransactionAction();

        $request = new DoReferenceTransaction(array(
            'REFERENCEID' => 'aReferenceId',
            'PAYMENTACTION' => 'anAction',
        ));

        $action->execute($request);
    }

    public function testShouldCallApiDoReferenceTransactionMethodWithExpectedRequiredArguments()
    {
        $testCase = $this;

        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('doReferenceTransaction')
            ->willReturnCallback(function (array $fields) use ($testCase) {
                $testCase->assertArrayHasKey('REFERENCEID', $fields);
                $testCase->assertSame('theReferenceId', $fields['REFERENCEID']);

                $testCase->assertArrayHasKey('AMT', $fields);
                $testCase->assertSame('theAmt', $fields['AMT']);

                $testCase->assertArrayHasKey('PAYMENTACTION', $fields);
                $testCase->assertSame('theAction', $fields['PAYMENTACTION']);

                return array();
            })
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

    public function testShouldCallApiDoReferenceTransactionMethodAndUpdateModelFromResponseOnSuccess()
    {
        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('doReferenceTransaction')
            ->willReturnCallback(function () {
                return array(
                    'FIRSTNAME' => 'theFirstname',
                    'EMAIL' => 'the@example.com',
                );
            })
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
        $this->assertSame('theFirstname', $model['FIRSTNAME']);

        $this->assertArrayHasKey('EMAIL', $model);
        $this->assertSame('the@example.com', $model['EMAIL']);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Payum\Paypal\ExpressCheckout\Nvp\Api
     */
    protected function createApiMock()
    {
        return $this->createMock('Payum\Paypal\ExpressCheckout\Nvp\Api', array(), array(), '', false);
    }
}
