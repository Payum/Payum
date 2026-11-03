<?php

declare(strict_types=1);

namespace Payum\Core\Bridge\Doctrine\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\JsonType;
use Doctrine\DBAL\Types\Type;
use JsonException;
use Payum\Core\Model\Identity;
use Payum\Core\Storage\IdentityInterface;
use Stringable;

/**
 * Stores a token's details as `{"class": ..., "id": ...}` JSON.
 */
final class IdentityType extends JsonType
{
    public const NAME = 'payum_identity';

    public static function register(): void
    {
        if (! Type::hasType(self::NAME)) {
            Type::addType(self::NAME, self::class);
        }
    }

    public static function toJson(IdentityInterface $identity): string
    {
        $id = $identity->getId();
        if ($id instanceof Stringable) {
            $id = (string) $id;
        }

        if (null !== $id && ! is_scalar($id)) {
            throw new ConversionException(sprintf('Could not convert the id of a "%s" to JSON: only scalar and Stringable ids can be stored.', $identity->getClass()));
        }

        return json_encode([
            'class' => $identity->getClass(),
            'id' => $id,
        ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    public static function fromJson(string $json): Identity
    {
        try {
            $data = json_decode($json, true, 2, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new ConversionException('Could not convert database value to "' . self::NAME . '": ' . $e->getMessage(), 0, $e);
        }

        if (! is_array($data) || ! isset($data['class']) || ! is_string($data['class']) || ! array_key_exists('id', $data)) {
            throw new ConversionException('Could not convert database value to "' . self::NAME . '": expected {"class": ..., "id": ...}, got ' . $json);
        }

        return new Identity($data['id'], $data['class']);
    }

    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        if (null === $value) {
            return null;
        }

        if (! $value instanceof IdentityInterface) {
            throw new ConversionException(sprintf('Could not convert a value of type "%s" to "%s": expected %s.', get_debug_type($value), self::NAME, IdentityInterface::class));
        }

        return self::toJson($value);
    }

    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?Identity
    {
        if (null === $value || '' === $value) {
            return null;
        }

        return self::fromJson(is_resource($value) ? (string) stream_get_contents($value) : $value);
    }

    public function getName(): string
    {
        return self::NAME;
    }

    /**
     * DBAL 3 tells this type apart from `json` by a column comment.
     */
    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }
}
