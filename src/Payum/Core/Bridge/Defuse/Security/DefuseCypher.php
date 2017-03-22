<?php
namespace Payum\Core\Bridge\Defuse\Security;

use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;
use Payum\Core\Security\CypherInterface;

class DefuseCypher implements CypherInterface
{
    /**
     * @var string
     */
    private $key;

    /**
     * {@inheritdoc}
     */
    public function __construct($secret)
    {
        $this->key = Key::loadFromAsciiSafeString($secret);
    }

    /**
     * {@inheritdoc}
     */
    public function decrypt($encryptedValue)
    {
        return Crypto::decrypt($encryptedValue, $this->key);
    }

    /**
     * {@inheritdoc}
     */
    public function encrypt($rawValue)
    {
        return Crypto::encrypt($rawValue, $this->key);
    }
}