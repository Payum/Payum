<?php

namespace Payum\Core\Request;

/**
 * @deprecated since 2.0.0, will be removed in 3.0. Dispatch Payum\Core\Command\RefundCommand instead. A
 *             gateway still built from actions takes one once wrapped with
 *             Payum\Core\Legacy\LegacyGatewayAdapter::wrap().
 */
class Refund extends Generic
{
}
