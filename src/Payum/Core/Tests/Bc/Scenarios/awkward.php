<?php

declare(strict_types=1);

use Payum\Core\Tests\Bc\Fixture\AwkwardGatewayFactory;
use Payum\Core\Tests\Bc\Fixture\AwkwardRedirect;
use Payum\Core\Tests\Bc\Scenario;

return [
    new Scenario('awkward_select', 'awkward', [], [
        [
            'op' => 'select',
        ],
    ], probes: [[
        AwkwardRedirect::class, [
            'psp_ref' => 'psp-1',
        ]]], factoryClass: AwkwardGatewayFactory::class),
    new Scenario('awkward_redirect_and_return', 'awkward', [], [
        [
            'op' => 'capture',
            'on' => 'token',
            'get' => [
                'utm' => 'x',
            ],
        ],
        [
            'op' => 'capture',
            'on' => 'token',
            'get' => [
                'psp_ref' => 'psp-1',
            ],
        ],
        [
            'op' => 'sync',
            'on' => 'token',
        ],
        [
            'op' => 'notify',
            'on' => 'token',
            'post' => [
                'event' => 'paid',
            ],
        ],
    ], factoryClass: AwkwardGatewayFactory::class),
    new Scenario('awkward_payment_without_token', 'awkward', [], [
        [
            'op' => 'capture',
            'on' => 'payment',
        ],
        [
            'op' => 'sync',
            'on' => 'payment',
        ],
        [
            'op' => 'refund',
            'on' => 'payment',
        ],
    ], factoryClass: AwkwardGatewayFactory::class),
    new Scenario('awkward_authorize_asks_for_a_card', 'awkward', [], [
        [
            'op' => 'authorize',
            'on' => 'token',
        ],
        [
            'op' => 'refund',
            'on' => 'token',
        ],
    ], factoryClass: AwkwardGatewayFactory::class),
];
