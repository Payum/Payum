<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Bc;

final class PaypalNvpBcTest extends BcTestCase
{
    protected static function scenarios(): string
    {
        return 'paypal_nvp';
    }

    /**
     * The converted amount is a decimal string formatted by moneyphp/money, where 1.x stored a float. What
     * is sent to the PSP is the same.
     */
    protected static function accepted(): array
    {
        return [
            'paypal_pro_checkout_capture_asks_for_a_card' => [
                0 => [
                    'details.AMT' => '12.34',
                ],
            ],
        ];
    }
}
