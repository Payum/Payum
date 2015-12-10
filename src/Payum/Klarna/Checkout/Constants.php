<?php
namespace Payum\Klarna\Checkout;

final class Constants
{
    const STATUS_CHECKOUT_INCOMPLETE = 'checkout_incomplete';

    const STATUS_CHECKOUT_COMPLETE = 'checkout_complete';

    const STATUS_CREATED = 'created';

    const GUI_LAYOUT_DESKTOP = 'desktop';

    const GUI_LAYOUT_MOBILE = 'mobile';

    const BASE_URI_LIVE = 'https://checkout.klarna.com/checkout/orders';

    const BASE_URI_SANDBOX = 'https://checkout.testdrive.klarna.com/checkout/orders';

    const BASE_URI_RECURRING_SANDBOX = 'https://checkout.testdrive.klarna.com/checkout/recurring/{recurring_token}/orders';

    const BASE_URI_RECURRING_LIVE = 'https://checkout.klarna.com/checkout/recurring/{recurring_token}/orders';

    const CONTENT_TYPE_AGGREGATED_ORDER_V2 = 'application/vnd.klarna.checkout.aggregated-order-v2+json';

    const CONTENT_TYPE_RECURRING_ORDER_V1 = 'application/vnd.klarna.checkout.recurring-order-v1+json';

    const ACCEPT_HEADER_RECURRING_ORDER_ACCEPTED_V1 = 'application/vnd.klarna.checkout.recurring-order-accepted-v1+json';

    private function __construct()
    {
    }
}
