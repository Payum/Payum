<?php
namespace Payum\Core\Request;

use Alcohol\ISO4217;

class GetCurrency
{
    /**
     * @var string
     */
    protected $code;

    protected $iso4217;

    /**
     * @param string|int $code
     */
    public function __construct($code)
    {
        $this->code = $code;
    }

    /**
     * @return string|int
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @return ISO4217
     */
    public function getIso4217()
    {
        return $this->iso4217;
    }

    /**
     * @param ISO4217 $iso4217
     */
    public function setIso4217(ISO4217 $iso4217)
    {
        $this->iso4217 = $iso4217;
    }
}