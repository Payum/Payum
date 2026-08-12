<?php

declare(strict_types=1);

namespace Payum\Core\Gateway;

use Payum\Core\Action\ActionInterface;

/**
 * Optional. Implement while a gateway is part-way through moving to handlers.
 *
 * A gateway with 1.x actions it has not ported yet lists them here, and they keep working alongside the
 * handlers it has. Requests that have a handler go to the handler; everything else falls through to these,
 * which is what lets a gateway move one operation at a time rather than all at once.
 *
 * Declaring any of these brings core's own actions and extensions along, since an action dispatching
 * GetHttpRequest or RenderTemplate still expects an answer. A gateway that declares none gets a clean
 * gateway with no 1.x machinery on it at all.
 *
 * Temporary by design: when the last action becomes a handler, drop the interface.
 *
 * @deprecated since 2.0, removed in 3.0 along with actions.
 */
interface DeclaresActions
{
    /**
     * Container ids, resolved from the gateway's own container.
     *
     * @return list<class-string<ActionInterface>>
     */
    public function actions(): array;
}
