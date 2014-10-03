<?php
namespace Payum\Core\Model;

class Currency implements CurrencyInterface
{
    /**
     * @var string
     */
    protected $code;

    /**
     * @var int
     */
    protected $digitsAfterDecimalPoint;

    /**
     * @param string $code
     * @param int $digitsAfterDecimalPoint
     */
    public function __construct($code, $digitsAfterDecimalPoint = 2)
    {
        $this->code = $code;
        $this->digitsAfterDecimalPoint = $digitsAfterDecimalPoint;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @return int
     */
    public function getDigitsAfterDecimalPoint()
    {
        return $this->digitsAfterDecimalPoint;
    }
} 