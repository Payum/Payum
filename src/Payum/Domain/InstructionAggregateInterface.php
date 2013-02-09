<?php
namespace Payum\Domain;

interface InstructionAggregateInterface
{
    /**
     * @return object
     */
    function getInstruction();
}