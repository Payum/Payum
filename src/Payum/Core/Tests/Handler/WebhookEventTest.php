<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Handler;

use Payum\Core\Handler\WebhookEvent;
use PHPUnit\Framework\TestCase;

final class WebhookEventTest extends TestCase
{
    public function testShouldCarryThePspsOwnIdentifiers(): void
    {
        $event = WebhookEvent::verified([
            'id' => 'evt_1',
        ], 'evt_1', 'payment_intent.succeeded');

        $this->assertSame('evt_1', $event->id);
        $this->assertSame('payment_intent.succeeded', $event->type);
        $this->assertSame([
            'id' => 'evt_1',
        ], $event->payload);
    }

    public function testShouldKnowWhetherItWasChecked(): void
    {
        $this->assertTrue(WebhookEvent::verified([])->isVerified());
        $this->assertFalse(WebhookEvent::unverified([])->isVerified());
    }

    public function testShouldTreatTheIdentifiersAsOptional(): void
    {
        $event = WebhookEvent::unverified([
            'txn' => '1',
        ]);

        $this->assertNull($event->id);
        $this->assertNull($event->type);
    }
}
