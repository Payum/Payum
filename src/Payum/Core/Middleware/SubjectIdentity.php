<?php

declare(strict_types=1);

namespace Payum\Core\Middleware;

use Doctrine\Persistence\Proxy;
use Payum\Core\Command\CommandInterface;
use Payum\Core\Model\SubjectInterface;
use Payum\Core\Registry\StorageRegistryInterface;
use Payum\Core\Result\Result;
use Payum\Core\Storage\IdentityInterface;
use Throwable;

/**
 * The stored payment or payout a command is about, read without loading it.
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
        $identity = $command->token()?->getDetails() ?? self::identify($command->subject(), $storages);

        if (! $identity instanceof IdentityInterface || null === $identity->getId() || '' === (string) $identity->getId()) {
            return null;
        }

        $class = $identity->getClass();

        if (is_a($class, Proxy::class, true)) {
            $class = (string) get_parent_class($class);
        }

        return $class . ':' . $identity->getId();
    }

    /**
     * What a storage does when asked to identify a model it never saved is not part of its contract: some
     * return no id, others throw.
     *
     * @param StorageRegistryInterface<object>|null $storages
     */
    private static function identify(?SubjectInterface $subject, ?StorageRegistryInterface $storages): ?IdentityInterface
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
}
