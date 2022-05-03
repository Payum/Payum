<?php

declare(strict_types=1);

namespace Payum\Core\ISO4217;

use Alcohol\ISO4217;

final class Currency {

    /**
     * @param string|string[] $country
     */
    public function __construct(private string $name, private string $alpha3, private string $numeric, private int $exp, private string|array $country)
    {
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
    public function getCountry(): array|string
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