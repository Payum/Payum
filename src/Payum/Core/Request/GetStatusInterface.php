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
     * @return bool
     */
    public function isNew();

    public function markCaptured();

    /**
     * @return bool
     */
    public function isCaptured();

    /**
     * @return bool
     */
    public function isAuthorized();

    public function markAuthorized();

    public function markPayedout();

    /**
     * @return bool
     */
    public function isPayedout();

    /**
     * @return bool
     */
    public function isRefunded();

    public function markRefunded();

    /**
     * @return bool
     */
    public function isSuspended();

    public function markSuspended();

    /**
     * @return bool
     */
    public function isExpired();

    public function markExpired();

    /**
     * @return bool
     */
    public function isCanceled();

    public function markCanceled();

    /**
     * @return bool
     */
    public function isPending();

    public function markPending();

    /**
     * @return bool
     */
    public function isFailed();

    public function markFailed();

    /**
     * @return bool
     */
    public function isUnknown();

    public function markUnknown();
}
