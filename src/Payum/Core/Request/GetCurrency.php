<?php
namespace Payum\Core\Request;

class GetCurrency
{
    /**
     * @var string
     */
    public $code;

    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $alpha3;

    /**
     * @var int
     */
    public $numeric;

    /**
     * @var int
     */
    public $exp;

    /**
     * @var string|string[]
     */
    public $country;

    /**
     * @param string|int $code
     */
    public function __construct($code)
    {
        $this->code = $code;
    }
}
