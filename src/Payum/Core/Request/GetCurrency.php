<?php

namespace Payum\Core\Request;

/**
 * @deprecated since 2.0.0, will be removed in 3.0. A handler calls
 *             Payum\Core\ISO4217\Currency::createFromIso4217Alpha3() or ::createFromIso4217Numeric()
 *             directly instead.
 */
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
