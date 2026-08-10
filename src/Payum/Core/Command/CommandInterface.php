<?php

declare(strict_types=1);

namespace Payum\Core\Command;

use Payum\Core\Gateway\Capability;
use Payum\Core\Result\Result;

/**
 * What the caller wants done. Immutable.
 *
 * A command is the v2 counterpart of a v1 Request, but the contract is inverted and that is why it is not
 * called one: a v1 Request is a mutable bag that actions write their results into, whereas a command is
 * read-only intent and the answer comes back as a {@see Result}.
 *
 * Keeping commands immutable and free of services is what makes it possible, later, to put one on a queue.
 *
 * @template-covariant TResult of Result
 */
interface CommandInterface
{
    /**
     * The capability this command exercises.
     *
     * It's used to derive a gateway's operation capabilities from its handler list, so that the two
     * can never disagree.
     */
    public static function capability(): Capability;
}
