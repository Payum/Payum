<?php

namespace Payum\Core\Tests\Request;

use Payum\Core\Request\BaseGetStatus;
use Payum\Core\Request\GetBinaryStatus;
use PHPUnit\Framework\TestCase;

class GetBinaryStatusTest extends TestCase
{
    public static function provideIsXXXMethods(): \Iterator
    {
        yield ['isCaptured'];
        yield ['isAuthorized'];
        yield ['isPayedout'];
        yield ['isRefunded'];
        yield ['isCanceled'];
        yield ['isPending'];
        yield ['isFailed'];
        yield ['isNew'];
        yield ['isUnknown'];
        yield ['isSuspended'];
        yield ['isExpired'];
    }

    public static function provideMarkXXXMethods(): \Iterator
    {
        yield ['markCaptured'];
        yield ['markAuthorized'];
        yield ['markPayedout'];
        yield ['markRefunded'];
        yield ['markCanceled'];
        yield ['markPending'];
        yield ['markFailed'];
        yield ['markNew'];
        yield ['markUnknown'];
        yield ['markSuspended'];
        yield ['markExpired'];
    }

    /**
     * @test
     */
    public function shouldBeSubClassOfBaseGetStatus()
    {
        $rc = new \ReflectionClass(GetBinaryStatus::class);

        $this->assertTrue($rc->isSubclassOf(BaseGetStatus::class));
    }

    /**
     * @test
     */
    public function shouldMarkUnknownInConstructor()
    {
        $getStatus = new GetBinaryStatus(new \stdClass());

        $this->assertTrue($getStatus->isUnknown());
    }

    /**
     * @test
     *
     * @dataProvider provideMarkXXXMethods
     */
    public function shouldAllowGetMarkedStatus($markXXXMethod)
    {
        $getStatus = new GetBinaryStatus(new \stdClass());

        $getStatus->$markXXXMethod();

        $this->assertNotEmpty($getStatus->getValue());
    }

    /**
     * @test
     *
     * @dataProvider provideIsXXXMethods
     */
    public function shouldCallIsXXXStatus($isXXXMethod)
    {
        $getStatus = new GetBinaryStatus(new \stdClass());

        $this->assertIsBool($getStatus->$isXXXMethod());
    }

    /**
     * @test
     */
    public function shouldNotMatchOthersThenCapturedStatus()
    {
        $getStatus = new GetBinaryStatus(new \stdClass());

        $getStatus->markCaptured();

        $this->assertTrue($getStatus->isCaptured());

        $this->assertFalse($getStatus->isCanceled());
        $this->assertFalse($getStatus->isAuthorized());
        $this->assertFalse($getStatus->isPayedout());
        $this->assertFalse($getStatus->isRefunded());
        $this->assertFalse($getStatus->isSuspended());
        $this->assertFalse($getStatus->isExpired());
        $this->assertFalse($getStatus->isPending());
        $this->assertFalse($getStatus->isFailed());
        $this->assertFalse($getStatus->isNew());
        $this->assertFalse($getStatus->isUnknown());
    }

    /**
     * @test
     */
    public function shouldNotMatchOthersThenFailedStatus()
    {
        $getStatus = new GetBinaryStatus(new \stdClass());

        $getStatus->markFailed();

        $this->assertTrue($getStatus->isFailed());

        $this->assertFalse($getStatus->isCaptured());
        $this->assertFalse($getStatus->isPayedout());
        $this->assertFalse($getStatus->isAuthorized());
        $this->assertFalse($getStatus->isSuspended());
        $this->assertFalse($getStatus->isExpired());
        $this->assertFalse($getStatus->isCanceled());
        $this->assertFalse($getStatus->isPending());
        $this->assertFalse($getStatus->isNew());
        $this->assertFalse($getStatus->isUnknown());
    }

    /**
     * @test
     */
    public function shouldNotMatchOthersThenPendingStatus()
    {
        $getStatus = new GetBinaryStatus(new \stdClass());

        $getStatus->markPending();

        $this->assertTrue($getStatus->isPending());

        $this->assertFalse($getStatus->isFailed());
        $this->assertFalse($getStatus->isSuspended());
        $this->assertFalse($getStatus->isExpired());
        $this->assertFalse($getStatus->isCaptured());
        $this->assertFalse($getStatus->isPayedout());
        $this->assertFalse($getStatus->isAuthorized());
        $this->assertFalse($getStatus->isCanceled());
        $this->assertFalse($getStatus->isNew());
        $this->assertFalse($getStatus->isUnknown());
    }

    /**
     * @test
     */
    public function shouldNotMatchOthersThenCanceledStatus()
    {
        $getStatus = new GetBinaryStatus(new \stdClass());

        $getStatus->markCanceled();

        $this->assertTrue($getStatus->isCanceled());

        $this->assertFalse($getStatus->isPending());
        $this->assertFalse($getStatus->isSuspended());
        $this->assertFalse($getStatus->isExpired());
        $this->assertFalse($getStatus->isFailed());
        $this->assertFalse($getStatus->isCaptured());
        $this->assertFalse($getStatus->isPayedout());
        $this->assertFalse($getStatus->isAuthorized());
        $this->assertFalse($getStatus->isNew());
        $this->assertFalse($getStatus->isUnknown());
    }

    /**
     * @test
     */
    public function shouldNotMatchOthersThenNewStatus()
    {
        $getStatus = new GetBinaryStatus(new \stdClass());

        $getStatus->markNew();

        $this->assertTrue($getStatus->isNew());

        $this->assertFalse($getStatus->isCaptured());
        $this->assertFalse($getStatus->isPayedout());
        $this->assertFalse($getStatus->isAuthorized());
        $this->assertFalse($getStatus->isSuspended());
        $this->assertFalse($getStatus->isExpired());
        $this->assertFalse($getStatus->isCanceled());
        $this->assertFalse($getStatus->isPending());
        $this->assertFalse($getStatus->isFailed());
        $this->assertFalse($getStatus->isUnknown());
    }

    /**
     * @test
     */
    public function shouldNotMatchOthersThenUnknownStatus()
    {
        $getStatus = new GetBinaryStatus(new \stdClass());

        $getStatus->markUnknown();

        $this->assertTrue($getStatus->isUnknown());

        $this->assertFalse($getStatus->isCaptured());
        $this->assertFalse($getStatus->isPayedout());
        $this->assertFalse($getStatus->isAuthorized());
        $this->assertFalse($getStatus->isSuspended());
        $this->assertFalse($getStatus->isExpired());
        $this->assertFalse($getStatus->isCanceled());
        $this->assertFalse($getStatus->isPending());
        $this->assertFalse($getStatus->isFailed());
        $this->assertFalse($getStatus->isNew());
    }

    /**
     * @test
     */
    public function shouldNotMatchOthersThenExpiredStatus()
    {
        $getStatus = new GetBinaryStatus(new \stdClass());

        $getStatus->markExpired();

        $this->assertTrue($getStatus->isExpired());

        $this->assertFalse($getStatus->isCaptured());
        $this->assertFalse($getStatus->isPayedout());
        $this->assertFalse($getStatus->isAuthorized());
        $this->assertFalse($getStatus->isSuspended());
        $this->assertFalse($getStatus->isCanceled());
        $this->assertFalse($getStatus->isPending());
        $this->assertFalse($getStatus->isFailed());
        $this->assertFalse($getStatus->isNew());
        $this->assertFalse($getStatus->isUnknown());
    }

    /**
     * @test
     */
    public function shouldNotMatchOthersThenSuspendedStatus()
    {
        $getStatus = new GetBinaryStatus(new \stdClass());

        $getStatus->markSuspended();

        $this->assertTrue($getStatus->isSuspended());

        $this->assertFalse($getStatus->isCaptured());
        $this->assertFalse($getStatus->isPayedout());
        $this->assertFalse($getStatus->isAuthorized());
        $this->assertFalse($getStatus->isExpired());
        $this->assertFalse($getStatus->isCanceled());
        $this->assertFalse($getStatus->isPending());
        $this->assertFalse($getStatus->isFailed());
        $this->assertFalse($getStatus->isNew());
        $this->assertFalse($getStatus->isUnknown());
    }

    /**
     * @test
     */
    public function shouldNotMatchOthersThenPayedoutStatus()
    {
        $getStatus = new GetBinaryStatus(new \stdClass());

        $getStatus->markPayedout();

        $this->assertTrue($getStatus->isPayedout());

        $this->assertFalse($getStatus->isCaptured());
        $this->assertFalse($getStatus->isAuthorized());
        $this->assertFalse($getStatus->isSuspended());
        $this->assertFalse($getStatus->isExpired());
        $this->assertFalse($getStatus->isCanceled());
        $this->assertFalse($getStatus->isPending());
        $this->assertFalse($getStatus->isFailed());
        $this->assertFalse($getStatus->isNew());
        $this->assertFalse($getStatus->isUnknown());
    }
}
