<?php

declare(strict_types=1);

namespace Payum\Core\Model;

use Money\Money;

/**
 * A model that can state its amount as a {@see Money}.
 *
 * Deliberately not folded into {@see PaymentInterface}: applications have their own Doctrine entities
 * mapped to that interface, and adding a method to it would break every one of them. A model opts in by
 * implementing this alongside it, and {@see \Payum\Core\Money\Amount} reads either shape.
 *
 * Implementing it does not mean storing a Money. {@see Payment} keeps its `totalAmount` and
 * `currencyCode` columns and maps over them, so no schema changes.
 */
interface MoneyAwareInterface
{
    public function getMoney(): ?Money;

    public function setMoney(?Money $money): void;
}
