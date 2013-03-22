<?php
namespace Payum\Tests\Request;

use Payum\Request\BinaryMaskStatusRequest;

class BinaryMaskStatusRequestTest extends \PHPUnit_Framework_TestCase
{
    public static function provideIsXXXMethods()
    {
        return array(
            array('isSuccess'),
            array('isCanceled'),
            array('isPending'),
            array('isFailed'),
            array('isNew'),
            array('isUnknown'),
        );
    }

    public static function provideMarkXXXMethods()
    {
        return array(
            array('markSuccess'),
            array('markCanceled'),
            array('markPending'),
            array('markFailed'),
            array('markNew'),
            array('markUnknown'),
        );
    }
    
    /**
     * @test
     */
    public function shouldImplementStatusRequestInterface()
    {
        $rc = new \ReflectionClass('Payum\Request\BinaryMaskStatusRequest');

        $this->assertTrue($rc->implementsInterface('Payum\Request\StatusRequestInterface'));
    }

    /**
     * @test
     */
    public function shouldBeSubClassOfBaseModelInteractiveRequest()
    {
        $rc = new \ReflectionClass('Payum\Request\BinaryMaskStatusRequest');

        $this->assertTrue($rc->isSubclassOf('Payum\Request\BaseModelInteractiveRequest'));
    }

    /**
     * @test
     */
    public function shouldMarkUnknownInConstructor()
    {
        $statusRequest = new BinaryMaskStatusRequest(new \stdClass);

        $this->assertTrue($statusRequest->isUnknown());
    }

    /**
     * @test
     * 
     * @dataProvider provideMarkXXXMethods
     */
    public function shouldAllowGetMarkedStatus($markXXXMethod)
    {
        $statusRequest = new BinaryMaskStatusRequest(new \stdClass);

        $statusRequest->$markXXXMethod();
        
        $this->assertNotEmpty($statusRequest->getStatus());
    }

    /**
     * @test
     */
    public function shouldNotMatchOthersStatusIfMarkedSuccess()
    {
        $statusRequest = new BinaryMaskStatusRequest(new \stdClass);

        $statusRequest->markSuccess();
        
        $this->assertTrue($statusRequest->isSuccess());
        
        $this->assertFalse($statusRequest->isCanceled());
        $this->assertFalse($statusRequest->isPending());
        $this->assertFalse($statusRequest->isFailed());
        $this->assertFalse($statusRequest->isNew());
        $this->assertFalse($statusRequest->isUnknown());
    }

    /**
     * @test
     */
    public function shouldNotMatchOthersStatusIfMarkedFailed()
    {
        $statusRequest = new BinaryMaskStatusRequest(new \stdClass);

        $statusRequest->markFailed();

        $this->assertTrue($statusRequest->isFailed());
        
        $this->assertFalse($statusRequest->isSuccess());
        $this->assertFalse($statusRequest->isCanceled());
        $this->assertFalse($statusRequest->isPending());
        $this->assertFalse($statusRequest->isNew());
        $this->assertFalse($statusRequest->isUnknown());
    }

    /**
     * @test
     */
    public function shouldNotMatchOthersStatusIfMarkedInPorgress()
    {
        $statusRequest = new BinaryMaskStatusRequest(new \stdClass);

        $statusRequest->markPending();

        $this->assertTrue($statusRequest->isPending());
        
        $this->assertFalse($statusRequest->isFailed());
        $this->assertFalse($statusRequest->isSuccess());
        $this->assertFalse($statusRequest->isCanceled());
        $this->assertFalse($statusRequest->isNew());
        $this->assertFalse($statusRequest->isUnknown());
    }

    /**
     * @test
     */
    public function shouldNotMatchOthersStatusIfMarkedCanceled()
    {
        $statusRequest = new BinaryMaskStatusRequest(new \stdClass);

        $statusRequest->markCanceled();

        $this->assertTrue($statusRequest->isCanceled());
        
        $this->assertFalse($statusRequest->isPending());
        $this->assertFalse($statusRequest->isFailed());
        $this->assertFalse($statusRequest->isSuccess());
        $this->assertFalse($statusRequest->isNew());
        $this->assertFalse($statusRequest->isUnknown());
    }

    /**
     * @test
     */
    public function shouldNotMatchOthersStatusIfMarkedNew()
    {
        $statusRequest = new BinaryMaskStatusRequest(new \stdClass);

        $statusRequest->markNew();

        $this->assertTrue($statusRequest->isNew());

        $this->assertFalse($statusRequest->isSuccess());
        $this->assertFalse($statusRequest->isCanceled());
        $this->assertFalse($statusRequest->isPending());
        $this->assertFalse($statusRequest->isFailed());
        $this->assertFalse($statusRequest->isUnknown());
    }

    /**
     * @test
     */
    public function shouldNotMatchOthersStatusIfMarkedUnknown()
    {
        $statusRequest = new BinaryMaskStatusRequest(new \stdClass);

        $statusRequest->markUnknown();

        $this->assertTrue($statusRequest->isUnknown());

        $this->assertFalse($statusRequest->isSuccess());
        $this->assertFalse($statusRequest->isCanceled());
        $this->assertFalse($statusRequest->isPending());
        $this->assertFalse($statusRequest->isFailed());
        $this->assertFalse($statusRequest->isNew());
    }
}

