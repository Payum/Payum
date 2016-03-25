<?php
namespace Payum\Core\Tests;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\CoreGatewayFactory;
use Payum\Core\GatewayInterface;
use Payum\Core\HttpClientInterface;
use Payum\Core\Payum;
use Payum\Core\Registry\RegistryInterface;
use Payum\Core\Registry\SimpleRegistry;
use Payum\Core\Security\GenericTokenFactoryInterface;
use Payum\Core\Security\HttpRequestVerifierInterface;
use Payum\Core\Storage\StorageInterface;

trait SkipOnPhp7Trait
{
    /**
     * @before
     */
    public function skipTestsIfPhp7()
    {
        if (version_compare(PHP_VERSION, '7.0.0') >= 0) {
            $this->markTestSkipped('Mongo is not supported by php7');
        }
    }
}
