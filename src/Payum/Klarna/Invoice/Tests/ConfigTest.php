<?php
namespace Payum\Klarna\Invoice\Tests;

use Payum\Klarna\Invoice\Config;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    public function testShouldAllowGetDefaultPublicProperties()
    {
        $config = new Config();

        $this->assertSame(\KlarnaCountry::SE, $config->country);
        $this->assertSame(\KlarnaLanguage::SV, $config->language);
        $this->assertSame(\KlarnaCurrency::SEK, $config->currency);
        $this->assertSame(\Klarna::BETA, $config->mode);
        $this->assertSame(\Klarna::BETA, $config->mode);
        $this->assertSame('json', $config->pClassStorage);
        $this->assertSame('./pclasses.json', $config->pClassStoragePath);
    }

    public function testShouldAllowSetExpectedPublicProperties()
    {
        $config = new Config();

        $config->country = 'country';
        $config->eid = 'eid';
        $config->secret = 'secret';
        $config->language = 'lang';
        $config->currency = 'currency';
        $config->mode = 'mode';
        $config->pClassStorage = 'storage';
        $config->pClassStoragePath = 'storagePath';

        $this->assertSame('country', $config->country);
        $this->assertSame('eid', $config->eid);
        $this->assertSame('secret', $config->secret);
        $this->assertSame('lang', $config->language);
        $this->assertSame('currency', $config->currency);
        $this->assertSame('mode', $config->mode);
        $this->assertSame('storage', $config->pClassStorage);
        $this->assertSame('storagePath', $config->pClassStoragePath);
    }
}
