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

    const CONTENT_TYPE_V2_PLUS_JSON = 'application/vnd.klarna.checkout.aggregated-order-v2+json';

    private function __construct() {}
}
