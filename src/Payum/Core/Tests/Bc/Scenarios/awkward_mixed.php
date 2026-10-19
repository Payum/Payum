<?php

declare(strict_types=1);

use Payum\Core\Tests\Bc\Scenario;

/*
 * Recorded on 1.x with every operation an action; replayed on 2.x with sync moved to a handler.
 */
return array_values(array_filter(
    require __DIR__ . '/awkward.php',
    static fn (Scenario $scenario): bool => in_array($scenario->name, ['awkward_redirect_and_return', 'awkward_payment_without_token'], true),
));
