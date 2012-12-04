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
            array('isInProgress'),
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
            array('markInProgress'),
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
    public function shouldBeSubClassOfLogicException()
    {
        $rc = new \ReflectionClass('Payum\Request\BinaryMaskStatusRequest');

        $this->assertTrue($rc->isSubclassOf('Payum\Exception\LogicException'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithModelAsArgument()
    {
        new BinaryMaskStatusRequest($this->createModelMock());
    }

    /**
     * @test
     */
    public function shouldAllowGetModelSetInConstructor()
    {
        $expectedModel = $this->createModelMock();
        
        $statusRequest = new BinaryMaskStatusRequest($expectedModel);
        
        $this->assertSame($expectedModel, $statusRequest->getModel());
    }

    /**
     * @test
     */
    public function shouldMarkUnknownInConstructor()
    {
        $statusRequest = new BinaryMaskStatusRequest($this->createModelMock());

        $this->assertTrue($statusRequest->isUnknown());
    }

    /**
     * @test
     * 
     * @dataProvider provideMarkXXXMethods
     */
    public function shouldAllowGetMarkedStatus($markXXXMethod)
    {
        $statusRequest = new BinaryMaskStatusRequest($this->createModelMock());

        $statusRequest->$markXXXMethod();
        
        $this->assertNotEmpty($statusRequest->getStatus());
    }

    /**
     * @test
     */
    public function shouldNotMatchOthersStatusIfMarkedSuccess()
    {
        $statusRequest = new BinaryMaskStatusRequest($this->createModelMock());

        $statusRequest->markSuccess();
        
        $this->assertTrue($statusRequest->isSuccess());
        
        $this->assertFalse($statusRequest->isCanceled());
        $this->assertFalse($statusRequest->isInProgress());
        $this->assertFalse($statusRequest->isFailed());
        $this->assertFalse($statusRequest->isNew());
        $this->assertFalse($statusRequest->isUnknown());
    }

    /**
     * @test
     */
    public function shouldNotMatchOthersStatusIfMarkedFailed()
    {
        $statusRequest = new BinaryMaskStatusRequest($this->createModelMock());

        $statusRequest->markFailed();

        $this->assertTrue($statusRequest->isFailed());
        
        $this->assertFalse($statusRequest->isSuccess());
        $this->assertFalse($statusRequest->isCanceled());
        $this->assertFalse($statusRequest->isInProgress());
        $this->assertFalse($statusRequest->isNew());
        $this->assertFalse($statusRequest->isUnknown());
    }

    /**
     * @test
     */
    public function shouldNotMatchOthersStatusIfMarkedInPorgress()
    {
        $statusRequest = new BinaryMaskStatusRequest($this->createModelMock());

        $statusRequest->markInProgress();

        $this->assertTrue($statusRequest->isInProgress());
        
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
        $statusRequest = new BinaryMaskStatusRequest($this->createModelMock());

        $statusRequest->markCanceled();

        $this->assertTrue($statusRequest->isCanceled());
        
        $this->assertFalse($statusRequest->isInProgress());
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
        $statusRequest = new BinaryMaskStatusRequest($this->createModelMock());

        $statusRequest->markNew();

        $this->assertTrue($statusRequest->isNew());

        $this->assertFalse($statusRequest->isSuccess());
        $this->assertFalse($statusRequest->isCanceled());
        $this->assertFalse($statusRequest->isInProgress());
        $this->assertFalse($statusRequest->isFailed());
        $this->assertFalse($statusRequest->isUnknown());
    }

    /**
     * @test
     */
    public function shouldNotMatchOthersStatusIfMarkedUnknown()
    {
        $statusRequest = new BinaryMaskStatusRequest($this->createModelMock());

        $statusRequest->markUnknown();

        $this->assertTrue($statusRequest->isUnknown());

        $this->assertFalse($statusRequest->isSuccess());
        $this->assertFalse($statusRequest->isCanceled());
        $this->assertFalse($statusRequest->isInProgress());
        $this->assertFalse($statusRequest->isFailed());
        $this->assertFalse($statusRequest->isNew());
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Payum\Domain\ModelInterface
     */
    protected function createModelMock()
    {
        return $this->getMock('Payum\Domain\ModelInterface');
    }
}

