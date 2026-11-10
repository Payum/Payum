<?php

declare(strict_types=1);

namespace Payum\Core\Idempotency;

use DateTimeImmutable;
use Money\Currency;
use Money\Money;
use Payum\Core\Exception\LogicException;
use Payum\Core\Result\AuthorizeResult;
use Payum\Core\Result\CancelResult;
use Payum\Core\Result\CaptureResult;
use Payum\Core\Result\Failure;
use Payum\Core\Result\FailureReason;
use Payum\Core\Result\NextAction;
use Payum\Core\Result\PaymentStatus;
use Payum\Core\Result\PayoutResult;
use Payum\Core\Result\RefundResult;
use Payum\Core\Result\Result;

/**
 * Turns a result into plain values a store can keep, and back.
 *
 * Written out field by field rather than serialized: what the PSP sent back ($raw) can hold card data and
 * is never kept, and nothing read back from a store gets to name a class to instantiate.
 *
 * @internal
 */
final class ResultNormalizer
{
    private const DATE_FORMAT = 'Y-m-d\TH:i:s.uP';

    /**
     * @return array<string, mixed>|null null for a result that cannot be replayed: one that still needs
     *                                   the customer, or one of a kind this does not know
     */
    public static function normalize(Result $result): ?array
    {
        if ($result->next instanceof NextAction) {
            return null;
        }

        [$type, $amount] = match (true) {
            $result instanceof CaptureResult => ['capture', $result->capturedMoney ?? $result->capturedAmount],
            $result instanceof AuthorizeResult => ['authorize', $result->authorizedMoney ?? $result->authorizedAmount],
            $result instanceof RefundResult => ['refund', $result->refundedMoney ?? $result->refundedAmount],
            $result instanceof CancelResult => ['cancel', null],
            $result instanceof PayoutResult => ['payout', $result->paidOutMoney ?? $result->paidOutAmount],
            default => [null, null],
        };

        if (null === $type) {
            return null;
        }

        return [
            'type' => $type,
            'status' => $result->status?->value,
            'transactionId' => $result->transactionId,
            'accepted' => $result->isAccepted(),
            'failure' => $result->failure instanceof Failure ? [
                'reason' => $result->failure->reason->value,
                'code' => $result->failure->code,
                'message' => $result->failure->message,
            ] : null,
            'amount' => $amount instanceof Money ? $amount->getAmount() : $amount,
            'currency' => $amount instanceof Money ? $amount->getCurrency()->getCode() : null,
            'expiresAt' => $result instanceof AuthorizeResult ? $result->expiresAt?->format(self::DATE_FORMAT) : null,
        ];
    }

    /**
     * @param array<string, mixed> $data what {@see self::normalize()} returned
     *
     * @throws LogicException when the data names no result this can build
     */
    public static function denormalize(array $data): Result
    {
        $status = isset($data['status']) ? PaymentStatus::from($data['status']) : null;
        $transactionId = $data['transactionId'] ?? null;
        $failure = isset($data['failure']) ? new Failure(
            FailureReason::from($data['failure']['reason']),
            $data['failure']['code'] ?? null,
            $data['failure']['message'] ?? null,
        ) : null;
        $amount = isset($data['amount'], $data['currency'])
            ? new Money((string) $data['amount'], new Currency($data['currency']))
            : ($data['amount'] ?? null);
        $accepted = true === ($data['accepted'] ?? false);

        return match ($data['type'] ?? null) {
            'capture' => $accepted
                ? CaptureResult::accepted($transactionId)
                : new CaptureResult($status, null, $transactionId, $failure, [], $amount),
            'authorize' => new AuthorizeResult(
                $status,
                null,
                $transactionId,
                $failure,
                [],
                $amount,
                isset($data['expiresAt']) ? new DateTimeImmutable($data['expiresAt']) : null,
            ),
            'refund' => $accepted
                ? RefundResult::accepted($transactionId)
                : new RefundResult($status, null, $transactionId, $failure, [], $amount),
            'cancel' => $accepted
                ? CancelResult::accepted($transactionId)
                : new CancelResult($status, null, $transactionId, $failure),
            'payout' => new PayoutResult($status, null, $transactionId, $failure, [], $amount),
            default => throw new LogicException(sprintf('An idempotency record holds no result Payum can rebuild: %s.', json_encode($data['type'] ?? null))),
        };
    }
}
