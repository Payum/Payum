<?php
namespace Payum;

interface PaymentInstructionAwareInterface  
{
    /**
     * @param object $instruction
     * 
     * @return void
     */
    function setPaymentInstruction($instruction);
}