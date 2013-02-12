<?php
namespace Payum\Bundle\PayumBundle\Context;

interface ContextInterface
{
    /**
     * @return \Payum\PaymentInterface
     */
    function getPayment();

    /**
     * @return \Payum\Storage\StorageInterface
     */
    function getStorage();

    /**
     * @param object $model
     * 
     * @return \Payum\Request\StatusRequestInterface
     */
    function createStatusRequest($model);

    /**
     * @return string
     */
    function getCaptureInteractiveController();

    /**
     * @return string
     */
    function getCaptureFinishedController();

    /**
     * @return string
     */
    function getName();
}