<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Bc;

final class PaypalExpressCheckoutBcTest extends BcTestCase
{
    protected static function scenarios(): string
    {
        return 'paypal_express_checkout';
    }

    /**
     * The converted amount is a decimal string formatted by moneyphp/money, where 1.x stored a float. What
     * is sent to the PSP is the same.
     */
    protected static function accepted(): array
    {
        return [
            'paypal_express_checkout_capture' => [
                0 => [
                    'details.PAYMENTREQUEST_0_AMT' => '12.34',
                ],
            ],
            'paypal_express_checkout_cancelled_by_buyer' => [
                0 => [
                    'details.PAYMENTREQUEST_0_AMT' => '12.34',
                ],
            ],
        ];
    }
}
