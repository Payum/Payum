<?php

declare(strict_types=1);

namespace Payum\Core\Middleware;

use Doctrine\Persistence\Proxy;
use Payum\Core\Command\CommandInterface;
use Payum\Core\Handler\Context;
use Payum\Core\Model\SubjectInterface;
use Payum\Core\Reference\ReferenceIndexInterface;
use Payum\Core\Reference\ReferenceNamespace;
use Payum\Core\Registry\StorageRegistryInterface;
use Payum\Core\Result\Result;
use Payum\Core\Storage\IdentityInterface;
use Throwable;

/**
 * The stored subject a command is about, such as a payment, payout, payment method or subscription, read
 * without loading it.
 *
 * @internal
 */
final class SubjectIdentity
{
    /**
     * @param CommandInterface<Result> $command
     * @param StorageRegistryInterface<object>|null $storages identifies a model handed to the command directly
     *
     * @return string|null `class:id`, or null when the subject was never saved
     */
    public static function of(CommandInterface $command, ?StorageRegistryInterface $storages): ?string
    {
        $identity = $command->token()?->getDetails() ?? self::identityOf($command->subject(), $storages);

        return $identity instanceof IdentityInterface ? self::key($identity) : null;
    }

    /**
     * @return string|null `class:id` with a proxy class resolved to its parent, or null when there is no id
     */
    public static function key(IdentityInterface $identity): ?string
    {
        if (null === $identity->getId() || '' === (string) $identity->getId()) {
            return null;
        }

        return self::className($identity) . ':' . $identity->getId();
    }

    /**
     * @return string the identity's class with a proxy class resolved to its parent
     */
    public static function className(IdentityInterface $identity): string
    {
        $class = $identity->getClass();

        return is_a($class, Proxy::class, true) ? (string) get_parent_class($class) : $class;
    }

    /**
     * What a storage does when asked to identify a model it never saved is not part of its contract: some
     * return no id, others throw.
     *
     * @param StorageRegistryInterface<object>|null $storages
     */
    public static function identityOf(?SubjectInterface $subject, ?StorageRegistryInterface $storages): ?IdentityInterface
    {
        if (! $subject instanceof SubjectInterface || ! $storages instanceof StorageRegistryInterface) {
            return null;
        }

        foreach ($storages->getStorages() as $storage) {
            if (! $storage->support($subject)) {
                continue;
            }

            try {
                return $storage->identify($subject);
            } catch (Throwable) {
                return null;
            }
        }

        return null;
    }

    /**
     * Indexes the PSP's reference for a saved subject so a later webhook naming it finds the subject.
     *
     * @param StorageRegistryInterface<object>|null $storages
     */
    public static function record(ReferenceIndexInterface $references, ?StorageRegistryInterface $storages, Context $context, SubjectInterface $subject, ?string $reference, string $gatewayName): void
    {
        if (null === $reference || '' === $reference) {
            return;
        }

        $identity = self::identityOf($subject, $storages);

        if ($identity instanceof IdentityInterface && null !== self::key($identity)) {
            $references->record(ReferenceNamespace::of($context), $reference, $gatewayName, $identity);
        }
    }
}
