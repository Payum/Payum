<?php
namespace Payum\Stripe\Tests\Action\Api;

use Payum\Core\Request\SimpleStatusRequest;
use Payum\Stripe\Action\StatusAction;

class StatusActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementsActionInterface()
    {
        $rc = new \ReflectionClass('Payum\Stripe\Action\StatusAction');

        $this->assertTrue($rc->implementsInterface('Payum\Core\Action\ActionInterface'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new StatusAction;
    }

    /**
     * @test
     */
    public function shouldSupportStatusRequestWithArrayAccessModel()
    {
        $action = new StatusAction;

        $this->assertTrue($action->supports(new SimpleStatusRequest(array())));
    }

    /**
     * @test
     */
    public function shouldNotSupportStatusRequestWithNotArrayAccessModel()
    {
        $action = new StatusAction;

        $this->assertFalse($action->supports(new SimpleStatusRequest('foo')));
    }

    /**
     * @test
     */
    public function shouldNotSupportNotStatusRequest()
    {
        $action = new StatusAction;

        $this->assertFalse($action->supports(new \stdClass));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\RequestNotSupportedException
     * @expectedExceptionMessage Action StatusAction is not supported the request stdClass.
     */
    public function throwRequestNotSupportedIfNotSupportedGiven()
    {
        $action = new StatusAction;

        $action->execute(new \stdClass);
    }

    /**
     * @test
     */
    public function shouldMarkFailedIfModelHasErrorSet()
    {
        $action = new StatusAction;

        $model = array(
            'error' => array('code' => 'foo'),
        );

        $action->execute($status = new SimpleStatusRequest($model));

        $this->assertTrue($status->isFailed());
    }

    /**
     * @test
     */
    public function shouldMarkNewIfModelHasNotCardSet()
    {
        $action = new StatusAction;

        $model = array();

        $action->execute($status = new SimpleStatusRequest($model));

        $this->assertTrue($status->isNew());
    }

    /**
     * @test
     */
    public function shouldMarkPendingIfModelHasNotUsedTokenSet()
    {
        $action = new StatusAction;

        $model = array(
            'card' => 'not-used-token'
        );

        $action->execute($status = new SimpleStatusRequest($model));

        $this->assertTrue($status->isPending());
    }

    /**
     * @test
     */
    public function shouldMarkSuccessIfModelHasSuccefullyUsedTokenSet()
    {
        $action = new StatusAction;

        $model = array(
            'card' => array('foo'),
            'captured' => true,
            'paid' => true,
        );

        $action->execute($status = new SimpleStatusRequest($model));

        $this->assertTrue($status->isSuccess());
    }

    /**
     * @test
     */
    public function shouldMarkUnknownIfStatusCouldBeGuessed()
    {
        $action = new StatusAction;

        $model = array(
            'card' => array('foo'),
            'captured' => false,
            'paid' => true,
        );

        $status = new SimpleStatusRequest($model);
        $status->markPending();

        $action->execute($status);

        $this->assertTrue($status->isUnknown());
    }
}