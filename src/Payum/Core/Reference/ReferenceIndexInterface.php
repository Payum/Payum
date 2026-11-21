<?php

declare(strict_types=1);

namespace Payum\Core\Reference;

use Payum\Core\Model\ReferenceRecord;
use Payum\Core\Storage\IdentityInterface;

/**
 * Finds the subject a PSP reference belongs to, for a notification that carries no token.
 */
interface ReferenceIndexInterface
{
    /**
     * Recording a reference again for the same subject changes nothing; for another subject it replaces
     * the earlier one.
     */
    public function record(string $namespace, string $reference, string $gatewayName, IdentityInterface $subject): void;

    public function find(string $namespace, string $reference): ?ReferenceRecord;
}
