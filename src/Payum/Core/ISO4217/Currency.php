<?php

declare(strict_types=1);

namespace Payum\Core\ISO4217;

use Alcohol\ISO4217;

final class Currency {
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $alpha3;

    /**
     * @var string
     */
    private $numeric;

    /**
     * @var int
     */
    private $exp;

    /**
     * @var string|string[]
     */
    private $country;

    /**
     * @param string $name
     * @param string $alpha3
     * @param string $numeric
     * @param int $exp
     * @param string|string[] $country
     */
    public function __construct(string $name, string $alpha3, string $numeric, int $exp, $country)
    {
        $this->name = $name;
        $this->alpha3 = $alpha3;
        $this->numeric = $numeric;
        $this->exp = $exp;
        $this->country = $country;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getAlpha3(): string
    {
        return $this->alpha3;
    }

    public function getNumeric(): string
    {
        return $this->numeric;
    }

    public function getExp(): int
    {
        return $this->exp;
    }

    /**
     * @return string|\string[]
     */
    public function getCountry()
    {
        return $this->country;
    }

    public static function createFromIso4217Numeric(string $numeric): self
    {
        $currency = (new ISO4217())->getByNumeric($numeric);

        return new self(
            $currency['name'],
            $currency['alpha3'],
            $currency['numeric'],
            $currency['exp'],
            $currency['country']
        );
    }

    public static function createFromIso4217Alpha3(string $alpha3): self
    {
        $currency = (new ISO4217())->getByAlpha3($alpha3);

        return new self(
            $currency['name'],
            $currency['alpha3'],
            $currency['numeric'],
            $currency['exp'],
            $currency['country']
        );
    }
}