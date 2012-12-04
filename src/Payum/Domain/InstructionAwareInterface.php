<?php
namespace Payum\Domain;

use Payum\PaymentInstructionInterface;

interface InstructionAwareInterface  
{
    /**
     * @param PaymentInstructionInterface $instruction
     * 
     * @return void
     */
    function setInstruction(PaymentInstructionInterface $instruction);
}