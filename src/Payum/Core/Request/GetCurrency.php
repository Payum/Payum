<?php
namespace Payum\Core\Request;

class GetCurrency
{
    public string $code;

    public string $name;

    public string $alpha3;

    public int $numeric;

    public int $exp;

    /**
     * @var string|string[]
     */
    public string|array $country;

    public function __construct(string|int $code)
    {
        $this->code = $code;
    }
}
