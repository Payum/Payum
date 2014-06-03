<?php
namespace Payum\Bundle\PayumBundle\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as BaseWebTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class WebTestCase extends BaseWebTestCase
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @var ContainerInterface
     */
    protected $container;

    protected function setUp()
    {
        parent::setUp();

        $this->client = static::createClient();
        $this->container = static::$kernel->getContainer();
    }

    /**
     * @return string
     */
    public static function getKernelClass()
    {
        require_once __DIR__ . '/app/AppKernel.php';

        return 'AppKernel';
    }
}