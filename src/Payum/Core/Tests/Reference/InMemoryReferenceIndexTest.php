<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Reference;

use Payum\Core\Model\Identity;
use Payum\Core\Model\Payment;
use Payum\Core\Reference\InMemoryReferenceIndex;
use PHPUnit\Framework\TestCase;

final class InMemoryReferenceIndexTest extends TestCase
{
    public function testShouldFindWhatWasRecorded(): void
    {
        $index = new InMemoryReferenceIndex();
        $index->record('Acme', 'sub_1', 'acme', new Identity('7', Payment::class));

        $record = $index->find('Acme', 'sub_1');

        $this->assertNotNull($record);
        $this->assertSame('acme', $record->getGatewayName());
        $this->assertSame(Payment::class, $record->getSubjectClass());
        $this->assertSame('7', $record->getSubjectId());
        $this->assertSame('7', $record->getIdentity()->getId());
        $this->assertSame(Payment::class, $record->getIdentity()->getClass());
    }

    public function testShouldNotFindAcrossNamespaces(): void
    {
        $index = new InMemoryReferenceIndex();
        $index->record('Acme', 'sub_1', 'acme', new Identity('7', Payment::class));

        $this->assertNull($index->find('Other', 'sub_1'));
    }

    public function testShouldOverwriteAReferenceThatMovedToAnotherSubject(): void
    {
        $index = new InMemoryReferenceIndex();
        $index->record('Acme', 'sub_1', 'acme', new Identity('7', Payment::class));
        $index->record('Acme', 'sub_1', 'acme', new Identity('8', Payment::class));

        $this->assertSame('8', $index->find('Acme', 'sub_1')?->getSubjectId());
    }
}
