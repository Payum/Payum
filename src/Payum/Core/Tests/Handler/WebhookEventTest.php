<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Handler;

use Payum\Core\Handler\WebhookEvent;
use Payum\Core\Result\Acknowledgement;
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

    public function testShouldCarryTheAcknowledgementItWasGiven(): void
    {
        $event = WebhookEvent::verified([
            'a' => 1,
        ], 'evt_1', 't', Acknowledgement::ok('[accepted]'));

        $this->assertSame('[accepted]', $event->acknowledgement->body);
    }

    public function testShouldHaveNoAcknowledgementByDefault(): void
    {
        $this->assertNull(WebhookEvent::verified([])->acknowledgement);
        $this->assertNull(WebhookEvent::unverified([])->acknowledgement);
    }

    public function testShouldSurviveSerialization(): void
    {
        $verified = WebhookEvent::verified([
            'a' => 1,
        ], 'evt_1', 't', new Acknowledgement(200, '[accepted]'));
        $unverified = WebhookEvent::unverified([
            'b' => 2,
        ]);

        $copy = unserialize(serialize($verified));
        $this->assertSame([
            'a' => 1,
        ], $copy->payload);
        $this->assertSame('evt_1', $copy->id);
        $this->assertSame('t', $copy->type);
        $this->assertTrue($copy->isVerified());
        $this->assertSame(200, $copy->acknowledgement->status);
        $this->assertSame('[accepted]', $copy->acknowledgement->body);

        $copy = unserialize(serialize($unverified));
        $this->assertSame([
            'b' => 2,
        ], $copy->payload);
        $this->assertNull($copy->id);
        $this->assertNull($copy->type);
        $this->assertFalse($copy->isVerified());
        $this->assertNull($copy->acknowledgement);
    }
}
