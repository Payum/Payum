<?php
namespace Payum\Core\Request;

interface GetStatusInterface extends ModelAwareInterface
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
     * @deprecated since 0.12, will be removed in next release. use self::markCaptured
     *
     * @return void
     */
    function markSuccess();

    /**
     * @deprecated since 0.12, will be removed in next release. use self::isCaptured
     *
     * @return boolean
     */
    function isSuccess();

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