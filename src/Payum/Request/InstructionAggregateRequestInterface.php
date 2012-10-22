<?php
namespace Payum\Request;


interface InstructionAggregateRequestInterface
{
    /**
     * @return \Payum\Request\InstructionInterface
     */
    function getInstruction();
}