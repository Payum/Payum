<?php
namespace Payum\Core\Tests\Bridge\Defuse\Security;

use Defuse\Crypto\Key;
use Payum\Core\Bridge\Defuse\Security\DefuseCypher;
use Payum\Core\Security\CypherInterface;

class DefuseCypherTest extends \PHPUnit_Framework_TestCase
{
    public function testShouldImplementCypherInterface()
    {
        $rc = new \ReflectionClass(DefuseCypher::class);

        $this->assertTrue($rc->implementsInterface(CypherInterface::class));
    }

    public function testShouldEncryptAndDecryptValue()
    {
        $secret = Key::createNewRandomKey()->saveToAsciiSafeString();

        $cypher = new DefuseCypher($secret);

        $cryptedValue = $cypher->encrypt('theValue');

        $this->assertNotSame('theValue', $cryptedValue);

        $decryptedValue = $cypher->decrypt($cryptedValue);

        $this->assertSame('theValue', $decryptedValue);
    }
}
