<?php

namespace Payum\Core\Request;

/**
 * Pay a large sum of money from funds under one’s control
 *
 * @deprecated since 2.0.0, will be removed in 3.0. Dispatch Payum\Core\Command\PayoutCommand instead. A
 *             gateway still built from actions takes one once wrapped with
 *             Payum\Core\Legacy\LegacyGatewayAdapter::wrap().
 */
class Payout extends Generic
{
}
