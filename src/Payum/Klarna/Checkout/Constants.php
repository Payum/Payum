<?php

namespace Payum\Klarna\Checkout;

final class Constants
{
    public const STATUS_CHECKOUT_INCOMPLETE = 'checkout_incomplete';

    public const STATUS_CHECKOUT_COMPLETE = 'checkout_complete';

    public const STATUS_CREATED = 'created';

    public const GUI_LAYOUT_DESKTOP = 'desktop';

    public const GUI_LAYOUT_MOBILE = 'mobile';

    public const BASE_URI_LIVE = 'https://checkout.klarna.com/checkout/orders';

    public const BASE_URI_SANDBOX = 'https://checkout.testdrive.klarna.com/checkout/orders';

    public const BASE_URI_RECURRING_SANDBOX = 'https://checkout.testdrive.klarna.com/checkout/recurring/{recurring_token}/orders';

    public const BASE_URI_RECURRING_LIVE = 'https://checkout.klarna.com/checkout/recurring/{recurring_token}/orders';

    public const CONTENT_TYPE_AGGREGATED_ORDER_V2 = 'application/vnd.klarna.checkout.aggregated-order-v2+json';

    public const CONTENT_TYPE_RECURRING_ORDER_V1 = 'application/vnd.klarna.checkout.recurring-order-v1+json';

    public const ACCEPT_HEADER_RECURRING_ORDER_ACCEPTED_V1 = 'application/vnd.klarna.checkout.recurring-order-accepted-v1+json';

    private function __construct()
    {
    }
}
