<?php

declare(strict_types=1);

namespace Payum\Core\Gateway;

/**
 * What a gateway is able to do.
 *
 * Operation capabilities (Capture, Authorize, Refund, ...) are *derived* from the gateway's handler list --
 * each handler interface declares a command, and each command declares its capability -- so they can never
 * disagree with reality. A descriptor only declares the nuance a handler list cannot express, by
 * implementing {@see DeclaresCapabilities}: partial refunds, multi-currency, 3-D Secure and so on.
 *
 * Backed by strings so a capability list can be persisted, serialised to JSON, or rendered.
 */
enum Capability: string
{
    // Operations -- derived from handlers(), never declared by hand.
    case Authorize = 'authorize';

    case Cancel = 'cancel';

    case Capture = 'capture';

    case Payout = 'payout';

    case Refund = 'refund';

    // Nuance -- declared, because no handler list implies them.
    case MultiCurrency = 'multi_currency';

    case PartialCapture = 'partial_capture';

    case PartialRefund = 'partial_refund';

    case Recurring = 'recurring';

    case StoredPaymentMethods = 'stored_payment_methods';

    case ThreeDSecure = 'three_d_secure';

    case Webhooks = 'webhooks';
}
