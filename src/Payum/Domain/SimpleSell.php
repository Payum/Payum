<?php
namespace Payum\Domain;

use Payum\PaymentInstructionInterface;

class SimpleSell implements ModelInterface, InstructionAggregateInterface, InstructionAwareInterface
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
     * @var PaymentInstructionInterface
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
     * {@inheritdoc}
     */
    public function getInstruction()
    {
        return $this->instruction;
    }

    /**
     * {@inheritdoc}
     */
    public function setInstruction(PaymentInstructionInterface $instruction)
    {
        $this->instruction = $instruction;
    }
}