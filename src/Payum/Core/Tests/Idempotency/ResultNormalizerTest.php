<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Idempotency;

use DateTimeImmutable;
use Money\Money;
use Payum\Core\Exception\LogicException;
use Payum\Core\Idempotency\ResultNormalizer;
use Payum\Core\Result\AuthorizeResult;
use Payum\Core\Result\CancelResult;
use Payum\Core\Result\CaptureResult;
use Payum\Core\Result\Failure;
use Payum\Core\Result\FailureReason;
use Payum\Core\Result\NextAction\Redirect;
use Payum\Core\Result\PaymentStatus;
use Payum\Core\Result\PayoutResult;
use Payum\Core\Result\RefundResult;
use Payum\Core\Result\Result;
use Payum\Core\Result\SyncResult;
use Payum\Core\Security\SensitiveValue;
use PHPUnit\Framework\TestCase;

final class ResultNormalizerTest extends TestCase
{
    /**
     * @return iterable<string, array{Result}>
     */
    public static function provideResults(): iterable
    {
        yield 'captured in Money' => [CaptureResult::captured('ch_1', Money::EUR(1000))];
        yield 'partially captured in minor units' => [CaptureResult::partiallyCaptured('ch_1', 400)];
        yield 'capture accepted' => [CaptureResult::accepted('ch_1')];
        yield 'capture declined' => [CaptureResult::failed(new Failure(FailureReason::Declined, 'card_declined', 'Your card was declined.'), PaymentStatus::Failed)];
        yield 'authorized' => [AuthorizeResult::authorized('au_1', Money::USD(2500), new DateTimeImmutable('2026-11-17T10:00:00+00:00'))];
        yield 'refunded' => [RefundResult::refunded('re_1', Money::EUR(500))];
        yield 'refund accepted' => [RefundResult::accepted('re_1')];
        yield 'canceled' => [CancelResult::canceled('void_1')];
        yield 'cancel accepted' => [CancelResult::accepted()];
        yield 'paid out' => [PayoutResult::paidOut('po_1', 9900)];
    }

    /**
     * @dataProvider provideResults
     */
    public function testShouldRebuildTheResultItRecorded(Result $result): void
    {
        $normalized = ResultNormalizer::normalize($result);

        $this->assertNotNull($normalized);
        // Stored as JSON by the database stores, so it has to survive the trip.
        $this->assertEquals($result, ResultNormalizer::denormalize(json_decode((string) json_encode($normalized), true)));
    }

    public function testShouldNeverRecordWhatThePspSentBack(): void
    {
        $result = CaptureResult::captured('ch_1', 1000, [
            'card' => [
                'number' => '4111111111111111',
                'cvc' => new SensitiveValue('123'),
            ],
            'customer' => 'cus_1',
        ]);

        $normalized = ResultNormalizer::normalize($result);
        $encoded = (string) json_encode($normalized);

        $this->assertStringNotContainsString('4111111111111111', $encoded);
        $this->assertStringNotContainsString('123', $encoded);
        $this->assertStringNotContainsString('cus_1', $encoded);
        $this->assertSame([], ResultNormalizer::denormalize($normalized ?? [])->raw);
    }

    public function testShouldNotRecordAResultItCannotRebuild(): void
    {
        $this->assertNull(ResultNormalizer::normalize(SyncResult::synced(PaymentStatus::Captured)));
    }

    public function testShouldRefuseToBuildAClassItDoesNotKnow(): void
    {
        $this->expectException(LogicException::class);

        ResultNormalizer::denormalize([
            'type' => 'DateTime',
        ]);
    }

    public function testShouldNotRecordAResultThatStillNeedsTheCustomer(): void
    {
        $this->assertNull(ResultNormalizer::normalize(CaptureResult::pending(new Redirect('https://acme.test/checkout'))));
    }
}
