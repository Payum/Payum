<?php

declare(strict_types=1);

namespace Payum\Core\Middleware;

use DateInterval;
use Money\Currency;
use Payum\Core\Command\AuthorizeCommand;
use Payum\Core\Command\CancelCommand;
use Payum\Core\Command\CaptureCommand;
use Payum\Core\Command\CommandInterface;
use Payum\Core\Command\HasAmount;
use Payum\Core\Command\RefundCommand;
use Payum\Core\Exception\IdempotencyKeyReusedException;
use Payum\Core\Handler\Context;
use Payum\Core\Idempotency\IdempotencyStoreInterface;
use Payum\Core\Idempotency\ResultNormalizer;
use Payum\Core\Model\IdempotencyRecord;
use Payum\Core\Registry\StorageRegistryInterface;
use Payum\Core\Result\Failure;
use Payum\Core\Result\Result;
use Psr\Clock\ClockInterface;

/**
 * Answers a repeat of an idempotency key with the result the key got the first time, without running
 * the command again.
 *
 * Only finished results are kept. One that sends the customer somewhere has a second leg still to run,
 * and a failure worth retrying would otherwise be repeated back forever.
 */
final class IdempotencyMiddleware implements MiddlewareInterface, HasPriority
{
    /**
     * @param StorageRegistryInterface<object>|null $storages identifies a payment handed to a command directly,
     *                                                       so a key reused for another payment is caught
     * @param int $ttl seconds a key is remembered for
     */
    public function __construct(
        private readonly IdempotencyStoreInterface $store,
        private readonly ClockInterface $clock,
        private readonly ?StorageRegistryInterface $storages = null,
        private readonly int $ttl = 86400,
    ) {
    }

    /**
     * Inside LockingMiddleware, so a repeat that waited for the lock finds the first result saved, and
     * outside the extensions and PersistStateMiddleware, so a replay writes nothing.
     */
    public static function priority(): int
    {
        return 800;
    }

    public function process(CommandInterface $command, Context $context, callable $next): Result
    {
        $key = $context->idempotencyKey();
        $subject = null === $key ? null : SubjectIdentity::of($command, $this->storages);

        // Without a stored subject, two different payments sharing a key would look like one operation
        // and the second would be handed the first one's result.
        if (null === $key || null === $subject) {
            return $next($command, $context);
        }

        $scope = hash('sha256', $context->gatewayName() . "\0" . $key);
        $fingerprint = $this->fingerprintOf($command, $subject);
        $now = $this->clock->now();
        $record = $this->store->find($scope);

        if ($record instanceof IdempotencyRecord && $record->getExpiresAt() > $now) {
            if ($record->getFingerprint() !== $fingerprint) {
                throw new IdempotencyKeyReusedException(sprintf(
                    'The idempotency key "%s" was already used for a different operation. Use a new key for each operation, and the same key only to retry it.',
                    $key,
                ));
            }

            return ResultNormalizer::denormalize($record->getResult());
        }

        $result = $next($command, $context);
        $normalized = ResultNormalizer::normalize($result);

        if (null !== $normalized && ! ($result->failure instanceof Failure && $result->failure->isRetriable())) {
            $record = new IdempotencyRecord();
            $record->setScope($scope);
            $record->setFingerprint($fingerprint);
            $record->setResult($normalized);
            $record->setExpiresAt($now->add(new DateInterval('PT' . $this->ttl . 'S')));

            $this->store->save($record);
        }

        return $result;
    }

    /**
     * @param CommandInterface<Result> $command
     */
    private function fingerprintOf(CommandInterface $command, string $subject): string
    {
        return hash('sha256', (string) json_encode([
            $command::class,
            $subject,
            match (true) {
                $command instanceof CaptureCommand, $command instanceof AuthorizeCommand => [$command->amount, self::currencyGivenTo($command)],
                $command instanceof RefundCommand => [$command->amount, self::currencyGivenTo($command), $command->reason],
                $command instanceof CancelCommand => $command->reason,
                default => null,
            },
        ]));
    }

    /**
     * The currency the caller wrote on the command, never the one money() would borrow from the payment:
     * a command on the token and the same command on the payment have to fingerprint alike.
     */
    private static function currencyGivenTo(HasAmount $command): ?string
    {
        // money() answers in the currency it is handed unless the caller gave a Money of their own.
        $code = $command->money(new Currency('XTS'))?->getCurrency()->getCode();

        return 'XTS' === $code ? null : $code;
    }
}
