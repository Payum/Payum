<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Action\Api;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Paypal\ExpressCheckout\Nvp\Action\Api\DoReferenceTransactionAction;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\DoReferenceTransaction;

class DoReferenceTransactionActionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test
     */
    public function shouldImplementActionInterface()
    {
        $rc = new \ReflectionClass(DoReferenceTransactionAction::class);

        $this->assertTrue($rc->implementsInterface(ActionInterface::class));
    }

    /**
     * @test
     */
    public function shouldImplementApoAwareInterface()
    {
        $rc = new \ReflectionClass(DoReferenceTransactionAction::class);

        $this->assertTrue($rc->implementsInterface(ApiAwareInterface::class));
    }

    /**
     * @test
     */
    public function shouldSupportDoReferenceTransactionRequestAndArrayAccessAsModel()
    {
        $action = new DoReferenceTransactionAction();

        $this->assertTrue($action->supports(new DoReferenceTransaction($this->createMock('ArrayAccess'))));
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
     */
    public function throwIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $this->expectException(\Payum\Core\Exception\RequestNotSupportedException::class);
        $action = new DoReferenceTransactionAction();

        $action->execute(new \stdClass());
    }

    /**
     * @test
     */
    public function throwIfReferenceIdNotSetInModel()
    {
        $this->expectException(\Payum\Core\Exception\LogicException::class);
        $this->expectExceptionMessage('REFERENCEID must be set.');
        $action = new DoReferenceTransactionAction();

        $action->execute(new DoReferenceTransaction(array()));
    }

    /**
     * @test
     */
    public function throwIfPaymentActionNotSet()
    {
        $this->expectException(\Payum\Core\Exception\LogicException::class);
        $this->expectExceptionMessage('PAYMENTACTION must be set.');
        $action = new DoReferenceTransactionAction();

        $request = new DoReferenceTransaction(array(
            'REFERENCEID' => 'aReferenceId',
        ));

        $action->execute($request);
    }

    /**
     * @test
     */
    public function throwIfAmtNotSet()
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
        return $this->createMock('Payum\Paypal\ExpressCheckout\Nvp\Api', array(), array(), '', false);
    }
}
