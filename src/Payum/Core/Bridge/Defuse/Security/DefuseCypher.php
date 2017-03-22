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
    public function decrypt($value)
    {
        return Crypto::decrypt($value, $this->key);
    }

    /**
     * {@inheritdoc}
     */
    public function encrypt($value)
    {
        return Crypto::encrypt($value, $this->key);
    }
}