<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Reference;

use Payum\Core\Model\Identity;
use Payum\Core\Model\Payment;
use Payum\Core\Model\ReferenceRecord;
use Payum\Core\Reference\StorageReferenceIndex;
use Payum\Core\Storage\FilesystemStorage;
use PHPUnit\Framework\TestCase;

final class StorageReferenceIndexTest extends TestCase
{
    private string $dir;

    protected function setUp(): void
    {
        $this->dir = sys_get_temp_dir() . '/' . uniqid('payum-ref-', true);
        mkdir($this->dir);
    }

    protected function tearDown(): void
    {
        array_map(unlink(...), glob($this->dir . '/*') ?: []);
        rmdir($this->dir);
    }

    public function testShouldFindWhatWasRecorded(): void
    {
        $index = $this->index();
        $index->record('Acme', 'sub_1', 'acme', new Identity('7', Payment::class));

        $record = $index->find('Acme', 'sub_1');

        $this->assertNotNull($record);
        $this->assertSame('acme', $record->getGatewayName());
        $this->assertSame(Payment::class, $record->getSubjectClass());
        $this->assertSame('7', $record->getSubjectId());
    }

    public function testShouldNotFindAcrossNamespaces(): void
    {
        $index = $this->index();
        $index->record('Acme', 'sub_1', 'acme', new Identity('7', Payment::class));

        $this->assertNull($index->find('Other', 'sub_1'));
    }

    public function testShouldOverwriteAReferenceThatMovedToAnotherSubject(): void
    {
        $index = $this->index();
        $index->record('Acme', 'sub_1', 'acme', new Identity('7', Payment::class));
        $index->record('Acme', 'sub_1', 'acme', new Identity('8', Payment::class));

        $this->assertSame('8', $index->find('Acme', 'sub_1')?->getSubjectId());
    }

    public function testShouldKeepOneRowForARepeatedRecord(): void
    {
        $index = $this->index();
        $index->record('Acme', 'sub_1', 'acme', new Identity('7', Payment::class));
        $index->record('Acme', 'sub_1', 'acme', new Identity('7', Payment::class));

        $this->assertCount(1, glob($this->dir . '/*'));
    }

    private function index(): StorageReferenceIndex
    {
        return new StorageReferenceIndex(new FilesystemStorage($this->dir, ReferenceRecord::class, 'key'));
    }
}
