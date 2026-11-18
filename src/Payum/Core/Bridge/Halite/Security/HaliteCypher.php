<?php

declare(strict_types=1);

namespace Payum\Core\Bridge\Halite\Security;

use ParagonIE\Halite\KeyFactory;
use ParagonIE\Halite\Symmetric\Crypto;
use ParagonIE\Halite\Symmetric\EncryptionKey;
use ParagonIE\HiddenString\HiddenString;
use Payum\Core\Security\CypherInterface;

final class HaliteCypher implements CypherInterface
{
    private readonly EncryptionKey $key;

    /**
     * @param EncryptionKey|string $key a key, or the hex string KeyFactory::export() produces
     */
    public function __construct(EncryptionKey|string $key)
    {
        $this->key = $key instanceof EncryptionKey ? $key : KeyFactory::importEncryptionKey(new HiddenString($key));
    }

    public function decrypt($value): string
    {
        return Crypto::decrypt($value, $this->key)->getString();
    }

    public function encrypt($value): string
    {
        return Crypto::encrypt(new HiddenString($value), $this->key);
    }
}
