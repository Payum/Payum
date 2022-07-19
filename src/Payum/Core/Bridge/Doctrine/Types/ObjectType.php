<?php

namespace Payum\Core\Bridge\Doctrine\Types;

use Doctrine\ODM\MongoDB\Types\Type;
use LogicException;

/**
 * Used as a workaround till I (or you?) found out how to store object to mongo.
 *
 * More details:
 *     http://stackoverflow.com/questions/19196453/doctrine-can-i-use-mongo-hash-field-to-store-php-object
 *     http://stackoverflow.com/questions/19257660/proper-way-to-load-mongodb-hash-associated-array-mapping-when-not-using-annotati
 *
 * Here's a PR that proposed such type to core:
 *     https://github.com/doctrine/mongodb-odm/pull/696
 */
class ObjectType extends Type
{
    public function convertToDatabaseValue($value): string
    {
        return serialize($value);
    }

    public function convertToPHPValue($value)
    {
        if (null === $value) {
            return;
        }

        $value = (is_resource($value)) ? stream_get_contents($value) : $value;
        $val = unserialize($value);
        if (false === $val && 'b:0;' !== $value) {
            throw new LogicException('Conversion exception: ' . $value . '. ' . $this->getName());
        }

        return $val;
    }

    public function closureToMongo(): string
    {
        return '$return = serialize($value);';
    }

    public function closureToPHP(): string
    {
        return '$return = unserialize($value);';
    }
}
