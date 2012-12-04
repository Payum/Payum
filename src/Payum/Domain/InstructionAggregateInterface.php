<?php
namespace Payum\Domain;

interface InstructionAggregateInterface
{
    /**
     * @return \Payum\PaymentInstructionInterface
     */
    function getInstruction();
}