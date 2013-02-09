<?php
namespace Payum\Request;

interface StatusRequestInterface extends InteractiveRequestInterface
{
    /**
     * @return object
     */
    function getModel();

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
    function markCanceled();

    /**
     * @return boolean
     */
    function isCanceled();

    /**
     * @return void
     */
    function markInProgress();

    /**
     * @return boolean
     */
    function isInProgress();

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