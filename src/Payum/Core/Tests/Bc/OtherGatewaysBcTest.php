<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Bc;

final class OtherGatewaysBcTest extends BcTestCase
{
    protected static function scenarios(): string
    {
        return 'others';
    }

    /**
     * The converted amount is a decimal string formatted by moneyphp/money, where 1.x stored a float. What
     * is sent to the PSP is the same.
     */
    protected static function accepted(): array
    {
        return [
            'authorize_net_aim_capture_asks_for_a_card' => [
                0 => [
                    'details.amount' => '12.34',
                ],
            ],
        ];
    }
}
