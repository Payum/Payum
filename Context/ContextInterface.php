<?php
namespace Payum\Bundle\PayumBundle\Context;

interface ContextInterface
{
    /**
     * @return \Payum\PaymentInterface
     */
    function getPayment();

    /**
     * @return \Payum\Storage\StorageInterface|null
     */
    function getStorage();

    /**
     * @return string
     */
    function getName();
}