<?php
namespace Payum\AuthorizeNet\Aim\Tests\Action;

use Payum\AuthorizeNet\Aim\Action\StatusAction;
use Payum\AuthorizeNet\Aim\Bridge\AuthorizeNet\AuthorizeNetAIM;
use Payum\AuthorizeNet\Aim\PaymentInstruction;
use Payum\Bridge\Spl\ArrayObject;
use Payum\Request\BinaryMaskStatusRequest;
use Payum\Request\StatusRequestInterface;

class StatusActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementActionInterface()
    {
        $rc = new \ReflectionClass('Payum\AuthorizeNet\Aim\Action\StatusAction');
        
        $this->assertTrue($rc->implementsInterface('Payum\Action\ActionInterface'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()   
    {
        new StatusAction();
    }

    /**
     * @test
     */
    public function shouldSupportStatusRequestAndArrayAccessAsModel()
    {
        $action = new StatusAction();

        $request = $this->createStatusRequestStub($this->getMock('ArrayAccess'));

        $this->assertTrue($action->supports($request));
    }

    /**
     * @test
     */
    public function shouldSupportStatusRequestWithPaymentInstructionAsModel()
    {
        $action = new StatusAction();

        $request = $this->createStatusRequestStub(new PaymentInstruction);

        $this->assertTrue($action->supports($request));
    }

    /**
     * @test
     */
    public function shouldNotSupportNotStatusRequest()
    {
        $action = new StatusAction();

        $request = new \stdClass();

        $this->assertFalse($action->supports($request));
    }

    /**
     * @test
     */
    public function shouldNotSupportStatusRequestAndNotArrayAccessAsModel()
    {
        $action = new StatusAction();

        $request = $this->createStatusRequestStub(new \stdClass());

        $this->assertFalse($action->supports($request));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Exception\RequestNotSupportedException
     */
    public function throwIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $action = new StatusAction();

        $action->execute(new \stdClass());
    }

    /**
     * @test
     */
    public function shouldMarkUnknownStatusIfEmptyArrayObjectSetAsModel()
    {
        $action = new StatusAction();

        $request = new BinaryMaskStatusRequest(new ArrayObject());

        $action->execute($request);
        
        $this->assertTrue($request->isUnknown());
    }

    /**
     * @test
     */
    public function shouldMarkSuccessStatusIfArrayObjectHasResponseCodeApproved()
    {
        $action = new StatusAction();

        $model = new ArrayObject();
        $model['response_code'] = \AuthorizeNetAIM_Response::APPROVED;

        $request = new BinaryMaskStatusRequest($model);

        $action->execute($request);

        $this->assertTrue($request->isSuccess());
    }

    /**
     * @test
     */
    public function shouldMarkFailedStatusIfArrayObjectHasResponseCodeError()
    {
        $action = new StatusAction();

        $model = new ArrayObject();
        $model['response_code'] = \AuthorizeNetAIM_Response::ERROR;

        $request = new BinaryMaskStatusRequest($model);

        $action->execute($request);

        $this->assertTrue($request->isFailed());
    }

    /**
     * @test
     */
    public function shouldMarkPendingStatusIfArrayObjectHasResponseCodeHeld()
    {
        $action = new StatusAction();

        $model = new ArrayObject();
        $model['response_code'] = \AuthorizeNetAIM_Response::HELD;

        $request = new BinaryMaskStatusRequest($model);

        $action->execute($request);

        $this->assertTrue($request->isPending());
    }

    /**
     * @test
     */
    public function shouldMarkCanceledStatusIfArrayObjectHasResponseCodeDeclined()
    {
        $action = new StatusAction();

        $model = new ArrayObject();
        $model['response_code'] = \AuthorizeNetAIM_Response::DECLINED;

        $request = new BinaryMaskStatusRequest($model);

        $action->execute($request);

        $this->assertTrue($request->isCanceled());
    }
    
    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|StatusRequestInterface
     */
    protected function createStatusRequestStub($model)
    {
        $status = $this->getMock('Payum\Request\StatusRequestInterface');

        $status
            ->expects($this->any())
            ->method('getModel')
            ->will($this->returnValue($model))
        ;
        
        return $status;
    }
}