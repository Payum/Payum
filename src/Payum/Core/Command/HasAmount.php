<?php

declare(strict_types=1);

namespace Payum\Core\Command;

use Money\Currency;
use Money\Money;

/**
 * A command that can act on part of its subject rather than all of it.
 *
 * Handlers should read {@see \Payum\Core\Handler\Context::amount()} instead, which resolves this against
 * the subject and so answers "how much is this execution for" in one call.
 *
 * Every implementation also carries a readonly $amount in minor units, which an interface cannot declare.
 */
interface HasAmount
{
    /**
     * What the caller asked for, or null for the subject's full amount.
     *
     * @param Currency|null $currency the subject's currency. Needed only when the caller gave minor units
     *                                and the command carries no payment to read a currency from, which is
     *                                the case for anything built from a token alone
     */
    public function money(?Currency $currency = null): ?Money;
}
