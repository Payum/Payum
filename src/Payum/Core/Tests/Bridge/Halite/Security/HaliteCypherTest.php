<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Bridge\Halite\Security;

use Defuse\Crypto\Exception\WrongKeyOrModifiedCiphertextException;
use Defuse\Crypto\Key;
use ParagonIE\Halite\Alerts\InvalidMessage;
use ParagonIE\Halite\KeyFactory;
use Payum\Core\Bridge\Defuse\Security\DefuseCypher;
use Payum\Core\Bridge\Halite\Security\HaliteCypher;
use Payum\Core\Model\GatewayConfig;
use PHPUnit\Framework\TestCase;

final class HaliteCypherTest extends TestCase
{
    public function testShouldEncryptAndDecryptValueWithAnExportedKey(): void
    {
        $cypher = new HaliteCypher(KeyFactory::export(KeyFactory::generateEncryptionKey())->getString());

        $cryptedValue = $cypher->encrypt('theValue');

        $this->assertNotSame('theValue', $cryptedValue);
        $this->assertSame('theValue', $cypher->decrypt($cryptedValue));
    }

    public function testShouldAcceptAKeyObject(): void
    {
        $cypher = new HaliteCypher(KeyFactory::generateEncryptionKey());

        $this->assertSame('theValue', $cypher->decrypt($cypher->encrypt('theValue')));
    }

    public function testShouldFailToDecryptWithAnotherKey(): void
    {
        $cryptedValue = (new HaliteCypher(KeyFactory::generateEncryptionKey()))->encrypt('theValue');

        $this->expectException(InvalidMessage::class);

        (new HaliteCypher(KeyFactory::generateEncryptionKey()))->decrypt($cryptedValue);
    }

    public function testShouldReEncryptAGatewayConfigEncryptedWithDefuse(): void
    {
        $defuse = new DefuseCypher(Key::createNewRandomKey()->saveToAsciiSafeString());
        $halite = new HaliteCypher(KeyFactory::generateEncryptionKey());

        $config = new GatewayConfig();
        $config->setConfig([
            'apiKey' => 'sk_test_123',
            'sandbox' => true,
        ]);
        $config->encrypt($defuse);

        $config->decrypt($defuse);
        $config->encrypt($halite);
        $config->decrypt($halite);

        $this->assertSame([
            'apiKey' => 'sk_test_123',
            'sandbox' => true,
            'encrypted' => true,
        ], $config->getConfig());

        $this->expectException(WrongKeyOrModifiedCiphertextException::class);

        $config->decrypt($defuse);
    }
}
