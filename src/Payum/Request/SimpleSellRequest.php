<?php
namespace Payum\Request;

class SimpleSellRequest implements InstructionAggregateRequestInterface, InstructionAwareRequestInterface
{
    /**
     * @var mixed
     */
    protected $id;
    
    /**
     * @var float
     */
    protected $price = 0;

    /**
     * @var string
     */
    protected $currency = '';

    /**
     * @var InstructionInterface
     */
    protected $instruction;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return float
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param float $price
     */
    public function setPrice($price)
    {
        $this->price = $price;
    }

    /**
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @param string $currency
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;
    }

    /**
     * @return \Payum\Request\InstructionInterface|null
     */
    public function getInstruction()
    {
        return $this->instruction;
    }

    /**
     * @param \Payum\Request\InstructionInterface $instruction
     */
    public function setInstruction(InstructionInterface $instruction)
    {
        $this->instruction = $instruction;
    }
}