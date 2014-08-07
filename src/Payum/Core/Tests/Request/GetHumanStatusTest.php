<?php
namespace Payum\Core\Tests\Request;

use Payum\Core\Request\GetHumanStatus;

class GetHumanStatusTest extends \PHPUnit_Framework_TestCase
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
            array('isSuspended'),
            array('isExpired')
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
            array('markSuspended'),
            array('markExpired')
        );
    }

    /**
     * @test
     */
    public function shouldBeSubClassOfBaseStatusRequest()
    {
        $rc = new \ReflectionClass('Payum\Core\Request\GetHumanStatus');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Request\BaseGetStatus'));
    }

    /**
     * @test
     */
    public function shouldMarkUnknownInConstructor()
    {
        $statusRequest = new GetHumanStatus(new \stdClass);

        $this->assertTrue($statusRequest->isUnknown());
    }

    /**
     * @test
     * 
     * @dataProvider provideMarkXXXMethods
     */
    public function shouldAllowGetMarkedStatus($markXXXMethod)
    {
        $statusRequest = new GetHumanStatus(new \stdClass);

        $statusRequest->$markXXXMethod();
        
        $this->assertNotEmpty($statusRequest->getStatus());
    }

    /**
     * @test
     *
     * @dataProvider provideIsXXXMethods
     */
    public function shouldCallIsXXXStatus($isXXXMethod)
    {
        $statusRequest = new GetHumanStatus(new \stdClass);

        $this->assertInternalType('boolean', $statusRequest->$isXXXMethod());
    }

    /**
     * @test
     */
    public function shouldNotMatchOthersThenSuccessStatus()
    {
        $statusRequest = new GetHumanStatus(new \stdClass);

        $statusRequest->markSuccess();
        
        $this->assertTrue($statusRequest->isSuccess());
        
        $this->assertFalse($statusRequest->isCanceled());
        $this->assertFalse($statusRequest->isSuspended());
        $this->assertFalse($statusRequest->isExpired());
        $this->assertFalse($statusRequest->isPending());
        $this->assertFalse($statusRequest->isFailed());
        $this->assertFalse($statusRequest->isNew());
        $this->assertFalse($statusRequest->isUnknown());
    }

    /**
     * @test
     */
    public function shouldNotMatchOthersThenFailedStatus()
    {
        $statusRequest = new GetHumanStatus(new \stdClass);

        $statusRequest->markFailed();

        $this->assertTrue($statusRequest->isFailed());
        
        $this->assertFalse($statusRequest->isSuccess());
        $this->assertFalse($statusRequest->isSuspended());
        $this->assertFalse($statusRequest->isExpired());
        $this->assertFalse($statusRequest->isCanceled());
        $this->assertFalse($statusRequest->isPending());
        $this->assertFalse($statusRequest->isNew());
        $this->assertFalse($statusRequest->isUnknown());
    }

    /**
     * @test
     */
    public function shouldNotMatchOthersThenPendingStatus()
    {
        $statusRequest = new GetHumanStatus(new \stdClass);

        $statusRequest->markPending();

        $this->assertTrue($statusRequest->isPending());
        
        $this->assertFalse($statusRequest->isFailed());
        $this->assertFalse($statusRequest->isSuspended());
        $this->assertFalse($statusRequest->isExpired());
        $this->assertFalse($statusRequest->isSuccess());
        $this->assertFalse($statusRequest->isCanceled());
        $this->assertFalse($statusRequest->isNew());
        $this->assertFalse($statusRequest->isUnknown());
    }

    /**
     * @test
     */
    public function shouldNotMatchOthersThenCanceledStatus()
    {
        $statusRequest = new GetHumanStatus(new \stdClass);

        $statusRequest->markCanceled();

        $this->assertTrue($statusRequest->isCanceled());
        
        $this->assertFalse($statusRequest->isPending());
        $this->assertFalse($statusRequest->isSuspended());
        $this->assertFalse($statusRequest->isExpired());
        $this->assertFalse($statusRequest->isFailed());
        $this->assertFalse($statusRequest->isSuccess());
        $this->assertFalse($statusRequest->isNew());
        $this->assertFalse($statusRequest->isUnknown());
    }

    /**
     * @test
     */
    public function shouldNotMatchOthersThenNewStatus()
    {
        $statusRequest = new GetHumanStatus(new \stdClass);

        $statusRequest->markNew();

        $this->assertTrue($statusRequest->isNew());

        $this->assertFalse($statusRequest->isSuccess());
        $this->assertFalse($statusRequest->isSuspended());
        $this->assertFalse($statusRequest->isExpired());
        $this->assertFalse($statusRequest->isCanceled());
        $this->assertFalse($statusRequest->isPending());
        $this->assertFalse($statusRequest->isFailed());
        $this->assertFalse($statusRequest->isUnknown());
    }

    /**
     * @test
     */
    public function shouldNotMatchOthersThenUnknownStatus()
    {
        $statusRequest = new GetHumanStatus(new \stdClass);

        $statusRequest->markUnknown();

        $this->assertTrue($statusRequest->isUnknown());

        $this->assertFalse($statusRequest->isSuccess());
        $this->assertFalse($statusRequest->isSuspended());
        $this->assertFalse($statusRequest->isExpired());
        $this->assertFalse($statusRequest->isCanceled());
        $this->assertFalse($statusRequest->isPending());
        $this->assertFalse($statusRequest->isFailed());
        $this->assertFalse($statusRequest->isNew());
    }

    /**
     * @test
     */
    public function shouldNotMatchOthersThenExpiredStatus()
    {
        $statusRequest = new GetHumanStatus(new \stdClass);

        $statusRequest->markExpired();

        $this->assertTrue($statusRequest->isExpired());

        $this->assertFalse($statusRequest->isSuccess());
        $this->assertFalse($statusRequest->isSuspended());
        $this->assertFalse($statusRequest->isCanceled());
        $this->assertFalse($statusRequest->isPending());
        $this->assertFalse($statusRequest->isFailed());
        $this->assertFalse($statusRequest->isNew());
        $this->assertFalse($statusRequest->isUnknown());
    }

    /**
     * @test
     */
    public function shouldNotMatchOthersThenSuspendedStatus()
    {
        $statusRequest = new GetHumanStatus(new \stdClass);

        $statusRequest->markSuspended();

        $this->assertTrue($statusRequest->isSuspended());

        $this->assertFalse($statusRequest->isSuccess());
        $this->assertFalse($statusRequest->isExpired());
        $this->assertFalse($statusRequest->isCanceled());
        $this->assertFalse($statusRequest->isPending());
        $this->assertFalse($statusRequest->isFailed());
        $this->assertFalse($statusRequest->isNew());
        $this->assertFalse($statusRequest->isUnknown());
    }
}

