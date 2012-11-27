<?php
namespace Payum\AuthorizeNet\Aim\Request;

use Payum\AuthorizeNet\Aim\Request\Instruction;
use Payum\Request\InstructionAggregateRequestInterface;

abstract class BaseInstructionRequest implements InstructionAggregateRequestInterface
{
    /**
     * @var \Payum\AuthorizeNet\Aim\Request\Instruction
     */
    protected $instruction;

    /**
     * @param \Payum\AuthorizeNet\Aim\Request\Instruction $instruction
     */
    public function __construct(Instruction $instruction)
    {
        $this->instruction = $instruction;
    }

    /**
     * @return \Payum\AuthorizeNet\Aim\Request\Instruction
     */
    public function getInstruction()
    {
        return $this->instruction;
    }
}