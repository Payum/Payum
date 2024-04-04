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

    public function testShouldBeSubClassOfBaseGetStatus()
    {
        $rc = new \ReflectionClass(GetBinaryStatus::class);

        $this->assertTrue($rc->isSubclassOf(BaseGetStatus::class));
    }

    public function testShouldMarkUnknownInConstructor()
    {
        $getStatus = new GetBinaryStatus(new \stdClass());

        $this->assertTrue($getStatus->isUnknown());
    }

    /**
     * @dataProvider provideMarkXXXMethods
     */
    public function testShouldAllowGetMarkedStatus($markXXXMethod)
    {
        $getStatus = new GetBinaryStatus(new \stdClass());

        $getStatus->$markXXXMethod();

        $this->assertNotEmpty($getStatus->getValue());
    }

    /**
     * @dataProvider provideIsXXXMethods
     */
    public function testShouldCallIsXXXStatus($isXXXMethod)
    {
        $getStatus = new GetBinaryStatus(new \stdClass());

        $this->assertIsBool($getStatus->$isXXXMethod());
    }

    public function testShouldNotMatchOthersThenCapturedStatus()
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

    public function testShouldNotMatchOthersThenFailedStatus()
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

    public function testShouldNotMatchOthersThenPendingStatus()
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

    public function testShouldNotMatchOthersThenCanceledStatus()
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

    public function testShouldNotMatchOthersThenNewStatus()
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

    public function testShouldNotMatchOthersThenUnknownStatus()
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

    public function testShouldNotMatchOthersThenExpiredStatus()
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

    public function testShouldNotMatchOthersThenSuspendedStatus()
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

    public function testShouldNotMatchOthersThenPayedoutStatus()
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
