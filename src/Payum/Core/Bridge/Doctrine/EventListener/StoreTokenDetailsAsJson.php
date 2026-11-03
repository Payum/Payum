<?php

declare(strict_types=1);

namespace Payum\Core\Bridge\Doctrine\EventListener;

use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Payum\Core\Bridge\Doctrine\Types\IdentityType;
use Payum\Core\Model\Token;

/**
 * Maps the `details` of every `Payum\Core\Model\Token` entity to `payum_identity` (JSON) instead of `object`.
 *
 * Register it for the `loadClassMetadata` event after migrating the column, see docs/storages.md.
 */
final class StoreTokenDetailsAsJson
{
    public function loadClassMetadata(LoadClassMetadataEventArgs $event): void
    {
        $metadata = $event->getClassMetadata();

        if (! is_a($metadata->getName(), Token::class, true) || ! isset($metadata->fieldMappings['details'])) {
            return;
        }

        IdentityType::register();

        $mapping = &$metadata->fieldMappings['details'];
        // @phpstan-ignore function.impossibleType (ORM 2 keeps field mappings as arrays, ORM 3 as FieldMapping objects)
        if (is_array($mapping)) {
            $mapping['type'] = IdentityType::NAME;
        } else {
            $mapping->type = IdentityType::NAME;
        }
    }
}
