<?php
namespace Payum\Request;

interface StatusRequestInterface extends InteractiveRequestInterface, ModelRequestInterface
{
    /**
     * @return mixed
     */
    function getStatus();

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
    function markSuccess();

    /**
     * @return boolean
     */
    function isSuccess();

    /**
     * @return void
     */
    function markSuspended();

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