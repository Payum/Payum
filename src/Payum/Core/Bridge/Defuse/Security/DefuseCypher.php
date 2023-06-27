<?php

namespace Payum\Core\Bridge\Defuse\Security;

use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;
use Payum\Core\Security\CypherInterface;

class DefuseCypher implements CypherInterface
{
    private string $key;

    public function __construct($secret)
    {
        $this->key = Key::loadFromAsciiSafeString($secret);
    }

    public function decrypt(string $value): string
    {
        return Crypto::decrypt($value, $this->key);
    }

    public function encrypt(string $value): string
    {
        return Crypto::encrypt($value, $this->key);
    }
}
