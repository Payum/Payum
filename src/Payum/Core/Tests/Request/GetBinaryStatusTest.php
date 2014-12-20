<?php
namespace Payum\Core\Tests\Request;

use Payum\Core\Request\GetBinaryStatus;

class GetBinaryStatusTest extends \PHPUnit_Framework_TestCase
{
    public static function provideIsXXXMethods()
    {
        return array(
            array('isCaptured'),
            array('isAuthorized'),
            array('isRefunded'),
            array('isCanceled'),
            array('isPending'),
            array('isFailed'),
            array('isNew'),
            array('isUnknown'),
            array('isSuspended'),
            array('isExpired'),
        );
    }

    public static function provideMarkXXXMethods()
    {
        return array(
            array('markCaptured'),
            array('markAuthorized'),
            array('markRefunded'),
            array('markCanceled'),
            array('markPending'),
            array('markFailed'),
            array('markNew'),
            array('markUnknown'),
            array('markSuspended'),
            array('markExpired'),
        );
    }

    /**
     * @test
     */
    public function shouldBeSubClassOfBaseGetStatus()
    {
        $rc = new \ReflectionClass('Payum\Core\Request\GetBinaryStatus');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Request\BaseGetStatus'));
    }

    /**
     * @test
     */
    public function shouldMarkUnknownInConstructor()
    {
        $statusRequest = new GetBinaryStatus(new \stdClass());

        $this->assertTrue($statusRequest->isUnknown());
    }

    /**
     * @test
     *
     * @dataProvider provideMarkXXXMethods
     */
    public function shouldAllowGetMarkedStatus($markXXXMethod)
    {
        $statusRequest = new GetBinaryStatus(new \stdClass());

        $statusRequest->$markXXXMethod();

        $this->assertNotEmpty($statusRequest->getValue());
    }

    /**
     * @test
     *
     * @dataProvider provideIsXXXMethods
     */
    public function shouldCallIsXXXStatus($isXXXMethod)
    {
        $statusRequest = new GetBinaryStatus(new \stdClass());

        $this->assertInternalType('boolean', $statusRequest->$isXXXMethod());
    }

    /**
     * @test
     */
    public function shouldNotMatchOthersThenCapturedStatus()
    {
        $statusRequest = new GetBinaryStatus(new \stdClass());

        $statusRequest->markCaptured();

        $this->assertTrue($statusRequest->isCaptured());

        $this->assertFalse($statusRequest->isCanceled());
        $this->assertFalse($statusRequest->isAuthorized());
        $this->assertFalse($statusRequest->isRefunded());
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
        $statusRequest = new GetBinaryStatus(new \stdClass());

        $statusRequest->markFailed();

        $this->assertTrue($statusRequest->isFailed());

        $this->assertFalse($statusRequest->isCaptured());
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
        $statusRequest = new GetBinaryStatus(new \stdClass());

        $statusRequest->markPending();

        $this->assertTrue($statusRequest->isPending());

        $this->assertFalse($statusRequest->isFailed());
        $this->assertFalse($statusRequest->isSuspended());
        $this->assertFalse($statusRequest->isExpired());
        $this->assertFalse($statusRequest->isCaptured());
        $this->assertFalse($statusRequest->isCanceled());
        $this->assertFalse($statusRequest->isNew());
        $this->assertFalse($statusRequest->isUnknown());
    }

    /**
     * @test
     */
    public function shouldNotMatchOthersThenCanceledStatus()
    {
        $statusRequest = new GetBinaryStatus(new \stdClass());

        $statusRequest->markCanceled();

        $this->assertTrue($statusRequest->isCanceled());

        $this->assertFalse($statusRequest->isPending());
        $this->assertFalse($statusRequest->isSuspended());
        $this->assertFalse($statusRequest->isExpired());
        $this->assertFalse($statusRequest->isFailed());
        $this->assertFalse($statusRequest->isCaptured());
        $this->assertFalse($statusRequest->isNew());
        $this->assertFalse($statusRequest->isUnknown());
    }

    /**
     * @test
     */
    public function shouldNotMatchOthersThenNewStatus()
    {
        $statusRequest = new GetBinaryStatus(new \stdClass());

        $statusRequest->markNew();

        $this->assertTrue($statusRequest->isNew());

        $this->assertFalse($statusRequest->isCaptured());
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
        $statusRequest = new GetBinaryStatus(new \stdClass());

        $statusRequest->markUnknown();

        $this->assertTrue($statusRequest->isUnknown());

        $this->assertFalse($statusRequest->isCaptured());
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
        $statusRequest = new GetBinaryStatus(new \stdClass());

        $statusRequest->markExpired();

        $this->assertTrue($statusRequest->isExpired());

        $this->assertFalse($statusRequest->isCaptured());
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
        $statusRequest = new GetBinaryStatus(new \stdClass());

        $statusRequest->markSuspended();

        $this->assertTrue($statusRequest->isSuspended());

        $this->assertFalse($statusRequest->isCaptured());
        $this->assertFalse($statusRequest->isExpired());
        $this->assertFalse($statusRequest->isCanceled());
        $this->assertFalse($statusRequest->isPending());
        $this->assertFalse($statusRequest->isFailed());
        $this->assertFalse($statusRequest->isNew());
        $this->assertFalse($statusRequest->isUnknown());
    }
}
