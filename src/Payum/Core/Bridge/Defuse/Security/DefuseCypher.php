<?php
namespace Payum\Core\Bridge\Defuse\Security;

use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;
use Payum\Core\Security\CypherInterface;

class DefuseCypher implements CypherInterface
{
    private string|Key $key;

    public function __construct($secret)
    {
        $this->key = Key::loadFromAsciiSafeString($secret);
    }

    /**
     * {@inheritdoc}
     */
    public function decrypt($value): string
    {
        return Crypto::decrypt($value, $this->key);
    }

    /**
     * {@inheritdoc}
     */
    public function encrypt($value): string
    {
        return Crypto::encrypt($value, $this->key);
    }
}