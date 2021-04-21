<?php
namespace Payum\Core\Request;

use Payum\Core\Model\ModelAggregateInterface;
use Payum\Core\Model\ModelAwareInterface;

interface GetStatusInterface extends ModelAwareInterface, ModelAggregateInterface
{
    /**
     * @return mixed
     */
    public function getValue();

    /**
     * @return void
     */
    public function markNew();

    /**
     * @return boolean
     */
    public function isNew();

    /**
     * @return void
     */
    public function markCaptured();

    /**
     * @return boolean
     */
    public function isCaptured();

    /**
     * @return boolean
     */
    public function isAuthorized();

    /**
     * @return void
     */
    public function markAuthorized();

    /**
     * @return void
     */
    public function markPayedout();

    /**
     * @return boolean
     */
    public function isPayedout();

    /**
     * @return boolean
     */
    public function isRefunded();

    /**
     * @return void
     */
    public function markRefunded();

    /**
     * @return boolean
     */
    public function isSuspended();

    /**
     * @return void
     */
    public function markSuspended();

    /**
     * @return boolean
     */
    public function isExpired();

    /**
     * @return void
     */
    public function markExpired();

    /**
     * @return boolean
     */
    public function isCanceled();

    /**
     * @return void
     */
    public function markCanceled();

    /**
     * @return boolean
     */
    public function isPending();

    /**
     * @return void
     */
    public function markPending();

    /**
     * @return boolean
     */
    public function isFailed();

    /**
     * @return void
     */
    public function markFailed();

    /**
     * @return boolean
     */
    public function isUnknown();

    /**
     * @return void
     */
    public function markUnknown();
}
