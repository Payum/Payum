<?php

declare(strict_types=1);

use Payum\Core\Tests\Bc\Scenario;
use Payum\Stripe\Request\Api\CreateCharge;
use Payum\Stripe\Request\Api\ObtainToken;
use Payum\Stripe\StripeDirectGatewayFactory;

$keys = [
    'publishable_key' => 'pk_test_bc',
    'secret_key' => 'sk_test_bc',
];

$statuses = [
    [
        'op' => 'status',
        'details' => [],
    ],
    [
        'op' => 'status',
        'details' => [
            'card' => 'tok_1',
        ],
    ],
    [
        'op' => 'status',
        'details' => [
            'error' => [
                'message' => 'declined',
            ],
        ],
    ],
    [
        'op' => 'status',
        'details' => [
            'status' => 'failed',
        ],
    ],
    [
        'op' => 'status',
        'details' => [
            'status' => 'succeeded',
            'captured' => true,
            'paid' => true,
        ],
    ],
    [
        'op' => 'status',
        'details' => [
            'status' => 'paid',
            'captured' => true,
            'paid' => true,
        ],
    ],
    [
        'op' => 'status',
        'details' => [
            'status' => 'succeeded',
            'captured' => false,
        ],
    ],
    [
        'op' => 'status',
        'details' => [
            'status' => 'succeeded',
            'refunded' => true,
        ],
    ],
    [
        'op' => 'status',
        'details' => [
            'status' => 'something else',
        ],
    ],
];

$charge = [200, [
    'id' => 'ch_1',
    'object' => 'charge',
    'amount' => 1234,
    'currency' => 'eur',
    'status' => 'succeeded',
    'captured' => true,
    'paid' => true,
    'refunded' => false,
]];

$scenarios = [];

foreach ([
    'stripe_checkout' => null,
    'stripe_js' => null,
    'stripe_direct' => StripeDirectGatewayFactory::class,
] as $factory => $class) {
    $scenarios[] = new Scenario($factory . '_select', $factory, $keys, [
        [
            'op' => 'select',
        ],
    ], probes: [[ObtainToken::class], [CreateCharge::class]], factoryClass: $class);
    $scenarios[] = new Scenario($factory . '_status', $factory, $keys, $statuses, factoryClass: $class);
}

foreach (['stripe_checkout', 'stripe_js'] as $factory) {
    $scenarios[] = new Scenario($factory . '_capture', $factory, $keys, [
        [
            'op' => 'capture',
            'on' => 'token',
        ],
        [
            'op' => 'capture',
            'on' => 'token',
            'post' => [
                'stripeToken' => 'tok_1',
            ],
            'stripe' => [$charge],
        ],
    ]);
}

$scenarios[] = new Scenario('stripe_direct_capture_asks_for_a_card', 'stripe_direct', $keys, [
    [
        'op' => 'capture',
        'on' => 'token',
    ],
], factoryClass: StripeDirectGatewayFactory::class);

return $scenarios;
