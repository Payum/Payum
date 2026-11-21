<?php

declare(strict_types=1);

namespace Payum\Core\Middleware;

use Payum\Core\Command\CommandInterface;
use Payum\Core\Handler\Context;
use Payum\Core\Reference\ReferenceIndexInterface;
use Payum\Core\Reference\ReferenceNamespace;
use Payum\Core\Registry\StorageRegistryInterface;
use Payum\Core\Result\Result;
use Payum\Core\Storage\IdentityInterface;

/**
 * Records each result's transaction id against its subject, so a later webhook naming that id finds the
 * payment.
 */
final class IndexReferencesMiddleware implements MiddlewareInterface, HasPriority
{
    /**
     * @param StorageRegistryInterface<object>|null $storages
     */
    public function __construct(
        private readonly ReferenceIndexInterface $references,
        private readonly ?StorageRegistryInterface $storages = null,
    ) {
    }

    /**
     * Inside PersistStateMiddleware, so a subject saved for the first time has an identity by now, and
     * outside RecordPaymentStatusMiddleware.
     */
    public static function priority(): int
    {
        return 75;
    }

    public function process(CommandInterface $command, Context $context, callable $next): Result
    {
        $result = $next($command, $context);

        $gatewayName = $context->gatewayName();

        if (null === $gatewayName || null === $result->transactionId || '' === $result->transactionId) {
            return $result;
        }

        $identity = SubjectIdentity::identityOf($context->subject(), $this->storages);

        if ($identity instanceof IdentityInterface && null !== SubjectIdentity::key($identity)) {
            $this->references->record(ReferenceNamespace::of($context), $result->transactionId, $gatewayName, $identity);
        }

        return $result;
    }
}
