<?php
namespace Payum\Request;

use Payum\Request\InstructionInterface;

interface InstructionAwareRequestInterface  
{
    /**
     * @param \Payum\Request\InstructionInterface $instruction
     * 
     * @return void
     */
    function setInstruction(InstructionInterface $instruction);
}