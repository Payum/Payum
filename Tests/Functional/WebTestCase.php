<?php
namespace Payum\Bundle\PayumBundle\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as BaseWebTestCase;

abstract class WebTestCase extends BaseWebTestCase
{
    /**
     * @return string
     */
    public static function getKernelClass()
    {
        require_once __DIR__ . '/app/AppKernel.php';

        return 'AppKernel';
    }
}