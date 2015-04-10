<?php
namespace Payum\Core\Request;

use Alcohol\ISO4217;

class GetCurrency
{
    /**
     * @var string
     */
    protected $code;

    protected $ISO4217;

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
    public function getISO4217()
    {
        return $this->ISO4217;
    }

    /**
     * @param ISO4217 $ISO4217
     */
    public function setISO4217(ISO4217 $ISO4217)
    {
        $this->ISO4217 = $ISO4217;
    }
}