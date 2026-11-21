<?php

declare(strict_types=1);

namespace Payum\Core\Reference;

use Payum\Core\Model\ReferenceRecord;
use Payum\Core\Storage\IdentityInterface;

/**
 * Remembers references for as long as the PHP process lives. The default only so that nothing needs
 * configuring; a webhook arriving in another request finds nothing.
 */
final class InMemoryReferenceIndex implements ReferenceIndexInterface
{
    /**
     * @var array<string, ReferenceRecord>
     */
    private array $records = [];

    public function record(string $namespace, string $reference, string $gatewayName, IdentityInterface $subject): void
    {
        $record = new ReferenceRecord();
        $record->setKey(self::key($namespace, $reference));
        $record->setNamespace($namespace);
        $record->setReference($reference);
        $record->setGatewayName($gatewayName);
        $record->setSubjectClass($subject->getClass());
        $record->setSubjectId((string) $subject->getId());

        $this->records[$record->getKey()] = $record;
    }

    public function find(string $namespace, string $reference): ?ReferenceRecord
    {
        return $this->records[self::key($namespace, $reference)] ?? null;
    }

    private static function key(string $namespace, string $reference): string
    {
        return hash('sha256', $namespace . "\0" . $reference);
    }
}
