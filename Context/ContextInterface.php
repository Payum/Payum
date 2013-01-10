<?php
namespace Payum\Bundle\PayumBundle\Context;

use Payum\Domain\ModelInterface;

interface ContextInterface
{
    /**
     * @return \Payum\PaymentInterface
     */
    function getPayment();

    /**
     * @return \Payum\Domain\Storage\ModelStorageInterface
     */
    function getStorage();

    /**
     * @param ModelInterface $model
     * 
     * @return \Payum\Request\StatusRequestInterface
     */
    function createStatusRequest(ModelInterface $model);

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