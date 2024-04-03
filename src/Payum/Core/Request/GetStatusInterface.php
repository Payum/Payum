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

    public function markNew();

    /**
     * @return boolean
     */
    public function isNew();

    public function markCaptured();

    /**
     * @return boolean
     */
    public function isCaptured();

    /**
     * @return boolean
     */
    public function isAuthorized();

    public function markAuthorized();

    public function markPayedout();

    /**
     * @return boolean
     */
    public function isPayedout();

    /**
     * @return boolean
     */
    public function isRefunded();

    public function markRefunded();

    /**
     * @return boolean
     */
    public function isSuspended();

    public function markSuspended();

    /**
     * @return boolean
     */
    public function isExpired();

    public function markExpired();

    /**
     * @return boolean
     */
    public function isCanceled();

    public function markCanceled();

    /**
     * @return boolean
     */
    public function isPending();

    public function markPending();

    /**
     * @return boolean
     */
    public function isFailed();

    public function markFailed();

    /**
     * @return boolean
     */
    public function isUnknown();

    public function markUnknown();
}
