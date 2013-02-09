<?php
namespace Payum;

interface PaymentInstructionAggregateInterface
{
    /**
     * @return object
     */
    function getPaymentInstruction();
}