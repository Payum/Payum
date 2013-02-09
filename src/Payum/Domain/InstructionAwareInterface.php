<?php
namespace Payum\Domain;

interface InstructionAwareInterface  
{
    /**
     * @param object $instruction
     * 
     * @return void
     */
    function setInstruction($instruction);
}