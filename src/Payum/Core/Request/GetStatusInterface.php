<?php
namespace Payum\Core\Request;

use Payum\Core\Model\ModelAggregateInterface;
use Payum\Core\Model\ModelAwareInterface;

interface GetStatusInterface extends ModelAwareInterface, ModelAggregateInterface
{
    /**
     * @return mixed
     */
    function getValue();

    /**
     * @return void
     */
    function markNew();

    /**
     * @return boolean
     */
    function isNew();

    /**
     * @return void
     */
    function markCaptured();

    /**
     * @return boolean
     */
    function isCaptured();

    /**
     * @return boolean
     */
    function isAuthorized();

    /**
     * @return void
     */
    function markAuthorized();

    /**
     * @return boolean
     */
    function isRefunded();

    /**
     * @return void
     */
    function markRefunded();

    /**
     * @return boolean
     */
    function isSuspended();

    /**
     * @return void
     */
    function markExpired();

    /**
     * @return boolean
     */
    function isExpired();

    /**
     * @return void
     */
    function markCanceled();

    /**
     * @return boolean
     */
    function isCanceled();

    /**
     * @return void
     */
    function markPending();

    /**
     * @return boolean
     */
    function isPending();

    /**
     * @return void
     */
    function markFailed();

    /**
     * @return boolean
     */
    function isFailed();

    /**
     * @return void
     */
    function markUnknown();

    /**
     * @return boolean
     */
    function isUnknown();
}