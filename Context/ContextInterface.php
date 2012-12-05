<?php
namespace Payum\PaymentBundle\Context;

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
    function getInteractiveController();

    /**
     * @return string
     */
    function getStatusController();

    /**
     * @return string
     */
    function getName();
}